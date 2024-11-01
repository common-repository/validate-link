<?php

/*
Plugin Name: Validate Link
Description: This is a simple plugin to validate post content to find and outline invalid links.
Version: 1.0.0
Author: Robin Zhao <boborabit@qq.com>
Author URI: https://blog.54zxy.com
License: GPLv2 or later
Text Domain: validate-link
*/

/* 
Copyright (C) 2018 Robin Zhao <boborabit@qq.com>

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
*/

add_action('plugins_loaded', function () {
    $plugin_rel_path = basename( dirname( __FILE__ ) ) . '/languages';
    load_plugin_textdomain( 'validate-link', false, $plugin_rel_path );
});

add_action('admin_notices', function () {
    global $current_user;
    $post_id = get_the_ID();
    $user_id = $current_user->ID;

    if ($error = get_transient("validate_on_save_errors_{$post_id}_{$user_id}")) {
?>
        <div class="error">
            <p><?php echo $error->get_error_message(); ?></p>
        </div>
<?php
        delete_transient("validate_on_save_errors_{$post_id}_{$user_id}");
    }
});

add_action('save_post', function ($post_id) {
    global $current_user;
    $user_id = $current_user->ID;
    $post = get_post($post_id);

    $regex = '@href="[^h/][a-zA-Z0-9.]*@';

    $matches = [];
    if (preg_match($regex, $post->post_content, $matches)) {
        $message = __('A valid link must start with http or /, found:', 'validate-link');
        $message .= ' [ ' . $matches[0] . ' ]';
        set_transient("validate_on_save_errors_{$post_id}_{$user_id}", new WP_Error(456, $message), 60);
    }
});
