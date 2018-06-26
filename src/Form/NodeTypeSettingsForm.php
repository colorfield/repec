<?php

namespace Drupal\repec\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

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

    // @todo check system wide settings first
    $form['enabled'] = [
      '#type' => 'checkbox',
      '#title' => t('Enable RePEc for this content type'),
      '#default_value' => $repec->getEntityBundleSettings('enabled', 'node', $node_type),
    ];

    $bundleFields = \Drupal::entityManager()->getFieldDefinitions('node', $node_type);
    $options = [];
    foreach ($bundleFields as $fieldName => $fieldDefinition) {
      if (!empty($fieldDefinition->getTargetBundle())) {
        $options[$fieldName] = $fieldDefinition->getLabel();
        // @todo validate
        // $fieldDefinition->getType();
      }
    }

    $repecFields = [
      'author_name' => t('Author-Name'),
      'abstract' => t('Abstract'),
      'creation_date' => t('Creation-Date'),
      'file_url' => t('File-URL'),
      'keywords' => t('Keywords'),
    ];

    foreach ($repecFields as $fieldKey => $fieldLabel) {
      $form[$fieldKey] = [
        '#type' => 'select',
        '#title' => $fieldLabel,
        '#options' => $options,
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
    $messenger = \Drupal::messenger();
    $messenger->addMessage(t('Your changes have been saved.'));
  }

}
