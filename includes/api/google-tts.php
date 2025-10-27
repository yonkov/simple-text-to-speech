<?php
/**
 * Google Cloud Text-to-Speech API Handler
 *
 * @package SimpleTextToSpeech
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Google Cloud Text-to-Speech API endpoint
 */
define( 'STTS_API_ENDPOINT', 'https://texttospeech.googleapis.com/v1/text:synthesize' );

/**
 * Generate audio from text
 *
 * @param string $text The text to convert to speech.
 * @param int    $post_id The post ID for reference.
 * @return array|WP_Error Array with audio data or WP_Error on failure.
 * @since 1.0.0
 */
function stts_generate_audio( $text, $post_id ) {
	$api_key = get_option( 'stts_google_api_key' );
	
	if ( empty( $api_key ) ) {
		return new WP_Error(
			'missing_api_key',
			__( 'Google Cloud API key is not configured. Please set it in the plugin settings.', 'simple-text-to-speech' )
		);
	}

	if ( empty( $text ) ) {
		return new WP_Error(
			'empty_text',
			__( 'No text content found to convert to speech.', 'simple-text-to-speech' )
		);
	}

	// Prepare text first (cleanup and truncate to 5000 chars).
	$prepared_text = stts_prepare_text( $text );
	$character_count = strlen( $prepared_text );

	// Check usage limit before generating.
	$usage_check = stts_check_usage_limit( $character_count );
	if ( is_wp_error( $usage_check ) ) {
		return $usage_check;
	}

	$language_code = get_option( 'stts_language_code', 'en-US' );
	$voice_name    = stts_get_voice_for_language( $language_code );
	$speaking_style = get_option( 'stts_speaking_style', 'neutral' );

	// Map speaking style to pitch and speaking rate.
	$style_params = stts_get_speaking_style_params( $speaking_style );

	// Prepare request body.
	$body = array(
		'input'       => array(
			'text' => $prepared_text,
		),
		'voice'       => array(
			'languageCode' => $language_code,
			'name'         => $voice_name,
		),
		'audioConfig' => array(
			'audioEncoding' => 'MP3',
			'speakingRate'  => $style_params['speaking_rate'],
			'pitch'         => $style_params['pitch'],
		),
	);

	// Make API request.
	$response = wp_remote_post(
		STTS_API_ENDPOINT,
		array(
			'headers' => array(
				'Content-Type'  => 'application/json',
				'X-Goog-Api-Key' => $api_key,
			),
			'body'    => wp_json_encode( $body ),
			'timeout' => 30,
		)
	);

	if ( is_wp_error( $response ) ) {
		return $response;
	}

	$response_code = wp_remote_retrieve_response_code( $response );
	$response_body = wp_remote_retrieve_body( $response );

	if ( 200 !== $response_code ) {
		$error_data    = json_decode( $response_body, true );
		$error_message = isset( $error_data['error']['message'] )
			? $error_data['error']['message']
			: __( 'Unknown API error occurred.', 'simple-text-to-speech' );
		
		return new WP_Error(
			'api_error',
			$error_message
		);
	}

	$data = json_decode( $response_body, true );

	if ( ! isset( $data['audioContent'] ) ) {
		return new WP_Error(
			'invalid_response',
			__( 'Invalid response from Google Cloud API.', 'simple-text-to-speech' )
		);
	}

	// Track usage after successful generation.
	stts_track_usage( $character_count );

	// Save audio file to media library.
	return stts_save_audio_file( $data['audioContent'], $post_id );
}

/**
 * Get speaking style parameters for Google TTS
 *
 * @param string $style Speaking style (neutral, calm, serious, excited).
 * @return array Array with speaking_rate and pitch.
 * @since 1.0.0
 */
function stts_get_speaking_style_params( $style ) {
	$styles = array(
		'neutral' => array(
			'speaking_rate' => 1.0,
			'pitch'         => 0.0,
		),
		'calm'    => array(
			'speaking_rate' => 0.85,
			'pitch'         => -2.0,
		),
		'serious' => array(
			'speaking_rate' => 0.9,
			'pitch'         => -1.0,
		),
		'excited' => array(
			'speaking_rate' => 1.15,
			'pitch'         => 2.0,
		),
	);

	return isset( $styles[ $style ] ) ? $styles[ $style ] : $styles['neutral'];
}

/**
 * Prepare text for TTS conversion
 *
 * @param string $text Raw text content.
 * @return string Cleaned text.
 * @since 1.0.0
 */
function stts_prepare_text( $text ) {
	// Remove HTML tags.
	$text = wp_strip_all_tags( $text );
	
	// Remove shortcodes.
	$text = strip_shortcodes( $text );
	
	// Normalize whitespace.
	$text = preg_replace( '/\s+/', ' ', $text );
	
	// Trim.
	$text = trim( $text );
	
	// Limit to 5000 characters (Google Cloud TTS limit).
	if ( strlen( $text ) > 5000 ) {
		$text = substr( $text, 0, 5000 );
	}
	
	return $text;
}

/**
 * Save audio file to WordPress media library
 *
 * @param string $audio_content Base64 encoded audio content.
 * @param int    $post_id The post ID to attach the audio to.
 * @return array|WP_Error Array with attachment data or WP_Error on failure.
 * @since 1.0.0
 */
function stts_save_audio_file( $audio_content, $post_id ) {
	// Decode base64 audio content.
	$audio_data = base64_decode( $audio_content );
	
	if ( false === $audio_data ) {
		return new WP_Error(
			'decode_error',
			__( 'Failed to decode audio content.', 'simple-text-to-speech' )
		);
	}

	// Require WordPress file handling functions.
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	// Generate filename.
	$post_title = get_the_title( $post_id );
	$filename   = sanitize_file_name( $post_title . '-audio-' . time() . '.mp3' );

	// Get upload directory.
	$upload_dir = wp_upload_dir();
	$file_path  = $upload_dir['path'] . '/' . $filename;

	// Save file.
	$saved = file_put_contents( $file_path, $audio_data );
	
	if ( false === $saved ) {
		return new WP_Error(
			'save_error',
			__( 'Failed to save audio file.', 'simple-text-to-speech' )
		);
	}

	// Prepare attachment data.
	$attachment = array(
		'post_mime_type' => 'audio/mpeg',
		'post_title'     => sprintf(
			/* translators: %s: post title */
			__( 'Audio for: %s', 'simple-text-to-speech' ),
			$post_title
		),
		'post_content'   => '',
		'post_status'    => 'inherit',
	);

	// Insert attachment.
	$attachment_id = wp_insert_attachment( $attachment, $file_path, $post_id );
	
	if ( is_wp_error( $attachment_id ) ) {
		return $attachment_id;
	}

	// Generate attachment metadata.
	$attachment_data = wp_generate_attachment_metadata( $attachment_id, $file_path );
	wp_update_attachment_metadata( $attachment_id, $attachment_data );

	// Store reference in post meta.
	update_post_meta( $post_id, '_stts_audio_attachment_id', $attachment_id );

	return array(
		'attachment_id' => $attachment_id,
		'url'           => wp_get_attachment_url( $attachment_id ),
	);
}

/**
 * Delete audio file for a post
 *
 * @param int $post_id The post ID.
 * @return bool|WP_Error True on success, WP_Error on failure.
 * @since 1.0.0
 */
function stts_delete_audio( $post_id ) {
	$attachment_id = get_post_meta( $post_id, '_stts_audio_attachment_id', true );
	
	if ( empty( $attachment_id ) ) {
		return new WP_Error(
			'no_audio',
			__( 'No audio file found for this post.', 'simple-text-to-speech' )
		);
	}

	$deleted = wp_delete_attachment( $attachment_id, true );
	
	if ( false === $deleted ) {
		return new WP_Error(
			'delete_error',
			__( 'Failed to delete audio file.', 'simple-text-to-speech' )
		);
	}

	// Remove post meta.
	delete_post_meta( $post_id, '_stts_audio_attachment_id' );

	return true;
}

/**
 * Get audio attachment ID for a post
 *
 * @param int $post_id The post ID.
 * @return int|null Attachment ID or null if not found.
 * @since 1.0.0
 */
function stts_get_audio_attachment_id( $post_id ) {
	$attachment_id = get_post_meta( $post_id, '_stts_audio_attachment_id', true );
	return $attachment_id ? (int) $attachment_id : null;
}

/**
 * Check if usage limit is reached
 *
 * @param int $character_count Number of characters to be generated.
 * @return bool|WP_Error True if within limit, WP_Error if exceeded.
 * @since 1.0.0
 */
function stts_check_usage_limit( $character_count ) {
	$usage_limit   = absint( get_option( 'stts_usage_limit', 1000000 ) );
	$current_month = gmdate( 'Y-m' );
	$usage_data    = get_option( 'stts_usage_data', array() );
	$monthly_usage = isset( $usage_data[ $current_month ] ) ? absint( $usage_data[ $current_month ] ) : 0;

	if ( ( $monthly_usage + $character_count ) > $usage_limit ) {
		return new WP_Error(
			'usage_limit_exceeded',
			sprintf(
				/* translators: 1: Current usage, 2: Limit */
				__( 'Monthly character limit exceeded. You have used %1$s of %2$s characters this month. Usage resets on the 1st of next month.', 'simple-text-to-speech' ),
				number_format_i18n( $monthly_usage ),
				number_format_i18n( $usage_limit )
			)
		);
	}

	return true;
}

/**
 * Track character usage
 *
 * @param int $character_count Number of characters generated.
 * @return void
 * @since 1.0.0
 */
function stts_track_usage( $character_count ) {
	$current_month = gmdate( 'Y-m' );
	$usage_data    = get_option( 'stts_usage_data', array() );
	
	if ( ! isset( $usage_data[ $current_month ] ) ) {
		$usage_data[ $current_month ] = 0;
	}

	$usage_data[ $current_month ] += absint( $character_count );

	// Clean up old months (keep last 12 months).
	$all_months = array_keys( $usage_data );
	if ( count( $all_months ) > 12 ) {
		sort( $all_months );
		$months_to_remove = array_slice( $all_months, 0, count( $all_months ) - 12 );
		foreach ( $months_to_remove as $month ) {
			unset( $usage_data[ $month ] );
		}
	}

	update_option( 'stts_usage_data', $usage_data );
}

/**
 * Get current month usage statistics
 *
 * @return array Usage statistics.
 * @since 1.0.0
 */
function stts_get_usage_stats() {
	$current_month = gmdate( 'Y-m' );
	$usage_data    = get_option( 'stts_usage_data', array() );
	$monthly_usage = isset( $usage_data[ $current_month ] ) ? absint( $usage_data[ $current_month ] ) : 0;
	$usage_limit   = absint( get_option( 'stts_usage_limit', 1000000 ) );

	return array(
		'current_month'   => $current_month,
		'monthly_usage'   => $monthly_usage,
		'usage_limit'     => $usage_limit,
		'usage_percent'   => ( $usage_limit > 0 ) ? min( 100, round( ( $monthly_usage / $usage_limit ) * 100, 1 ) ) : 0,
		'remaining'       => max( 0, $usage_limit - $monthly_usage ),
		'limit_reached'   => $monthly_usage >= $usage_limit,
	);
}
