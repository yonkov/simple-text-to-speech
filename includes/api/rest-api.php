<?php
/**
 * REST API Handler
 *
 * @package SimpleTextToSpeech
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * API namespace
 */
define( 'STTS_REST_NAMESPACE', 'simple-tts/v1' );

/**
 * Initialize REST API
 *
 * @since 1.0.0
 */
function stts_rest_api_init() {
	add_action( 'rest_api_init', 'stts_register_rest_routes' );
}
add_action( 'init', 'stts_rest_api_init', 0 );

/**
 * Register REST API routes
 *
 * @since 1.0.0
 */
function stts_register_rest_routes() {
	register_rest_route(
		STTS_REST_NAMESPACE,
		'/generate',
		array(
			'methods'             => 'POST',
			'callback'            => 'stts_rest_generate_audio',
			'permission_callback' => 'stts_rest_check_permissions',
			'args'                => array(
				'post_id' => array(
					'required'          => true,
					'type'              => 'integer',
					'sanitize_callback' => 'absint',
				),
			),
		)
	);

	register_rest_route(
		STTS_REST_NAMESPACE,
		'/delete',
		array(
			'methods'             => 'POST',
			'callback'            => 'stts_rest_delete_audio',
			'permission_callback' => 'stts_rest_check_permissions',
			'args'                => array(
				'post_id' => array(
					'required'          => true,
					'type'              => 'integer',
					'sanitize_callback' => 'absint',
				),
			),
		)
	);

	register_rest_route(
		STTS_REST_NAMESPACE,
		'/status/(?P<post_id>\d+)',
		array(
			'methods'             => 'GET',
			'callback'            => 'stts_rest_get_audio_status',
			'permission_callback' => 'stts_rest_check_permissions',
			'args'                => array(
				'post_id' => array(
					'required'          => true,
					'type'              => 'integer',
					'sanitize_callback' => 'absint',
				),
			),
		)
	);
}

/**
 * Check if user has permission to use API
 *
 * @return bool True if user has permission.
 * @since 1.0.0
 */
function stts_rest_check_permissions() {
	return current_user_can( 'edit_posts' );
}

/**
 * Generate audio for a post
 *
 * @param WP_REST_Request $request REST request object.
 * @return WP_REST_Response|WP_Error Response object.
 * @since 1.0.0
 */
function stts_rest_generate_audio( $request ) {
	$post_id = $request->get_param( 'post_id' );
	
	// Verify post exists and user can edit it.
	$post = get_post( $post_id );
	if ( ! $post || ! current_user_can( 'edit_post', $post_id ) ) {
		return new WP_Error(
			'invalid_post',
			esc_html__( 'Invalid post ID or insufficient permissions.', 'simple-text-to-speech' ),
			array( 'status' => 403 )
		);
	}

	// Get post content.
	$content = $post->post_content;
	
	// Apply content filters to process shortcodes, blocks, etc.
	$content = apply_filters( 'the_content', $content );
	
	// Also include title.
	$text = $post->post_title . '. ' . $content;

	// Generate audio.
	$result = stts_generate_audio( $text, $post_id );

	if ( is_wp_error( $result ) ) {
		return new WP_Error(
			$result->get_error_code(),
			$result->get_error_message(),
			array( 'status' => 500 )
		);
	}

	// Get file size and calculate usage percentage.
	$file_size = 0;
	$usage_percentage = 0;
	
	if ( isset( $result['attachment_id'] ) ) {
		$file_path = get_attached_file( $result['attachment_id'] );
		if ( $file_path && file_exists( $file_path ) ) {
			$file_size = filesize( $file_path );
		}
	}
	
	// Get usage stats for percentage.
	$usage_stats = stts_get_usage_stats();
	if ( $usage_stats['usage_limit'] > 0 ) {
		$usage_percentage = round( ( $usage_stats['monthly_usage'] / $usage_stats['usage_limit'] ) * 100, 2 );
	}

	return new WP_REST_Response(
		array(
			'success'          => true,
			'message'          => esc_html__( 'Audio successfully generated.', 'simple-text-to-speech' ),
			'attachment_id'    => $result['attachment_id'],
			'url'              => $result['url'],
			'file_size'        => $file_size,
			'file_size_formatted' => size_format( $file_size, 2 ),
			'usage_percentage' => $usage_percentage,
		),
		200
	);
}

/**
 * Delete audio for a post
 *
 * @param WP_REST_Request $request REST request object.
 * @return WP_REST_Response|WP_Error Response object.
 * @since 1.0.0
 */
function stts_rest_delete_audio( $request ) {
	$post_id = $request->get_param( 'post_id' );
	
	// Verify post exists and user can edit it.
	if ( ! get_post( $post_id ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return new WP_Error(
			'invalid_post',
			esc_html__( 'Invalid post ID or insufficient permissions.', 'simple-text-to-speech' ),
			array( 'status' => 403 )
		);
	}

	// Delete audio.
	$result = stts_delete_audio( $post_id );

	if ( is_wp_error( $result ) ) {
		return new WP_Error(
			$result->get_error_code(),
			$result->get_error_message(),
			array( 'status' => 500 )
		);
	}

	return new WP_REST_Response(
		array(
			'success' => true,
			'message' => esc_html__( 'Audio deleted successfully.', 'simple-text-to-speech' ),
		),
		200
	);
}

/**
 * Get audio status for a post
 *
 * @param WP_REST_Request $request REST request object.
 * @return WP_REST_Response|WP_Error Response object.
 * @since 1.0.0
 */
function stts_rest_get_audio_status( $request ) {
	$post_id = $request->get_param( 'post_id' );
	
	// Verify post exists and user can edit it.
	if ( ! get_post( $post_id ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return new WP_Error(
			'invalid_post',
			esc_html__( 'Invalid post ID or insufficient permissions.', 'simple-text-to-speech' ),
			array( 'status' => 403 )
		);
	}

	$attachment_id = stts_get_audio_attachment_id( $post_id );
	$usage_stats   = stts_get_usage_stats();
	
	$response = array(
		'has_audio'      => ! empty( $attachment_id ),
		'usage'          => $usage_stats,
	);

	if ( $attachment_id ) {
		$response['attachment_id'] = $attachment_id;
		$response['url']           = wp_get_attachment_url( $attachment_id );
	}

	return new WP_REST_Response( $response, 200 );
}
