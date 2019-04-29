# WP Contact Form Template

A simple contact form template for usage inside a theme in WordPress

![Screenshot on my private blog](https://github.com/bueltge/WP-Contact-Form-Template/raw/master/screenshot-1.png)

### Background
Every plugin I found was easy to implement, but very often to much overload in WordPress ... Tables, Shortcodes, includes on all pages in frontend and much more. For an simple contact form was it easy to use a page template.

## Use page template on pages
Create a WordPress page called “Contact” or “Contact Me” or whatever. In the right sidebar you will see a drop-down list under “Page Template.” Choose this template “Contact.” At the top of the right sidebar, under “Discussion,” uncheck the box marked “Allow Pings.” Now save your work and you’re done.

## Use the form template
In your page template called “Contact” or whatever inlcude the form template. Copy the template inside your theme.

	<?php get_template_part( 'contact', 'form' ); ?>

## Examples
  * Code: See the page template inside this repo for the default WordPress 'TwentyTen' theme:

	`https://github.com/bueltge/WP-Contact-Form-Template/blob/master/twentyeleven/page-contact.php`

	@see: [twentyeleven/page-contact.php](https://github.com/bueltge/WP-Contact-Form-Template/blob/master/twentyeleven/page-contact.php)

## Settings
You can only set the string for textdomain, that is translatable for the language files of the theme.
The string was set on start of the form template `contact-form.php`.

	// settings
	// text domain string from theme for translation in theme language files
	$text_domain_string = 'default';

## Other Notes
### Requirements
 * PHP 5.2
 * WordPress ;)

### Contact & Feedback
This template is designed and developed by me ([Frank Bültge](https://bueltge.de))

Please let me know if you like the plugin or you hate it or whatever ... Please fork it, add an issue for ideas and bugs.

### Disclaimer
I'm German and my English might be gruesome here and there. So please be patient with me and let me know of typos or grammatical farts. Thanks

### License
Good news, this plugin is free for everyone! Since it's released under the GPL, you can use it free of charge on your personal or commercial blog.
