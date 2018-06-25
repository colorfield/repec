<?php

namespace Drupal\repec;

use Drupal\Core\Entity\ContentEntityInterface;

/**
 * Interface RepecInterface.
 */
interface RepecInterface {

  const TEMPLATE_WORKING_PAPER = 0;

  /**
   * Creates a RePEc template.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity that is the subject of the template.
   * @param int $templateType
   *   The template type.
   */
  public function createTemplate(ContentEntityInterface $entity, $templateType);

  /**
   * Updates a RePEc template.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity that is the subject of the template.
   * @param int $templateType
   *   The template type.
   */
  public function updateTemplate(ContentEntityInterface $entity, $templateType);

  /**
   * Removes a RePEc template.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity that is the subject of the template.
   * @param int $templateType
   *   The template type.
   */
  public function deleteTemplate(ContentEntityInterface $entity, $templateType);

  /**
   * Checks if an entity type and bundle is RePEc enabled.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity that is the subject of the template.
   *
   * @return bool
   *   Is the entity RePEc enabled.
   */
  public function isEnabled(ContentEntityInterface $entity);

}
