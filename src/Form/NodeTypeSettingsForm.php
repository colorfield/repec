<?php

namespace Drupal\repec\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\repec\RepecInterface;

/**
 * Node type settings form.
 */
class NodeTypeSettingsForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'repec_node_type_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $node_type = NULL) {
    /** @var \Drupal\repec\RepecInterface $repec */
    $repec = \Drupal::service('repec');
    $storage = [
      'node_type' => $node_type,
    ];
    $form_state->setStorage($storage);

    // @todo add date format options
    // @todo check system wide settings first
    $form['enabled'] = [
      '#type' => 'checkbox',
      '#title' => t('Enable RePEc for this content type'),
      '#default_value' => $repec->getEntityBundleSettings('enabled', 'node', $node_type),
    ];

    $form['serie'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Serie'),
      '#states' => [
        'visible' => [
          ':input[name="enabled"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['serie']['serie_type'] = [
      '#type' => 'select',
      '#title' => t('Series'),
      '#options' => $repec->availableSeries(),
      '#default_value' => $repec->getEntityBundleSettings('serie_type', 'node', $node_type),
      '#states' => [
        'visible' => [
          ':input[name="enabled"]' => ['checked' => TRUE],
        ],
        'required' => [
          ':input[name="enabled"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['serie']['serie_name'] = [
      '#type' => 'textfield',
      '#title' => t('Serie name'),
      '#description' => t('Name for the serie (example: Working Paper).'),
      '#default_value' => $repec->getEntityBundleSettings('serie_name', 'node', $node_type),
      '#states' => [
        'visible' => [
          ':input[name="enabled"]' => ['checked' => TRUE],
        ],
        'required' => [
          ':input[name="enabled"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['serie']['serie_directory'] = [
      '#type' => 'textfield',
      '#title' => t('Templates directory for this serie'),
      '#description' => t('It must have exactly six letters. Currently limited to Working Paper so defaulting to "wpaper"'),
      '#maxlength' => 6,
      '#size' => 6,
      // The serie_directory is currently not configurable because
      // it is hardcoded as a default value in the
      // RepecInterface::getSeriesTemplate()
      // due to the current limitation to working papers.
      // '#default_value' => $repec->getEntityBundleSettings
      // ('serie_directory', 'node', $node_type),.
      '#default_value' => RepecInterface::SERIES_WORKING_PAPER,
      '#disabled' => TRUE,
      '#states' => [
        'visible' => [
          ':input[name="enabled"]' => ['checked' => TRUE],
        ],
        'required' => [
          ':input[name="enabled"]' => ['checked' => TRUE],
        ],
      ],
    ];

    $form['restriction'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Optional restriction'),
      '#states' => [
        'visible' => [
          ':input[name="enabled"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['restriction']['restriction_by_field'] = [
      '#type' => 'checkbox',
      '#title' => t('Limit shared entities by field'),
      '#description' => t('While enabled, allows to evaluate a boolean field to share the entity on RePEc or not.'),
      '#default_value' => $repec->getEntityBundleSettings('restriction_by_field', 'node', $node_type),
      '#states' => [
        'visible' => [
          ':input[name="enabled"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $form['restriction']['restriction_field'] = [
      '#type' => 'select',
      '#title' => 'Restriction field',
      '#description' => t('Select the boolean field that will be used to post on RePEc.'),
      '#options' => $this->getBooleanFields('node', $node_type),
      '#default_value' => $repec->getEntityBundleSettings('restriction_field', 'node', $node_type),
      '#states' => [
        'visible' => [
          ':input[name="restriction_by_field"]' => ['checked' => TRUE],
        ],
        'required' => [
          ':input[name="restriction_by_field"]' => ['checked' => TRUE],
        ],
      ],
    ];

    $bundleFields = \Drupal::entityManager()->getFieldDefinitions('node', $node_type);
    $fieldOptions = [];
    foreach ($bundleFields as $fieldName => $fieldDefinition) {
      if (!empty($fieldDefinition->getTargetBundle())) {
        $fieldOptions[$fieldName] = $fieldDefinition->getLabel();
        // @todo validate
        // $fieldDefinition->getType();
      }
    }

    $repecTemplateFields = $repec->getTemplateFields(RepecInterface::SERIES_WORKING_PAPER);

    $form['template_field_mapping'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Template field mapping'),
      '#states' => [
        'visible' => [
          ':input[name="enabled"]' => ['checked' => TRUE],
        ],
      ],
    ];
    foreach ($repecTemplateFields as $fieldKey => $fieldLabel) {
      $form['template_field_mapping'][$fieldKey] = [
        '#type' => 'select',
        '#title' => $fieldLabel,
        '#options' => $fieldOptions,
        '#default_value' => $repec->getEntityBundleSettings($fieldKey, 'node', $node_type),
        '#states' => [
          'visible' => [
            ':input[name="enabled"]' => ['checked' => TRUE],
          ],
          'required' => [
            ':input[name="enabled"]' => ['checked' => TRUE],
          ],
        ],
      ];
    }

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => t('Save configuration'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  private function getBooleanFields($entity_type_id, $bundle) {
    $result = [];
    $bundleFields = \Drupal::entityManager()->getFieldDefinitions($entity_type_id, $bundle);
    /** @var \Drupal\Core\Field\FieldDefinitionInterface $fieldDefinition */
    foreach ($bundleFields as $fieldName => $fieldDefinition) {
      if (!empty($fieldDefinition->getTargetBundle()) && $fieldDefinition->getType() === 'boolean') {
        $result[$fieldName] = $fieldDefinition->getLabel();
      }
    }
    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
    // @todo validate selected field types

    // @todo validate multiple bundle configuration for the same serie:
    // an existing serie must have the same value as another
    // potentially used bundle.
    $directory = $form_state->getValue('serie_directory');
    if (strlen($directory) !== 6) {
      $form_state->setErrorByName('serie_directory', t('Serie directory must have exactly 6 letters.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $storage = $form_state->getStorage();
    $node_type = $storage['node_type'];
    // Update RePEc settings.
    $settings = [];
    /** @var \Drupal\repec\RepecInterface $repec */
    $repec = \Drupal::service('repec');
    // Empty configuration if set again to disabled.
    if (!$values['enabled']) {
      $settings = $repec->getEntityBundleSettingDefaults();
    }
    else {
      $settings = $repec->getEntityBundleSettings('all', 'node', $node_type);
      foreach ($repec->availableEntityBundleSettings() as $setting) {
        if (isset($values[$setting])) {
          $settings[$setting] = is_array($values[$setting]) ? array_keys(array_filter($values[$setting])) : $values[$setting];
        }
      }
    }
    $repec->setEntityBundleSettings($settings, 'node', $node_type);
    $repec->createSeriesTemplate();

    $messenger = \Drupal::messenger();
    $messenger->addMessage(t('Your changes have been saved.'));
  }

}
