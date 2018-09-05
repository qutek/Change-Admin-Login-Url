<?php

function calu_trailingslashit( $string ) {

	if( '/' === substr( get_option( 'permalink_structure' ), -1, 1 ) ){
		return trailingslashit( $string );
	} else {
		return untrailingslashit( $string );
	}
}

function calu_login_slug(){
	$slug = 'login';

	if( get_option( 'calu_url' ) ){
		$slug = get_option( 'calu_url' );
	} else if( is_multisite() && is_plugin_active_for_network( WPST_CWPL_PLUGIN_BASENAME ) && get_site_option( 'calu_url', 'login' ) ){
		$slug = get_site_option( 'calu_url', 'login' );
	}

	return $slug;
}

function calu_login_url( $scheme = null ) {
	if ( get_option( 'permalink_structure' ) ) {
		return calu_trailingslashit( home_url( '/', $scheme ) . calu_login_slug() );
	} else {
		return home_url( '/', $scheme ) . '?' . calu_login_slug();
	}
}

function calu_filter_wp_login_php( $url, $scheme = null ) {
	if ( strpos( $url, 'wp-login.php' ) !== false ) {
		if ( is_ssl() ) {
			$scheme = 'https';
		}

		$args = explode( '?', $url );

		if ( isset( $args[1] ) ) {
			parse_str( $args[1], $args );
			$url = add_query_arg( $args, calu_login_url( $scheme ) );
		} else {
			$url = calu_login_url( $scheme );
		}
	}

	return $url;
}

function calu_default_wp_admin_handler(){
	global $pagenow, $wp_query;

	$pagenow = 'index.php';

	if ( ! defined( 'WP_USE_THEMES' ) ) {
		define( 'WP_USE_THEMES', true );
	}

	wp();

	$wp_query->set_404();
    status_header( 404 );

    remove_action( 'template_redirect', '_wp_admin_bar_init', 0 );

	$_SERVER['REQUEST_URI'] = calu_trailingslashit( '/wp-admin-404/' );

	require_once( ABSPATH . WPINC . '/template-loader.php' );

	die();
}