<?php
/**
 * BuddyPress Developer Theme filters for Restsplain.
 *
 * @package bporg-developer
 * @since 1.0.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Forces the schema to only be populated with BuddyPress endpoints.
 *
 * @since 1.0.0
 *
 * @param array $data the transformed Restsplain schema output.
 * @return array the BP REST Restsplain schema output.
 */
function bporg_developer_restsplain_schema( $data ) {
    $page_id      = url_to_postid( wp_get_referer() );
    $schema_url   = 'https://cldup.com/c1qvLy1iYB.json';
    $bp_endpoints = null;

    /**
     * When used as a shortcode, it's possible to customize the schema
     * URL: [restsplain file="https://link.to/bp-endpoints.json"]
     */
	if ( $page_id ) {
		$page = get_post( $page_id );
		preg_match( '/' . get_shortcode_regex( array( 'restsplain' ) ) . '/', $page->post_content, $matches );
		if ( isset( $matches[3] ) ) {
			$args = shortcode_parse_atts( $matches[3] );
			if ( isset( $args['file'] ) && $args['file'] ) {
				$schema_url = $args['file'];
			}
		}
    }

    /**
     * Try to fetch the BP endpoints.
     *
     * NB: this allowes to avoid having BuddyPress & the BP REST API
     * activated on the documentation site.
     */
    $response = wp_remote_get( $schema_url );
    if ( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) ) {
        $bp_endpoints = json_decode( wp_remote_retrieve_body( $response ), true );
    }

	if ( ! is_array( $bp_endpoints ) ) {
		if ( isset( $data['namespaces'] ) && is_array( $data['namespaces'] ) ) {
			foreach ( $data['namespaces'] as $i => $namespace ) {
				if ( 'buddypress/v1' !== $namespace && '/' !== $namespace ) {
					unset( $data['namespaces'][ $i ] );
				}
			}
			$data['namespaces'] = array_values( $data['namespaces'] );
		}
		if ( isset( $data['routes'] ) && is_array( $data['routes'] ) ) {
			foreach ( $data['routes'] as $route => $endpoints ) {
				if ( false === strpos( $route, '/buddypress/v1' ) && '/' !== $route ) {
					unset( $data['routes'][ $route ] );
				} elseif ( 0 === strpos( $route, '/buddypress/v1' ) ) {
					// Neutralize links.
					if ( isset( $endpoints['_links'] ) ) {
						foreach ( $endpoints['_links'] as $key_link => $link ) {
							$endpoints['_links'][ $key_link ] = str_replace( home_url(), 'site.url', $link );
						}
					}
					$data['routes'][ $route ] = $endpoints;
				}
			}
		}
	} else {
		$data['namespaces'] = array( 'buddypress/v1' );
		$routes = array_intersect_key( $data['routes'], array( '/' => true ) );
		$data['routes'] = array_merge( $routes, $bp_endpoints );
	}
	return $data;
}
add_filter( 'restsplain_schema', 'bporg_developer_restsplain_schema' );

/**
 * Edit the Restsplain config to include the BP logo and use a code theme
 * looking like the one used by DevHub for Syntax Highlighter Evolve
 *
 * @since 1.0.0
 *
 * @param array $config The Restsplain config.
 * @return array The Restsplain config.
 */
function bporg_developer_resplain_config( $config = array() ) {
    $config['logo']      = get_theme_file_uri( 'images/buddypress-logo.png' );
    $config['codeTheme'] = 'idea';

    return $config;
}
add_filter( 'restsplain_config', 'bporg_developer_resplain_config' );
