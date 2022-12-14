<?php

declare(strict_types=1);

namespace Drupal\superbar;

use Drupal\content_moderation\ModerationInformationInterface;
use Drupal\content_moderation\Plugin\Field\ModerationStateFieldItemList;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\workflows\WorkflowInterface;

/**
 * Moderation state.
 */
final class ModerationState {

  /**
   * Constructs a ModerationState.
   */
  final private function __construct(
    private ModerationInformationInterface $moderationInformation,
    private ContentEntityInterface $entity,
  ) {
  }

  /**
   * Creates a ModerationState.
   */
  final static public function create(
    ModerationInformationInterface $moderationInformation,
    ContentEntityInterface $entity,
  ): static {
    return new static($moderationInformation, $entity);
  }

  /**
   * Get the moderation state label.
   *
   * @return string|null
   *   The moderation state label, or null if the state does not exist
   */
  public function getModerationStateLabel(): ?string {
    if ($this->entity->hasField('moderation_state') === FALSE) {
      return NULL;
    }

    $fieldList = $this->entity->get('moderation_state');
    assert($fieldList instanceof ModerationStateFieldItemList);
    /** @var string|null $moderationState */
    $moderationState = $fieldList->value ?? NULL;

    return ($this->getStatesFromContentModerationField()[$moderationState] ?? NULL)?->label();
  }

  /**
   * Get the allowed workflow states for this entity.
   *
   * The keys are often more useful than the values of this method.
   *
   * @return \Drupal\workflows\StateInterface[]
   *   The allowed workflow states for this entity keyed by state ID
   */
  protected function getStatesFromContentModerationField(): array {
    return $this->getWorkflowFromContentModerationField()?->getTypePlugin()?->getStates() ?? [];
  }

  /**
   * Get the workflow that applies to this entity.
   *
   * @return \Drupal\workflows\WorkflowInterface|null
   *   An entity, or NULL if no workflow applies to this entity
   */
  protected function getWorkflowFromContentModerationField(): ?WorkflowInterface {
    return $this->moderationInformation->getWorkflowForEntity($this->entity);
  }

}
