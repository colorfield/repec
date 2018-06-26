<?php

namespace Drupal\repec;

use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Class Repec.
 */
class Repec implements RepecInterface {

  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Drupal\Core\File\FileSystemInterface definition.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * Drupal\Core\Config\ConfigFactoryInterface definition.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * System wide settings.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  private $settings;

  /**
   * Constructs a new Repec object.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, FileSystemInterface $file_system, ConfigFactoryInterface $config_factory) {
    $this->entityTypeManager = $entity_type_manager;
    $this->fileSystem = $file_system;
    $this->configFactory = $config_factory;
    $this->settings = $this->configFactory->get('repec.settings');
  }

  /**
   * {@inheritdoc}
   */
  public function createEntityTemplate(ContentEntityInterface $entity, $templateType) {
    // TODO: implement.
  }

  /**
   * {@inheritdoc}
   */
  public function updateEntityTemplate(ContentEntityInterface $entity, $templateType) {
    // TODO: implement.
  }

  /**
   * {@inheritdoc}
   */
  public function deleteEntityTemplate(ContentEntityInterface $entity, $templateType) {
    // TODO: implement.
  }

  /**
   * {@inheritdoc}
   */
  public function availableSeries() {
    return [
      // The series is subject to be extended
      // but currently limited to wpaper.
      RepecInterface::SERIES_WORKING_PAPER => t('Paper series'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getTemplateFields($templateType) {
    // @todo extend to other templates via a factory
    $result = [];
    switch ($templateType) {
      case RepecInterface::SERIES_WORKING_PAPER:
        $result = [
          'author_name' => t('Author-Name'),
          'abstract' => t('Abstract'),
          'creation_date' => t('Creation-Date'),
          'file_url' => t('File-URL'),
          'keywords' => t('Keywords'),
        ];
        break;
    }
    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function isBundleEnabled(ContentEntityInterface $entity) {
    return $this->getEntityBundleSettings('enabled', $entity->getEntityTypeId(), $entity->bundle());
  }

  /**
   * Returns a list of enabled entity types.
   *
   * Example: if entity_type_id is node_type, returns the enabled content types.
   *
   * @param string $entity_type_id
   *   The entity type (e.g. node_type);.
   *
   * @return array
   *   List of enabled entity types.
   */
  private function getEnabledEntityTypeBundles($entity_type_id) {
    $result = [];
    try {
      $entityTypes = $this->entityTypeManager->getStorage($entity_type_id)->loadMultiple();
      foreach ($entityTypes as $entityType) {
        // @todo check enabled
        $result[] = $entityType->id();
      }
    }
    catch (InvalidPluginDefinitionException $exception) {
      \Drupal::messenger()->addError($exception->getMessage());
    }
    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function getEntityBundleSettings($setting, $entity_type_id, $bundle) {
    $config = $this->configFactory->getEditable('repec.settings');
    $settings = unserialize($config->get('repec_bundle.' . $entity_type_id . '.' . $bundle));
    if (empty($settings)) {
      $settings = [];
    }
    $settings += $this->getEntityBundleSettingDefaults();

    if ($setting == 'all') {
      return $settings;
    }
    return isset($settings[$setting]) ? $settings[$setting] : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function setEntityBundleSettings(array $settings, $entity_type_id, $bundle) {
    $config = \Drupal::configFactory()->getEditable('repec.settings');
    // Do not store default values.
    foreach ($this->getEntityBundleSettingDefaults() as $setting => $default_value) {
      if (isset($settings[$setting]) && $settings[$setting] == $default_value) {
        unset($settings[$setting]);
      }
    }
    $config->set('repec_bundle.' . $entity_type_id . '.' . $bundle, serialize($settings));
    $config->save();
  }

  /**
   * {@inheritdoc}
   */
  public function availableEntityBundleSettings() {
    return [
      'enabled',
      'serie_type',
      'serie_name',
      'serie_directory',
      'author_name',
      'abstract',
      'creation_date',
      'file_url',
      'keywords',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getEntityBundleSettingDefaults() {
    $defaults = [];
    $defaults['enabled'] = FALSE;
    $defaults['serie_type'] = '';
    $defaults['serie_name'] = '';
    $defaults['serie_directory'] = '';
    $defaults['author_name'] = '';
    $defaults['abstract'] = '';
    $defaults['creation_date'] = '';
    $defaults['file_url'] = '';
    $defaults['keywords'] = '';
    return $defaults;
  }

}
