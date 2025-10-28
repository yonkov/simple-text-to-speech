<?php
// Simple test runner for stts_prepare_text().
// This file is intended to be run from the plugin root via: php tests/test-stts-prepare-text.php

// Make sure ABSPATH is defined so included plugin file doesn't exit.
if ( ! defined( 'ABSPATH' ) ) {
    define( 'ABSPATH', __DIR__ . '/..' );
}

// Provide minimal WordPress function stubs used by stts_prepare_text().
if ( ! function_exists( 'get_option' ) ) {
    function get_option( $name, $default = null ) {
        if ( 'blog_charset' === $name ) {
            return 'UTF-8';
        }
        return $default;
    }
}

if ( ! function_exists( 'strip_shortcodes' ) ) {
    function strip_shortcodes( $text ) {
        // Very small approximation for tests: remove [shortcode] blocks.
        return preg_replace( '/\[[^\]]+\]/', '', $text );
    }
}

if ( ! function_exists( 'wp_strip_all_tags' ) ) {
    function wp_strip_all_tags( $text ) {
        return strip_tags( $text );
    }
}

// Include the file that contains stts_prepare_text().
require_once __DIR__ . '/../includes/api/google-tts.php';

$tests = array(
    'basic_quotes' => array(
        'input' => 'He said &quot;Hello&quot; and then left.',
        'expected' => 'He said Hello and then left.'
    ),
    'smart_quotes_and_apostrophes' => array(
        'input' => "Don’t get me wrong. I don't think you need more persuasion.",
        'expected' => "Don't get me wrong. I don't think you need more persuasion."
    ),
    'double_quotes_removed' => array(
        'input' => 'Navigate to the "Text to Speech" settings.',
        'expected' => 'Navigate to the Text to Speech settings.'
    ),
    'punctuation_colon_semicolon' => array(
        'input' => 'Time: 10:00; ready?',
        'expected' => 'Time 10 00 ready?'
    ),
    'greater_than_and_arrows' => array(
        'input' => 'Left > IAM & Admin > Create a Project.',
        'expected' => 'Left IAM and Admin Create a Project.'
    ),
    'parentheses_and_slashes' => array(
        'input' => 'Install (step 1/2) and activate.',
        'expected' => 'Install step 1 2 and activate.'
    ),
    'ellipsis_and_dashes' => array(
        'input' => 'Wait... this is important — read carefully.',
        'expected' => 'Wait. this is important read carefully.'
    ),
    'complex_example' => array(
        'input' => "Don't get me wrong.  I don't think you need more persuasion why you might need this feature on your website, so let's get into it. Once you install and activate it, navigate to the \"Text to Speech\" sub page in the Settings menu item in the left sidebar or in the plugin list page, hover over the plugin and click \"Settings\". Left > IAM & Admin > Create a Project.",
        'expected' => "Don't get me wrong. I don't think you need more persuasion why you might need this feature on your website, so let's get into it. Once you install and activate it, navigate to the Text to Speech sub page in the Settings menu item in the left sidebar or in the plugin list page, hover over the plugin and click Settings. Left IAM and Admin Create a Project."
    ),
);

$all_passed = true;

foreach ( $tests as $name => $case ) {
    $output = stts_prepare_text( $case['input'] );
    if ( $output === $case['expected'] ) {
        echo "[PASS] $name\n";
    } else {
        echo "[FAIL] $name\n";
        echo "  Input   : {$case['input']}\n";
        echo "  Expected: {$case['expected']}\n";
        echo "  Got     : $output\n\n";
        $all_passed = false;
    }
}

if ( $all_passed ) {
    echo "All tests passed.\n";
    exit(0);
}

echo "Some tests failed.\n";
exit(1);
