#Additional Post Formats
Contributors: tnash

Tags: Post Formats, Posts

Requires at least: 3.6

Tested up to: 4.2.2

License URI: http://www.gnu.org/licenses/gpl-2.0.html


##Description
Small plugin to provide themes and plugins a way to add additional post formats. Given controversy surrounding the existence and use of post formats and the fact the core team deliberately prevented post formats to be easily expanded this plugin should be considered a hack and liable to break in future versions of WordPress.


##Installation

1. Upload 'additional-post-formats.php' to the '/wp-content/plugins/' directory,
2. Activate the plugin through the 'Plugins' menu in WordPress.

Once activated themes and plugins can add additional post formats

##Other Notes

###Usage
Once activated the plugin provides the ability to add additional post formats, for a plugin or theme to add an additional post format they will need to use the add_filter function calling `tnapf_get_post_format_strings` to modify the post_formats list for example:

`function example_additional_post_formats($formats){
	$new_post_formats = array(
			'example'    => _x( 'Example Additional Format','Post format' ),
			'example2'   => _x( 'Second Example','Post format' ),
			);
	return array_merge($formats,$new_post_formats);
}
add_filter('tnapf_get_post_format_strings','example_additional_post_formats',10,1);`

This will add 2 additional Post Formats, for a theme to use them they will need to add 

`add_theme_support( 'additional-post-formats', array('example','example2'));`

Within their theme functions file or similar, like post-formats this should be done before init a good hook to use is after_setup_theme

###WARNING
This plugin should be considered a hack as it provides functionality that the core team deliberately kept disabled. Therefore it is likely that on future updates of WordPress it will break.

##Frequently Asked Questions

###Why do I have to call `additional-post-formats`
The standard Post Formats string get's stripped early on by the default functionality. Also it means that when the plugin is disabled it doesn't leave to much mess behind.

###Why did you build this?
I think post formats are a great idea for when you need to display the same content type in a different way. I developed the plugin specifically to allow me to select different Schema formats for my blog posts.

###Why is this not on Extend
Did I mention this is a hack?

##Changelog
- 1.0.2 Fixed a couple of notices
- 1.0.1 Added blank index.php and made sure plugin can't be called directly.
- 1.0.0 Initial Version
