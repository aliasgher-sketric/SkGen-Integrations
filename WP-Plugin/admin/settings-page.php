<?php
/**
 * SketricGen Chat Widget Admin Settings
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Add admin menu
function sketricgen_add_admin_menu() {
    add_options_page(
        'SketricGen Chat Widget', 
        'SketricGen Chat', 
        'manage_options', 
        'sketricgen-chat-widget', 
        'sketricgen_options_page'
    );
}
add_action('admin_menu', 'sketricgen_add_admin_menu');

// Register settings
function sketricgen_register_settings() {
    register_setting(
        'sketricgen_options_group', 
        'sketricgen_options', 
        'sketricgen_sanitize_options'
    );
    
    add_settings_section(
        'sketricgen_settings_section', 
        'Widget Settings', 
        'sketricgen_settings_section_callback', 
        'sketricgen-chat-widget'
    );
    
    add_settings_field(
        'enabled', 
        'Enable Widget', 
        'sketricgen_enabled_field_callback', 
        'sketricgen-chat-widget', 
        'sketricgen_settings_section'
    );
    
    add_settings_field(
        'assistant_id', 
        'Agent ID', 
        'sketricgen_assistant_id_field_callback', 
        'sketricgen-chat-widget', 
        'sketricgen_settings_section'
    );
    
    add_settings_field(
        'api_secret_key', 
        'API Secret Key', 
        'sketricgen_api_secret_key_field_callback', 
        'sketricgen-chat-widget', 
        'sketricgen_settings_section'
    );
    
    add_settings_field(
        'style_config', 
        'Style Configuration', 
        'sketricgen_style_config_field_callback', 
        'sketricgen-chat-widget', 
        'sketricgen_settings_section'
    );
}
add_action('admin_init', 'sketricgen_register_settings');

// Settings section callback
function sketricgen_settings_section_callback() {
    echo '<p>Configure your SketricGen AI chat widget. You\'ll need your Agent ID and API Secret Key from the SketricGen dashboard.</p>';
}

// Enabled field callback
function sketricgen_enabled_field_callback() {
    $options = get_option('sketricgen_options');
    $enabled = isset($options['enabled']) ? $options['enabled'] : false;
    
    echo '<input type="checkbox" id="enabled" name="sketricgen_options[enabled]" ' . checked($enabled, true, false) . ' />';
    echo '<label for="enabled"> Enable the SketricGen chat widget on your site</label>';
}

// Assistant ID field callback
function sketricgen_assistant_id_field_callback() {
    $options = get_option('sketricgen_options');
    $assistant_id = isset($options['assistant_id']) ? $options['assistant_id'] : '';
    
    echo '<input type="text" id="assistant_id" name="sketricgen_options[assistant_id]" value="' . esc_attr($assistant_id) . '" class="regular-text" />';
    echo '<p class="description">Enter the Agent ID from your SketricGen dashboard</p>';
}

// API Secret Key field callback
function sketricgen_api_secret_key_field_callback() {
    $options = get_option('sketricgen_options');
    $api_secret_key = isset($options['api_secret_key']) ? $options['api_secret_key'] : '';
    
    echo '<input type="password" id="api_secret_key" name="sketricgen_options[api_secret_key]" value="' . esc_attr($api_secret_key) . '" class="regular-text" autocomplete="off" />';
    echo '<p class="description">Enter your API Secret Key from the SketricGen dashboard (this authorizes your widget)</p>';
}

// Style Configuration field callback
function sketricgen_style_config_field_callback() {
    $options = get_option('sketricgen_options');
    $default_config = json_encode([
        'position' => 'bottom-right',
        'primaryColor' => '#3884fc',
        'buttonIcon' => 'chat',
        'headerText' => 'Chat with us',
        'isDarkMode' => false
    ], JSON_PRETTY_PRINT);
    
    $style_config = isset($options['style_config']) && !empty($options['style_config']) ? 
        $options['style_config'] : $default_config;
    
    echo '<textarea id="style_config" name="sketricgen_options[style_config]" rows="10" class="large-text code">' . 
        esc_textarea($style_config) . '</textarea>';
    echo '<p class="description">Enter JSON configuration for widget styling. Available options include position, primaryColor, buttonIcon, etc.</p>';
    echo '<p class="description">Example: <code>' . esc_html($default_config) . '</code></p>';
}

// Sanitize options
function sketricgen_sanitize_options($input) {
    $sanitized_input = array();
    
    $sanitized_input['enabled'] = isset($input['enabled']);
    $sanitized_input['assistant_id'] = sanitize_text_field($input['assistant_id']);
    $sanitized_input['api_secret_key'] = sanitize_text_field($input['api_secret_key']);
    
    // Sanitize and validate JSON input
    if (isset($input['style_config'])) {
        $json_config = trim($input['style_config']);
        if (!empty($json_config)) {
            $decoded = json_decode($json_config, true);
            if ($decoded === null && json_last_error() !== JSON_ERROR_NONE) {
                // Invalid JSON, add error and use default
                add_settings_error(
                    'sketricgen_options',
                    'invalid_json',
                    'Invalid JSON in Style Configuration. Please check your syntax.',
                    'error'
                );
                // Keep the previous valid value or use default
                $options = get_option('sketricgen_options', array());
                $sanitized_input['style_config'] = isset($options['style_config']) ? $options['style_config'] : '{}';
            } else {
                // Valid JSON
                $sanitized_input['style_config'] = $json_config;
            }
        } else {
            $sanitized_input['style_config'] = '{}';
        }
    } else {
        $sanitized_input['style_config'] = '{}';
    }
    
    return $sanitized_input;
}

// Options page HTML
function sketricgen_options_page() {
    ?>
    <div class="wrap">
        <h1>SketricGen Chat Widget Settings</h1>
        <form action="options.php" method="post">
            <?php
            settings_fields('sketricgen_options_group');
            do_settings_sections('sketricgen-chat-widget');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}
