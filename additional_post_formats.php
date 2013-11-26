<?php
/*
Plugin Name: Additional Post Formats
Plugin URI: http://www.timnash.co.uk/additional-post-formats/
Git URI: https://github.com/timnashcouk/additional-post-formats
Description: Adding additional Post Formats
Version: 1.0.1
Author: Tim Nash
Author URI: http://www.timnash.co.uk
License: GPL version 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html

Provides: additional-post-formats

*/
/*
 	This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

//Don't call this file directly!
if ( ! defined( 'WPINC' ) ) {
        die;
}

/**
 * Hijacks the $_wp_theme_features storing add_theme_support and adds post format data to additional formats and vice versa
 *
 */
function tn_add_post_formats()
{
	global $_wp_theme_features;
	$additional_post_formats = $_wp_theme_features['additional-post-formats'];
	$post_formats = $_wp_theme_features['post-formats'][0];
	if(is_array($additional_post_formats)){
		foreach ($additional_post_formats[0] as $format) {
			$post_formats[] = $format;
		}
	}

	$_wp_theme_features['post-formats'][0] = $post_formats;
	$_wp_theme_features['additional-post-formats'][0] = $post_formats;
}

add_action('init', 'tn_add_post_formats',20);

/**
 * Returns an array of post format slugs to their translated and pretty display versions with additional filter
 *
 * @return array The array of translated post format names.
 */
function tnapf_get_post_format_strings()
{
	//Default Strings from default post formats
	$strings = array(
		'standard' => _x( 'Standard', 'Post format' ), // Special case. any value that evals to false will be considered standard
		'aside'    => _x( 'Aside',    'Post format' ),
		'chat'     => _x( 'Chat',     'Post format' ),
		'gallery'  => _x( 'Gallery',  'Post format' ),
		'link'     => _x( 'Link',     'Post format' ),
		'image'    => _x( 'Image',    'Post format' ),
		'quote'    => _x( 'Quote',    'Post format' ),
		'status'   => _x( 'Status',   'Post format' ),
		'video'    => _x( 'Video',    'Post format' ),
		'audio'    => _x( 'Audio',    'Post format' ),
	);
	return apply_filters('tnapf_get_post_format_strings',$strings);
}

/**
 * Returns a pretty, translated version of a post format slug
 *
 * @uses tnapf_get_post_format_strings()
 *
 * @param string $slug A post format slug.
 * @return string The translated post format name.
 */
function tnapf_get_post_format_string( $slug ) 
{
	$strings = tnapf_get_post_format_strings();
	if ( !$slug )
		return $strings['standard'];
	else
		return ( isset( $strings[$slug] ) ) ? $strings[$slug] : '';
}

/**
 * Retrieves an array of post format slugs.
 *
 * @uses tnapf_get_post_format_strings()
 *
 * @return array The array of post format slugs.
 */
function tnapf_get_post_format_slugs() {
	$slugs = array_keys( tnapf_get_post_format_strings() );
	return array_combine( $slugs, $slugs );
}

/**
 *  Defines a custom meta box for post formats
 *
 */
function tnapf_custom_meta() 
{
    add_meta_box( 'tnapf_meta', __( 'Post Format', 'tnapf-textdomain' ), 'tnapf_meta_callback', 'post','side' );
}
add_action( 'add_meta_boxes', 'tnapf_custom_meta' );

/**
 * Adds modified post formats meta box
 *
 * @uses tnapf_get_post_format_string()
 *
 * @param object $post existing post object
 */
function tnapf_meta_callback($post)
{
	if ( current_theme_supports( 'additional-post-formats' ) && post_type_supports( $post->post_type, 'post-formats' ) ) :
	$post_formats = get_theme_support( 'additional-post-formats' );

	if ( is_array( $post_formats[0] ) ) :
		$post_format = get_post_format( $post->ID );
		if ( !$post_format )
			$post_format = '0';
		// Add in the current one if it isn't there yet, in case the current theme doesn't support it
		if ( $post_format && !in_array( $post_format, $post_formats[0] ) )
			{ $post_formats[0][] = $post_format; }
	?>
	<div id="post-formats-select">
		<input type="radio" name="post_format" class="post-format" id="post-format-0" value="0" <?php checked( $post_format, '0' ); ?> /> <label for="post-format-0" class="post-format-icon post-format-standard"><?php echo get_post_format_string( 'standard' ); ?></label>
		<?php foreach ( $post_formats[0] as $format ) : ?>
		<br /><input type="radio" name="post_format" class="post-format" id="post-format-<?php echo esc_attr( $format ); ?>" value="<?php echo esc_attr( $format ); ?>" <?php checked( $post_format, $format ); ?> /> <label for="post-format-<?php echo esc_attr( $format ); ?>" class="post-format-icon post-format-<?php echo esc_attr( $format ); ?>"><?php echo esc_html( tnapf_get_post_format_string( $format ) ); ?></label>
		<?php endforeach; ?><br />
	</div>
	<?php endif; endif;
}

/**
 * Removes default Post Formats box
 *
 * @uses remove_meta_box
 *
 */
function tnapf_remove_post_formats_meta_box()
{
	  remove_meta_box('formatdiv', 'post', 'side');

}
add_action( 'admin_menu' , 'tnapf_remove_post_formats_meta_box' );

/**
 * On save reests the saved post format term
 *
 * @uses tnapf_get_post_format_slugs()
 *
 * @param int $post_id ID of the saved post
 */
function tnapf_reset_post_format($post_id)
{
	$post_data = &$_POST;
	if ( isset( $post_data['post_format'] ) )
		{
			$post = get_post( $post_id );
			$format = $post_data['post_format'];
			if ( empty( $post ) )
				return new WP_Error( 'invalid_post', __( 'Invalid post' ) );

			if ( ! empty( $format ) ) {
					$format = sanitize_key( $format );
					if ( 'standard' === $format || ! in_array( $format, tnapf_get_post_format_slugs() ) )
						$format = '';
					else
						$format = 'post-format-' . $format;
			}
			return wp_set_post_terms( $post->ID, $format, 'post_format' );
		}
}
add_action( 'save_post' , 'tnapf_reset_post_format');

