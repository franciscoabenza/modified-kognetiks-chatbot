<?php
/**
 * Kognetiks Chatbot for WordPress - Download File from API - Ver 2.0.3
 *
 * This file contains the code for downloading a file generated by an Assistant.
 * 
 *
 * @package chatbot-chatgpt
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

function download_an_openai_file($file_id) {

    global $session_id;

    $downloads_dir = CHATBOT_CHATGPT_PLUGIN_DIR_PATH . 'downloads/';

    // Ensure the directory exists or attempt to create it
    if (!file_exists($downloads_dir) && !wp_mkdir_p($downloads_dir)) {
        // Error handling, e.g., log the error or handle the failure appropriately
        $responses[] = array(
            'status' => 'error',
            'message' => 'Oops! File download failed.'
        );
        http_response_code(500); // Send a 500 Internal Server Error status code
        exit;
    } else {
        $index_file_path = $downloads_dir . 'index.php';
        if (!file_exists($index_file_path)) {
            $file_content = "<?php\n// Silence is golden.\n?>";
            file_put_contents($index_file_path, $file_content);
        }
    }
    // Protect the directory - Ver 2.0.0
    chmod($downloads_dir, 0700);

    $api_key = esc_attr(get_option('chatbot_chatgpt_api_key'));
    if (empty($api_key)) {
        $responses[] = array(
            'status' => 'error',
            'message' => 'Oops! Your API key is missing. Please enter your API key in the Chatbot settings.'
        );
        http_response_code(500); // Send a 500 Internal Server Error status code
        exit;
    }

    // API endpoint to retrieve the file content
    $api_file_url = "https://api.openai.com/v1/files/$file_id/content";

    // Initialize cURL session
    $ch = curl_init($api_file_url);

    // Set cURL options
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $api_key"
    ]);

    // Execute cURL request
    $response = curl_exec($ch);

    // Check for cURL errors
    if (curl_errno($ch)) {
        error_log('cURL error: ' . curl_error($ch));
        curl_close($ch);
        return false;
    }

    // Get HTTP status code
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    // Close cURL session
    curl_close($ch);

    // Check if the request was successful
    if ($http_code == 200) {
        // Define the file path
        $file_path = $downloads_dir . 'downloaded_file';

        // Save the file content locally
        file_put_contents($file_path, $response);

        // Return the file URL
        $responses[] = array(
            'status' => 'success',
            'message' => 'File downloaded successfully. ' . content_url( $downloads_dir . 'downloaded_file')
        );
        return true;
    } else {
        $responses[] = array(
            'status' => 'error',
            'message' => 'Oops! Failed to retrieve the file: ' . $http_code
        );
        back_trace( 'ERROR', 'Failed to retrieve the file: ' . $http_code);
        return false;
    }

}