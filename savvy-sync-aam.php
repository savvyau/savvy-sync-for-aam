<?php
/**
 * Plugin Name: Savvy Sync for AAM
 * Description: Syncs AAM User Settings for Savvy Solutions Client Sites
 * Version: 1.3.2
 * Author: Savvy Solutions
 * Author URI: https://sync.savvysolutions.digital/
 */

defined('ABSPATH') or die;

// Listen for our webhook trigger
function check_for_savvy_sync_webhook() {
    $allowed_ips = [get_option('savvy_sync_allowed_ip')]; // Now fetches from the WordPress options
    $request_ip = $_SERVER['REMOTE_ADDR'];
    $token = isset($_GET['token']) ? $_GET['token'] : '';
    
    if (isset($_GET['savvy_sync_trigger']) && $_GET['savvy_sync_trigger'] == '1') {
        if (in_array($request_ip, $allowed_ips) && $token === get_option('savvy_sync_token')) {
            savvy_sync_update_db();
            wp_die('Text fetched and database updated successfully.');
        } else {
            wp_die('Access denied: IP address or token is not authorized.', 'Access Denied', array('response' => 403));
        }
    }
}

function savvy_sync_update_db() {
    // Get the remote text file URL from the plugin settings
    $textFileUrl = get_option('savvy_sync_remote_url');
    
    // Fetch the text content from the remote URL
    $rawText = savvy_sync_fetch_text_from_url($textFileUrl);

    if (!empty($rawText)) {
        global $wpdb;
        $tableName = $wpdb->prefix . 'options';
        $dataToUpdate = ['option_value' => $rawText];
        $where = ['option_name' => 'your_option_name'];
        
        $wpdb->update($tableName, $dataToUpdate, $where);
    }
}

function savvy_sync_fetch_text_from_url($url) {
    $response = wp_remote_get($url);
    if (is_wp_error($response)) {
        return ''; // Return empty string if there's an error
    }
    $body = wp_remote_retrieve_body($response);
    return $body; // Return the fetched text content
}
// Add an admin page and menu item
add_action('admin_menu', 'savvy_sync_admin_menu');

function savvy_sync_admin_menu() {
    add_menu_page(
        'Savvy Sync Settings', // Page title
        'Savvy Sync', // Menu title
        'manage_options', // Capability
        'savvy-sync-settings', // Menu slug
        'savvy_sync_settings_page', // Function to display the page
        'dashicons-admin-generic', // Icon URL
        90 // Position
    );
}

// Display the settings page
function savvy_sync_settings_page() {
?>
<div class="wrap">
    <h2>Savvy Sync Settings</h2>
    <form method="post" action="options.php">
        <?php
            settings_fields('savvy-sync-settings-group');
            do_settings_sections('savvy-sync-settings-group');
        ?>
        <table class="form-table">
            <tr valign="top">
                <th scope="row">Remote Text File URL</th>
                <td><input type="text" name="savvy_sync_remote_url" value="<?php echo esc_attr(get_option('savvy_sync_remote_url')); ?>" /></td>
            </tr>
            <tr valign="top">
                <th scope="row">Allowed IP Address</th>
                <td><input type="text" name="savvy_sync_allowed_ip" value="<?php echo esc_attr(get_option('savvy_sync_allowed_ip')); ?>" /></td>
            </tr>
             
            <tr valign="top">
                <th scope="row">Token</th>
                <td><input type="text" name="savvy_sync_token" value="<?php echo esc_attr(get_option('savvy_sync_token')); ?>" /></td>
            </tr>
        </table>
        
        <?php submit_button(); ?>
    </form>
</div>
<?php
}
// Register settings
add_action('admin_init', 'savvy_sync_register_settings');

function savvy_sync_register_settings() {
    // Register our settings
    register_setting('savvy-sync-settings-group', 'savvy_sync_remote_url');
    register_setting('savvy-sync-settings-group', 'savvy_sync_allowed_ip');
    register_setting('savvy-sync-settings-group', 'savvy_sync_token');
}
function check_for_savvy_sync_webhook() {
    $allowed_ips = [get_option('savvy_sync_allowed_ip')]; // Now fetches from the WordPress options
    $request_ip = $_SERVER['REMOTE_ADDR'];
    $token = isset($_GET['token']) ? $_GET['token'] : '';
    
    if (isset($_GET['savvy_sync_trigger']) && $_GET['savvy_sync_trigger'] == '1') {
        if (in_array($request_ip, $allowed_ips) && $token === get_option('savvy_sync_token')) {
            savvy_sync_update_db();
            wp_die('Text fetched and database updated successfully.');
        } else {
            wp_die('Access denied: IP address or token is not authorized.', 'Access Denied', array('response' => 403));
        }
    }
}
