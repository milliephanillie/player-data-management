# Player Data Management Plugin

Update player_tv_url field where it does not exist.

* Registers the players post type
* Provides a network settings page to update player meta
* Form submission on the settings page triggers a background task that runs a loop on players with the missing field, and updates the post.
* This background task can also be trigger by accessing the admin-post.php?action={action} url directly.

# Directions

1. Install and Activate Plugin
2. Navigate to http://example.com/wp-admin/network/settings.php?page=nba-player-test
3. Click Update Player TV URLSs
