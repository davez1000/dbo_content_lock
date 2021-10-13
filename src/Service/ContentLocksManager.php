<?php

namespace Drupal\abs_content_lock\Service;

use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Management and helper functions for content locks.
 */
class ContentLocksManager {

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * Gets content locks.
   *
   * @return array
   */
  public function getLocks() {
    $state = \Drupal::state()->get('abs_content_lock') ?? [];

    $rows = [];

    if (!empty($state)) {
      foreach ($state as $id => $params) {
        $id_parts = preg_split('/_/', $id);
        switch ($id_parts[0]) {
          case 'node':
            $entity = $this->entityTypeManager->getStorage('node')->load($id_parts[1]);
            if (!empty($entity)) {
              $user = $this->entityTypeManager->getStorage('user')->load($params['uid']);
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

    return $rows;

  }
}
