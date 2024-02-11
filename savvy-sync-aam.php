<?php
/**
 * Plugin Name: Savvy Sync for AAM
 * Description: Syncs AAM User Settings for Savvy Solutions Client Sites
 * Version: 1.0
 * Author: Savvy Solutions
 * Author URI: https://sync.savvysolutions.digital/
 */

defined('ABSPATH') or die;

// Register activation and deactivation hooks
register_activation_hook(__FILE__, 'savvy_sync_activation');
register_deactivation_hook(__FILE__, 'savvy_sync_deactivation');

// Activation callback
function savvy_sync_activation() {
    if (!wp_next_scheduled('savvy_sync_cron_event')) {
        wp_schedule_event(time(), 'hourly', 'savvy_sync_cron_event');
    }
}

// Deactivation callback
function savvy_sync_deactivation() {
    $timestamp = wp_next_scheduled('savvy_sync_cron_event');
    wp_unschedule_event($timestamp, 'savvy_sync_cron_event');
}

// Hook into that action that'll fire every three hours
add_action('savvy_sync_cron_event', 'savvy_sync_update_db');

// Function to fetch text and update DB
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

// Fetch text from URL
function savvy_sync_fetch_text_from_url($url) {
    $response = wp_remote_get($url);
    if (is_wp_error($response)) {
        return '';
    }
    $body = wp_remote_retrieve_body($response);
    return $body;
}
