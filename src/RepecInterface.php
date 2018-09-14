<?php

namespace Drupal\repec;

use Drupal\Core\Entity\ContentEntityInterface;

/**
 * Interface RepecInterface.
 *
 * @todo refactor in several interfaces/services for a better
 * separation of concern.
 */
interface RepecInterface {

  const TEMPLATE_ARCHIVE = 'arch';
  const TEMPLATE_SERIES = 'seri';

  const SERIES_WORKING_PAPER = 'wpaper';

  /**
   * Returns the archive directory.
   *
   * @return string
   *   Directory from the public:// file system.
   */
  public function getArchiveDirectory();

  /**
   * Initializes the RePEc directory/file structure based on the configuration.
   *
   * Creates the archive code directory, creates the archive and series
   * templates, iterates through each bundle to create the entity templates.
   */
  public function initializeTemplates();

  /**
   * Maps the archive template to the system wide configuration.
   *
   * @return array
   *   RDF template.
   */
  public function getArchiveTemplate();

  /**
   * Maps the series template to the bundle(s) configuration.
   *
   * @return array
   *   RDF template.
   */
  public function getSeriesTemplate();

  /**
   * Maps a template to an entity based on its bundle configuration.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity that is the subject of the template.
   *
   * @return array
   *   RDF template.
   */
  public function getPaperTemplate(ContentEntityInterface $entity);

  /**
   * Maps a template to an entity based on its bundle configuration.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity that is the subject of the template.
   *
   * @return array
   *   RDF template.
   */
  public function getEntityTemplate(ContentEntityInterface $entity);

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
   * Creates a template or re-creates it if it exists.
   *
   * The scope of this template is site wide: generic templates like
   * archive or series.
   *
   * @param array $template
   *   RDF template.
   * @param string $templateType
   *   Template type.
   */
  public function createTemplate(array $template, $templateType);

  /**
   * Creates a series template.
   */
  public function createSeriesTemplate();

  /**
   * Creates a RePEc template.
   *
   * The scope of this template is per entity, so meant tho be stored in
   * a sub-directory e.g. aaa/wpaper.
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
   * The scope of this template is per entity, so meant tho be stored in
   * a sub-directory e.g. aaa/wpaper.
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
   * The scope of this template is per entity, so meant tho be stored in
   * a sub-directory e.g. aaa/wpaper.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity that is the subject of the template.
   */
  public function deleteEntityTemplate(ContentEntityInterface $entity);

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
   * Checks if an entity type and bundle is RePEc enabled.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity that is the subject of the template.
   *
   * @return bool
   *   Is the entity bundle enabled for RePEc.
   */
  public function isBundleEnabled(ContentEntityInterface $entity);

  /**
   * Checks if an entity can be shared with RePEc.
   *
   * This is a wrapper for various checks depending on the several possible
   * conditions to share a template: published, per entity configuration,
   * content access, ...
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity that is the subject of the template.
   *
   * @return bool
   *   Is the entity be shareable on RePEc.
   */
  public function isEntityShareable(ContentEntityInterface $entity);

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
