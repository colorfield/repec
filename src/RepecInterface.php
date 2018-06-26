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
  public function isBundleEnabled(ContentEntityInterface $entity);

  /**
   * Returns RePEc's settings for an entity type bundle.
   *
   * @param string $setting
   *   One of the repec_available_entity_bundle_settings(), e.g. 'enabled'.
   *   If 'all' is passed, all available settings are returned.
   * @param string $entity_type_id
   *   The id of the entity type to return settings for.
   * @param string $bundle
   *   The id of the bundle to return settings for.
   *
   * @return string|array
   *   The value of the given setting or an array of all settings.
   */
  public function getEntityBundleSettings($setting, $entity_type_id, $bundle);

  /**
   * Saves RePEc's settings of an entity type bundle.
   *
   * @param array $settings
   *   The repec_available_entity_bundle_settings().
   * @param string $entity_type_id
   *   The id of the entity type to set the settings for.
   * @param string $bundle
   *   The id of the bundle to set the settings for.
   */
  public function setEntityBundleSettings(array $settings, $entity_type_id, $bundle);

  /**
   * Returns RePEc's entity type bundle available settings.
   *
   * @return array
   *   List of entity bundle available settings.
   */
  public function availableEntityBundleSettings();

  /**
   * Defines default values for RePEc settings.
   *
   * @return array
   *   List of entity bundle default settings.
   */
  public function getEntityBundleSettingDefaults();

}
