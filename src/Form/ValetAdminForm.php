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
      '#required' => TRUE,
      '#options' => array(18 => $this->t('Alt'), 17 => $this->t('Ctrl'), 16 => $this->t('Shift')),
      '#default_value' => $config->get('modifier'),
    );
    $form['hotkey'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Hotkey'),
      '#required' => TRUE,
      '#description' => $this->t('The value entered in this field will automatically be translated into the javascript keycode used to trigger Valet.'),
      '#maxlength' => 32,
      '#default_value' => $config->get('hotkey'),
      '#attached' => array(
        'library' => array(
          'valet/valet.admin',
        ),
      ),
    );

    $form['position'] = array(
      '#type' => 'select',
      '#title' => $this->t('Position'),
      '#required' => TRUE,
      '#options' => array(
        'left' => $this->t('Left'),
        'right' => $this->t('Right'),
        'overlay' => $this->t('Overlay'),
      ),
      '#default_value' => $config->get('position'),
    );

    $form['plugin_settings'] = array(
      '#type' => 'vertical_tabs',
    );

    $form['plugins'] = array(
      '#type' => 'container',
      '#tree' => TRUE,
    );

    $manager = \Drupal::service('plugin.manager.valet');
    $plugins = $manager->getDefinitions();
    uasort($plugins, array('Drupal\Component\Utility\SortArray', 'sortByWeightElement'));
    foreach($plugins as $id => $plugin){
      $instance = $manager->createInstance($id);
      $form['plugins'][$id] = array(
        '#type' => 'details',
        '#title' => $plugin['label'] . ' ' . $this->t('Plugin'),
        '#group' => 'plugin_settings',
      );

      $form['plugins'][$id]['enabled'] = array(
        '#type' => 'checkbox',
        '#title' => t('Enabled'),
        '#default_value' => $config->get('plugins.'.$id.'.enabled'),
      );

      if($plugin_form = $instance->buildForm(array(), $form_state)){
        $form['plugins'][$id]['settings'] = $plugin_form + [
          '#type' => 'container',
          '#states' => array(
            'disabled' => array('input[name="plugins['.$id.'][enabled]"]' => array('checked' => FALSE)),
          ),
        ];
      }
    }

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

    $this->config('valet.admin')
      ->set('modifier', $form_state->getValue('modifier'))
      ->set('hotkey', $form_state->getValue('hotkey'))
      ->set('position', $form_state->getValue('position'))
      ->set('plugins', $form_state->getValue('plugins'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
