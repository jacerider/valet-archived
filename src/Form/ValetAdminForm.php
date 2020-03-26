<?php

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
      'valet.admin',
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

    $form['modifier'] = [
      '#type' => 'select',
      '#title' => $this->t('Hotkey Modifier'),
      '#required' => TRUE,
      '#options' => [
        18 => $this->t('Alt'),
        17 => $this->t('Ctrl'),
        16 => $this->t('Shift'),
      ],
      '#default_value' => $config->get('modifier'),
    ];
    $form['hotkey'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Hotkey'),
      '#required' => TRUE,
      '#description' => $this->t('The value entered in this field will automatically be translated into the javascript keycode used to trigger Valet.'),
      '#maxlength' => 32,
      '#default_value' => $config->get('hotkey'),
      '#attached' => [
        'library' => [
          'valet/valet.admin',
        ],
      ],
    ];

    $form['position'] = [
      '#type' => 'select',
      '#title' => $this->t('Position'),
      '#required' => TRUE,
      '#options' => [
        'left' => $this->t('Left'),
        'right' => $this->t('Right'),
        'overlay' => $this->t('Overlay'),
      ],
      '#default_value' => $config->get('position'),
    ];

    if (\Drupal::service('module_handler')->moduleExists('toolbar')) {
      $form['toolbar_disable'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Disable toolbar for user 1.'),
        '#default_value' => $config->get('toolbar_disable'),
      ];
    }

    $form['plugin_settings'] = [
      '#type' => 'vertical_tabs',
    ];

    $form['plugins'] = [
      '#type' => 'container',
      '#tree' => TRUE,
    ];

    $manager = \Drupal::service('plugin.manager.valet');
    $plugins = $manager->getDefinitions();
    uasort($plugins, ['Drupal\Component\Utility\SortArray', 'sortByWeightElement']);
    foreach ($plugins as $id => $plugin) {
      $definition = $manager->getDefinition($id);
      if (!$definition['class']::isApplicable()) {
        continue;
      }
      $instance = $manager->createInstance($id);
      $form['plugins'][$id] = [
        '#type' => 'details',
        '#title' => $plugin['label'] . ' ' . $this->t('Plugin'),
        '#group' => 'plugin_settings',
      ];

      $form['plugins'][$id]['enabled'] = [
        '#type' => 'checkbox',
        '#title' => t('Enabled'),
        '#default_value' => $config->get('plugins.' . $id . '.enabled'),
      ];

      if ($plugin_form = $instance->buildForm([], $form_state)) {
        $form['plugins'][$id]['settings'] = $plugin_form + [
          '#type' => 'container',
          '#states' => [
            'disabled' => ['input[name="plugins[' . $id . '][enabled]"]' => ['checked' => FALSE]],
          ],
        ];
      }
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $this->config('valet.admin')
      ->set('modifier', $form_state->getValue('modifier'))
      ->set('hotkey', $form_state->getValue('hotkey'))
      ->set('position', $form_state->getValue('position'))
      ->set('toolbar_disable', $form_state->getValue('toolbar_disable'))
      ->set('plugins', $form_state->getValue('plugins'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
