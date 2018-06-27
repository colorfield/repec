<?php

namespace Drupal\repec;

use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\file\Entity\File;

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
  public function initializeTemplates() {
    $basePath = $this->settings->get('base_path');
    if (empty($basePath)) {
      \Drupal::messenger()->addError(t('The base path cannot be empty.'));
      return;
    }

    $archiveDirectory = $this->getArchiveDirectory();
    if (!empty($archiveDirectory) &&
      file_prepare_directory($archiveDirectory, FILE_CREATE_DIRECTORY)) {
      // Remove all files of type .rdf.
      // @todo use Drupal file system unlink
      $files = glob($this->getArchiveDirectory() . '/*.rdf');
      foreach ($files as $file) {
        if (is_file($file)) {
          unlink($file);
        }
      }

      $this->createArchiveTemplate();
      $this->createSeriesTemplate();

      // @todo extend to other entity types
      foreach ($this->getEnabledEntityTypeBundles('node_type') as $nodeType) {
        // @todo for each content type, create entity templates.
      }

    }
    else {
      \Drupal::messenger()->addError(t('Directory @path could not be created.', [
        '@path' => $basePath,
      ]));
    }

  }

  /**
   * {@inheritdoc}
   */
  public function getArchiveTemplate() {
    // @todo use hook_repec_archive_mapping
    $url = $this->settings->get('provider_homepage');
    $url .= '/' . $this->settings->get('base_path');
    $url .= '/' . $this->settings->get('archive_code');
    return [
      [
        'attribute' => 'Template-type',
        'value' => 'ReDIF-Archive 1.0',
      ],
      [
        'attribute' => 'Handle',
        'value' => 'RePEc:' . $this->settings->get('archive_code'),
      ],
      [
        'attribute' => 'Name',
        'value' => $this->settings->get('provider_name'),
      ],
      [
        'attribute' => 'Maintainer-Name',
        'value' => $this->settings->get('maintainer_name'),
      ],
      [
        'attribute' => 'Maintainer-Email',
        'value' => $this->settings->get('maintainer_email'),
      ],
      [
        'attribute' => 'Description',
        // @todo review 'publications'
        'value' => 'This archive collects publications from ' . $this->settings->get('provider_name'),
      ],
      [
        'attribute' => 'URL',
        'value' => $url,
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getSeriesTemplate() {
    // @todo use hook_repec_series_mapping
    return [
      [
        'attribute' => 'Template-type',
        'value' => 'ReDIF-Series 1.0',
      ],
      [
        'attribute' => 'Name',
        // @todo get from bundle series configuration.
        'value' => 'Working Paper',
      ],
      [
        'attribute' => 'Provider-Name',
        'value' => $this->settings->get('provider_name'),
      ],
      [
        'attribute' => 'Provider-Homepage',
        'value' => $this->settings->get('provider_homepage'),
      ],
      [
        'attribute' => 'Provider-Institution',
        'value' => $this->settings->get('provider_institution'),
      ],
      [
        'attribute' => 'Maintainer-Name',
        'value' => $this->settings->get('maintainer_name'),
      ],
      [
        'attribute' => 'Maintainer-Email',
        'value' => $this->settings->get('maintainer_email'),
      ],
      [
        'attribute' => 'Type',
        // @todo get from bundle series configuration.
        'value' => 'ReDIF-Paper',
      ],
      [
        'attribute' => 'Handle',
        'value' => 'RePEc:' . $this->settings->get('archive_code') . ':wpaper',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getPaperTemplate(ContentEntityInterface $entity) {
    $result = [
      [
        'attribute' => 'Template-Type',
        'value' => 'ReDIF-Paper 1.0',
      ],
      [
        'attribute' => 'Title',
        'value' => $entity->label(),
      ],
      [
        'attribute' => 'Number',
        // Entity id cannot be used here as there could be
        // probably several entity types in a further release.
        'value' => $entity->uuid(),
      ],
      [
        'attribute' => 'Handle',
        // @todo review unicity of node id for a shared series within several entity types.
        'value' => 'RePEc:' . $this->settings->get('archive_code') . ':wpaper:' . $entity->id(),
      ],
    ];
    $templateFields = $this->getTemplateFields(RepecInterface::SERIES_WORKING_PAPER);
    foreach ($templateFields as $attributeKey => $attributeName) {
      foreach ($this->getFieldValues($entity, $attributeKey, $attributeName->render()) as $fieldValue) {
        $result[] = $fieldValue;
      }
    }
    return $result;
  }

  /**
   * Returns the archive directory.
   *
   * @return string
   *   Directory from the public:// file system.
   */
  private function getArchiveDirectory() {
    // @todo check config
    $basePath = $this->settings->get('base_path');
    $archiveCode = $this->settings->get('archive_code');
    $result = 'public://' . $basePath . '/' . $archiveCode . '/';
    return $result;
  }

  /**
   * Gets the value of a field based on a RePEC attribute.
   *
   * The attribute / field mapping is done via the entity type configuration.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity to get the field value from.
   * @param string $attribute_key
   *   The attribute key that is mapped to the field for the entity bundle.
   * @param string $attribute_name
   *   The RDF attribute name used in the template.
   *
   * @return array
   *   The field values to be used in the RDF template.
   */
  private function getFieldValues(ContentEntityInterface $entity, $attribute_key, $attribute_name) {
    $result = [];
    $fieldValue = $this->getFieldValueFromAttribute($entity, $attribute_key);
    switch ($attribute_key) {
      // Files need to append the File-Format and is single valued
      // so it is limited to the first one.
      case 'file_url':
        $result = $this->getFileAttributes($fieldValue);
        break;

      // Authors can be multiple.
      case 'author_name':
        $result[] = [
          'attribute' => $attribute_name,
          'value' => $this->getDefaultAttributeValue($fieldValue),
        ];
        break;

      // Keywords can be multiple
      // and are loaded from the taxonomy.
      case 'keywords':
        $result[] = [
          'attribute' => $attribute_name,
          'value' => $this->getDefaultAttributeValue($fieldValue),
        ];
        break;

      // @todo creation date fallback to entity created date
      // @todo date format
      default:
        // Default to single valued attribute mapping.
        $result[] = [
          'attribute' => $attribute_name,
          'value' => $this->getDefaultAttributeValue($fieldValue),
        ];
        break;
    }
    return $result;
  }

  /**
   * Get the entity field value for a RePEc attribute.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity that will be used to get its bundle configuration.
   * @param string $attribute_key
   *   The RePEc attribute that is mapped to the field.
   *
   * @return array
   *   Entity field value.
   */
  private function getFieldValueFromAttribute(ContentEntityInterface $entity, $attribute_key) {
    $result = [];
    $fieldName = $this->getEntityBundleSettings($attribute_key, $entity->getEntityTypeId(), $entity->bundle());
    if ($entity->hasField($fieldName)) {
      $result = $entity->get($fieldName)->getValue();
    }
    return $result;
  }

  /**
   * Get a single valued attribute.
   *
   * @param array $fieldValue
   *   Entity field value.
   *
   * @return string
   *   Attribute value.
   */
  private function getDefaultAttributeValue(array $fieldValue) {
    return empty($fieldValue[0]['value']) ? '' : $fieldValue[0]['value'];
  }

  /**
   * Get a RePEc attribute/value pairs for an entity file field value.
   *
   * @param array $fieldValue
   *   Entity field value.
   *
   * @return array
   *   List of attributes/values for a RePEc file.
   */
  private function getFileAttributes(array $fieldValue) {
    $result = [];
    if (!empty($fieldValue[0]['target_id'])) {
      $file = File::load($fieldValue[0]['target_id']);
      $uri = $file->getFileUri();
      $url = str_replace(' ', '%20', file_create_url($uri));
      $result[] = [
        'attribute' => 'File-URL',
        'value' => $url,
      ];
      $result[] = [
        'attribute' => 'File-Format',
        'value' => ucfirst($file->getMimeType()),
      ];
    }
    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function getTemplateFields($templateType) {
    // @todo extend to other templates via a factory
    $result = [];
    switch ($templateType) {
      // @todo this is a port of the Drupal 7 module review paper template
      // as there are many more fields:
      // https://ideas.repec.org/t/papertemplate.html
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
  public function getEntityTemplate(ContentEntityInterface $entity) {
    // @todo review usage of RDF module.
    // @todo implement and refactor with getPaperTemplate().
  }

  /**
   * Creates the archive template.
   */
  private function createArchiveTemplate() {
    $template = $this->getArchiveTemplate();
    $this->createTemplate($template, RepecInterface::TEMPLATE_ARCHIVE);
  }

  /**
   * {@inheritdoc}
   */
  public function createSeriesTemplate() {
    $template = $this->getSeriesTemplate();
    $this->createTemplate($template, RepecInterface::TEMPLATE_SERIES);
  }

  /**
   * {@inheritdoc}
   */
  public function createTemplate(array $template, $templateType) {
    $directory = $this->getArchiveDirectory();
    $fileName = $this->settings->get('archive_code') . $templateType . '.rdf';
    $content = '';
    foreach ($template as $item) {
      $content .= $item['attribute'] . ': ' . $item['value'] . "\n";
    }

    if (!file_put_contents($directory . '/' . $fileName, $content)) {
      \Drupal::messenger()->addError(t('File @file_name could not be created', [
        '@file_name' => $fileName,
      ]));
    }
  }

  /**
   * Maps the series fields with the node fields to create the template file.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity that is the subject of the mapping.
   */
  private function createPaperTemplate(ContentEntityInterface $entity) {
    $template = $this->getPaperTemplate($entity);
    $serieDirectoryConfig = $this->getEntityBundleSettings('serie_directory', $entity->getEntityTypeId(), $entity->bundle());
    $directory = $this->getArchiveDirectory() . $serieDirectoryConfig . '/';

    if (!empty($directory) &&
      file_prepare_directory($directory, FILE_CREATE_DIRECTORY)) {

      $fileName = $serieDirectoryConfig . '_' . $entity->getEntityTypeId() . '_' . $entity->id() . '.rdf';

      $content = '';
      foreach ($template as $item) {
        $content .= $item['attribute'] . ': ' . $item['value'] . "\n";
      }

      if (!file_put_contents($directory . '/' . $fileName, $content)) {
        \Drupal::messenger()->addError(t('File @file_name could not be created', [
          '@file_name' => $fileName,
        ]));
      }

    }
    else {
      \Drupal::messenger()->addError(t('Directory @path could not be created.', [
        '@path' => $directory,
      ]));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function createEntityTemplate(ContentEntityInterface $entity, $templateType) {
    // @todo based on the bundle configuration, select series
    // via a factory to get the right template.
    // Currently limiting it to wpaper series.
    $this->createPaperTemplate($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function updateEntityTemplate(ContentEntityInterface $entity, $templateType) {
    // @todo delete should be runned when entity is unpublished
    // Otherwise, barely re-create the entity template.
    $this->createEntityTemplate($entity, $templateType);
  }

  /**
   * {@inheritdoc}
   */
  public function deleteEntityTemplate(ContentEntityInterface $entity, $templateType) {
    // @todo implement
  }

  /**
   * {@inheritdoc}
   */
  public function availableSeries() {
    return [
      // The series list is subject to be extended
      // but currently limited to wpaper.
      RepecInterface::SERIES_WORKING_PAPER => t('Paper series'),
    ];
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
