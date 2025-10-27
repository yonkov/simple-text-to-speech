document.addEventListener('DOMContentLoaded', function() {
	const iconContainers = document.querySelectorAll('.stts-audio-container.stts-style-icon');
	
	iconContainers.forEach(function(container) {
		const button = container.querySelector('.stts-icon-button');
		const audio = container.querySelector('audio');
		
		if (!button || !audio) {
			return;
		}
		
		button.addEventListener('click', function(e) {
			e.preventDefault();
			const isVisible = container.classList.contains('stts-player-visible');
			
			if (!isVisible) {
				// Show player and play
				container.classList.add('stts-player-visible');
				audio.play();
			} else {
				// Toggle play/pause
				if (audio.paused) {
					audio.play();
				} else {
					audio.pause();
				}
			}
		});
		
		// Update icon based on play/pause state
		audio.addEventListener('play', function() {
			button.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="10" y1="15" x2="10" y2="9"></line><line x1="14" y1="15" x2="14" y2="9"></line></svg>';
			button.setAttribute('aria-label', button.getAttribute('data-pause-label') || 'Pause audio');
		});
		
		audio.addEventListener('pause', function() {
			button.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon><path d="M15.54 8.46a5 5 0 0 1 0 7.07"></path></svg>';
			button.setAttribute('aria-label', button.getAttribute('data-play-label') || 'Play audio');
		});
	});
});
