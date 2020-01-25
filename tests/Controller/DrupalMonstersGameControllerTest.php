<?php

namespace Drupal\drupal_monsters\Tests;

use Drupal\simpletest\WebTestBase;
use Drupal\drupal_monsters\DrupalMonstersGameDataInterface;

/**
 * Provides automated tests for the drupal_monsters module.
 */
class DrupalMonstersGameControllerTest extends WebTestBase {

  /**
   * Drupal\drupal_monsters\DrupalMonstersGameDataInterface definition.
   *
   * @var \Drupal\drupal_monsters\DrupalMonstersGameDataInterface
   */
  protected $drupalMonstersGameData;


  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return [
      'name' => "drupal_monsters DrupalMonstersGameController's controller functionality",
      'description' => 'Test Unit for module drupal_monsters and controller DrupalMonstersGameController.',
      'group' => 'Other',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
  }

  /**
   * Tests drupal_monsters functionality.
   */
  public function testDrupalMonstersGameController() {
    // Check that the basic functions of module drupal_monsters.
    $this->assertEquals(TRUE, TRUE, 'Test Unit Generated via Drupal Console.');
  }

}
