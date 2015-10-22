<?php

/**
 * @file
 * Contains Drupal\valet\Form\ValetAdminForm.
 */

namespace Drupal\valet\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class ValetAdminForm.
 *
 * @package Drupal\valet\Form
 */
class ValetAdminForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'valet.admin'
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'valet_admin_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('valet.admin');
    $form['modifier'] = array(
      '#type' => 'select',
      '#title' => $this->t('Hotkey Modifier'),
      '#options' => array(18 => $this->t('Alt'), 17 => $this->t('Ctrl'), 16 => $this->t('Shift')),
      '#default_value' => $config->get('modifier'),
    );
    $form['hotkey'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Hotkey'),
      '#description' => $this->t('The value entered in this field will automatically be translated into the javascript keycode used to trigger Valet.'),
      '#maxlength' => 32,
      '#default_value' => $config->get('hotkey'),
      '#attached' => array(
        'library' => array(
          'valet/valet.admin',
        ),
      ),
    );

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $this->config('valet.admin')
      ->set('modifier', $form_state->getValue('modifier'))
      ->set('hotkey', $form_state->getValue('hotkey'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
