<?php

if ( !defined('ABSPATH') ) {
  wp_die(__('Invalid Request', 'nba'));
}

?>
<div class="wrap">
  <h2><?php _e('NBA Player Test', 'nba'); ?></h2>
  <p class="description"><?php _e('Batch update player tv url field for players.', 'nba'); ?></p>
  <form method="post"
        action="<?php echo add_query_arg([ 'action' => 'nba_player_test' ], network_admin_url('edit.php')); ?>">
    <?php submit_button('Update Player TV URLs'); ?>
  </form>
</div>