<?php

declare(strict_types=1);

namespace Drupal\superbar\Controller;

use Drupal\Component\Serialization\Json;
use Drupal\content_moderation\ModerationInformationInterface;
use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
use Drupal\Core\Cache\CacheableJsonResponse;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Http\Exception\CacheableBadRequestHttpException;
use Drupal\Core\Http\Exception\CacheableNotFoundHttpException;
use Drupal\Core\Menu\LocalTaskManagerInterface;
use Drupal\Core\Plugin\Context\ContextRepositoryInterface;
use Drupal\Core\Render\Element;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Routing\StackedRouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\superbar\ModerationState;
use Drupal\user\Entity\Role;
use Drupal\user\UserInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Backend for Superbar.
 */
final class Superbar implements ContainerInjectionInterface {

  /**
   * Constructs a Superbar.
   */
  final public function __construct(
    private string $environment,
    private HttpKernelInterface $httpKernel,
    private RequestStack $requestStack,
    private AccountInterface $currentUser,
    private StackedRouteMatchInterface $currentRouteMatch,
    private EntityTypeManagerInterface $entityTypeManager,
    private LocalTaskManagerInterface $localTaskManager,
    private BreadcrumbBuilderInterface $breadcrumbBuilder,
    private ContextRepositoryInterface $contextRepository,
    private RendererInterface $renderer,
    private ?ModerationInformationInterface $moderationInformation,
  ) {
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): static {
    return new static(
      // @phpstan-ignore-next-line
      $container->getParameter('kernel.environment'),
      $container->get('http_kernel'),
      $container->get('request_stack'),
      $container->get('current_user'),
      $container->get('current_route_match'),
      $container->get('entity_type.manager'),
      $container->get('plugin.manager.menu.local_task'),
      $container->get('breadcrumb'),
      $container->get('context.repository'),
      $container->get('renderer'),
      $container->get('content_moderation.moderation_information', ContainerInterface::NULL_ON_INVALID_REFERENCE),
    );
  }

  /**
   * Controller response.
   */
  public function __invoke(Request $request): Response|array {
    $cacheability = (new CacheableMetadata())->setCacheContexts([
      'url',
      'url.query_args',
    ]);

    $path = $request->query->get('path');
    if (is_string($path) === FALSE || strlen($path) === 0) {
      throw new CacheableBadRequestHttpException($cacheability);
    }

    $cacheability->addCacheContexts(['user']);

    $response = (new CacheableJsonResponse())
      ->setMaxAge(0)
      ->addCacheableDependency($cacheability);

    $data = [
      'currentUser' => [
        'isAuthenticated' => $this->currentUser->isAuthenticated(),
        'displayName' => $this->currentUser->getDisplayName(),
        'view' => NULL,
        'edit' => NULL,
        'role' => NULL,
      ],
      'site' => [
        'environment' => [
          'id' => $this->environment,
          'colorPrimary' => '#ff6',
          'colorSecondary' => '#111',
        ],
      ],
      'route' => [
        'entityType' => NULL,
      ],
      'pathLinks' => [],
      'breadcrumb' => [],
    ];

    $userStorage = $this->entityTypeManager->getStorage('user');
    if ($this->currentUser->isAuthenticated() && ($user = $userStorage->load($this->currentUser->id())) instanceof UserInterface) {
      /** @var array<string, \Drupal\Core\Url> $urls */
      $urls = [
        'view' => $user->toUrl(),
        'edit' => $user->toUrl('edit-form'),
      ];
      foreach ($urls as $k => $url) {
        if ($url->access($this->currentUser)) {
          $generatedUrl = $url->toString(TRUE);
          $response->addCacheableDependency($generatedUrl);
          $data['currentUser'][$k] = $generatedUrl->getGeneratedUrl();
        }
      }

      // Get the first non- built-in role by weight.
      $roleIds = $user->getRoles();
      if (count($roleIds) > 0) {
        /** @var \Drupal\user\RoleStorageInterface $roleStorage */
        $roleStorage = $this->entityTypeManager->getStorage('user_role');
        $roles = $roleStorage->loadMultiple($user->getRoles());
        if (count($roles) > 0) {
          uasort($roles, Role::class . '::sort');
          $role = array_pop($roles);
          $data['currentUser']['role'] = $role->label();
        }
      }
    }

    // Take most parts of the request, change path and accept.
    $currentRequest = $this->requestStack->getCurrentRequest() ?? throw new \LogicException();

    $subRequest = Request::create(
      $path,
      Request::METHOD_GET,
      cookies: $currentRequest->cookies->all(),
      server: $currentRequest->server->all(),
    );
    $subRequest->headers = (clone $currentRequest->headers);
    if ($currentRequest->hasSession()) {
      $subRequest->setSession($currentRequest->getSession());
    }

    $subResponse = $this->httpKernel->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
    if (200 !== $subResponse->getStatusCode()) {
      $this->requestStack->pop();
      throw new CacheableNotFoundHttpException($response->getCacheableMetadata());
    }

    $new = $this->currentRouteMatch->getRouteMatchFromRequest($subRequest) ?? throw new \LogicException();
    $newRoutename = $new->getRouteName() ?? throw new \LogicException();
    ['tabs' => $tabs, 'cacheability' => $taskCacheability] = $this->localTaskManager->getLocalTasks($newRoutename, level: 0);
    if ($taskCacheability !== NULL) {
      $response->addCacheableDependency($taskCacheability);
    }

    /** @var array<string, array{'#link': array{url: \Drupal\Core\Url}}> $visibleTabs */
    $visibleTabs = count(Element::getVisibleChildren($tabs)) > 1 ? $tabs : [];

    // Re-order by weight.
    Element::children($visibleTabs, sort: TRUE);
    unset($visibleTabs['#sorted']);

    $data['pathLinks'] = [];
    foreach ($visibleTabs as ['#link' => $link]) {
      ['url' => $url, 'title' => $title] = $link;
      if ($url->access($this->currentUser) === FALSE) {
        continue;
      }

      $data['pathLinks'][] = [
        'title' => $title,
        'path' => $url->toString(),
      ];
    }

    // Return the entity type this path represents.
    // @todo should we only allow responses for entities? Currently allows any path.

    $contextName = '@entity_route_context.entity_route_context:canonical_entity';
    $contexts = $this->contextRepository->getRuntimeContexts([$contextName]);
    /** @var \Drupal\Core\Entity\EntityInterface|null $entity */
    $entity = isset($contexts[$contextName]) ? $contexts[$contextName]->getContextValue() : NULL;
    if ($entity !== NULL) {
      $data['route']['entityType'] = $entity->getEntityTypeId();
      $data['route']['entityTypeLabel'] = $entity->getEntityType()->getLabel();
      $data['route']['entityTypeLabelSingular'] = $entity->getEntityType()->getSingularLabel();
      $data['route']['entityId'] = is_numeric($entity->id()) ? (int) $entity->id() : $entity->id();
      $data['route']['entityLabel'] = $entity->label();

      if ($entity instanceof ContentEntityInterface && $this->moderationInformation !== NULL) {
        $moderationState = ModerationState::create($this->moderationInformation, $entity);
        $data['route']['currentStateLabel'] = $moderationState->getModerationStateLabel();
      }
    }

    $breadcrumb = $this->breadcrumbBuilder->build($new);
    foreach ($breadcrumb->getLinks() as $link) {
      $url = $link->getUrl();
      if ($url->access($this->currentUser) === FALSE) {
        continue;
      }

      $title = $link->getText();
      if (is_array($title)) {
        $this->renderer->renderPlain($title);
      }
      $data['breadcrumb'][] = [
        'title' => (string) $title,
        'path' => $url->toString(),
      ];
    }

    $this->requestStack->pop();
    return $response->setJson(Json::encode($data));
  }

}
