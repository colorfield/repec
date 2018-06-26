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
      '#description' => $this->t('This code must be registered and provided by RePEc. It has three letters. See https://ideas.repec.org/t/archivehandle.html'),
      '#maxlength' => 3,
      '#size' => 3,
      '#default_value' => $config->get('archive_code'),
      '#required' => TRUE,
    ];
    $form['base_path'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Base path'),
      '#description' => $this->t('Path for the main directory to store the template files. It will be created on the public file system (public://, by default /sites/default/files). Do not include a trailing slash (example: RePEC).'),
      '#maxlength' => 254,
      '#size' => 64,
      '#default_value' => $config->get('base_path'),
      '#required' => TRUE,
    ];
    $form['provider_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Provider name'),
      '#description' => $this->t('Name of the provider institution (example: University of Southern California, Lusk Center for Real Estate).'),
      '#maxlength' => 200,
      '#size' => 64,
      '#default_value' => $config->get('provider_name'),
      '#required' => TRUE,
    ];
    $form['provider_homepage'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Provider homepage'),
      '#description' => $this->t('Homepage of the provider institution, without a trailing slash (example: http://lusk.usc.edu).'),
      '#maxlength' => 250,
      '#size' => 64,
      '#default_value' => $config->get('provider_homepage'),
      '#required' => TRUE,
    ];
    $form['provider_institution'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Provider institution'),
      '#description' => $this->t('(example: RePEc:edi:lcuscus).'),
      '#maxlength' => 200,
      '#size' => 64,
      '#default_value' => $config->get('provider_institution'),
      '#required' => TRUE,
    ];
    $form['maintainer_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Maintainer name'),
      '#maxlength' => 160,
      '#size' => 64,
      '#default_value' => $config->get('maintainer_name'),
      '#required' => TRUE,
    ];
    $form['maintainer_email'] = [
      '#type' => 'email',
      '#title' => $this->t('Maintainer email'),
      '#default_value' => $config->get('maintainer_email'),
      '#required' => TRUE,
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
    // @todo validate provider homepage
    // @todo remove trailing slash from provider homepage if any
    $archiveCode = $form_state->getValue('archive_code');
    if (strlen($archiveCode) !== 3) {
      $form_state->setErrorByName('archive_code', t('Archive code must have exactly 3 letters.'));
    }
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

    // @todo if directory exists, add confirmation, this removes / recreates all rdf files.
    /** @var \Drupal\repec\RepecInterface $repec */
    $repec = \Drupal::service('repec');
    $repec->initializeTemplates();
  }

}
