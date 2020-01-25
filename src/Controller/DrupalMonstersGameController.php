<?php

namespace Drupal\drupal_monsters\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class DrupalMonstersGameController.
 */
class DrupalMonstersGameController extends ControllerBase {

  /**
   * Drupal\drupal_monsters\DrupalMonstersGameDataInterface definition.
   *
   * @var \Drupal\drupal_monsters\DrupalMonstersGameDataInterface
   */
  protected $gameData;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    return $instance;
  }

  /**
   * Index.
   *
   * @return string
   *   Return Hello string.
   */
  public function index() {
    return [
      '#type' => 'markup',
      '#markup' => $this->t('Implement method: index'),
      '#cache' => [
        'max-age' => 0,
      ],
    ];
  }

}
