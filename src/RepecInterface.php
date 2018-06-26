<?php

namespace Drupal\repec;

use Drupal\Core\Entity\ContentEntityInterface;

/**
 * Interface RepecInterface.
 */
interface RepecInterface {

  const SERIES_WORKING_PAPER = 'wpaper';

  /**
   * Creates a RePEc template.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity that is the subject of the template.
   * @param int $templateType
   *   The template type.
   */
  public function createEntityTemplate(ContentEntityInterface $entity, $templateType);

  /**
   * Updates a RePEc template.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity that is the subject of the template.
   * @param int $templateType
   *   The template type.
   */
  public function updateEntityTemplate(ContentEntityInterface $entity, $templateType);

  /**
   * Removes a RePEc template.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity that is the subject of the template.
   * @param int $templateType
   *   The template type.
   */
  public function deleteEntityTemplate(ContentEntityInterface $entity, $templateType);

  /**
   * Get RePEc series.
   *
   * @see https://ideas.repec.org/t/seritemplate.html
   * @see https://ideas.repec.org/t/rdfintro.html
   *
   * @return array
   *   List of RePEc series template.
   */
  public function availableSeries();

  /**
   * Returns the RePEc template fields for a template type.
   *
   * @param string $templateType
   *   The template type.
   *
   * @return array
   *   Key value indexed template for RePEc fields.
   */
  public function getTemplateFields($templateType);

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
