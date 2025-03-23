<?php
/**
 * Plugin Name: SketricGen Chat Widget
 * Plugin URI: https://sketricgen.ai/
 * Description: Integrates SketricGen AI chat widget into your WordPress site.
 * Version: 1.0.0
 * Author: Sketric Solutions
 * Author URI: https://sketricgen.ai/
 * Text Domain: sketricgen-chat-widget
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('SKETRICGEN_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('SKETRICGEN_PLUGIN_URL', plugin_dir_url(__FILE__));
// Removed hardcoded API URL constant as it's already in the source script

// Include admin settings file
require_once SKETRICGEN_PLUGIN_PATH . 'admin/settings-page.php';

// Enqueue scripts and styles
function sketricgen_enqueue_scripts() {
    // Only enqueue if widget is enabled
    $options = get_option('sketricgen_options', array());
    $enabled = isset($options['enabled']) ? $options['enabled'] : false;
    $assistant_id = isset($options['assistant_id']) ? $options['assistant_id'] : '';
    $api_secret_key = isset($options['api_secret_key']) ? $options['api_secret_key'] : '';
    $style_config = isset($options['style_config']) ? $options['style_config'] : '{}';
    
    if ($enabled && !empty($assistant_id) && !empty($api_secret_key)) {
        // Create a global configuration object before loading the widget
        wp_add_inline_script(
            'jquery', 
            'window.SketricGenConfig = {' .
            '  agentId: "' . esc_js($assistant_id) . '",' .
            '  apiKey: "' . esc_js($api_secret_key) . '",' .
            '  styleConfig: ' . $style_config .
            '};',
            'after'
        );
        
        // Enqueue the SketricGen widget script
        wp_enqueue_script(
            'sketricgen-widget', 
            SKETRICGEN_PLUGIN_URL . 'assets/js/sketricgen-widget.js',
            array('jquery'), 
            '1.0.0', 
            true
        );
    }
}
add_action('wp_enqueue_scripts', 'sketricgen_enqueue_scripts');

// Activation hook
function sketricgen_activate() {
    // Set default options
    $default_options = array(
        'enabled' => false,
        'assistant_id' => '',
        'api_secret_key' => '',
        'style_config' => json_encode([
            'position' => 'bottom-right',
            'primaryColor' => '#3884fc'
        ])
    );
    
    add_option('sketricgen_options', $default_options);
}
register_activation_hook(__FILE__, 'sketricgen_activate');

// Deactivation hook
function sketricgen_deactivate() {
    // Cleanup if needed
}
register_deactivation_hook(__FILE__, 'sketricgen_deactivate');
