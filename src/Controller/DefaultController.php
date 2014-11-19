<?php /**
 * @file
 * Contains \Drupal\follow\Controller\DefaultController.
 */

namespace Drupal\follow\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Access\AccessResult;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Default controller for the follow module.
 */
class DefaultController extends ControllerBase {


  public function follow_css($filepath) {
    $destination = file_stream_wrapper_get_instance_by_scheme('public')->getDirectoryPath(). '/css/follow.css';
    if ($destination = follow_save_css()) {
      return new BinaryFileResponse($destination, 200, array('Content-Type' => 'text/css', 'Content-Length' => filesize($destination)));
    }
    else {
      \Drupal::logger('follow')->notice('Unable to generate the Follow CSS located at %path.', array('%path' => $destination));
      drupal_add_http_header('Status', '500 Internal Server Error');
      print t('Error generating CSS.');
      drupal_exit();
    }
  }

 /**
  * Access callback for user follow links editing.
  */
  function follow_links_user_access($user) {
    return AccessResult::allowedIf(((($this->currentUser()->uid == $user) && $this->currentUser()->hasPermission('edit own follow //links')) || $this->currentUser()->hasPermission('edit any user follow links')) && $user > 0);
  }
}
