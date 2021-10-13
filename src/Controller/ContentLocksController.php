<?php

/**
 * @file
 *
 * Page to show list of content locks with options to unlock.
 */

namespace Drupal\abs_content_lock\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Methods to deal with content locks.
 */
class ContentLocksController extends Controllerbase {

  /**
   * Deletes the lock from the state object.
   */
  public function deleteLock($id) {
    if (preg_match('/^[a-z]+_\d+$/i', $id)) {
      $state = \Drupal::state()->get('abs_content_lock') ?? [];
      // Delete the lock.
      unset($state[$id]);
      \Drupal::state()->set('abs_content_lock', $state);
    }

    $redirect_route = \Drupal::moduleHandler()->moduleExists('kb_pub_tools') ? 'kb_pub_tools.default' : 'abs_content_lock.locks_page';
    return new RedirectResponse(Url::fromRoute($redirect_route)->toString());
  }

  /**
   * Displays a page showing all current content locks.
   */
  public function locksPage() {

    $state = \Drupal::state()->get('abs_content_lock') ?? [];
    $markup = '';

    $rows = [];
    $header = [
      'content_id' => t('ContentID'),
      'title' => t('Title'),
      'editor' => t('Editor'),
      'lock_started' => t('Lock started'),
      'link' => '',
    ];
    if (!empty($state)) {
      foreach($state as $id => $params) {
        $id_parts = preg_split('/_/', $id);
        switch ($id_parts[0]) {
          case 'node':
            $entity = \Drupal::entityTypeManager()->getStorage('node')->load($id_parts[1]);
            if (!empty($entity)) {
              $user = \Drupal::entityTypeManager()->getStorage('user')->load($params['uid']);
              $url = Url::fromRoute('abs_content_lock.delete_lock', ['id' => $id]);
              $link = Link::fromTextAndUrl(t('UNLOCK'), $url);
              $rows[] = [
                'content_id' => $id_parts[1],
                'title' => Link::createFromRoute($entity->getTitle(), 'entity.node.canonical', ['node' => $id_parts[1]]),
                'editor' => $user->getUsername(),
                'lock_started' => \Drupal::service('date.formatter')->format($params['time'], 'custom', 'Y-m-d H:i:s e'),
                'link' => $link,
              ];
            }
            break;
        }
      }
    }

    return [
      '#theme' => 'table',
      '#header' => $header,
      '#rows' => $rows,
    ];
  }
}
