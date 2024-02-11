<?php
/**
 * Plugin Name: Savvy Sync for AAM
 * Description: Syncs AAM User Settings for Savvy Solutions Client Sites
 * Version: 1.1
 * Author: Savvy Solutions
 * Author URI: https://sync.savvysolutions.digital/
 */

defined('ABSPATH') or die;

// Listen for our webhook trigger
add_action('init', 'check_for_savvy_sync_webhook');

function check_for_savvy_sync_webhook() {
    // Check if our specific GET parameters are present
    if (isset($_GET['savvy_sync_trigger']) && $_GET['savvy_sync_trigger'] == '1') {
        // Simple security check - replace 'your_secret_token' with a real secret token
        $token = isset($_GET['token']) ? $_GET['token'] : '';
        if ($token === 'your_secret_token') {
            savvy_sync_update_db();
            wp_die('Text fetched and database updated successfully.'); // Stop execution and output message
        } else {
            wp_die('Invalid token.', 'Authentication Error', array('response' => 403));
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
