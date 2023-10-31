<?php
/*
Plugin Name: Localise Media
Description: Displays an OpenStreetMap when the 'loc_media' shortcode is used.
Version: 1.0
Author: Your Name
*/

// Plugin code goes here
function enqueue_loc_media_script() {
    if (has_shortcode(get_post()->post_content, 'loc_media')) {
        wp_enqueue_script('jquery');  // Enqueue jQuery
        wp_enqueue_script('openlayers', 'https://cdn.jsdelivr.net/npm/ol@v8.1.0/dist/ol.js', array(), null, true);
        wp_enqueue_script('loc-media', plugin_dir_url(__FILE__) . 'loc-media.js', array('openlayers'), null, true);
    }
}
add_action('wp_enqueue_scripts', 'enqueue_loc_media_script');

function loc_media_shortcode($atts) {
    return '<div id="loc-media-container" style="height: 400px;"></div>';
}
add_shortcode('loc_media', 'loc_media_shortcode');

// settings
///////////

// define the settinhs
function loc_media_default_settings() {
    $defaults = array(
        'map_height' => '400',
        'map_width' => '100%',
    );
    return apply_filters('loc_media_default_settings', $defaults);
}

// register the settings
function loc_media_register_settings() {
    $default_settings = loc_media_default_settings();

    register_setting('loc_media_settings', 'loc_media_settings', 'loc_media_sanitize_settings');

    add_settings_section('loc_media_general_section', 'General Settings', 'loc_media_general_section_callback', 'loc_media_settings');

    add_settings_field('map_height', 'Map Height', 'loc_media_map_height_callback', 'loc_media_settings', 'loc_media_general_section');
    add_settings_field('map_width', 'Map Width', 'loc_media_map_width_callback', 'loc_media_settings', 'loc_media_general_section');
}
add_action('admin_init', 'loc_media_register_settings');

// sanitize and validate settings
function loc_media_sanitize_settings($input) {
    $sanitized_input = array();

    if (isset($input['map_height'])) {
        $sanitized_input['map_height'] = sanitize_text_field($input['map_height']);
    }

    if (isset($input['map_width'])) {
        $sanitized_input['map_width'] = sanitize_text_field($input['map_width']);
    }

    return $sanitized_input;
}

// callback functions
function loc_media_general_section_callback() {
    echo 'General settings for the Localize Media plugin.';
}

function loc_media_map_height_callback() {
    $options = get_option('loc_media_settings');
    echo "<input type='text' id='map_height' name='loc_media_settings[map_height]' value='{$options['map_height']}' />";
}

function loc_media_map_width_callback() {
    $options = get_option('loc_media_settings');
    echo "<input type='text' id='map_width' name='loc_media_settings[map_width]' value='{$options['map_width']}' />";
}

// create the Settings page
function loc_media_settings_page() {
    ?>
    <div class="wrap">
        <h2>Localize Media Settings</h2>
        <form method="post" action="options.php">
            <?php settings_fields('loc_media_settings'); ?>
            <?php do_settings_sections('loc_media_settings'); ?>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

// Add to admin menu
function loc_media_menu() {
    add_options_page('Localize Media Settings', 'Localize Media', 'manage_options', 'loc-media-settings', 'loc_media_settings_page');
}
add_action('admin_menu', 'loc_media_menu');

// zdd link to settings in the plugin page
function loc_media_plugin_action_links($links) {
    $settings_link = '<a href="' . admin_url('options-general.php?page=loc-media-settings') . '">Settings</a>';
    array_unshift($links, $settings_link);
    return $links;
}

$plugin = plugin_basename(__FILE__);
add_filter("plugin_action_links_$plugin", 'loc_media_plugin_action_links');

// media custom fields
//////////////////////

function add_image_attachment_fields_to_edit( $form_fields, $post ) {

    error_log('add_image_attachment_fields_to_edit IN');
	

	// Add a Credit field
	$form_fields["credit_text"] = array(
		"label" => __("Credit"),
		"input" => "text", // this is default if "input" is omitted
		"value" => esc_attr( get_post_meta($post->ID, "_credit_text", true) ),
		"helps" => __("The owner of the image."),
	);
	
	// Add a Credit field
	$form_fields["credit_link"] = array(
		"label" => __("Credit URL"),
		"input" => "text", // this is default if "input" is omitted
		"value" => esc_url( get_post_meta($post->ID, "_credit_link", true) ),
		"helps" => __("Attribution link to the image source or owners website."),
	);
	
	// Add Caption before Credit field 
	error_log('add_image_attachment_fields_to_edit OUT');
	return $form_fields;
}
function add_image_attachment_fields_to_save( $post, $attachment ) {
    error_log('add_image_attachment_fields_to_save IN');
	if ( isset( $attachment['credit_text'] ) )
		update_post_meta( $post['ID'], '_credit_text', esc_attr($attachment['credit_text']) );
		
	if ( isset( $attachment['credit_link'] ) )
		update_post_meta( $post['ID'], '_credit_link', esc_url($attachment['credit_link']) );

	return $post;
}

// function add_custom_media_field($form_fields, $post) {
//     error_log('add_custom_media_field IN');
//     $form_fields['custom_field'] = array(
//         'label' => 'Custom Field',
//         'input' => 'text',
//         'value' => get_post_meta($post->ID, 'custom_field', true),
//     );
//     return $form_fields;
// }

// function save_custom_media_field($post, $attachment) {
//     error_log('save_custom_media_field IN');
//     if (isset($attachment['custom_field'])) {
//         update_post_meta($post['ID'], 'custom_field', $attachment['custom_field']);
//     }
//     return $post;
// }
function loc_media_activation_function() {
    error_log('loc_media_activation_function IN');
    apply_filter("attachment_fields_to_edit", "add_image_attachment_fields_to_edit", null, 2);
    //add_filter('attachment_fields_to_edit', 'add_custom_media_field', 10, 2);
    //add_filter('attachment_fields_to_save', 'save_custom_media_field', 10, 2);
    apply_filter("attachment_fields_to_save", "add_image_attachment_fields_to_save", null , 2);
    error_log('loc_media_activation_function OUT');
}

add_filter("attachment_fields_to_edit", "add_image_attachment_fields_to_edit", null, 2);

?>
