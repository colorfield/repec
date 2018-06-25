<?php

namespace Drupal\repec\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class SettingsForm.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'repec.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'repec_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('repec.settings');
    $form['archive_code'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Archive code'),
      '#description' => $this->t('This code must be registered and provided by RePEc. This has three letters.'),
      '#maxlength' => 3,
      '#size' => 3,
      '#default_value' => $config->get('archive_code'),
    ];
    $form['base_path'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Base path'),
      '#description' => $this->t('This is the path for the main directory to store the template files. This is the directory to be checked by RePEc system. You must setup this path on the filesystem.'),
      '#maxlength' => 254,
      '#size' => 64,
      '#default_value' => $config->get('base_path'),
    ];
    $form['provider_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Provider name'),
      '#description' => $this->t('This is the name of the provider institution (example: University of Southern California, Lusk Center for Real Estate).'),
      '#maxlength' => 200,
      '#size' => 64,
      '#default_value' => $config->get('provider_name'),
    ];
    $form['provider_homepage'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Provider homepage'),
      '#description' => $this->t('This is the homepage of the provider institution (example: http://lusk.usc.edu).'),
      '#maxlength' => 250,
      '#size' => 64,
      '#default_value' => $config->get('provider_homepage'),
    ];
    $form['provider_institution'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Provider institution'),
      '#description' => $this->t('This is the provider institution (example: RePEc:edi:lcuscus).'),
      '#maxlength' => 200,
      '#size' => 64,
      '#default_value' => $config->get('provider_institution'),
    ];
    $form['maintainer_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Maintainer name'),
      '#maxlength' => 160,
      '#size' => 64,
      '#default_value' => $config->get('maintainer_name'),
    ];
    $form['maintainer_email'] = [
      '#type' => 'email',
      '#title' => $this->t('Maintainer email'),
      '#default_value' => $config->get('maintainer_email'),
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // @todo validate provider homepage
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->config('repec.settings')
      ->set('archive_code', $form_state->getValue('archive_code'))
      ->set('base_path', $form_state->getValue('base_path'))
      ->set('provider_name', $form_state->getValue('provider_name'))
      ->set('provider_homepage', $form_state->getValue('provider_homepage'))
      ->set('provider_institution', $form_state->getValue('provider_institution'))
      ->set('maintainer_name', $form_state->getValue('maintainer_name'))
      ->set('maintainer_email', $form_state->getValue('maintainer_email'))
      ->save();
  }

}
