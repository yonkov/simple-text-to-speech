<?php
/**
 * Settings Page Handler
 *
 * @package Simple Text to Speech
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Initialize settings
 *
 * @since 1.0.0
 */
function stts_settings_init() {
	add_action( 'admin_menu', 'stts_add_settings_page' );
	add_action( 'admin_init', 'stts_save_settings' );
	add_action( 'admin_enqueue_scripts', 'stts_enqueue_admin_styles' );
}
add_action( 'init', 'stts_settings_init', 0 );

/**
 * Enqueue admin styles
 *
 * @param string $hook The current admin page hook.
 * @since 1.0.0
 */
function stts_enqueue_admin_styles( $hook ) {
	if ( 'settings_page_simple_text_to_speech' !== $hook ) {
		return;
	}

	wp_enqueue_style(
		'stts-admin-styles',
		STTS_URL . 'admin/styles.css',
		array(),
		STTS_VERSION
	);

	wp_enqueue_script(
		'stts-admin-script',
		STTS_URL . 'admin/script.js',
		array(),
		STTS_VERSION,
		true
	);

	// Pass voice data to JavaScript.
	$all_languages = stts_get_available_languages();
	$voice_data    = array();
	
	foreach ( $all_languages as $lang_code => $lang_name ) {
		$voice_data[ $lang_code ] = stts_get_voices_for_language( $lang_code );
	}

	wp_localize_script(
		'stts-admin-script',
		'sttsVoiceData',
		$voice_data
	);
}

/**
 * Add settings page to WordPress admin
 *
 * @since 1.0.0
 */
function stts_add_settings_page() {
	add_options_page(
		esc_html__( 'Text to Speech Settings', 'simple-text-to-speech' ),
		esc_html__( 'Text to Speech', 'simple-text-to-speech' ),
		'manage_options',
		'simple_text_to_speech',
		'stts_render_settings_page'
	);
}

/**
 * Save settings
 *
 * @since 1.0.0
 */
function stts_save_settings() {
	if ( ! isset( $_POST['stts_settings_nonce'] ) ) {
		return;
	}

	if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['stts_settings_nonce'] ) ), 'stts_save_settings' ) ) {
		return;
	}

	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	if ( isset( $_POST['stts_google_api_key'] ) ) {
		update_option( 'stts_google_api_key', sanitize_text_field( wp_unslash( $_POST['stts_google_api_key'] ) ) );
	}

	if ( isset( $_POST['stts_language_code'] ) ) {
		update_option( 'stts_language_code', sanitize_text_field( wp_unslash( $_POST['stts_language_code'] ) ) );
	}

	if ( isset( $_POST['stts_voice_name'] ) ) {
		update_option( 'stts_voice_name', sanitize_text_field( wp_unslash( $_POST['stts_voice_name'] ) ) );
	}

	if ( isset( $_POST['stts_usage_limit'] ) ) {
		$usage_limit = absint( $_POST['stts_usage_limit'] );
		if ( $usage_limit > 0 ) {
			update_option( 'stts_usage_limit', $usage_limit );
		}
	}

	if ( isset( $_POST['stts_speaking_style'] ) ) {
		$allowed_styles = array( 'neutral', 'calm', 'serious', 'excited' );
		$speaking_style = sanitize_text_field( wp_unslash( $_POST['stts_speaking_style'] ) );
		if ( in_array( $speaking_style, $allowed_styles, true ) ) {
			update_option( 'stts_speaking_style', $speaking_style );
		}
	}

	if ( isset( $_POST['stts_player_style'] ) ) {
		$allowed_player_styles = array( 'audio', 'icon' );
		$player_style = sanitize_text_field( wp_unslash( $_POST['stts_player_style'] ) );
		if ( in_array( $player_style, $allowed_player_styles, true ) ) {
			update_option( 'stts_player_style', $player_style );
		}
	}

	add_settings_error(
		'stts_messages',
		'stts_message',
		esc_html__( 'Settings saved successfully.', 'simple-text-to-speech' ),
		'updated'
	);
}

/**
 * Render settings page
 *
 * @since 1.0.0
 */
function stts_render_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$default_tab = 'settings';
	$active_tab  = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : $default_tab; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	?>
	<div class="wrap">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

		<?php settings_errors( 'stts_messages' ); ?>

		<h2 class="nav-tab-wrapper">
			<a href="<?php echo esc_url( admin_url( 'options-general.php?page=simple_text_to_speech&tab=settings' ) ); ?>" class="nav-tab <?php echo esc_attr( $active_tab === 'settings' ? 'nav-tab-active' : '' ); ?>">
				<?php esc_html_e( 'Settings', 'simple-text-to-speech' ); ?>
			</a>
			<a href="<?php echo esc_url( admin_url( 'options-general.php?page=simple_text_to_speech&tab=styles' ) ); ?>" class="nav-tab <?php echo esc_attr( $active_tab === 'styles' ? 'nav-tab-active' : '' ); ?>">
				<?php esc_html_e( 'Styles', 'simple-text-to-speech' ); ?>
			</a>
			<a href="<?php echo esc_url( admin_url( 'options-general.php?page=simple_text_to_speech&tab=faq' ) ); ?>" class="nav-tab <?php echo esc_attr( $active_tab === 'faq' ? 'nav-tab-active' : '' ); ?>">
				<?php esc_html_e( 'FAQ', 'simple-text-to-speech' ); ?>
			</a>
		</h2>

		<?php
		switch ( $active_tab ) :
			case 'settings':
				stts_render_settings_tab();
				break;

			case 'styles':
				stts_render_styles_tab();
				break;

			case 'faq':
				stts_render_faq_tab();
				break;
		endswitch;
		?>
	</div>
	<?php
}

/**
 * Render Settings tab content
 *
 * @since 1.0.0
 */
function stts_render_settings_tab() {
	$api_key       = get_option( 'stts_google_api_key', '' );
	$language_code = get_option( 'stts_language_code', 'en-US' );
	$voice_name    = get_option( 'stts_voice_name', '' );
	$usage_limit   = get_option( 'stts_usage_limit', 1000000 );
	$speaking_style = get_option( 'stts_speaking_style', 'neutral' );
	$available_languages = stts_get_available_languages();
	$available_voices    = stts_get_voices_for_language( $language_code );
	?>
	<form method="post" action="">
		<?php wp_nonce_field( 'stts_save_settings', 'stts_settings_nonce' ); ?>
		
		<table class="form-table">
			<tr>
				<th scope="row">
					<label for="stts_google_api_key">
						<?php esc_html_e( 'Google Cloud API Key', 'simple-text-to-speech' ); ?>
					</label>
				</th>
				<td>
					<input 
						type="text" 
						id="stts_google_api_key" 
						name="stts_google_api_key" 
						value="<?php echo esc_attr( $api_key ); ?>" 
						class="regular-text"
						placeholder="<?php esc_attr_e( 'AIzaSy...', 'simple-text-to-speech' ); ?>"
					/>
					<p class="description">
						<?php
						printf(
							/* translators: %s: link to Google Cloud Console */
							esc_html__( 'Your Google Cloud API key with Text-to-Speech API enabled. Get it from the %s.', 'simple-text-to-speech' ),
							'<a href="' . esc_url( 'https://console.cloud.google.com/apis/credentials' ) . '" target="_blank" rel="noopener">' . esc_html__( 'Google Cloud Console', 'simple-text-to-speech' ) . '</a>'
						);
						?>
					</p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="stts_language_code">
						<?php esc_html_e( 'Language', 'simple-text-to-speech' ); ?>
					</label>
				</th>
				<td>
					<select 
						id="stts_language_code" 
						name="stts_language_code" 
						class="regular-text"
					>
						<?php foreach ( $available_languages as $code => $name ) : ?>
							<option value="<?php echo esc_attr( $code ); ?>" <?php selected( $language_code, $code ); ?>>
								<?php echo esc_html( $name ); ?>
							</option>
						<?php endforeach; ?>
					</select>
					<p class="description">
						<?php esc_html_e( 'Select the language for text-to-speech conversion.', 'simple-text-to-speech' ); ?>
					</p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="stts_voice_name">
						<?php esc_html_e( 'Voice', 'simple-text-to-speech' ); ?>
					</label>
				</th>
				<td>
					<select 
						id="stts_voice_name" 
						name="stts_voice_name" 
						class="regular-text"
					>
						<?php foreach ( $available_voices as $voice_code => $voice_label ) : ?>
							<option value="<?php echo esc_attr( $voice_code ); ?>" <?php selected( $voice_name, $voice_code ); ?>>
								<?php echo esc_html( $voice_label ); ?>
							</option>
						<?php endforeach; ?>
					</select>
					<p class="description">
						<?php esc_html_e( 'Select the voice for the chosen language. Different voices have different characteristics and genders.', 'simple-text-to-speech' ); ?>
					</p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="stts_speaking_style">
						<?php esc_html_e( 'Speaking Style', 'simple-text-to-speech' ); ?>
					</label>
				</th>
				<td>
					<select 
						id="stts_speaking_style" 
						name="stts_speaking_style" 
						class="regular-text"
					>
						<?php
						$speaking_styles = array(
							'neutral' => esc_html__( 'Neutral (Default)', 'simple-text-to-speech' ),
							'calm'    => esc_html__( 'Calm', 'simple-text-to-speech' ),
							'serious' => esc_html__( 'Serious', 'simple-text-to-speech' ),
							'excited' => esc_html__( 'Excited', 'simple-text-to-speech' ),
						);
						foreach ( $speaking_styles as $style_value => $style_label ) :
							?>
							<option value="<?php echo esc_attr( $style_value ); ?>" <?php selected( $speaking_style, $style_value ); ?>>
								<?php echo esc_html( $style_label ); ?>
							</option>
						<?php endforeach; ?>
					</select>
					<p class="description">
						<?php esc_html_e( 'Choose the tone and style of the voice. This affects the speaking rate and pitch to convey different emotions.', 'simple-text-to-speech' ); ?>
					</p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="stts_usage_limit">
						<?php esc_html_e( 'Monthly Character Limit', 'simple-text-to-speech' ); ?>
					</label>
				</th>
				<td>
					<input 
						type="number" 
						id="stts_usage_limit" 
						name="stts_usage_limit" 
						value="<?php echo esc_attr( $usage_limit ); ?>" 
						class="regular-text"
						min="1000"
						step="1000"
					/>
					<p class="description">
						<?php esc_html_e( 'Maximum number of characters that can be converted to speech per month. Default is 1,000,000 characters. Google Cloud offers 1 million WaveNet characters free per month. Exceeding this limit may incur additional charges. Usage resets automatically on the 1st of each month.', 'simple-text-to-speech' ); ?>
					</p>
				</td>
			</tr>
		</table>

		<?php submit_button( esc_html__( 'Save Settings', 'simple-text-to-speech' ) ); ?>
	</form>
	<?php
}

/**
 * Render Styles tab content
 *
 * @since 1.0.0
 */
function stts_render_styles_tab() {
	$player_style = get_option( 'stts_player_style', 'audio' );
	?>
	<form method="post" action="">
		<?php wp_nonce_field( 'stts_save_settings', 'stts_settings_nonce' ); ?>
		
		<h2><?php esc_html_e( 'Audio Player Styles', 'simple-text-to-speech' ); ?></h2>
		<p><?php esc_html_e( 'Choose how the audio player will appear on your posts and pages.', 'simple-text-to-speech' ); ?></p>

		<table class="form-table">
			<tr>
				<th scope="row">
					<label for="stts_player_style">
						<?php esc_html_e( 'Player Style', 'simple-text-to-speech' ); ?>
					</label>
				</th>
				<td>
					<select 
						id="stts_player_style" 
						name="stts_player_style" 
						class="regular-text"
					>
						<option value="audio" <?php selected( $player_style, 'audio' ); ?>>
							<?php esc_html_e( 'Audio Player (Default)', 'simple-text-to-speech' ); ?>
						</option>
						<option value="icon" <?php selected( $player_style, 'icon' ); ?>>
							<?php esc_html_e( 'Icon Only', 'simple-text-to-speech' ); ?>
						</option>
					</select>
					<p class="description">
						<?php esc_html_e( 'Display audio player or an audio icon that reveals the player when clicked.', 'simple-text-to-speech' ); ?>
					</p>
				</td>
			</tr>
		</table>

		<?php submit_button( esc_html__( 'Save Settings', 'simple-text-to-speech' ) ); ?>
	</form>
	<?php
}

/**
 * Render FAQ tab content
 *
 * @since 1.0.0
 */
function stts_render_faq_tab() {
	?>
	<div class="stts-faq-content">
		<h2><?php esc_html_e( 'Frequently Asked Questions', 'simple-text-to-speech' ); ?></h2>
		<div class="stts-faq-item">
			<h3><?php esc_html_e( '1. How to use this plugin?', 'simple-text-to-speech' ); ?></h3>
			<p>
				<?php
				printf(
					/* translators: %s: link to Google Cloud Console */
					esc_html__( 'To use the plugin, you first need to create an account in %1$s and get an API key for their Text to Speech service. After that, edit any post or page in the Block Editor and click the "Generate Audio" button in the right sidebar. You can also upload external audio via the "Upload Audio" button.', 'simple-text-to-speech' ),
					'<a href="' . esc_url( 'https://console.cloud.google.com/' ) . '" target="_blank" rel="noopener">' . esc_html__( 'Google Cloud', 'simple-text-to-speech' ) . '</a>'
				);
				?>
			</p>
		</div>
		<div class="stts-faq-item">
			<h3><?php esc_html_e( '2. How do I get a Google Cloud API key?', 'simple-text-to-speech' ); ?></h3>
			<p>
				<?php
				printf(
					/* translators: %1$s: link to Google Cloud Console, %2$s: link to Text-to-Speech API, %3$s: settings link */
					esc_html__( '1. Go to the %1$s%2$s2. Create a new project or select an existing one%3$s3. Enable the %4$s%5$s4. Go to "Credentials" and create an API key. Restrict the key to Cloud Text-to-Speech API%6$s5. Copy the API key and paste it in the Settings tab', 'simple-text-to-speech' ),
					'<a href="' . esc_url( 'https://console.cloud.google.com/apis/credentials' ) . '" target="_blank" rel="noopener">' . esc_html__( 'Google Cloud Console', 'simple-text-to-speech' ) . '</a>',
					'<br>',
					'<br>',
					'<a href="' . esc_url( 'https://console.cloud.google.com/flows/enableapi?apiid=texttospeech.googleapis.com' ) . '" target="_blank" rel="noopener">' . esc_html__( 'Text-to-Speech API', 'simple-text-to-speech' ) . '</a>',
					'<br>',
					'<br>'
				);
				?>
			</p>
		</div>

		<div class="stts-faq-item">
			<h3><?php esc_html_e( '3. How do I generate audio for a post?', 'simple-text-to-speech' ); ?></h3>
			<p><?php esc_html_e( 'Edit any post or page in the Block Editor. In the sidebar on the right, you will find a "Text to Speech" panel. Click the "Generate Audio" button to create an audio file from your post content.', 'simple-text-to-speech' ); ?></p>
		</div>

		<div class="stts-faq-item">
			<h3><?php esc_html_e( '4. Where are the audio files stored?', 'simple-text-to-speech' ); ?></h3>
			<p><?php esc_html_e( 'Audio files are stored in your WordPress Media Library (uploads folder). They are automatically attached to the respective post or page.', 'simple-text-to-speech' ); ?></p>
		</div>

		<div class="stts-faq-item">
			<h3><?php esc_html_e( '5. What languages are supported?', 'simple-text-to-speech' ); ?></h3>
			<p>
				<?php
				printf(
					/* translators: %s: link to supported languages */
					esc_html__( 'Google Cloud Text-to-Speech supports over 50 languages and variants. You can find the complete list of %s in the Google Cloud documentation.', 'simple-text-to-speech' ),
					'<a href="' . esc_url( 'https://cloud.google.com/text-to-speech/docs/voices' ) . '" target="_blank" rel="noopener">' . esc_html__( 'supported languages and voices', 'simple-text-to-speech' ) . '</a>'
				);
				?>
			</p>
		</div>

		<div class="stts-faq-item">
			<h3><?php esc_html_e( '6. Does this plugin cost money?', 'simple-text-to-speech' ); ?></h3>
			<p>
				<?php
				printf(
					/* translators: %s: link to pricing page */
					esc_html__( 'The plugin is free but Google Cloud Text-to-Speech is a paid service with a generous free tier of 1 million characters per month for WaveNet voices, which is what this plugin uses. Check the %s for additional information.', 'simple-text-to-speech' ),
					'<a href="' . esc_url( 'https://cloud.google.com/text-to-speech/pricing' ) . '" target="_blank" rel="noopener">' . esc_html__( 'Google Cloud pricing', 'simple-text-to-speech' ) . '</a>'
				);
				?>
			</p>
		</div>

		<div class="stts-faq-item">
			<h3><?php esc_html_e( '7. Where can I read more about this plugin?', 'simple-text-to-speech' ); ?></h3>
			<p>
				<?php
				printf(
					/* translators: %s: link to pricing page */
					esc_html__( 'A detailed overview of the plugin is available %s.', 'simple-text-to-speech' ),
					'<a href="' . esc_url( 'https://nasiothemes.com/how-to-generate-audio-versions-of-your-wordpress-posts-and-pages-with-ai/' ) . '" target="_blank" rel="noopener">' . esc_html__( 'here', 'simple-text-to-speech' ) . '</a>'
				);
				?>
			</p>
		</div>
	</div>
	<?php
}

