<?php
/**
 * Plugin Name: Simple Text to Speech
 * Plugin URI: https://github.com/yonkov/simple-text-to-speech
 * Description: Convert WordPress posts and pages to audio using Google Cloud Text-to-Speech API.
 * Version: 1.0.0
 * Requires at least: 6.7
 * Requires PHP: 7.2
 * Author: Nasio Themes
 * Author URI: https://nasiothemes.com
 * License: GPLv2
 * Text Domain: simple-text-to-speech
 *
 * @package Simple Text to Speech
 * @since 1.0.0
 */

/**
 * Exit if accessed directly
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin Constants
 */
define( 'STTS_VERSION', '1.0.0' );
define( 'STTS_PATH', plugin_dir_path( __FILE__ ) );
define( 'STTS_URL', plugin_dir_url( __FILE__ ) );

/**
 * Load plugin files
 */
require_once STTS_PATH . 'includes/admin/languages.php';
require_once STTS_PATH . 'includes/admin/settings.php';
require_once STTS_PATH . 'includes/api/google-tts.php';
require_once STTS_PATH . 'includes/api/rest-api.php';

/**
 * Register post meta for audio attachment
 *
 * @since 1.0.0
 */
function stts_register_post_meta() {
	register_post_meta(
		'',
		'_stts_audio_attachment_id',
		array(
			'show_in_rest' => true,
			'single'       => true,
			'type'         => 'integer',
			'auth_callback' => function() {
				return current_user_can( 'edit_posts' );
			}
		)
	);
}
add_action( 'init', 'stts_register_post_meta' );

/**
 * Enqueue editor assets
 *
 * @since 1.0.0
 */
function stts_enqueue_editor_assets() {
	$asset_file = STTS_PATH . 'build/editor.asset.php';
	
	if ( ! file_exists( $asset_file ) ) {
		return;
	}
	
	$asset = include $asset_file;
	
	wp_enqueue_style(
		'stts-editor',
		STTS_URL . 'build/editor.css',
		array(),
		$asset['version']
	);
	
	wp_enqueue_script(
		'stts-editor',
		STTS_URL . 'build/editor.js',
		$asset['dependencies'],
		$asset['version'],
		true
	);
	
	wp_localize_script(
		'stts-editor',
		'sttsData',
		array(
			'nonce'   => wp_create_nonce( 'wp_rest' ),
			'apiUrl'  => rest_url( 'simple-tts/v1' ),
			'hasApiKey' => ! empty( get_option( 'stts_google_api_key' ) ),
			'languageName' => stts_get_current_language_name(),
			'speakingStyle' => stts_get_current_speaking_style_name(),
			'settingsUrl' => admin_url( 'options-general.php?page=simple_text_to_speech' ),
			'allowedAudioMimes' => stts_get_allowed_audio_mime_types(),
			'allowedAudioExtensions' => stts_get_allowed_audio_extensions(),
		)
	);
}
add_action( 'enqueue_block_editor_assets', 'stts_enqueue_editor_assets' );

/**
 * Get current language name
 *
 * @return string Language name.
 * @since 1.0.0
 */
function stts_get_current_language_name() {
	$language_code = get_option( 'stts_language_code', 'en-US' );
	$available_languages = stts_get_available_languages();
	return isset( $available_languages[ $language_code ] ) ? $available_languages[ $language_code ] : $language_code;
}

/**
 * Get current speaking style name
 *
 * @return string Speaking style name.
 * @since 1.0.0
 */
function stts_get_current_speaking_style_name() {
	$speaking_style = get_option( 'stts_speaking_style', 'neutral' );
	
	$speaking_styles = array(
		'neutral' => esc_html__( 'Neutral', 'simple-text-to-speech' ),
		'calm'    => esc_html__( 'Calm', 'simple-text-to-speech' ),
		'serious' => esc_html__( 'Serious', 'simple-text-to-speech' ),
		'excited' => esc_html__( 'Excited', 'simple-text-to-speech' ),
	);
	
	return isset( $speaking_styles[ $speaking_style ] ) ? $speaking_styles[ $speaking_style ] : $speaking_style;
}

/**
 * Get allowed audio MIME types for browser playback
 * Centralized list used by both PHP and JavaScript
 *
 * @return array Allowed audio MIME types.
 * @since 1.0.0
 */
function stts_get_allowed_audio_mime_types() {
	return array(
		'audio/mpeg',      // MP3
		'audio/mp3',       // MP3 (alternative)
		'audio/wav',       // WAV
		'audio/ogg',       // OGG
		'audio/webm',      // WebM
		'audio/mp4',       // M4A
		'audio/aac',       // AAC
		'audio/flac',      // FLAC
		'audio/x-m4a',     // M4A (alternative)
		'audio/x-wav',     // WAV (alternative)
	);
}

/**
 * Get allowed audio file extensions
 * Centralized list used by both PHP and JavaScript
 *
 * @return array Allowed audio file extensions.
 * @since 1.0.0
 */
function stts_get_allowed_audio_extensions() {
	return array( 'mp3', 'wav', 'ogg', 'oga', 'webm', 'm4a', 'aac', 'flac' );
}

/**
 * Enqueue meta box scripts
 *
 * @since 1.0.0
 */
function stts_enqueue_meta_box_scripts() {
	$current_screen = get_current_screen();
	
	// Only enqueue on post edit screens.
	if ( ! $current_screen || 'post' !== $current_screen->base ) {
		return;
	}
	
	// Don't enqueue if Block Editor is active.
	if ( method_exists( $current_screen, 'is_block_editor' ) && $current_screen->is_block_editor() ) {
		return;
	}
	
	// Enqueue WordPress media scripts for upload functionality.
	wp_enqueue_media();
	// Enqueue admin styles for meta box (also used on settings page).
	wp_enqueue_style(
		'stts-admin-styles',
		STTS_URL . 'admin/styles.css',
		array(),
		STTS_VERSION
	);
	wp_enqueue_script(
		'stts-meta-box',
		STTS_URL . 'admin/meta-box.js',
		array(),
		STTS_VERSION,
		true
	);
	
	wp_enqueue_script(
		'stts-admin-script',
		STTS_URL . 'admin/script.js',
		array( 'jquery' ),
		STTS_VERSION,
		true
	);
	
	// Pass allowed audio types to JavaScript.
	wp_localize_script(
		'stts-admin-script',
		'sttsAudioConfig',
		array(
			'allowedMimes' => stts_get_allowed_audio_mime_types(),
			'allowedExtensions' => stts_get_allowed_audio_extensions(),
		)
	);
}
add_action( 'admin_enqueue_scripts', 'stts_enqueue_meta_box_scripts' );

/**
 * Add meta box for Classic Editor and other editors
 *
 * @since 1.0.0
 */
function stts_add_meta_box() {
	// Only add meta box if Block Editor is not active.
	$current_screen = get_current_screen();
	if ( $current_screen && method_exists( $current_screen, 'is_block_editor' ) && $current_screen->is_block_editor() ) {
		return;
	}
	
	$post_types = get_post_types( array( 'public' => true ), 'names' );
	
	foreach ( $post_types as $post_type ) {
		add_meta_box(
			'stts_meta_box',
			esc_html__( 'Text to Speech', 'simple-text-to-speech' ),
			'stts_render_meta_box',
			$post_type,
			'side',
			'default'
		);
	}
}
add_action( 'add_meta_boxes', 'stts_add_meta_box' );

/**
 * Render meta box content
 *
 * @param WP_Post $post Current post object.
 * @since 1.0.0
 */
function stts_render_meta_box( $post ) {
	$audio_id  = get_post_meta( $post->ID, '_stts_audio_attachment_id', true );
	$audio_url = '';
	
	if ( $audio_id ) {
		$audio_url = wp_get_attachment_url( $audio_id );
	}
	
	$has_api_key = ! empty( get_option( 'stts_google_api_key' ) );
	$language_code = get_option( 'stts_language_code', 'en-US' );
	$available_languages = stts_get_available_languages();
	$language_name = isset( $available_languages[ $language_code ] ) ? $available_languages[ $language_code ] : $language_code;
	$speaking_style = stts_get_current_speaking_style_name();
	
	wp_nonce_field( 'stts_upload_audio', 'stts_upload_audio_nonce' );
	?>
	<div class="stts-meta-box">
		<?php if ( ! $has_api_key && ! $audio_url ) : ?>
			<p class="stts-notice stts-notice-warning">
				<?php esc_html_e( 'Google Cloud API key is not configured.', 'simple-text-to-speech' ); ?>
				<a href="<?php echo esc_url( admin_url( 'options-general.php?page=simple_text_to_speech' ) ); ?>">
					<?php esc_html_e( 'Configure settings', 'simple-text-to-speech' ); ?>
				</a>
			</p>
		<?php endif; ?>
		
		<?php if ( $audio_url ) : ?>
		
			<audio controls src="<?php echo esc_url( $audio_url ); ?>" style="width: 100%; margin: 10px 0;">
				<?php esc_html_e( 'Your browser does not support the audio element.', 'simple-text-to-speech' ); ?>
			</audio>
			<div class="stts-meta-box-actions">
				<button type="button" 
						class="button button-secondary button-small stts-delete-audio"
						data-post-id="<?php echo esc_attr( $post->ID ); ?>"
						data-nonce="<?php echo esc_attr( wp_create_nonce( 'stts_delete_audio_' . $post->ID ) ); ?>"
						onclick="return confirm('<?php echo esc_js( esc_html__( 'Are you sure you want to delete the audio file?', 'simple-text-to-speech' ) ); ?>');">
					<?php esc_html_e( 'Delete Audio', 'simple-text-to-speech' ); ?>
				</button>
			</div>
		<?php elseif ( $has_api_key ) : ?>
			<p class="description" style="margin-top: 0;">
				<?php
				printf(
					/* translators: 1: language name, 2: speaking style, 3: settings page URL, 4: line break */
					esc_html__( 'Selected language: %1$s.%4$sTonality: %2$s.%4$sChange in %3$s.', 'simple-text-to-speech' ),
					'<strong>' . esc_html( $language_name ) . '</strong>',
					'<strong>' . esc_html( $speaking_style ) . '</strong>',
					'<a href="' . esc_url( admin_url( 'options-general.php?page=simple_text_to_speech' ) ) . '">' . esc_html__( 'plugin settings', 'simple-text-to-speech' ) . '</a>',
					'<br>'
				);
				?>
			</p>
			<p><?php esc_html_e( 'No audio file generated yet.', 'simple-text-to-speech' ); ?></p>
			<div class="stts-meta-box-actions">
				<button type="button" 
						class="button button-primary button-small stts-generate-audio"
						data-post-id="<?php echo esc_attr( $post->ID ); ?>"
						data-nonce="<?php echo esc_attr( wp_create_nonce( 'stts_generate_audio_' . $post->ID ) ); ?>">
					<?php esc_html_e( 'Generate Audio', 'simple-text-to-speech' ); ?>
				</button>
				<button type="button" 
						class="button button-secondary button-small stts-upload-audio-btn"
						data-title="<?php echo esc_attr__( 'Select Audio File', 'simple-text-to-speech' ); ?>"
						data-button-text="<?php echo esc_attr__( 'Use this audio', 'simple-text-to-speech' ); ?>">
					<?php esc_html_e( 'Upload Audio', 'simple-text-to-speech' ); ?>
				</button>
				<input type="hidden" name="stts_uploaded_audio_id" id="stts_uploaded_audio_id" value="" />
			</div>
			<p class="description">
				<?php esc_html_e( 'Don\'t forget to save the post first. Audio will be generated from the saved content.', 'simple-text-to-speech' ); ?>
			</p>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * Save uploaded audio from meta box
 *
 * @param int $post_id Post ID.
 * @since 1.0.0
 */
function stts_save_uploaded_audio( $post_id ) {
	// Check if our nonce is set and verify it.
	if ( ! isset( $_POST['stts_upload_audio_nonce'] ) || 
	     ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['stts_upload_audio_nonce'] ) ), 'stts_upload_audio' ) ) {
		return;
	}
	
	// Check if this is an autosave.
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	
	// Check user permissions.
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	
	// Check if upload was triggered.
	if ( ! isset( $_POST['stts_save_uploaded_audio'] ) ) {
		return;
	}
	
	// Save the uploaded audio ID.
	if ( isset( $_POST['stts_uploaded_audio_id'] ) && ! empty( $_POST['stts_uploaded_audio_id'] ) ) {
		$audio_id = absint( $_POST['stts_uploaded_audio_id'] );
		
		// Validate that the attachment exists and is an audio file.
		$attachment = get_post( $audio_id );
		if ( ! $attachment || 'attachment' !== $attachment->post_type ) {
			return;
		}
		
		// Get attachment MIME type.
		$mime_type = get_post_mime_type( $audio_id );
		
		// Validate MIME type.
		if ( ! in_array( $mime_type, stts_get_allowed_audio_mime_types(), true ) ) {
			// Not a valid audio format - don't save.
			return;
		}
		
		update_post_meta( $post_id, '_stts_audio_attachment_id', $audio_id );
	}
}
add_action( 'save_post', 'stts_save_uploaded_audio' );

/**
 * Handler for generating audio from meta box
 *
 * @since 1.0.0
 */
function stts_ajax_generate_audio() {
	// Check if post_id is set.
	if ( ! isset( $_POST['post_id'] ) ) {
		wp_die( esc_html__( 'Invalid request.', 'simple-text-to-speech' ) );
	}
	
	$post_id = absint( $_POST['post_id'] );
	
	// Check nonce.
	if ( ! isset( $_POST['stts_generate_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['stts_generate_nonce'] ) ), 'stts_generate_audio_' . $post_id ) ) {
		wp_die( esc_html__( 'Security check failed.', 'simple-text-to-speech' ) );
	}
	
	// Check permissions.
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		wp_die( esc_html__( 'You do not have permission to edit this post.', 'simple-text-to-speech' ) );
	}
	
	// Get post content.
	$post = get_post( $post_id );
	if ( ! $post ) {
		wp_die( esc_html__( 'Post not found.', 'simple-text-to-speech' ) );
	}
	
	$content = $post->post_content;
	
	// Apply content filters.
	$content = apply_filters( 'the_content', $content );
	
	// Include title.
	$text = $post->post_title . '. ' . $content;
	
	// Generate audio.
	$result = stts_generate_audio( $text, $post_id );
	
	if ( is_wp_error( $result ) ) {
		wp_die( esc_html( $result->get_error_message() ) );
	}
	
	// Redirect back to post edit screen.
	$redirect_url = add_query_arg(
		array(
			'post'         => $post_id,
			'action'       => 'edit',
			'stts_message' => 'generated',
			'stts_nonce'   => wp_create_nonce( 'stts_notice_' . $post_id ),
		),
		admin_url( 'post.php' )
	);
	wp_safe_redirect( $redirect_url );
	exit;
}
add_action( 'admin_post_stts_generate_audio', 'stts_ajax_generate_audio' );

/**
 * Handler for deleting audio from meta box
 *
 * @since 1.0.0
 */
function stts_ajax_delete_audio() {
	// Check if post_id is set.
	if ( ! isset( $_POST['post_id'] ) ) {
		wp_die( esc_html__( 'Invalid request.', 'simple-text-to-speech' ) );
	}
	
	$post_id = absint( $_POST['post_id'] );
	
	// Check nonce.
	if ( ! isset( $_POST['stts_delete_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['stts_delete_nonce'] ) ), 'stts_delete_audio_' . $post_id ) ) {
		wp_die( esc_html__( 'Security check failed.', 'simple-text-to-speech' ) );
	}
	
	// Check permissions.
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		wp_die( esc_html__( 'You do not have permission to edit this post.', 'simple-text-to-speech' ) );
	}
	
	// Delete audio.
	$result = stts_delete_audio( $post_id );
	
	if ( is_wp_error( $result ) ) {
		wp_die( esc_html( $result->get_error_message() ) );
	}
	
	// Redirect back to post edit screen.
	$redirect_url = add_query_arg(
		array(
			'post'         => $post_id,
			'action'       => 'edit',
			'stts_message' => 'deleted',
			'stts_nonce'   => wp_create_nonce( 'stts_notice_' . $post_id ),
		),
		admin_url( 'post.php' )
	);
	wp_safe_redirect( $redirect_url );
	exit;
}
add_action( 'admin_post_stts_delete_audio', 'stts_ajax_delete_audio' );

/**
 * Display admin notices
 *
 * @since 1.0.0
 */
function stts_admin_notices() {
	if ( ! isset( $_GET['stts_message'] ) || ! isset( $_GET['stts_nonce'] ) || ! isset( $_GET['post'] ) ) {
		return;
	}
	
	$post_id = absint( $_GET['post'] );
	
	// Verify nonce.
	if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['stts_nonce'] ) ), 'stts_notice_' . $post_id ) ) {
		return;
	}
	
	$message = sanitize_text_field( wp_unslash( $_GET['stts_message'] ) );
	
	if ( 'generated' === $message ) {
		?>
		<div class="notice notice-success is-dismissible">
			<p><?php esc_html_e( 'Audio generated successfully!', 'simple-text-to-speech' ); ?></p>
		</div>
		<?php
	} elseif ( 'deleted' === $message ) {
		?>
		<div class="notice notice-success is-dismissible">
			<p><?php esc_html_e( 'Audio deleted successfully!', 'simple-text-to-speech' ); ?></p>
		</div>
		<?php
	}
}
add_action( 'admin_notices', 'stts_admin_notices' );

/**
 * Add Settings link in WordPress Plugins Page
 *
 * @param array $links Existing plugin action links.
 * @return array Modified plugin action links.
 * @since 1.0.0
 */
function stts_settings_link( array $links ) {
	$url           = admin_url( 'options-general.php?page=simple_text_to_speech' );
	$settings_link = '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Settings', 'simple-text-to-speech' ) . '</a>';
	$links[]       = $settings_link;
	return $links;
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'stts_settings_link' );

/**
 * Enqueue frontend styles and scripts
 *
 * @since 1.0.0
 */
function stts_enqueue_frontend_styles() {
	if ( is_singular() ) {
		wp_enqueue_style(
			'stts-frontend-styles',
			STTS_URL . 'assets/styles.css',
			array(),
			STTS_VERSION
		);
		
		wp_enqueue_script(
			'stts-frontend-script',
			STTS_URL . 'assets/script.js',
			array(),
			STTS_VERSION,
			true
		);
	}
}
add_action( 'wp_enqueue_scripts', 'stts_enqueue_frontend_styles' );

/**
 * Display audio player in post content
 *
 * @param string $content Post content.
 * @return string Modified content with audio player.
 * @since 1.0.0
 */
function stts_display_audio_player( $content ) {
	// Only display on singular pages (all public post types).
	if ( ! is_singular() ) {
		return $content;
	}

	// Get the current post ID.
	$post_id = get_the_ID();
	if ( ! $post_id ) {
		return $content;
	}

	// Check if audio exists.
	$attachment_id = stts_get_audio_attachment_id( $post_id );
	if ( ! $attachment_id ) {
		return $content;
	}

	// Get audio URL.
	$audio_url = wp_get_attachment_url( $attachment_id );
	if ( ! $audio_url ) {
		return $content;
	}

	// Get player style from settings.
	$player_style = get_option( 'stts_player_style', 'audio' );
	
	// Build audio player HTML with selected style.
	if ( 'icon' === $player_style ) {
		// Icon style - SVG icon that reveals player on click.
		$audio_player = '<div class="stts-audio-container stts-style-icon">';
		$audio_player .= '<span class="stts-screen-reader-text">' . esc_html__( 'Listen to this article', 'simple-text-to-speech' ) . '</span>';
		$audio_player .= '<button class="stts-icon-button" aria-label="' . esc_attr__( 'Listen to this article', 'simple-text-to-speech' ) . '" data-play-label="' . esc_attr__( 'Listen to this article', 'simple-text-to-speech' ) . '" data-pause-label="' . esc_attr__( 'Pause audio', 'simple-text-to-speech' ) . '">';
		$audio_player .= '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="feather feather-volume-1"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon><path d="M15.54 8.46a5 5 0 0 1 0 7.07"></path></svg>';
		$audio_player .= '</button>';
		$audio_player .= '<audio controls preload="metadata">';
		$audio_player .= '<source src="' . esc_url( $audio_url ) . '" type="audio/mpeg">';
		$audio_player .= esc_html__( 'Your browser does not support the audio element.', 'simple-text-to-speech' );
		$audio_player .= '</audio>';
		$audio_player .= '</div>';
	} else {
		// Audio player style - simple audio player.
		$audio_player = '<div class="stts-audio-container">';
		$audio_player .= '<span class="stts-screen-reader-text">' . esc_html__( 'Listen to this article', 'simple-text-to-speech' ) . '</span>';
		$audio_player .= '<audio controls preload="metadata">';
		$audio_player .= '<source src="' . esc_url( $audio_url ) . '" type="audio/mpeg">';
		$audio_player .= esc_html__( 'Your browser does not support the audio element.', 'simple-text-to-speech' );
		$audio_player .= '</audio>';
		$audio_player .= '</div>';
	}

	// Prepend audio player to content.
	return $audio_player . $content;
}
add_filter( 'the_content', 'stts_display_audio_player', 5 );
