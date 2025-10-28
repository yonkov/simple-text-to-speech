/**
 * Meta Box JavaScript
 *
 * Handles Generate and Delete audio actions in Classic Editor meta box
 *
 * @package Simple Text to Speech
 * @since 1.0.0
 */

( function() {
	'use strict';

	/**
	 * Handle Generate Audio button click
	 */
	document.addEventListener( 'click', function( event ) {
		if ( ! event.target.classList.contains( 'stts-generate-audio' ) ) {
			return;
		}

		const button = event.target;
		const postId = button.getAttribute( 'data-post-id' );
		const nonce = button.getAttribute( 'data-nonce' );

		if ( ! postId || ! nonce ) {
			return;
		}

		// Disable button and show loading state.
		button.disabled = true;
		const originalText = button.textContent;
		button.textContent = 'Generating...';

		// Create form data.
		const formData = new FormData();
		formData.append( 'action', 'stts_generate_audio' );
		formData.append( 'post_id', postId );
		formData.append( 'stts_generate_nonce', nonce );

		// Submit to admin-post.php.
		fetch( ajaxurl.replace( 'admin-ajax.php', 'admin-post.php' ), {
			method: 'POST',
			body: formData,
			credentials: 'same-origin'
		} )
		.then( function( response ) {
			if ( response.redirected ) {
				// Follow the redirect.
				window.location.href = response.url;
			} else {
				return response.text();
			}
		} )
		.catch( function( error ) {
			console.error( 'Error:', error );
			button.disabled = false;
			button.textContent = originalText;
			alert( 'An error occurred while generating audio.' );
		} );
	} );

	/**
	 * Handle Delete Audio button click
	 */
	document.addEventListener( 'click', function( event ) {
		if ( ! event.target.classList.contains( 'stts-delete-audio' ) ) {
			return;
		}

		const button = event.target;
		const postId = button.getAttribute( 'data-post-id' );
		const nonce = button.getAttribute( 'data-nonce' );

		if ( ! postId || ! nonce ) {
			return;
		}

		// Disable button and show loading state.
		button.disabled = true;
		const originalText = button.textContent;
		button.textContent = 'Deleting...';

		// Create form data.
		const formData = new FormData();
		formData.append( 'action', 'stts_delete_audio' );
		formData.append( 'post_id', postId );
		formData.append( 'stts_delete_nonce', nonce );

		// Submit to admin-post.php.
		fetch( ajaxurl.replace( 'admin-ajax.php', 'admin-post.php' ), {
			method: 'POST',
			body: formData,
			credentials: 'same-origin'
		} )
		.then( function( response ) {
			if ( response.redirected ) {
				// Follow the redirect.
				window.location.href = response.url;
			} else {
				return response.text();
			}
		} )
		.catch( function( error ) {
			console.error( 'Error:', error );
			button.disabled = false;
			button.textContent = originalText;
			alert( 'An error occurred while deleting audio.' );
		} );
	} );
} )();
