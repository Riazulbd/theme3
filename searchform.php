<?php
/**
 * Search form (used by get_search_form()).
 *
 * @package Narayanganj_News
 */

defined( 'ABSPATH' ) || exit;

echo nn_search_form( 'nn-s-' . wp_unique_id() ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped inside.
