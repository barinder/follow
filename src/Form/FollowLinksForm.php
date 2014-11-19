<?php

/**
 * @file
 * Contains \Drupal\follow\Form\FollowLinksForm.
 */

namespace Drupal\follow\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\follow\FollowLink;

class FollowLinksForm extends FormBase {

  public function getFormId() {
    return 'follow_links_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state, $user = 0) {
    $form = array();

    $form['uid'] = array('#type' => 'hidden', '#value' => $user);

    $header = array(t('Name'), t('URL'), t('Weight'),);
    if (\Drupal::currentUser()->hasPermission('change follow link titles')) { 
      $header[] = t('Customized Name');
    }
    
    $form['follow_links'] = array(
      '#type' => 'table',
      '#header' => $header,
      '#tabledrag' => array(
        array(
          'action' => 'order',
          'relationship' => 'sibling',
          'group' => 'follow-order-weight',
        ),
      ),
    );

    // List all the available links.
    $links = FollowLink::load($user);
    $networks = follow_networks_load($user, TRUE);

    // Put all our existing links at the top, sorted by weight.
    if (is_array($links)) {
      foreach ($links as $name => $link) {
        $title = $networks[$name]['title'];
        $form['follow_links'][$name] = $this->_follow_links_form_link($link, $title, $user);
        // Unset this specific network so we don't add the same one again below.
        unset($networks[$name]);
      }
    }

    $form['follow_links_disabled'] = array(
      '#type' => 'table',
      '#header' => array(t('Name'), t('URL')),
      '#tabledrag' => array(
        array(
          'action' => 'order',
          'relationship' => 'sibling',
          'group' => 'follow-order-weight',
        ),
      ),
    );

    // Now add all the empty ones.
    foreach ($networks as $name => $info) {
      $link = new FollowLink();
      $link->name = $name;
      $form['follow_links_disabled'][$name] = $this->_follow_links_form_link($link, $info['title'], $user);
    }
    $form['submit'] = array('#type' => 'submit', '#value' => t('Submit'));

    return $form;
  }

  /**
   * Helper function to create an individual link form element.
   */
  private function _follow_links_form_link($link, $title, $uid) {
    $elements = array();

    $elements['name'] = array(
      '#markup' => $title,
   );

    if (isset($link->lid)) {
      $elements['#weight'] = $link->weight;
      $elements['#attributes']['class'][] = 'draggable';
      $elements['lid'] = array(
        '#type' => 'hidden',
        '#value' =>  $link->lid,
        '#attributes' => array('class' => array('hidden')),
        '#prefix' => '<div>',
        '#suffix' => '</div>',
      );
      $elements['weight'] = array(
        '#type' => 'weight',
        '#default_value' => $link->weight,
        '#attributes' => array('class' => array('follow-order-weight')),
      );
    }
    $elements['url'] = array(
      '#type' => 'textfield',
      '#follow_network' => $link->name,
      '#follow_uid' => $uid,
      '#default_value' => isset($link->url) ? $link->url : '',
      '#element_validate' => array('follow_url_validate'),
    );
    // Provide the title of the link only if the link URL is there and the user
    // has the appropriate access.
    $elements['title'] = array(
      '#type' => 'textfield',
      '#default_value' => isset($link->title) ? $link->title : '',
      '#size' => 15,
      '#access' => \Drupal::currentUser()->hasPermission('change follow link titles') && !empty($link->url),
    );

    return $elements;
}

  public function submitForm(array &$form, FormStateInterface $form_state) {

    $values = $form_state->getValues();
    $links = $values['follow_links'];
    $links_disabled = $values['follow_links_disabled'];

    foreach($links as $name => $link) {
      $parsed = follow_parse_url(trim($link['url']));
      $link['path'] = $parsed['path'];
      $link['options'] = serialize($parsed['options']);

      if (empty($link['url'])) {
        FollowLink::delete($link['lid']);
        continue;
      }
      else {
        unset($link['url']);
        $link = new FollowLink($link);
        $link->uid = $values['uid'];
        $link->name = $name;
        $link->update();
      }
    }

    foreach($links_disabled as $name => $link) {
      $parsed = follow_parse_url(trim($link['url']));
      $link['path'] = $parsed['path'];
      $link['options'] = serialize($parsed['options']);
      if (empty($link['url'])) {
        continue;
      }
      else {
        unset($link['url']);
        $link = new FollowLink($link);
        $link->uid = $values['uid'];
        $link->name = $name;
        $link->create();
      }
    }

  }
}
