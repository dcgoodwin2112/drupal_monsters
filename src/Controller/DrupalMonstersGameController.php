<?php

namespace Drupal\drupal_monsters\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class DrupalMonstersGameController.
 */
class DrupalMonstersGameController extends ControllerBase {

  /**
   * Drupal\Core\Database\Connection definition.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Drupal\Core\Session\AccountProxyInterface definition.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->database = $container->get('database');
    $instance->currentUser = $container->get('current_user');
    return $instance;
  }

  /**
   * Index.
   *
   * @return string
   *   Return Hello string.
   */
  public function index() {
    // Show monster selection form if new user.
    if (!$this->hasUserMon()) {
      $form = \Drupal::formBuilder()->getForm('\Drupal\drupal_monsters\Form\ChooseMonForm');
      return [
        'form' => $form,
      ];
    }
    $user_mon = $this->getUserMon();
    $types = $this->getTypes();
    return [
      '#theme' => 'user_monster',
      '#name' => $user_mon->b_name,
      '#nickname' => $user_mon->nickname,
      '#type' => $types[$user_mon->tid],
      '#attack' => $user_mon->attack,
      '#defense' => $user_mon->defense,
      '#stamina' => $user_mon->stamina,
      '#level' => $user_mon->level,
      '#experience' => $user_mon->experience,
      '#mv1' => [
        'name' => $user_mon->mv1_name,
        'type' => $types[$user_mon->mv1_tid],
        'power' => $user_mon->mv1_power,
        'damage_mult' => $user_mon->mv1_mult,
      ],
      '#mv2' => [
        'name' => $user_mon->mv2_name,
        'type' => $types[$user_mon->mv2_tid],
        'power' => $user_mon->mv2_power,
        'damage_mult' => $user_mon->mv2_mult,
      ],
      '#cache' => [
        'max-age' => 0,
      ],
    ];
  }

  /**
   * Check if current user has an existing user monster record.
   *
   * @return bool
   *   Returns TRUE is user has existing monster record.
   */
  protected function hasUserMon() {
    $uid = $this->currentUser()->id();
    $query = $this->database->select('drupal_monsters_user_mon', 'um')
      ->fields('um', ['uid'])
      ->condition('uid', $uid)
      ->range(0, 1)
      ->execute();
    $results = $query->fetch();
    return (!empty($results)) ? TRUE : FALSE;
  }

  /**
   * Get monster data for current user.
   *
   * @return object
   *   Monster data.
   */
  protected function getUserMon() {
    $uid = $this->currentUser()->id();
    $result = $this->database->query(
      "SELECT
        um.mid,
        bm.tid,
        bm.name AS b_name,
        um.nickname,
        (um.attack + bm.attack) AS attack,
        (um.defense + bm.defense) AS defense,
        (um.stamina + bm.stamina) AS stamina,
        um.level,
        um.experience,
        dmm1.name AS mv1_name,
        dmm1.power AS mv1_power,
        dmm1.damage_mult AS mv1_mult,
        dmm1.tid AS mv1_tid,
        dmm2.name AS mv2_name,
        dmm2.power AS mv2_power,
        dmm2.damage_mult AS mv2_mult,
        dmm2.tid AS mv2_tid
        FROM {drupal_monsters_user_mon} AS um
          LEFT JOIN {drupal_monsters_mon} AS bm ON (um.mid=bm.mid)
          LEFT JOIN {drupal_monsters_move} AS dmm1 ON (um.mvid1=dmm1.mvid)
          LEFT JOIN {drupal_monsters_move} AS dmm2 ON (um.mvid2=dmm2.mvid)
        WHERE uid=:uid",
      [':uid' => $uid]
    );
    return $result->fetch();
  }

  /**
   * Get elemental type data.
   *
   * @return string[]
   *   Array of type data. Keyed by tid.
   */
  protected function getTypes() {
    $result = $this->database->select('drupal_monsters_type', 't')
      ->fields('t', ['tid', 'name'])
      ->execute();
    return $result->fetchAllKeyed();
  }

}
