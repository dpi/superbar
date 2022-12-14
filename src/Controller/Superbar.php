<?php

declare(strict_types=1);

namespace Drupal\superbar\Controller;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Cache\CacheableJsonResponse;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Drupal\node\Entity\Node;
use Drupal\user\UserInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Backend for Superbar.
 */
final class Superbar implements ContainerInjectionInterface {

  /**
   * Constructs a Superbar.
   */
  final public function __construct(
    private AccountInterface $currentUser,
    private EntityTypeManagerInterface $entityTypeManager,
    private string $environment,
  ) {
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): static {
    return new static(
      $container->get('current_user'),
      $container->get('entity_type.manager'),
      // @phpstan-ignore-next-line
      $container->getParameter('kernel.environment'),
    );
  }

  /**
   * Controller response.
   */
  public function __invoke(Request $request): Response|array {
    $response = (new CacheableJsonResponse())
      ->setMaxAge(0)
      ->addCacheableDependency(((new CacheableMetadata())->setCacheContexts([
        'user',
        'url.query_args',
      ])));

    $data = [
      'currentUser' => [
        'isAuthenticated' => $this->currentUser->isAuthenticated(),
        'displayName' => $this->currentUser->getDisplayName(),
        'view' => NULL,
        'edit' => NULL,
      ],
      'site' => [
        'environment' => [
          'id' => $this->environment,
          'colorPrimary' => '#ff6',
          'colorSecondary' => '#111',
        ],
      ],
      'pathLinks' => [],
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
    }

    $path = $request->query->get('path');
    if (is_string($path)) {
      // Hardcode this for now... Very unsafe!
      if ($path === '/home') {
        $node = Url::fromUri('internal:' . $path);
        [1 => $nid] = explode('/' , $node->getInternalPath());
        $node = Node::load($nid) ?? throw new \LogicException('Explosion!');
        foreach (array_keys($node->getEntityType()->getLinkTemplates()) as $linkTemplate) {
          $url = $node->toUrl($linkTemplate);
          if ($url->access($this->currentUser)) {
            $generatedUrl = $url->toString(TRUE);
            $response->addCacheableDependency($generatedUrl);
            $generated = $generatedUrl->getGeneratedUrl();
            $data['pathLinks'][$generated] = [
              'title' => ucwords(str_replace(['-', '_'], ' ', $linkTemplate)),
              'path' => $generated,
            ];
          }
        }
      }
    }

    // Clean keys as these were used for de-duplication.
    $data['pathLinks'] = array_values($data['pathLinks']);

    return $response->setJson(Json::encode($data));
  }

}
