<?php
/**
 * Footer navigation. Synced into a wp_navigation post via `wp dch sync`.
 * Each item: ['label' => string, 'url' => string, 'children' => array?]
 */

defined( 'ABSPATH' ) || exit;

return [
	[ 'label' => 'About',   'url' => '/about' ],
	[ 'label' => 'Contact', 'url' => '/about#contact' ],
];
