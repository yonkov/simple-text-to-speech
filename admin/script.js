/**
 * Admin Scripts
 *
 * @package SimpleTextToSpeech
 * @since 1.0.0
 */

(function() {

	/**
	 * Update voice dropdown based on selected language
	 *
	 * @since 1.0.0
	 */
	function updateVoiceDropdown() {
		const languageSelect = document.getElementById('stts_language_code');
		const voiceSelect = document.getElementById('stts_voice_name');
		const selectedLanguage = languageSelect.value;

		if (!sttsVoiceData || !sttsVoiceData[selectedLanguage]) {
			return;
		}

		const voices = sttsVoiceData[selectedLanguage];
		const currentVoice = voiceSelect.value;

		// Clear existing options
		voiceSelect.innerHTML = '';

		// Add new options
		Object.keys(voices).forEach(function(voiceCode) {
			const option = document.createElement('option');
			option.value = voiceCode;
			option.textContent = voices[voiceCode];
			voiceSelect.appendChild(option);
		});

		// Try to preserve selection if the voice exists for new language
		if (voices[currentVoice]) {
			voiceSelect.value = currentVoice;
		} else {
			// Select first option
			voiceSelect.value = Object.keys(voices)[0];
		}
	}

	/**
	 * Initialize on document ready
	 *
	 * @since 1.0.0
	 */
	document.addEventListener('DOMContentLoaded', function() {
		const languageSelect = document.getElementById('stts_language_code');

		if (languageSelect) {
			languageSelect.addEventListener('change', updateVoiceDropdown);
		}
		
		// Upload audio button handler for meta box
		const uploadButton = document.querySelector('.stts-upload-audio-btn');
		
		if (uploadButton) {
			uploadButton.addEventListener('click', function(e) {
				e.preventDefault();
				
				const mediaUploader = wp.media({
					title: uploadButton.dataset.title || 'Select Audio File',
					button: {
						text: uploadButton.dataset.buttonText || 'Use this audio'
					},
					library: {
						type: 'audio'
					},
					multiple: false
				});
				
				mediaUploader.on('select', function() {
					const attachment = mediaUploader.state().get('selection').first().toJSON();
					const hiddenInput = document.getElementById('stts_uploaded_audio_id');
					
					if (hiddenInput) {
						hiddenInput.value = attachment.id;
						
						// Create hidden input for save flag
						const saveInput = document.createElement('input');
						saveInput.type = 'hidden';
						saveInput.name = 'stts_save_uploaded_audio';
						saveInput.value = '1';
						
						const form = document.getElementById('post');
						if (form) {
							form.appendChild(saveInput);
							
							// Trigger publish/update button
							const publishButton = document.getElementById('publish');
							if (publishButton) {
								publishButton.click();
							}
						}
					}
				});
				
				mediaUploader.open();
			});
		}
	});

})();
