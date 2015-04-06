<?php
/*
    Plugin Name: Admin Notice
    Plugin URI: https://github.com/adamwalter/admin-notice
    Description: Display a custom notice to all users in the admin area
    Version: 1.0
    Author: Adam Walter
    Author URI: http://adamwalter.com
    Text Domain: admin-notice
    License: GPLv2

    Copyright 2015  ADAM WALTER  (email : hello@adamwalter.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

defined( 'ABSPATH' ) or die( 'Do not access this file directly.' );

/**
 *  Setup
 */

function agw_admin_notice_init() {
     $plugin_dir = basename( dirname( __FILE__ ) . '/languages' );
     load_plugin_textdomain( 'admin-notice', false, $plugin_dir );
}

function agw_admin_notice_activation() {
    if ( ! current_user_can( 'activate_plugins' ) )
        return;
    $plugin = isset( $_REQUEST['plugin'] ) ? $_REQUEST['plugin'] : '';
    check_admin_referer( "activate-plugin_{$plugin}" );
}

function agw_admin_notice_deactivation() {
    if ( ! current_user_can( 'activate_plugins' ) )
        return;
    $plugin = isset( $_REQUEST['plugin'] ) ? $_REQUEST['plugin'] : '';
    check_admin_referer( "deactivate-plugin_{$plugin}" );
}

function agw_admin_notice_uninstall() {
    if ( ! current_user_can( 'activate_plugins' ) )
        return;
    delete_option( 'agw_admin_notice_msg' );
    delete_option( 'agw_admin_notice_priority' );
    delete_option( 'agw_admin_notice_enable' );
}

register_activation_hook( __FILE__, 'agw_admin_notice_activation' );
register_deactivation_hook( __FILE__, 'agw_admin_notice_deactivation' );
register_uninstall_hook( __FILE__, 'agw_admin_notice_uninstall' );

/**
 *  Options
 */

function agw_admin_notice_settings_register() {
    register_setting( 'agw_admin_notice_settings_group', 'agw_admin_notice_msg', 'agw_admin_notice_msg_validate' );
    register_setting( 'agw_admin_notice_settings_group', 'agw_admin_notice_priority' );
    register_setting( 'agw_admin_notice_settings_group', 'agw_admin_notice_enable' );
}

function agw_admin_notice_settings_options() {
    add_dashboard_page(
        'Admin Notice',
        'Admin Notice',
        'edit_dashboard',
        'admin_notice',
        'agw_admin_notice_settings_page'
    );
}

function agw_admin_notice_settings_page() {
?>
<div class="wrap">
<h2><?php _e( 'Admin Notice', 'admin-notice' ); ?></h2>
<form method="post" action="options.php">

    <?php settings_fields( 'agw_admin_notice_settings_group' ); ?>
    <?php do_settings_sections( 'agw_admin_notice_settings_group' ); ?>

    <table class="form-table">
        <tr valign="top">
            <th scope="row"><label for="agw_admin_notice_msg"><?php _e( 'Message', 'admin-notice' ); ?></label></th>
            <td>
                <?php
                $message = get_option( 'agw_admin_notice_msg' );
                $editor_args = array(
                    'textarea_name' => 'agw_admin_notice_msg',
                    'media_buttons' => false,
                    'textarea_rows' => 6,
                    'quicktags' => false,
                    'tinymce' => array(
                        'toolbar1' => 'bold,italic,underline,link,unlink,removeformat,undo,redo',
                        'toolbar2' => ''
                    )
                );
                wp_editor( $message, 'agw_admin_notice_msg', $editor_args );
                ?>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><label for="agw_admin_notice_priority"><?php _e( 'Priority', 'admin-notice' ); ?></label></th>
            <td>
                <?php $priority = get_option( 'agw_admin_notice_priority', 'high' ); ?>
                <label><input type="radio" id="agw_admin_notice_priority" name="agw_admin_notice_priority" <?php echo ( $priority === 'high' ? 'checked="checked"' : '' ); ?> value="high"><?php _e( 'High', 'admin-notice' ); ?></label>
                <label><input type="radio" id="agw_admin_notice_priority" name="agw_admin_notice_priority" <?php echo ( $priority === 'medium' ? 'checked="checked"' : '' ); ?> value="medium"><?php _e( 'Medium', 'admin-notice' ); ?></label>
                <label><input type="radio" id="agw_admin_notice_priority" name="agw_admin_notice_priority" <?php echo ( $priority === 'low' ? 'checked="checked"' : '' ); ?> value="low"><?php _e( 'Low', 'admin-notice' ); ?></label>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><label for="agw_admin_notice_enable"><?php _e('', 'admin-notice'); ?>Enable/Disable</label></th>
            <td>
                <?php $enabled = get_option( 'agw_admin_notice_enable', 'false' ); ?>
                <label><input type="radio" id="agw_admin_notice_enable" name="agw_admin_notice_enable" <?php echo ( $enabled === 'true' ? 'checked="checked"' : '' ); ?> value="true" /><?php _e( 'Enable', 'admin-notice' ); ?></label>
                <label><input type="radio" id="agw_admin_notice_enable" name="agw_admin_notice_enable" <?php echo ( $enabled === 'false' ? 'checked="checked"' : '' ); ?> value="false" /><?php _e( 'Disable', 'admin-notice' ); ?></label>
            </td>
        </tr>
    </table>

    <?php submit_button(); ?>

</form>
</div>
<?php }

function agw_admin_notice_msg_validate( $input ) {

    $allowed_html = array(
        'a' => array(
            'href' => array(),
            'title' => array(),
            'target' => array()
        ),
        'em' => array(),
        'strong' => array(),
        'span' => array(
            'style' => array()
        )
    );
    $allowed_protocols = array(
        'http' => array(),
        'https' => array(),
        'mailto' => array()
    );

    $input = wp_kses( $input, $allowed_html, $allowed_protocols );

    return $input;
}

function agw_admin_notice_action_links( $links ) {
    $custom_links = array(
         '<a href="' . admin_url( 'index.php?page=admin_notice' ) . '">Settings</a>',
         );
    return array_merge( $custom_links, $links );
}

/**
 *  Output
 */

function agw_admin_notice() {

    $message = get_option( 'agw_admin_notice_msg' );
    $enabled = get_option( 'agw_admin_notice_enable' );
    $priority = get_option( 'agw_admin_notice_priority' );

    if ( $message !== '' && $enabled === 'true' ) { ?>

        <div class="agw-admin-notice-wrap">
            <div class="agw-admin-notice agw-admin-notice-<?php echo esc_attr($priority); ?>">
                <p><?php echo $message; ?></p>
            </div>
        </div>

    <?php }
}

function agw_admin_notice_css() {

    echo "<style>
        .agw-admin-notice-wrap {
            margin: 2.75em 20px 0 2px;
        }
        .agw-admin-notice {
            padding: 1em 1.25em;
            background: #fff;
            -webkit-box-shadow: 0 1px 1px 0 rgba(0,0,0,.1);
                    box-shadow: 0 1px 1px 0 rgba(0,0,0,.1);
        }
        .agw-admin-notice p {
            margin: 0;
            font-size: 1em;
        }
        .agw-admin-notice-high,
        .agw-admin-notice-high a,
        .agw-admin-notice-medium,
        .agw-admin-notice-medium a,
        .agw-admin-notice-low,
        .agw-admin-notice-low a {
            color: #fff;
        }
        .agw-admin-notice-high {
            background-color: #dd3d36;
        }
        .agw-admin-notice-medium {
            background-color: #ffba00;
        }
        .agw-admin-notice-low {
            background-color: #7ad03a;
        }
    </style>\n";
}

if ( is_admin() ) {

    add_action( 'plugins_loaded', 'agw_admin_notice_init' );
    add_action( 'admin_init', 'agw_admin_notice_settings_register' );
    add_action( 'admin_menu', 'agw_admin_notice_settings_options' );
    add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'agw_admin_notice_action_links' );
    add_action( 'admin_notices', 'agw_admin_notice' );
    add_action( 'admin_head', 'agw_admin_notice_css' );

}