<?php

require_once(plugin_dir_path(__FILE__) . '/plugin.php');

class PlayerTestPage {
  const ACTION = 'nba_player_test';
  const TITLE = 'Batch Update Player TV URL';
  const MENU_SLUG = 'nba-player-test';
  const CAPABILITY = 'manage_options';
  const PARENT_SLUG = 'settings.php';
  const SETTINGS_UPDATED = 'settings_updated';
  const SETTINGS_UPDATED_MSG = 'Players Updated';
  const SETTINGS_ERRORS = 'settings_errors';

  private $page_hook;

  public function boot() {
    if ( is_admin() ) {
      add_action('network_admin_menu', [ $this, 'add_menu' ]);
      add_action('network_admin_edit_' . self::ACTION, [ $this, 'save' ], 10);
      add_action('network_admin_edit_' . self::ACTION, [ $this, 'set_errors_transient' ], 20);
      add_action('network_admin_edit_' . self::ACTION, [ $this, 'redirect' ], 30);
      add_action('network_admin_edit_' . self::ACTION, [ $this, '_exit' ], 999);
      add_action('network_admin_notices', [ $this, 'display_settings_errors' ]);
    }
  }

  /**
   * Add NBA Player Test settings page.
   */
  public function add_menu() {
    $this->page_hook = add_submenu_page(
      self::PARENT_SLUG,
      __(self::TITLE, 'nba'),
      __(self::TITLE, 'nba'),
      self::CAPABILITY,
      self::MENU_SLUG,
      [ $this, 'render' ]
    );
  }

  public function render() {
    include plugin_dir_path(__FILE__ ) . '/templates/player-settings.php';
  }

  public function save() {
    $player_settings = new PlayerTest();
    $player_settings->trigger_batch_update();
  }

  /**
   * Wrapper for exit.
   *
   * Used after the action 'admin_edit_' . self::ACTION instead of
   * calling exit in save for testing purposes.
   */
  public function _exit() {
    exit;
  }

  /**
   * Set errors as a transient to be displayed on settings page
   *
   * If no errors were registered add a general 'updated' message.
   *
   * @see https://developer.wordpress.org/reference/functions/add_settings_error/
   * @see https://developer.wordpress.org/reference/functions/get_settings_error/
   * @see https://developer.wordpress.org/reference/functions/set_site_transient/
   */
  public function set_errors_transient() {
    if ( ! count(get_settings_errors()) ) {
      add_settings_error('general', self::SETTINGS_UPDATED, __(self::SETTINGS_UPDATED_MSG), 'updated');
    }
    set_transient(self::SETTINGS_ERRORS, get_settings_errors(), 30);
  }

  /**
   * Redirect back to settings page
   *
   * @see https://developer.wordpress.org/reference/functions/add_query_arg/
   * @see https://developer.wordpress.org/reference/functions/admin_url/
   * @see https://developer.wordpress.org/reference/functions/wp_redirect/
   */
  public function redirect() {
    wp_redirect(add_query_arg(
      [
        'page' => self::MENU_SLUG,
        'settings-updated' => 'true',
      ],
      network_admin_url(self::PARENT_SLUG)
    ));
  }

  /**
   * @see https://developer.wordpress.org/reference/functions/settings_errors/
   */
  public function display_settings_errors() {
    global $hook_suffix;

    if ( $hook_suffix === $this->page_hook ) {
      settings_errors();
    }
  }
}