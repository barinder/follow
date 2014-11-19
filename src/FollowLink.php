<?php

namespace Drupal\follow;

/**
 * Handles CRUD operations to {follow_links} table.
 */
class FollowLink {

  public $lid;
  public $name;
  public $uid;
  public $path;
  public $options;
  public $title;
  public $weight;

  public function __construct(Array $properties=array()){
    foreach($properties as $key => $value){
      $this->{$key} = $value;
    }
  }

  public function create() {
    $link = get_object_vars($this);
    return db_insert('follow_links')
      ->fields($link)
      ->execute();
  }

  public function update() {
    $link = get_object_vars($this);
    return db_update('follow_links')
      ->condition('lid', $link['lid'])
      ->fields($link)
      ->execute();
  }

  public static function delete($lid) {
    return db_delete('follow_links')
      ->condition('lid', $lid)
      ->execute();
  }

  /**
   * Loader function for individual links.
   *
   * @param $uid
   *   An int containing the uid of the user. uid 0 pulls the site follow links.
   * @return
   *   A single link in array format, or FALSE if none matched the incoming ID.
   */
  public static function load($uid = 0) {
    $links = array();
    $sql = "SELECT * FROM {follow_links} WHERE uid = :uid ORDER BY weight ASC";
    $result = db_query($sql, array(':uid' => $uid));
    foreach ($result as $link) {
      $link->options = unserialize($link->options);
      $link->url = follow_build_url($link->path, $link->options);
      $links[$link->name] = $link;
    }
    return $links;
  }

}
