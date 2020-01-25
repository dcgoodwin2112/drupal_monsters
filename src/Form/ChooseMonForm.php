<?php

namespace Drupal\drupal_monsters\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Session\AccountProxyInterface;

/**
 * Class ChooseMonForm.
 */
class ChooseMonForm extends FormBase {

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
   * Object Array definition.
   *
   * @var object[]
   */
  protected $monsters;

  /**
   * Monster form constructor.
   *
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection service.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   The current_user service.
   */
  public function __construct(Connection $database, AccountProxyInterface $current_user) {
    $this->database = $database;
    $this->currentUser = $current_user;
    $this->monsters = $this->getMonsters();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database'),
      $container->get('current_user')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'choose_mon_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $options = [];
    foreach ($this->monsters as $mon) {
      $options[$mon->mid] = "{$mon->name} ({$mon->t_name} type) A{$mon->attack} D{$mon->defense} S{$mon->stamina}";
    }

    $form['monster_info'] = [
      '#markup' => $this->getMonsterInfo(1),
      '#prefix' => '<div id="monster-info">',
      '#suffix' => '</div>',
    ];
    $form['monster'] = [
      '#type' => 'select',
      '#title' => $this->t('Select a Monster'),
      '#description' => $this->t('Monster Name (Elemental Type) Attack Defense Stamina'),
      '#options' => $options,
      '#ajax' => [
        'callback' => '::updateMonsterInfo',
        'disable-refocus' => FALSE,
        'event' => 'change',
        'wrapper' => 'monster-info',
        'progress' => [
          'type' => 'throbber',
          'message' => $this->t('Updating monster info...'),
        ],
      ],
    ];
    $form['nickname'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Nickname'),
      '#description' => $this->t('Choose a nickname for your Drupalmon. (optional)'),
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    return $form;
  }

  /**
   * Ajax function for setting the monster info area.
   *
   * @param array $form
   *   The monster form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state interface.
   *
   * @return array
   *   The updated monster info field data.
   */
  public function updateMonsterInfo(array &$form, FormStateInterface $form_state) {
    $form['monster_info']['#markup'] = $this->getMonsterInfo($form_state->getValue('monster'));
    return $form['monster_info'];
  }

  /**
   * Get the base monsters from the database.
   *
   * @return object[]
   *   Array of monster objects.
   */
  protected function getMonsters() {
    $mon_fields = [
      'mid',
      'tid',
      'name',
      'attack',
      'defense',
      'stamina',
    ];
    $query = $this->database->select('drupal_monsters_mon', 'dm');
    $query->join('drupal_monsters_type', 't', 'dm.tid = t.tid');
    $query->fields('dm', $mon_fields);
    $query->fields('t', ['name']);
    $results = $query->execute();
    return $results->fetchAllAssoc('mid');
  }

  /**
   * Format HTML for monster info area.
   *
   * @param int $monster_id
   *   The ID of the selected monster.
   *
   * @return string
   *   The formatted monster info.
   */
  protected function getMonsterInfo(int $monster_id) {
    $mon = $this->monsters[$monster_id];
    $out = "Name: {$mon->name}<br>";
    $out .= "Type: {$mon->t_name}<br>";
    $out .= "ATK: {$mon->attack}<br>";
    $out .= "DEF: {$mon->defense}<br>";
    $out .= "STA: {$mon->stamina}<br>";
    return $out;
  }

  /**
   * Get randomized moves for selected mon.
   *
   * @param int $type_id
   *   The type of the selected monster.
   *
   * @return object[]
   *   Array of query data contain monster move ids.
   */
  protected function getMonMoves(int $type_id) {
    $query = $this->database->select('drupal_monsters_move', 'dmm')
      ->fields('dmm', ['mvid', 'tid'])
      ->condition('tid', [1, $type_id], 'IN')
      ->orderRandom()
      ->execute();
    return $query->fetchAll();

  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    foreach ($form_state->getValues() as $key => $value) {
      // @TODO: Validate fields.
    }
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    // Get id of selected mon.
    $mid = $form_state->getValue('monster');
    // Get randomized Move Ids.
    $moves = $this->getMonMoves($this->monsters[$mid]->tid);
    // Set fields fields for db insert.
    $values = [
      'mid' => $mid,
      'uid' => $this->currentUser()->id(),
      'nickname' => $form_state->getValue('nickname'),
      'mvid1' => $moves[0]->mvid,
      'mvid2' => $moves[1]->mvid,
    ];
    $this->database->insert('drupal_monsters_user_mon')->fields($values)->execute();
  }

}
