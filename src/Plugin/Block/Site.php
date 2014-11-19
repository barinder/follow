<?php

/**
 * @file
 * Contains \Drupal\follow\Plugin\Block\Site.
 */

namespace Drupal\follow\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides the Site block.
 *
 * @Block(
 *   id = "site",
 *   admin_label = @Translation("Follow Site")
 * )
 */
class Site extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $args = explode('/', current_path());
    if (($content = _follow_block_content('site'))
        && (\Drupal::config('follow.settings')->get('follow_site_block_user') || !($args[0] == 'user' && is_numeric($args[1])))) {
      return array(
     //   'subject' => _follow_block_subject(),
        'content' => $content,
      );
    }
  }

    /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form['follow_title'] = array(
      '#type' => 'radios',
      '#title' => t('Default block title'),
      '#default_value' => \Drupal::config('follow.settings')->get('follow_site_block_title'),
      '#options' => array(
        FOLLOW_NAME => t('Follow @name on', array('@name' => \Drupal::config('follow.settings')->get('site_name'))),
        FOLLOW_ME => t('Follow me on'),
        FOLLOW_US => t('Follow us on'),
      ),
    );
    $form['follow_user'] = array(
      '#type' => 'checkbox',
      '#title' => t('User pages'),
      '#description' => t('Should this block display on user profile pages?'),
      '#default_value' => \Drupal::config('follow.settings')->get('follow_site_block_user'),
    );
    $form['follow_alignment'] = array(
      '#type' => 'select',
      '#title' => t('Alignment'),
      '#options' => array(
        'vertical' => t('Vertical'),
        'horizontal' => t('Horizontal'),
      ),
      '#description' => t('Whether the icons are to appear horizontally beside each other, or one after another in a list.'),
      '#default_value' => \Drupal::config('follow.settings')->get("follow_{$delta}_alignment"),
    );
    // Allow changing which icon style to use on the global service links.
    $form['follow_icon_style'] = array(
      '#type' => 'select',
      '#title' => t('Icon Style'),
      '#options' => follow_icon_style_options(),
      '#description' => t('How the Follow icons should appear.'),
      '#default_value' => \Drupal::config('follow.settings')->get("follow_{$delta}_icon_style"),
    );
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    \Drupal::config('follow.settings')->set("follow_site_block_title", $values['follow_title'])->save();
    \Drupal::config('follow.settings')->set('follow_site_block_user', $values['follow_user'])->save();
    \Drupal::config('follow.settings')->set("follow_site_alignment", $values['follow_alignment'])->save();
    \Drupal::config('follow.settings')->set("follow_site_icon_style", $values['follow_icon_style'])->save();
    // Reset the CSS in case the styles changed.
    follow_save_css(TRUE);
  }
}
