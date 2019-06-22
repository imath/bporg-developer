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
		$routes             = array_intersect_key( $data['routes'], array( '/' => true ) );
		$page_titles        = array_flip( wp_list_pluck( $data['pages'], 'title' ) );

		foreach( $bp_endpoints as $bp_route => $route_data ) {
			if ( isset( $page_titles[ $bp_route ] ) ) {
				$bp_endpoints[ $bp_route ]['description'] = $data['pages'][ $page_titles[ $bp_route ] ]['content'];
				$bp_endpoints[ $bp_route ]['excerpt']     = $data['pages'][ $page_titles[ $bp_route ] ]['excerpt'];
				unset( $data['pages'][ $page_titles[ $bp_route ] ] );
			}
		}

		// Merge only what we need.
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

/**
 * Set the section title for the Restsplain page.
 *
 * @since 1.0.0
 *
 * @param string $section_title The title of the section displayed.
 * @return string The title of the section displayed.
 */
function bporg_developer_set_restsplain_section_title( $section_title = '' ) {
	if ( is_singular() && is_page_template() ) {
		$page_id = get_queried_object_id();

		if ( 'page-restsplain.php' === get_page_template_slug( $page_id ) ) {
			$section_title = __( 'BP REST API Reference', 'bporg-developer' );
		}
	}

	return $section_title;
}
add_filter( 'bporg_developer_get_site_section_title', 'bporg_developer_set_restsplain_section_title' );

/**
 * Set the section URL for the Restsplain page.
 *
 * @since 1.0.0
 *
 * @param string $url  The URL of the section displayed.
 * @param string $slug The slug of the section displayed.
 * @return string The URL of the section displayed.
 */
function bporg_developer_set_restsplain_section_url( $url = '', $slug = '' ) {
	if ( is_singular() && is_page_template() ) {
		$page = get_queried_object();

		if ( $slug === $page->post_name ) {
			$url = get_permalink( $page );
		}
	}

	return $url;
}
add_filter( 'bporg_developer_get_site_section_url', 'bporg_developer_set_restsplain_section_url', 10, 2 );
