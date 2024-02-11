<?php
/**
 * Plugin Name: Savvy Sync for AAM
 * Description: Syncs AAM User Settings for Savvy Solutions Client Sites
 * Version: 1.2
 * Author: Savvy Solutions
 * Author URI: https://sync.savvysolutions.digital/
 */

defined('ABSPATH') or die;

// Listen for our webhook trigger
function check_for_savvy_sync_webhook() {
    // Specify the allowed IP address(es)
    $allowed_ips = ['123.123.123.123']; // Replace this with the actual IP address(es) you expect the request to come from

    // Get the IP address of the request
    $request_ip = $_SERVER['REMOTE_ADDR'];

    // Check if our specific GET parameters are present and if the request is from an allowed IP
    if (isset($_GET['savvy_sync_trigger']) && $_GET['savvy_sync_trigger'] == '1' && in_array($request_ip, $allowed_ips)) {
        // Simple security check - replace 'your_secret_token' with a real secret token
        $token = isset($_GET['token']) ? $_GET['token'] : '';
        if ($token === 'your_secret_token') {
            savvy_sync_update_db();
            wp_die('Text fetched and database updated successfully.'); // Stop execution and output message
        } else {
            wp_die('Invalid token.', 'Authentication Error', array('response' => 403));
        }
    } elseif (isset($_GET['savvy_sync_trigger']) && $_GET['savvy_sync_trigger'] == '1') {
        // If the trigger is present but the IP address is not allowed
        wp_die('Access denied: Your IP address is not authorized to perform this action.', 'IP Address Error', array('response' => 403));
    }
}
}

function savvy_sync_update_db() {
    $textFileUrl = 'YOUR_TEXT_FILE_URL_HERE';
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
        return '';
    }
    $body = wp_remote_retrieve_body($response);
    return $body;
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
