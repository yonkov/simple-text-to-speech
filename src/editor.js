import { registerPlugin } from '@wordpress/plugins';
import { PluginDocumentSettingPanel } from '@wordpress/editor';
import { Button, Notice, Spinner } from '@wordpress/components';
import { useSelect, useDispatch } from '@wordpress/data';
import { store as editorStore } from '@wordpress/editor';
import { useState, useEffect, useRef } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';
import { MediaUpload, MediaUploadCheck } from '@wordpress/block-editor';
import './editor.css';

/**
 * Text to Speech Panel Component
 *
 * @since 1.0.0
 */
function TextToSpeechPanel() {
	const [isGenerating, setIsGenerating] = useState( false );
	const [isDeleting, setIsDeleting] = useState( false );
	const [audioData, setAudioData] = useState( null );
	const [usageData, setUsageData] = useState( null );
	const [error, setError] = useState( null );
	const [success, setSuccess] = useState( null );

	const postId = useSelect( ( select ) => select( editorStore ).getCurrentPostId(), [] );

	const isPostDirty = useSelect( ( select ) => select( editorStore ).isEditedPostDirty(), [] );

	const { editPost } = useDispatch( editorStore );

	// Action to open the document sidebar so the plugin panel is visible by default.
	const { openGeneralSidebar } = useDispatch( 'core/edit-post' );

	// Check if API key is configured.
	const hasApiKey = window.sttsData?.hasApiKey || false;
	
	// Get language info.
	const languageName = window.sttsData?.languageName || '';
	const speakingStyle = window.sttsData?.speakingStyle || '';
	const settingsUrl = window.sttsData?.settingsUrl || '';

	// Load audio status on mount.
	useEffect(
		function () {
			if ( postId ) {
				loadAudioStatus();
			}
		},
		[postId]
	);

	// Open the Document sidebar once when a post is available so the
	// PluginDocumentSettingPanel is visible by default. Guard with a ref so
	// we only open it a single time during this component lifecycle.
	const _sidebarOpened = useRef( false );
	useEffect( function () {
		if ( postId && ! _sidebarOpened.current ) {
			if ( typeof openGeneralSidebar === 'function' ) {
				openGeneralSidebar( 'edit-post/document' );
			}
			_sidebarOpened.current = true;
		}
	}, [ postId, openGeneralSidebar ] );

	/**
	 * Load current audio status
	 */
	function loadAudioStatus() {
		apiFetch( {
			path: `/simple-tts/v1/status/${postId}`,
			method: 'GET',
		} )
			.then( function ( response ) {
				if ( response.has_audio ) {
					setAudioData( {
						attachmentId: response.attachment_id,
						url: response.url,
					} );
				} else {
					setAudioData( null );
				}
				
				if ( response.usage ) {
					setUsageData( response.usage );
				}
			} )
			.catch( function ( err ) {
				console.error( 'Error loading audio status:', err );
			} );
	}

	/**
	 * Generate audio from post content
	 */
	function handleGenerateAudio() {
		setIsGenerating( true );
		setError( null );
		setSuccess( null );

		apiFetch( {
			path: '/simple-tts/v1/generate',
			method: 'POST',
			data: {
				post_id: postId,
			},
		} )
			.then( function ( response ) {
				setIsGenerating( false );
				
				// Build success message with file size and usage info.
				let message = response.message;
				if ( response.file_size_formatted && response.usage_percentage !== undefined ) {
					message += ' ' + sprintf( 
						__( 'File size: %s. Usage: %s%% of monthly limit.', 'simple-text-to-speech' ),
						response.file_size_formatted,
						response.usage_percentage
					);
				}
				
				setSuccess( message );
				setAudioData( {
					attachmentId: response.attachment_id,
					url: response.url,
				} );
				
				// Update post meta to mark post as modified and enable Update button.
				editPost( {
					meta: {
						_stts_audio_attachment_id: response.attachment_id
					}
				} );
				
				// Reload status to update usage.
				loadAudioStatus();
			} )
			.catch( function ( err ) {
				setIsGenerating( false );
				setError( err.message || __( 'Failed to generate audio.', 'simple-text-to-speech' ) );
			} );
	}

	/**
	 * Delete audio file
	 */
	function handleDeleteAudio() {
		if ( ! window.confirm( __( 'Are you sure you want to delete the audio file?', 'simple-text-to-speech' ) ) ) {
			return;
		}

		setIsDeleting( true );
		setError( null );
		setSuccess( null );

		apiFetch( {
			path: '/simple-tts/v1/delete',
			method: 'POST',
			data: {
				post_id: postId,
			},
		} )
			.then( function ( response ) {
				setIsDeleting( false );
				setSuccess( response.message );
				setAudioData( null );
				
				// Update post meta to mark post as modified and enable Update button.
				editPost( {
					meta: {
						_stts_audio_attachment_id: 0
					}
				} );
			} )
			.catch( function ( err ) {
				setIsDeleting( false );
				setError( err.message || __( 'Failed to delete audio.', 'simple-text-to-speech' ) );
			} );
	}

	/**
	 * Handle external audio upload
	 */
	function handleUploadAudio( media ) {
		if ( ! media || ! media.id ) {
			return;
		}

		// Check if it's an audio file.
		if ( media.type !== 'audio' ) {
			setError( __( 'Please select an audio file (MP3, WAV, etc.).', 'simple-text-to-speech' ) );
			return;
		}

		// Get allowed types from PHP (centralized configuration).
		const allowedMimeTypes = window.sttsData?.allowedAudioMimes || [];

		if ( media.mime && allowedMimeTypes.length > 0 && ! allowedMimeTypes.includes( media.mime ) ) {
			setError( 
				sprintf(
					__( 'Audio format "%s" is not supported. Please use MP3, WAV, OGG, WebM, M4A, AAC, or FLAC.', 'simple-text-to-speech' ),
					media.mime
				)
			);
			return;
		}

		// Update post meta with the uploaded audio attachment ID.
		editPost( { 
			meta: { 
				_stts_audio_attachment_id: media.id
			} 
		});

		// Update local state.
		setAudioData( {
			attachmentId: media.id,
			url: media.url,
		} );
		
		setSuccess( __( 'Audio file uploaded successfully!', 'simple-text-to-speech' ) );
	}

	return (
		<PluginDocumentSettingPanel
			name="text-to-speech-panel"
			title={ __( 'Text to Speech', 'simple-text-to-speech' ) }
			className="stts-document-panel"
		>
			{ ! hasApiKey && ! audioData && (
				<Notice status="warning" isDismissible={ false }>
					{ __( 'Google Cloud API key is not configured.', 'simple-text-to-speech' ) }
					{ ' ' }
					<a href={ settingsUrl }>
						{ __( 'Configure settings', 'simple-text-to-speech' ) }
					</a>
				</Notice>
			) }

			{ error && (
				<Notice
					status="error"
					isDismissible={ true }
					onRemove={ function () {
						setError( null );
					} }
				>
					{ error }
				</Notice>
			) }

			{ success && (
				<Notice
					status="success"
					isDismissible={ true }
					onRemove={ function () {
						setSuccess( null );
					} }
				>
					{ success }
				</Notice>
			) }

			<div className="stts-panel-content">
				{ usageData && usageData.limit_reached && (
					<Notice status="error" isDismissible={ false }>
						{ __( 'Monthly character limit reached. Audio generation is disabled until next month.', 'simple-text-to-speech' ) }
					</Notice>
				) }

				{ audioData ? (
					<div className="stts-audio-info">

						<audio controls src={ audioData.url } className="stts-audio-player">
							{ __( 'Your browser does not support the audio element.', 'simple-text-to-speech' ) }
						</audio>

						<Button
							variant="secondary"
							isDestructive
							onClick={ handleDeleteAudio }
							disabled={ isDeleting }
						>
							{ isDeleting ? (
								<>
									<Spinner />
									{ __( 'Deleting...', 'simple-text-to-speech' ) }
								</>
							) : (
								__( 'Delete Audio', 'simple-text-to-speech' )
							) }
						</Button>
					</div>
				) : (
					<div className="stts-no-audio">
						{ hasApiKey && (
							<>
							<p>
								{ __( 'Generate an audio file.', 'simple-text-to-speech' ) }
								{ ' ' }
								<br />
								{ languageName && (
									<>
										{ __( 'Selected language:', 'simple-text-to-speech' ) }
										{ ' ' }
										{ languageName }
										{ '.' }
										<br/>
										{ speakingStyle && (
											<>
												{ __( 'Tonality:', 'simple-text-to-speech' ) }
												{ ' ' }
												{ speakingStyle }
												{ '.' }
												<br/>
											</>
										) }
										{ __( 'Change in', 'simple-text-to-speech' ) }
										{ ' ' }
										<a href={ settingsUrl }>
											{ __( 'plugin settings', 'simple-text-to-speech' ) }
										</a>
										{ '.' }
									</>
								) }
							</p>								<div className="stts-action-buttons">
									{ isPostDirty && ! success && ! isGenerating && (
										<Notice status="warning" isDismissible={ false }>
											{ __( 'Please save your post first. Audio will be generated from the saved content.', 'simple-text-to-speech' ) }
										</Notice>
									) }

									<Button
										variant="primary"
										onClick={ handleGenerateAudio }
										disabled={ isGenerating || ! hasApiKey || ( usageData && usageData.limit_reached ) || ( isPostDirty && ! success ) }
									>
										{ isGenerating ? (
											<>
												<Spinner />
												{ __( 'Generating...', 'simple-text-to-speech' ) }
											</>
										) : (
											__( 'Generate Audio', 'simple-text-to-speech' )
										) }
									</Button>

									<MediaUploadCheck>
										<MediaUpload
											onSelect={ handleUploadAudio }
											allowedTypes={ [ 'audio' ] }
											value={ audioData ? audioData.attachmentId : 0 }
											render={ function ( { open } ) {
												return (
													<Button
														variant="secondary"
														onClick={ open }
														disabled={ ! hasApiKey }
													>
														{ __( 'Upload Audio', 'simple-text-to-speech' ) }
													</Button>
												);
											} }
										/>
									</MediaUploadCheck>
								</div>
							</>
						) }
					</div>
				) }
			</div>
		</PluginDocumentSettingPanel>
	);
}

// Register the plugin.
registerPlugin( 'simple-text-to-speech', {
	render: TextToSpeechPanel,
	icon: 'controls-volumeon',
} );
