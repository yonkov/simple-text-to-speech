<?php
/**
 * Language and Voice Configuration
 *
 * @package SimpleTextToSpeech
 * @since 1.0.0
 * @link https://docs.cloud.google.com/text-to-speech/docs/list-voices-and-types
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get available languages with their WaveNet voices
 *
 * @return array Associative array of language codes => language names.
 * @since 1.0.0
 */
function stts_get_available_languages() {
	return array(
		'af-ZA' => 'Afrikaans (South Africa)',
		'ar-XA' => 'Arabic',
		'bn-IN' => 'Bengali (India)',
		'bg-BG' => 'Bulgarian (Bulgaria)',
		'ca-ES' => 'Catalan (Spain)',
		'yue-HK' => 'Cantonese (Hong Kong)',
		'cs-CZ' => 'Czech (Czech Republic)',
		'da-DK' => 'Danish (Denmark)',
		'nl-NL' => 'Dutch (Netherlands)',
		'nl-BE' => 'Dutch (Belgium)',
		'en-AU' => 'English (Australia)',
		'en-IN' => 'English (India)',
		'en-GB' => 'English (UK)',
		'en-US' => 'English (US)',
		'fil-PH' => 'Filipino (Philippines)',
		'fi-FI' => 'Finnish (Finland)',
		'fr-CA' => 'French (Canada)',
		'fr-FR' => 'French (France)',
		'gl-ES' => 'Galician (Spain)',
		'de-DE' => 'German (Germany)',
		'el-GR' => 'Greek (Greece)',
		'gu-IN' => 'Gujarati (India)',
		'he-IL' => 'Hebrew (Israel)',
		'hi-IN' => 'Hindi (India)',
		'hu-HU' => 'Hungarian (Hungary)',
		'is-IS' => 'Icelandic (Iceland)',
		'id-ID' => 'Indonesian (Indonesia)',
		'it-IT' => 'Italian (Italy)',
		'ja-JP' => 'Japanese (Japan)',
		'kn-IN' => 'Kannada (India)',
		'ko-KR' => 'Korean (South Korea)',
		'lv-LV' => 'Latvian (Latvia)',
		'lt-LT' => 'Lithuanian (Lithuania)',
		'ms-MY' => 'Malay (Malaysia)',
		'ml-IN' => 'Malayalam (India)',
		'cmn-CN' => 'Mandarin Chinese (China)',
		'cmn-TW' => 'Mandarin Chinese (Taiwan)',
		'mr-IN' => 'Marathi (India)',
		'nb-NO' => 'Norwegian (Norway)',
		'pl-PL' => 'Polish (Poland)',
		'pt-BR' => 'Portuguese (Brazil)',
		'pt-PT' => 'Portuguese (Portugal)',
		'pa-IN' => 'Punjabi (India)',
		'ro-RO' => 'Romanian (Romania)',
		'ru-RU' => 'Russian (Russia)',
		'sr-RS' => 'Serbian (Serbia)',
		'sk-SK' => 'Slovak (Slovakia)',
		'es-ES' => 'Spanish (Spain)',
		'es-US' => 'Spanish (United States)',
		'sv-SE' => 'Swedish (Sweden)',
		'ta-IN' => 'Tamil (India)',
		'te-IN' => 'Telugu (India)',
		'th-TH' => 'Thai (Thailand)',
		'tr-TR' => 'Turkish (Turkey)',
		'uk-UA' => 'Ukrainian (Ukraine)',
		'ur-IN' => 'Urdu (India)',
		'vi-VN' => 'Vietnamese (Vietnam)',
	);
}

/**
 * Get voice name for a language code
 *
 * @param string $language_code Language code.
 * @return string Voice name.
 * @since 1.0.0
 */
function stts_get_voice_for_language( $language_code ) {
	// Get user's selected voice or use default.
	$selected_voice = get_option( 'stts_voice_name' );
	
	// If a specific voice is selected and it matches the language, use it.
	if ( $selected_voice && strpos( $selected_voice, $language_code ) === 0 ) {
		return $selected_voice;
	}
	
	// Otherwise, return default voice for the language.
	$voices = array(
		'af-ZA' => 'af-ZA-Standard-A',
		'ar-XA' => 'ar-XA-Wavenet-A',
		'bn-IN' => 'bn-IN-Wavenet-A',
		'bg-BG' => 'bg-BG-Standard-B',
		'ca-ES' => 'ca-ES-Standard-B',
		'yue-HK' => 'yue-HK-Standard-A',
		'cs-CZ' => 'cs-CZ-Wavenet-B',
		'da-DK' => 'da-DK-Wavenet-F',
		'nl-NL' => 'nl-NL-Wavenet-F',
		'nl-BE' => 'nl-BE-Wavenet-C',
		'en-AU' => 'en-AU-Wavenet-B',
		'en-IN' => 'en-IN-Wavenet-A',
		'en-GB' => 'en-GB-Wavenet-B',
		'en-US' => 'en-US-Wavenet-D',
		'fil-PH' => 'fil-PH-Wavenet-A',
		'fi-FI' => 'fi-FI-Wavenet-B',
		'fr-CA' => 'fr-CA-Wavenet-A',
		'fr-FR' => 'fr-FR-Wavenet-C',
		'gl-ES' => 'gl-ES-Standard-B',
		'de-DE' => 'de-DE-Wavenet-F',
		'el-GR' => 'el-GR-Wavenet-B',
		'gu-IN' => 'gu-IN-Wavenet-A',
		'he-IL' => 'he-IL-Wavenet-A',
		'hi-IN' => 'hi-IN-Wavenet-A',
		'hu-HU' => 'hu-HU-Wavenet-B',
		'is-IS' => 'is-IS-Standard-B',
		'id-ID' => 'id-ID-Wavenet-A',
		'it-IT' => 'it-IT-Wavenet-A',
		'ja-JP' => 'ja-JP-Wavenet-A',
		'kn-IN' => 'kn-IN-Wavenet-A',
		'ko-KR' => 'ko-KR-Wavenet-A',
		'lv-LV' => 'lv-LV-Standard-B',
		'lt-LT' => 'lt-LT-Standard-B',
		'ms-MY' => 'ms-MY-Wavenet-A',
		'ml-IN' => 'ml-IN-Wavenet-A',
		'cmn-CN' => 'cmn-CN-Wavenet-A',
		'cmn-TW' => 'cmn-TW-Wavenet-A',
		'mr-IN' => 'mr-IN-Wavenet-A',
		'nb-NO' => 'nb-NO-Wavenet-F',
		'pl-PL' => 'pl-PL-Wavenet-F',
		'pt-BR' => 'pt-BR-Wavenet-A',
		'pt-PT' => 'pt-PT-Wavenet-E',
		'pa-IN' => 'pa-IN-Wavenet-A',
		'ro-RO' => 'ro-RO-Wavenet-B',
		'ru-RU' => 'ru-RU-Wavenet-A',
		'sr-RS' => 'sr-RS-Standard-B',
		'sk-SK' => 'sk-SK-Wavenet-B',
		'es-ES' => 'es-ES-Wavenet-B',
		'es-US' => 'es-US-Wavenet-A',
		'sv-SE' => 'sv-SE-Wavenet-A',
		'ta-IN' => 'ta-IN-Wavenet-A',
		'te-IN' => 'te-IN-Standard-A',
		'th-TH' => 'th-TH-Neural2-C',
		'tr-TR' => 'tr-TR-Wavenet-A',
		'uk-UA' => 'uk-UA-Wavenet-B',
		'ur-IN' => 'ur-IN-Wavenet-A',
		'vi-VN' => 'vi-VN-Wavenet-A',
	);
	
	// Return the voice for the language code, or default to en-US.
	return isset( $voices[ $language_code ] ) ? $voices[ $language_code ] : 'en-US-Wavenet-D';
}

/**
 * Get available voices for a language code
 *
 * @param string $language_code Language code.
 * @return array Array of voice options (code => label with gender).
 * @since 1.0.0
 */
function stts_get_voices_for_language( $language_code ) {
	$all_voices = array(
		'cmn-CN' => array(
			'cmn-CN-Wavenet-A' => 'Voice A (Female)',
			'cmn-CN-Wavenet-B' => 'Voice B (Male)',
			'cmn-CN-Wavenet-C' => 'Voice C (Male)',
			'cmn-CN-Wavenet-D' => 'Voice D (Female)',
		),
		'da-DK' => array(
			'da-DK-Wavenet-A' => 'Voice A (Female)',
			'da-DK-Wavenet-C' => 'Voice C (Male)',
			'da-DK-Wavenet-D' => 'Voice D (Female)',
			'da-DK-Wavenet-E' => 'Voice E (Female)',
		),
		'de-DE' => array(
			'de-DE-Wavenet-A' => 'Voice A (Female)',
			'de-DE-Wavenet-B' => 'Voice B (Male)',
			'de-DE-Wavenet-C' => 'Voice C (Female)',
			'de-DE-Wavenet-D' => 'Voice D (Male)',
			'de-DE-Wavenet-E' => 'Voice E (Male)',
			'de-DE-Wavenet-F' => 'Voice F (Female)',
		),
		'en-AU' => array(
			'en-AU-Wavenet-A' => 'Voice A (Female)',
			'en-AU-Wavenet-B' => 'Voice B (Male)',
			'en-AU-Wavenet-C' => 'Voice C (Female)',
			'en-AU-Wavenet-D' => 'Voice D (Male)',
		),
		'en-GB' => array(
			'en-GB-Wavenet-A' => 'Voice A (Female)',
			'en-GB-Wavenet-B' => 'Voice B (Male)',
			'en-GB-Wavenet-C' => 'Voice C (Female)',
			'en-GB-Wavenet-D' => 'Voice D (Male)',
			'en-GB-Wavenet-F' => 'Voice F (Female)',
		),
		'en-IN' => array(
			'en-IN-Wavenet-A' => 'Voice A (Female)',
			'en-IN-Wavenet-B' => 'Voice B (Male)',
			'en-IN-Wavenet-C' => 'Voice C (Male)',
			'en-IN-Wavenet-D' => 'Voice D (Female)',
		),
		'en-US' => array(
			'en-US-Wavenet-A' => 'Voice A (Male)',
			'en-US-Wavenet-B' => 'Voice B (Male)',
			'en-US-Wavenet-C' => 'Voice C (Female)',
			'en-US-Wavenet-D' => 'Voice D (Male)',
			'en-US-Wavenet-E' => 'Voice E (Female)',
			'en-US-Wavenet-F' => 'Voice F (Female)',
			'en-US-Wavenet-G' => 'Voice G (Female)',
			'en-US-Wavenet-H' => 'Voice H (Female)',
			'en-US-Wavenet-I' => 'Voice I (Male)',
			'en-US-Wavenet-J' => 'Voice J (Male)',
		),
		'es-ES' => array(
			'es-ES-Wavenet-B' => 'Voice B (Male)',
			'es-ES-Wavenet-C' => 'Voice C (Female)',
			'es-ES-Wavenet-D' => 'Voice D (Female)',
		),
		'es-US' => array(
			'es-US-Wavenet-A' => 'Voice A (Female)',
			'es-US-Wavenet-B' => 'Voice B (Male)',
			'es-US-Wavenet-C' => 'Voice C (Male)',
		),
		'fr-CA' => array(
			'fr-CA-Wavenet-A' => 'Voice A (Female)',
			'fr-CA-Wavenet-B' => 'Voice B (Male)',
			'fr-CA-Wavenet-C' => 'Voice C (Female)',
			'fr-CA-Wavenet-D' => 'Voice D (Male)',
		),
		'fr-FR' => array(
			'fr-FR-Wavenet-A' => 'Voice A (Female)',
			'fr-FR-Wavenet-B' => 'Voice B (Male)',
			'fr-FR-Wavenet-C' => 'Voice C (Female)',
			'fr-FR-Wavenet-D' => 'Voice D (Male)',
		),
		'hi-IN' => array(
			'hi-IN-Wavenet-A' => 'Voice A (Female)',
			'hi-IN-Wavenet-B' => 'Voice B (Male)',
			'hi-IN-Wavenet-C' => 'Voice C (Male)',
			'hi-IN-Wavenet-D' => 'Voice D (Female)',
		),
		'it-IT' => array(
			'it-IT-Wavenet-A' => 'Voice A (Female)',
			'it-IT-Wavenet-B' => 'Voice B (Female)',
			'it-IT-Wavenet-C' => 'Voice C (Male)',
			'it-IT-Wavenet-D' => 'Voice D (Male)',
		),
		'ja-JP' => array(
			'ja-JP-Wavenet-A' => 'Voice A (Female)',
			'ja-JP-Wavenet-B' => 'Voice B (Female)',
			'ja-JP-Wavenet-C' => 'Voice C (Male)',
			'ja-JP-Wavenet-D' => 'Voice D (Male)',
		),
		'ko-KR' => array(
			'ko-KR-Wavenet-A' => 'Voice A (Female)',
			'ko-KR-Wavenet-B' => 'Voice B (Female)',
			'ko-KR-Wavenet-C' => 'Voice C (Male)',
			'ko-KR-Wavenet-D' => 'Voice D (Male)',
		),
		'nl-NL' => array(
			'nl-NL-Wavenet-A' => 'Voice A (Female)',
			'nl-NL-Wavenet-B' => 'Voice B (Male)',
			'nl-NL-Wavenet-C' => 'Voice C (Male)',
			'nl-NL-Wavenet-D' => 'Voice D (Female)',
			'nl-NL-Wavenet-E' => 'Voice E (Female)',
		),
		'pl-PL' => array(
			'pl-PL-Wavenet-A' => 'Voice A (Female)',
			'pl-PL-Wavenet-B' => 'Voice B (Male)',
			'pl-PL-Wavenet-C' => 'Voice C (Male)',
			'pl-PL-Wavenet-D' => 'Voice D (Female)',
			'pl-PL-Wavenet-E' => 'Voice E (Female)',
		),
		'pt-BR' => array(
			'pt-BR-Wavenet-A' => 'Voice A (Female)',
			'pt-BR-Wavenet-B' => 'Voice B (Male)',
			'pt-BR-Wavenet-C' => 'Voice C (Female)',
		),
		'pt-PT' => array(
			'pt-PT-Wavenet-A' => 'Voice A (Female)',
			'pt-PT-Wavenet-B' => 'Voice B (Male)',
			'pt-PT-Wavenet-C' => 'Voice C (Male)',
			'pt-PT-Wavenet-D' => 'Voice D (Female)',
		),
		'ru-RU' => array(
			'ru-RU-Wavenet-A' => 'Voice A (Female)',
			'ru-RU-Wavenet-B' => 'Voice B (Male)',
			'ru-RU-Wavenet-C' => 'Voice C (Female)',
			'ru-RU-Wavenet-D' => 'Voice D (Male)',
			'ru-RU-Wavenet-E' => 'Voice E (Female)',
		),
		'sv-SE' => array(
			'sv-SE-Wavenet-A' => 'Voice A (Female)',
			'sv-SE-Wavenet-B' => 'Voice B (Female)',
			'sv-SE-Wavenet-C' => 'Voice C (Female)',
		),
		'tr-TR' => array(
			'tr-TR-Wavenet-A' => 'Voice A (Female)',
			'tr-TR-Wavenet-B' => 'Voice B (Male)',
			'tr-TR-Wavenet-C' => 'Voice C (Female)',
			'tr-TR-Wavenet-D' => 'Voice D (Female)',
			'tr-TR-Wavenet-E' => 'Voice E (Male)',
		),
	);
	
	// Return voices for the language or a default single option.
	if ( isset( $all_voices[ $language_code ] ) ) {
		return $all_voices[ $language_code ];
	}
	
	// For languages with only one voice, return that.
	$default_voice = stts_get_voice_for_language( $language_code );
	return array( $default_voice => 'Default Voice' );
}

