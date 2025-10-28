=== Simple Text to Speech ===
Contributors: nasiothemes
Plugin Name: Simple Text to Speech
Plugin URI: https://github.com/yonkov/simple-text-to-speech
Tags: text to speech, tts, audio, accessibility, ai
Author URI: https://nasiothemes.com/
Author: Nasio Themes
Requires at least: 6.7
Requires PHP: 7.2
Tested up to: 6.8
Stable tag: 1.0.0
License: GPLv2

Easily generate audio version of your content using Google Cloud Text-to-Speech API.

== Description ==

Simple Text to Speech is a WordPress plugin that allows you to convert WordPress posts and pages to audio. It uses [Google Cloud Text-to-Speech API]((https://console.cloud.google.com/marketplace/product/google/texttospeech.googleapis.com)) with WaveNet realistic AI voices. 
The plugin supports more than 50 languages and seamlessly integrates with the WordPress Block Editor but it also works in the good old Classic editor. 

When the audio has been generated for a post (from the page editor sidebar), an audio player will be displayed automatically at the top of your post, which visitors can listen to.

= Features =

* **Block Editor Integration** - Generate audio directly from the post/page editor with a convenient sidebar panel
* **AI Voices** - Use Google's premium WaveNet voices for natural-sounding speech
* **Multiple Languages** - Supports over 50 languages and variants
* **Accessibility ready** - Improves content accessibility by providing audio versions of your posts and pages
* **Automatic Storage** - Audio files are automatically saved to your WordPress Media Library
* **Easy Management** - Delete audio files from either the editor panel or Media Library
* **Custom audio upload** - Ability to upload audio from external sources and using human voices
* **REST API** - Built-in REST API endpoints for audio generation and management
* **Multi-language Support** - Supports 50+ languages and language variants

= How It Works =

1. Configure your Google Cloud API key in Settings > Text to Speech
2. Edit any post or page in the Block Editor
3. Find the "Text to Speech" panel in the document sidebar
4. Click "Generate Audio" to create an MP3 file from your content
5. Audio is saved to Media Library and attached to the post
6. Play, download, or delete audio directly from the panel

= Google Cloud Text-to-Speech =

This plugin uses Google Cloud's Text-to-Speech API, which requires an API key. Google offers a generous free tier:
* WaveNet voices: 1 million characters per month free
* Standard voices: 4 million characters per month free

This plugin uses predominantly WaveNet AI voices, as well as Standard AI voices when WaveNet voices are not available for the specific language.

Learn more about [Google Cloud Text-to-Speech pricing](https://cloud.google.com/text-to-speech/pricing).

== Installation ==

1. Upload the plugin files to `/wp-content/plugins/simple-text-to-speech/` or install through the WordPress plugins screen
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Settings > Text to Speech
4. Enter your Google Cloud API key
5. Configure language and voice settings (defaults to en-US with WaveNet voice)
6. Start generating audio for your posts and pages!

== Frequently Asked Questions ==

= 1. How to use this plugin? =

To use the plugin, you first need to create an account in [Google Cloud](https://console.cloud.google.com/) and get an API key for their Text to Speech service. After that, edit any post or page in the Block Editor and click the "Generate Audio" button in the right sidebar. You can also upload external audio via the "Upload Audio" button.

= 2. How do I get a Google Cloud API key? =

1. Go to the [Google Cloud Console](https://console.cloud.google.com/apis/credentials)
2. Create a new project or select an existing one
3. [Enable the Text-to-Speech API](https://console.cloud.google.com/flows/enableapi?apiid=texttospeech.googleapis.com)
4. Go to "Credentials" and create an API key. Restrict the key to Cloud Text-to-Speech API
5. Copy the API key and paste it in the Settings tab

= 3. How do I generate audio for a post? =

Edit any post or page in the Block Editor. In the sidebar on the right, you will find a "Text to Speech" panel. Click the "Generate Audio" button to create an audio file from your post content.

= 4. Where are the audio files stored? =

Audio files are stored in your WordPress Media Library (uploads folder). They are automatically attached to the respective post or page.

= 5. What languages are supported? =

Google Cloud Text-to-Speech supports over 30 languages and variants. You can find the complete list of [supported languages and voices](https://cloud.google.com/text-to-speech/docs/voices) in the Google Cloud documentation.

= 6. Does this plugin cost money? =

The plugin is free but Google Cloud Text-to-Speech is a paid service with a generous free tier of 1 million characters per month for WaveNet voices, which is what this plugin uses. Check the [Google Cloud pricing](https://cloud.google.com/text-to-speech/pricing) for additional information.

== Changelog ==

= 1.0.0 - October 2025 =
* Initial release

