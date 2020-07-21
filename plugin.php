<?php
/**
 * Plugin Name: Player Test
 */

if ( ! defined( 'ABSPATH' ) ) {
  die( 'Invalid request.' );
}

require_once(plugin_dir_path(__FILE__) . '/PlayerTestPage.php');

if ( ! class_exists( 'PlayerTest' ) ) :

  /**
   * Class PlayerTest
   */
class PlayerTest {
  const ADMIN_POST_ACTION = 'nba_player_test';
  const TASK = 'batch_update_player_task';

  public function __construct() {}

  /**
   * Init all of our actions
   */
  public function init_actions() {
    /* /wp-admin/admin-post.php?action=batch_update_task */
    add_action('admin_post_batch_update_player_task', [$this, 'run_task']);
    // can also run without this hook if we take the time to authenticate
    add_action('admin_post_nopriv_batch_update_player_task', [$this, 'run_task']);
    add_action('init', [$this, 'register_player_post_type']);

    $settings_page = new PlayerTestPage();
    $settings_page->boot();
  }

  /**
   * register the players post type
   */
  public function register_player_post_type() {
    $data = array(
      'labels' => array(
        'name' => _x('Players', 'post type general name'),
        'singular_name' => _x('Player', 'post type singular name'),
        'menu_name' => _x('Players', 'admin menu'),
        'name_admin_bar' => _x('Player', 'add new on admin bar'),
        'add_new' => _x('Add New', 'Player'),
        'add_new_item' => __('Add New Player'),
        'new_item' => __('New Player'),
        'edit_item' => __('Edit Player'),
        'view_item' => __('View Player'),
        'all_items' => __('All Players'),
        'search_items' => __('Search Player'),
        'parent_item_colon' => __('Parent Player:'),
        'not_found' => __('No Player found.'),
        'not_found_in_trash' => __('No Player found in Trash.')
      ),
      'public' => true,
      'has_archive' => true,
      'publicly_queryable' => true,
      'exclude_from_search' => true,
      'show_ui' => true,
      'show_in_menu' => true,
      'menu_icon' => 'dashicons-admin-site',
      'supports' => array('title', 'custom-fields'),
      'taxonomies' => array( 'category' ),
      'show_in_rest' => true,
    );

    register_post_type('players', $data);
  }

  /**
   * Get players without the player_tv_url field
   *
   * @return WP_Query
   */
  public function get_players() {
      $args = [
        'posts_per_page' => -1,
        'post_type'      => 'players',
        'post_status'    => 'publish',
        'meta_query'     =>
          [
            'key'       => 'player_tv_url',
            'compare'   => 'NOT EXISTS',
            'type'      => 'STRING'
          ]
      ];

      $wp_query = new \WP_Query($args);

      return $wp_query;
  }

  /**
   * Trigger the background task
   *
   * @return array
   */
  public function trigger_batch_update(){
    $url = admin_url('admin-post.php');

    $request_args = [
      'method' => 'POST',
      'timeout' => .01,
      'blocking' => false,
      'body' => [
        'action' => self::TASK,
      ],
    ];

    return wp_remote_post($url, $request_args);
  }

  /**
   * Run the background task
   */
  public function run_task() {
    $player_query = $this->get_players();

    while ($player_query->have_posts()) : $player_query->the_post();
      $post_id = get_the_ID();

      $player_external_id = get_post_meta( $post_id, 'player_external_id', true );

      if ( $player_external_id ) {
        add_post_meta(
          $post_id,
          'player_tv_url',
          sprintf('http://www.nba-player-tv.com/channel/%s', $player_external_id),
          true
        );
      } else {
        // log player with missing player_external_id field
        error_log(sprintf('Post with ID %s is missing player_external_id field', $post_id));
      }

    endwhile;

    wp_reset_postdata();
  }
}

$player_test = new PlayerTest();
$player_test->init_actions();

endif;