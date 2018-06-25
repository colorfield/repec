<?php

namespace Drupal\repec;

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
   * Constructs a new Repec object.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, FileSystemInterface $file_system, ConfigFactoryInterface $config_factory) {
    $this->entityTypeManager = $entity_type_manager;
    $this->fileSystem = $file_system;
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public function createTemplate(ContentEntityInterface $entity, $templateType) {
    // TODO: Implement createTemplate() method.
  }

  /**
   * {@inheritdoc}
   */
  public function updateTemplate(ContentEntityInterface $entity, $templateType) {
    // TODO: Implement updateTemplate() method.
  }

  /**
   * {@inheritdoc}
   */
  public function deleteTemplate(ContentEntityInterface $entity, $templateType) {
    // TODO: Implement deleteTemplate() method.
  }

  /**
   * {@inheritdoc}
   */
  public function isEnabled($entity_type) {
    // TODO: Implement isEnabled() method.
  }

}
