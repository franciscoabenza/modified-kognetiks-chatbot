<?php
/*
 * Plugin Name: Chatbot ChatGPT
 * Plugin URI:  https://github.com/kognetiks/chatbot-chatgpt
 * Description: A simple plugin to add a Chatbot ChatGPT to your Wordpress Website.
 * Version:     1.6.7
 * Author:      Kognetiks.com
 * Author URI:  https://www.kognetiks.com
 * License:     GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *  
 * This program is free software; you can redistribute it and/or modify it under the terms of the GNU
 * General Public License version 2, as published by the Free Software Foundation. You may NOT assume
 * that you can use any other version of the GPL.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without
 * even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * 
 * You should have received a copy of the GNU General Public License
 * along with Chatbot ChatGPT. If not, see https://www.gnu.org/licenses/gpl-2.0.html.
 * 
*/

// If this file is called directly, die.
defined( 'WPINC' ) || die;

// If this file is called directly, die.
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

// Declare Globals here - Ver 1.6.3
global $wpdb;  // Declare the global $wpdb object

// Include necessary files
require_once plugin_dir_path(__FILE__) . 'includes/chatbot-chatgpt-globals.php'; // Globals - Ver 1.6.5

// Include necessary files - Custom GPT Assistants - Ver 1.6.7
require_once plugin_dir_path(__FILE__) . 'includes/chatbot-chatgpt-custom-gpt.php'; // Custom GPT Assistants - Ver 1.6.7

// Include necessary files - Knowledge Navigator
require_once plugin_dir_path(__FILE__) . 'includes/chatbot-chatgpt-kn-acquire.php'; // Knowledge Navigator Acquistion - Ver 1.6.3
require_once plugin_dir_path(__FILE__) . 'includes/chatbot-chatgpt-kn-acquire-words.php'; // Knowledge Navigator Acquistion - Ver 1.6.5
require_once plugin_dir_path(__FILE__) . 'includes/chatbot-chatgpt-kn-acquire-word-pairs.php'; // Knowledge Navigator Acquistion - Ver 1.6.5
require_once plugin_dir_path(__FILE__) . 'includes/chatbot-chatgpt-kn-analysis.php'; // Knowlege Navigator Analysis- Ver 1.6.2
require_once plugin_dir_path(__FILE__) . 'includes/chatbot-chatgpt-kn-db.php'; // Knowledge Navigator - Database Management - Ver 1.6.3
require_once plugin_dir_path(__FILE__) . 'includes/chatbot-chatgpt-kn-scheduler.php'; // Knowledge Navigator - Scheduler - Ver 1.6.3
require_once plugin_dir_path(__FILE__) . 'includes/chatbot-chatgpt-kn-settings.php'; // Knowlege Navigator - Settings - Ver 1.6.1

// Include necessary files
require_once plugin_dir_path(__FILE__) . 'includes/chatbot-chatgpt-db-management.php'; // Database Management for Reporting - Ver 1.6.3
require_once plugin_dir_path(__FILE__) . 'includes/chatbot-chatgpt-settings.php';
require_once plugin_dir_path(__FILE__) . 'includes/chatbot-chatgpt-settings-api-model.php'; // Refactoring Settings - Ver 1.5.0
require_once plugin_dir_path(__FILE__) . 'includes/chatbot-chatgpt-settings-api-test.php'; // Refactoring Settings - Ver 1.6.3
require_once plugin_dir_path(__FILE__) . 'includes/chatbot-chatgpt-settings-avatar.php'; // Refactoring Settings - Ver 1.5.0
require_once plugin_dir_path(__FILE__) . 'includes/chatbot-chatgpt-settings-buttons.php'; // Refactoring Settings - Ver 1.6.5
require_once plugin_dir_path(__FILE__) . 'includes/chatbot-chatgpt-settings-diagnostics.php'; // Refactoring Settings - Ver 1.6.5
require_once plugin_dir_path(__FILE__) . 'includes/chatbot-chatgpt-settings-links.php'; // Refactoring Settings - Ver 1.5.0
require_once plugin_dir_path(__FILE__) . 'includes/chatbot-chatgpt-settings-localize.php'; // Fixing localStorage - Ver 1.6.1
require_once plugin_dir_path(__FILE__) . 'includes/chatbot-chatgpt-settings-notices.php'; // Notices - Ver 1.6.3
require_once plugin_dir_path(__FILE__) . 'includes/chatbot-chatgpt-settings-premium.php'; // Refactoring Settings - Ver 1.5.0
require_once plugin_dir_path(__FILE__) . 'includes/chatbot-chatgpt-settings-registration.php'; // Refactoring Settings - Ver 1.5.0
require_once plugin_dir_path(__FILE__) . 'includes/chatbot-chatgpt-settings-reporting.php'; // Reporting - Ver 1.6.3
require_once plugin_dir_path(__FILE__) . 'includes/chatbot-chatgpt-settings-setup.php'; // Refactoring Settings - Ver 1.5.0
require_once plugin_dir_path(__FILE__) . 'includes/chatbot-chatgpt-settings-skins.php'; // Adpative Skins - Ver 1.6.7
require_once plugin_dir_path(__FILE__) . 'includes/chatbot-chatgpt-settings-support.php'; // Refactoring Settings - Ver 1.5.0
require_once plugin_dir_path(__FILE__) . 'includes/chatbot-chatgpt-shortcode.php';
require_once plugin_dir_path(__FILE__) . 'includes/chatbot-chatgpt-upgrade.php'; // Ver 1.6.7

// Diagnotics on/off setting can be found on the Settings tab - Ver 1.5.0
// update_option('chatbot_chatgpt_diagnostics', 'Off');
global $chatbot_chatgpt_diagnostics;
$chatbot_chatgpt_diagnostics = esc_attr(get_option('chatbot_chatgpt_diagnostics', 'Off'));

// Custom buttons on/off setting can be found on the Settings tab - Ver 1.6.5
global $chatbot_chatgpt_enable_custom_buttons;
$chatbot_chatgpt_enable_custom_buttons = esc_attr(get_option('chatbot_chatgpt_enable_custom_buttons', 'Off'));

// Suppress Notices on/off setting can be found on the Settings tab - Ver 1.6.5
global $chatbot_chatgpt_suppress_notices;
$chatbot_chatgpt_suppress_notices = esc_attr(get_option('chatbot_chatgpt_suppress_notices', 'Off'));

// Suppress Attribution on/off setting can be found on the Settings tab - Ver 1.6.5
global $chatbot_chatgpt_suppress_attribution;
$chatbot_chatgpt_suppress_attribution = esc_attr(get_option('chatbot_chatgpt_suppress_attribution', 'Off'));

// Context History - Ver 1.6.1
$context_history = [];

// Enqueue plugin scripts and styles
function chatbot_chatgpt_enqueue_scripts() {
    // Ensure the Dashicons font is properly enqueued - Ver 1.1.0
    wp_enqueue_style( 'dashicons' );
    wp_enqueue_style('chatbot-chatgpt-css', plugins_url('assets/css/chatbot-chatgpt.css', __FILE__));
    wp_enqueue_script('chatbot-chatgpt-js', plugins_url('assets/js/chatbot-chatgpt.js', __FILE__), array('jquery'), '1.0', true);
    // Enqueue the chatbot-chatgpt-local.js file - Ver 1.4.1
    wp_enqueue_script('chatbot-chatgpt-local', plugins_url('assets/js/chatbot-chatgpt-local.js', __FILE__), array('jquery'), '1.0', true);

    // Defaults for Ver 1.6.1
    $defaults = array(
        'chatgpt_bot_name' => 'Chatbot ChatGPT',
        // TODO IDEA - Add a setting to fix or randomize the bot prompt
        'chatgpt_chatbot_bot_prompt' => 'Enter your question ...',
        'chatgpt_initial_greeting' => 'Hello! How can I help you today?',
        'chatgpt_subsequent_greeting' => 'Hello again! How can I help you?',
        'chatgptStartStatus' => 'closed',
        'chatgptStartStatusNewVisitor' => 'closed',
        'chatgpt_disclaimer_setting' => 'No',
        'chatgpt_max_tokens_setting' => '150',
        'chatgpt_width_setting' => 'Narrow',
        'chatbot_chatgpt_diagnostics' => 'Off',
        'chatgpt_avatar_icon_setting' => 'icon-001.png',
        'chatgpt_avatar_icon_url_setting' => '',
        'chatgpt_custom_avatar_icon_setting' => 'icon-001.png',
        'chatgpt_avatar_greeting_setting' => 'Howdy!!! Great to see you today! How can I help you?',
        'chatgpt_model_choice' => 'gpt-3.5-turbo',
        'chatgpt_max_tokens_setting' => 150,
        'chatbot_chatgpt_conversation_context' => 'You are a versatile, friendly, and helpful assistant designed to support me in a variety of tasks.',
        'chatbot_chatgpt_enable_custom_buttons' => 'Off',
        'chatbot_chatgpt_custom_button_name_1' => '',
        'chatbot_chatgpt_custom_button_url_1' => '',
        'chatbot_chatgpt_custom_button_name_2' => '',
        'chatbot_chatgpt_custom_button_url_2' => '',
    );

    // Revised for Ver 1.5.0 
    $option_keys = array(
        'chatgpt_bot_name',
        'chatgpt_chatbot_bot_prompt', // Added in Ver 1.6.6
        'chatgpt_initial_greeting',
        'chatgpt_subsequent_greeting',
        'chatgptStartStatus',
        'chatgptStartStatusNewVisitor',
        'chatgpt_disclaimer_setting',
        'chatgpt_max_tokens_setting',
        'chatgpt_width_setting',
        'chatbot_chatgpt_diagnostics',
        // Avatar Options - Ver 1.5.0
        'chatgpt_avatar_icon_setting',
        'chatgpt_avatar_icon_url_setting',
        'chatgpt_custom_avatar_icon_setting',
        'chatgpt_avatar_greeting_setting',
        'chatbot_chatgpt_enable_custom_buttons',
        'chatbot_chatgpt_custom_button_name_1',
        'chatbot_chatgpt_custom_button_url_1',
        'chatbot_chatgpt_custom_button_name_2',
        'chatbot_chatgpt_custom_button_url_2',

    );

    $chatbot_settings = array();
    foreach ($option_keys as $key) {
        $default_value = isset($defaults[$key]) ? $defaults[$key] : '';
        $chatbot_settings[$key] = esc_attr(get_option($key, $default_value));
    }

    $chatbot_settings['iconBaseURL'] = plugins_url( 'assets/icons/', __FILE__ );
    wp_localize_script('chatbot-chatgpt-js', 'plugin_vars', array(
        'pluginUrl' => plugins_url('', __FILE__ ),
    ));

    wp_localize_script('chatbot-chatgpt-local', 'chatbotSettings', $chatbot_settings);

    wp_localize_script('chatbot-chatgpt-js', 'chatbot_chatgpt_params', array(
        'ajax_url' => admin_url('admin-ajax.php'),
    ));

    // Populate the chatbot settings array with values from the database, using default values where necessary
    $chatbot_settings = array();
    foreach ($option_keys as $key) {
        $default_value = isset($defaults[$key]) ? $defaults[$key] : '';
        $chatbot_settings[$key] = esc_attr(get_option($key, $default_value));
        // DIAG - Log key and value
        // error_log( 'Chatbot ChatGPT: chatbot-chatgpt Key: ' . $key . ', Value: ' . $chatbot_settings[$key]);
    }

    // Update localStorage - Ver 1.6.1
    echo "<script type=\"text/javascript\">
    document.addEventListener('DOMContentLoaded', (event) => {
        // Encode the chatbot settings array into JSON format for use in JavaScript
        let chatbotSettings = " . json_encode($chatbot_settings) . ";

        Object.keys(chatbotSettings).forEach((key) => {
            if(!localStorage.getItem(key)) {
                // DIAG - Log the key and value
                // console.log('Setting ' + key + ' in localStorage');
                localStorage.setItem(key, chatbotSettings[key]);
            } else {
                // DIAG - Log the key and value
                // console.log(key + ' is already set in localStorage');
            }
        });
    });
    </script>";
    
}
add_action('wp_enqueue_scripts', 'chatbot_chatgpt_enqueue_scripts');


// Settings and Deactivation Links - Ver - 1.5.0
function enqueue_jquery_ui() {
    wp_enqueue_style('wp-jquery-ui-dialog');
    wp_enqueue_script('jquery-ui-dialog');
}
add_action( 'admin_enqueue_scripts', 'enqueue_jquery_ui' );


// Handle Ajax requests
function chatbot_chatgpt_send_message() {
    // Retrieve the API key
    $api_key = esc_attr(get_option('chatgpt_api_key'));
    // Retrieve the Use Custom GPT Assistant Id
    $use_assistant_id = esc_attr(get_option('chatbot_chatgpt_use_custom_gpt_assistant_id'));
    // Retrieve the Assistant ID
    $assistant_id = esc_attr(get_option('chatbot_chatgpt_assistant_id'));
    // Retrieve the model from the settings or default to gpt-3.5-turbo
    $model = esc_attr(get_option('chatgpt_model_choice', 'gpt-3.5-turbo'));
    // Retrieve the Max tokens - Ver 1.4.2
    $max_tokens = esc_attr(get_option('chatgpt_max_tokens_setting', 150));

    // Send only clean text via the API
    $message = sanitize_text_field($_POST['message']);

    // FIXME - ADD THIS BACK IN AFTER DECIDING WHAT TO DO ABOUT MISSING OR BAD API KEYS
    // Check API key and message
    if (!$api_key || !$message) {
        wp_send_json_error('Invalid API key or message');
    }

    // Check if the Custom GPT Assistant Id is blank, null, or "Please provide the Customer GPT Assistant Id."
    if (empty($assistant_id) || $assistant_id == "Please provide the Customer GPT Assistant Id.") {
        // Override the $use_assistant_id and set it to 'No'
        $use_assistant_id = 'No';
        // DIAG - Log the response
        // error_log('Chatbot ChatGPT: chatbot-chatgpt.php $use_assistant_id override ' . print_r($use_assistant_id, true));
    }

    if ($use_assistant_id == 'Yes') {
        // Send message to Custom GPT API - Ver 1.6.7
        $response = chatbot_chatgpt_custom_gpt_call_api($api_key, $message);
        // DIAG - Log the response
        // error_log('Chatbot ChatGPT: chatbot-chatgpt.php - chatbot_chatgpt_custom_gpt_call_api - $response: ' . print_r($response, true));
        // Return response
        ob_clean(); // Clean (erase) the output buffer
        if (substr($response, 0, 6) === 'Error:' || substr($response, 0, 7) === 'Failed:') {
            // wp_send_json_error($response);
            wp_send_json_error('Oops! Something went wrong on our end. Please try again later.');
        } else {
            wp_send_json_success($response);
        }
        // wp_send_json_success($response);
    } else {
        // Send message to ChatGPT API
        $response = chatbot_chatgpt_call_api($api_key, $message);
        // DIAG - Log the response
        // error_log('Chatbot ChatGPT: chatbot-chatgpt.php - chatbot_chatgpt_call_api - $response: ' . print_r($response, true));
        // Return response
        wp_send_json_success($response);
    }

    wp_send_json_error('Oops, I fell through the cracks!');

}

add_action('wp_ajax_chatbot_chatgpt_send_message', 'chatbot_chatgpt_send_message');
add_action('wp_ajax_nopriv_chatbot_chatgpt_send_message', 'chatbot_chatgpt_send_message');

// Settings and Deactivation - Ver 1.5.0
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'chatbot_chatgpt_plugin_action_links');
add_action('wp_ajax_chatbot_chatgpt_deactivation_feedback', 'chatbot_chatgpt_deactivation_feedback');
add_action('admin_footer', 'chatbot_chatgpt_admin_footer');

// Crawler aka Knowledge Navigator - Ver 1.6.1
function chatbot_chatgpt_kn_status_activation() {
    add_option('chatbot_chatgpt_kn_status', 'Never Run');
    // clear any old scheduled runs
    if (wp_next_scheduled('crawl_scheduled_event_hook')) {
        // error_log( 'Chatbot ChatGPT: BEFORE wp_clear_scheduled_hook -  crawl_scheduled_event_hook');
        wp_clear_scheduled_hook('crawl_scheduled_event_hook');
    }
    // clear the 'knowledge_navigator_scan_hook' hook on plugin activation - Ver 1.6.3
    if (wp_next_scheduled('knowledge_navigator_scan_hook')) {
        // error_log( 'Chatbot ChatGPT: BEFORE wp_clear_scheduled_hook -  knowledge_navigator_scan_hook');
        wp_clear_scheduled_hook('knowledge_navigator_scan_hook'); // Clear scheduled runs
    }
}
register_activation_hook(__FILE__, 'chatbot_chatgpt_kn_status_activation');

// Clean Up in Aisle 4
function chatbot_chatgpt_kn_status_deactivation() {
    delete_option('chatbot_chatgpt_kn_status');
    wp_clear_scheduled_hook('knowledge_navigator_scan_hook'); 
}
register_deactivation_hook(__FILE__, 'chatbot_chatgpt_kn_status_deactivation');

// Function to add a new message and response, keeping only the last five - Ver 1.6.1
function addEntry($transient_name, $newEntry) {
    $context_history = get_transient($transient_name);
    if (!$context_history) {
        $context_history = [];
    }

    // Determine the total length of all existing entries
    $totalLength = 0;
    foreach ($context_history as $entry) {
        if (is_string($entry)) {
            $totalLength += strlen($entry);
        } elseif (is_array($entry)) {
            $totalLength += strlen(json_encode($entry)); // Convert to string if an array
        }
    }

    // IDEA - How will the new threading option from OpenAI change how this works?
    // Define thresholds for the number of entries to keep
    $maxEntries = 30; // Default maximum number of entries
    if ($totalLength > 5000) { // Higher threshold
        $maxEntries = 20;
    }
    if ($totalLength > 10000) { // Lower threshold
        $maxEntries = 10;
    }

    while (count($context_history) >= $maxEntries) {
        array_shift($context_history); // Remove the oldest element
    }

    if (is_array($newEntry)) {
        $newEntry = json_encode($newEntry); // Convert the array to a string
    }

    array_push($context_history, $newEntry); // Append the new element
    set_transient($transient_name, $context_history); // Update the transient
}


// Function to return message and response - Ver 1.6.1
function concatenateHistory($transient_name) {
    $context_history = get_transient($transient_name);
    if (!$context_history) {
        return ''; // Return an empty string if the transient does not exist
    }
    return implode(' ', $context_history); // Concatenate the array values into a single string
}


// Call the ChatGPT API
function chatbot_chatgpt_call_api($api_key, $message) {
    // Diagnostics - Ver 1.6.1
    global $chatbot_chatgpt_diagnostics;
    global $learningMessages;
    global $errorResponses;
    global $stopWords;

    // Reporting - Ver 1.6.3
    global $wpdb;

    // The current ChatGPT API URL endpoint for gpt-3.5-turbo and gpt-4
    $api_url = 'https://api.openai.com/v1/chat/completions';

    $headers = array(
        'Authorization' => 'Bearer ' . $api_key,
        'Content-Type' => 'application/json',
    );

    // Select the OpenAI Model
    // Get the saved model from the settings or default to "gpt-3.5-turbo"
    $model = esc_attr(get_option('chatgpt_model_choice', 'gpt-3.5-turbo'));
    // Max tokens - Ver 1.4.2
    $max_tokens = intval(esc_attr(get_option('chatgpt_max_tokens_setting', '150')));

    // Conversation Context - Ver 1.6.1
    $context = "";
    $context = esc_attr(get_option('chatbot_chatgpt_conversation_context', 'You are a versatile, friendly, and helpful assistant designed to support me in a variety of tasks.'));
 
    // Context History - Ver 1.6.1
     $chatgpt_last_response = concatenateHistory('context_history');
    // DIAG Diagnostics - Ver 1.6.1
    // error_log( 'Chatbot ChatGPT: context_history' . print_r($chatgpt_last_response, true));
    
    // IDEA Strip any href links and text from the $chatgpt_last_response
    $chatgpt_last_response = preg_replace('/\[URL:.*?\]/', '', $chatgpt_last_response);

    // IDEA Strip any $learningMessages from the $chatgpt_last_response
    $chatgpt_last_response = str_replace($learningMessages, '', $chatgpt_last_response);

    // IDEA Strip any $errorResponses from the $chatgpt_last_response
    $chatgpt_last_response = str_replace($errorResponses, '', $chatgpt_last_response);
    
    // Knowledge Navigator keyword append for context
    $chatbot_chatgpt_kn_conversation_context = get_option('chatbot_chatgpt_kn_conversation_context', '');

    // Append prior message, then context, then Knowledge Navigator - Ver 1.6.1
    $context = $chatgpt_last_response . ' ' . $context . ' ' . $chatbot_chatgpt_kn_conversation_context;

    // DIAG Diagnostics - Ver 1.6.1
    // error_log( 'Chatbot ChatGPT: $context: ' . print_r($context, true));

    // Added Role, System, Content Static Veriable - Ver 1.6.0
    $body = array(
        'model' => $model,
        'max_tokens' => $max_tokens,
        'temperature' => 0.5,
        'messages' => array(
            array('role' => 'system', 'content' => $context),
            array('role' => 'user', 'content' => $message)
            ),
    );

    // Context History - Ver 1.6.1
    addEntry('context_history', $message);

    // DIAG Diagnostics - Ver 1.6.1
    // error_log( 'Chatbot ChatGPT: storedc: ' . print_r($chatbot_chatgpt_kn_conversation_context, true));
    // error_log( 'Chatbot ChatGPT: context: ' . print_r($context, true));
    // error_log( 'Chatbot ChatGPT: message: ' . print_r($message, true));  

    $args = array(
        'headers' => $headers,
        'body' => json_encode($body),
        'method' => 'POST',
        'data_format' => 'body',
        'timeout' => 50, // Increase the timeout values to 15 seconds to wait just a bit longer for a response from the engine
    );

    $response = wp_remote_post($api_url, $args);
    // DIAG Diagnostics - Ver 1.6.7
    // error_log( 'Chatbot ChatGPT: chatbot-chatgpt.php - chatbot_chatgpt_call_api - $response: ' . print_r($response, true));

    // Handle any errors that are returned from the chat engine
    if (is_wp_error($response)) {
        return 'Error: ' . $response->get_error_message().' Please check Settings for a valid API key or your OpenAI account for additional information.';
    }

    // Return json_decode(wp_remote_retrieve_body($response), true);
    $response_body = json_decode(wp_remote_retrieve_body($response), true);
    if (isset($response_body['message'])) {
        $response_body['message'] = trim($response_body['message']);
        if (substr($response_body['message'], -1) !== '.') {
            $response_body['message'] .= '.';
        }
    }

    // Retrieve links to the highest scoring documents - Ver 1.6.3
    $table_name = $wpdb->prefix . 'chatbot_chatgpt_knowledge_base';
    $words = explode(" ", $message);
    $match_found = false;
    $highest_score = 0;
    $highest_score_word = "";
    $highest_score_url = "";

    // Strip out $stopWords
    $words = array_diff($words, $stopWords);
    // DIAG Error_log for $words - Ver 1.6.5
    // error_log( 'Chatbot ChatGPT: $words: ' . print_r($words, true));

    // Loop through each word in the message
    foreach ($words as $key => $word) {
        // Strip off any trailing punctuation
        $word = rtrim($word, ".,;:!?");
    
        // Check for plural
        // if (substr($word, -1) == "s") {
        //     $word_singular = substr($word, 0, -1);
        //     $words[] = $word_singular;
        // }
   
        // Remove s at end of any words - Ver 1.6.5 - 2023 10 11
        $word = rtrim($word, 's');

        // Count the number of $words
        $word_count = count($words);
    
        // Check if the key exists before accessing it
        if (isset($words[$key + 1])) {
            // Create the word pair
            $word_pair = $word . " " . $words[$key + 1];
    
            // Find the highest score for the word pair
            $result = $wpdb->get_row($wpdb->prepare("SELECT score, url FROM $table_name WHERE word = %s ORDER BY score DESC LIMIT 1", $word_pair));
            // Exit if there is an error
            if (!$wpdb->last_error) {
                if ($result !== null && $result->score > $highest_score) {
                    $highest_score = $result->score;
                    $highest_score_word = $word_pair;
                    $highest_score_url = $result->url;
                }
                // Add your success handling code here
            } else {
                // Handle error here
                $highest_score = 0;
            }
        }
    
        // Find the highest score for the word
        $result = $wpdb->get_row($wpdb->prepare("SELECT score, url FROM $table_name WHERE word = %s ORDER BY score DESC LIMIT 1", $word));
        // Exit if there is an error
        if (!$wpdb->last_error) {
            if ($result !== null && $result->score > $highest_score) {
                $highest_score = $result->score;
                $highest_score_word = $word;
                $highest_score_url = $result->url;
            }
            // Add your success handling code here
        } else {
            // Handle error here
            $highest_score = 0;
        }
    }

    if (!isset($response_body['content'])) {
        $response_body['content'] = "";
    }

    // DIAG Diagnostic - Ver 1.6.5
    // error_log( 'Chatbot ChatGPT: $highest_score: ' . print_r($highest_score, true));
    // error_log( 'Chatbot ChatGPT: $highest_score_word: ' . print_r($highest_score_word, true));
    // error_log( 'Chatbot ChatGPT: $highest_score_url: ' . print_r($highest_score_url, true));

    // IDEA Append message and link if found to ['choices'][0]['message']['urls']
    if ($highest_score > 0) {
        // Return the URL with the highest score
        $match_found = true;
        $response_body['choices'][0]['message']['urls'] = $highest_score_url;
        if (!isset($response_body['choices'][0]['message']['content'])) {
            $response_body['choices'][0]['message']['content'] = '';
        }
        $response_body['choices'][0]['message']['content'] .= $learningMessages[array_rand($learningMessages)];
        $response_body['choices'][0]['message']['content'] .= "[URL: " . $highest_score_url . "]";
    } else {
        // If no match is found, return a generic response
        $match_found = false;
        if (!isset($response_body['choices'][0]['message']['content'])) {
            $response_body['choices'][0]['message']['content'] = '';
        }
        // Only append $errorResponses if there is no response from the engine
        if (empty($response_body['choices'][0]['message']['content'])) {
            $response_body['choices'][0]['message']['content'] .= $errorResponses[array_rand($errorResponses)];
        }
    }

    // Find bolded text in $response_body['choices'][0]['message']['content'] and replace with <b> tags - Ver 1.6.3
    // $response_body['choices'][0]['message']['content'] = preg_replace('/\*\*(.*?)\*\*/', '<b>$1</b>', $response_body['choices'][0]['message']['content']);

    // Find <strong></strong> in $response_body['choices'][0]['message']['content'] and replace with <b> tags - Ver 1.6.3
    // $response_body['choices'][0]['message']['content'] = preg_replace('/<strong>(.*?)<\/strong>/', '<b>$1</b>', $response_body['choices'][0]['message']['content']);
    
    // Strip out any <strong></strong> tags in $response_body['choices'][0]['message']['content'] - Ver 1.6.3
    $response_body['choices'][0]['message']['content'] = preg_replace('/<strong>(.*?)<\/strong>/', '$1', $response_body['choices'][0]['message']['content']);
    // Strip out any <b></b> tags in $response_body['choices'][0]['message']['content'] - Ver 1.6.3
    $response_body['choices'][0]['message']['content'] = preg_replace('/<b>(.*?)<\/b>/', '$1', $response_body['choices'][0]['message']['content']);

    // DIAG - error_log for $score, $match_found, $highest_score, $highest_score_url - Diagnostic - Ver 1.6.3
    // error_log( 'Chatbot ChatGPT: $match_found: ' . print_r($match_found, true));
    // error_log( 'Chatbot ChatGPT: $highest_score: ' . print_r($highest_score, true));
    // error_log( 'Chatbot ChatGPT: $highest_score_word: ' . print_r($highest_score_word, true));
    // error_log( 'Chatbot ChatGPT: $highest_score_url: ' . print_r($highest_score_url, true));
    // error_log( 'Chatbot ChatGPT: $response_body: ' . print_r($response_body, true));
    
    // Interaction Tracking - Ver 1.6.3
    update_interaction_tracking();

    if (isset($response_body['choices']) && !empty($response_body['choices'])) {
        // Handle the response from the chat engine
        // Context History - Ver 1.6.1
        addEntry('context_history', $response_body['choices'][0]['message']['content']);
        return $response_body['choices'][0]['message']['content'];
    } else {
        // Handle any errors that are returned from the chat engine
        //
        // IDEA USE ALTERNATE MODEL TO GENERATE A RESPONSE HERE
        //
        // return 'Error: Unable to fetch response from ChatGPT API. Please check Settings for a valid API key or your OpenAI account for additional information.';

        // IDEA Return one of the $errorResponses - Ver 1.6.3
        // IDEA Belt and Suspenders - We shouldn't be here unless something went really wrong up above this point
        // return $errorResponses[array_rand($errorResponses)];
        return;
    }
}


function enqueue_greetings_script() {
    global $chatbot_chatgpt_diagnostics;

    // DIAG Diagnostics - Ver 1.6.1
    // error_log( 'Chatbot ChatGPT: ENTERING enqueue_greetings_script');

    wp_enqueue_script('greetings', plugin_dir_url(__FILE__) . 'assets/js/greetings.js', array('jquery'), null, true);

    $greetings = array(
        'initial_greeting' => esc_attr(get_option('chatgpt_initial_greeting', 'Hello! How can I help you today?')),
        'subsequent_greeting' => esc_attr(get_option('chatgpt_subsequent_greeting', 'Hello again! How can I help you?')),
    );

    wp_localize_script('greetings', 'greetings_data', $greetings);

    // DIAG Diagnostics - Ver 1.6.1
    // error_log( 'Chatbot ChatGPT: EXITING enqueue_greetings_script');

}
add_action('wp_enqueue_scripts', 'enqueue_greetings_script');
