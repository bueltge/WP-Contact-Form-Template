<?php
/**
 * Simple contact form template for WordPress
 * 
 * Use it in a template for pages in WordPress, include it easily via get_template_part( 'contact', 'form' );
 * 
 * @author   Frank Bueltge <frank@bueltge.de>
 * @version  07/25/2012
 * 
 * 
 * -----------------------------------------------------------------------------
 * Settings
 * -----------------------------------------------------------------------------
 * 
 * text domain string from theme for translation in theme language files
 * or you use the language files inside the folder /contact-form-languages/
 * and copy this folder include the files in your theme
 */
$text_domain_string = 'contact-form';
/* Make the Contact Form Template available for translation.
 * Translations can be added to the /contact-form-languages/ directory.
 */
load_theme_textdomain( $text_domain_string, get_template_directory() . '/contact-form-languages' );

// form processing if the input field has been set
if ( isset( $_POST['submit'] ) ) {
	
	// output form values for debugging
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG )
		var_dump($_POST);
	
	$spam    = filter_var( trim( $_POST['spamcheck'] ), FILTER_SANITIZE_STRING);
	$from    = filter_var( trim( strip_tags( $_POST['from'] ) ), FILTER_SANITIZE_STRING);
	$email   = trim( $_POST['email'] );
	$message = filter_var( trim( $_POST['text'] ), FILTER_SANITIZE_STRING);
	if ( isset( $_POST['cc'] ) )
		$cc    = intval( $_POST['cc'] );
	else
		$cc    = FALSE;
	
	// check for spam input field
	if ( ! empty( $spam ) ) {
		$spam_error = __( 'Spammer? The spam protection field needs to be empty.', $text_domain_string );
		$has_error  = TRUE;
	}
	
	// check sender name, string
	if ( empty( $from ) ) {
		$from_error = __( 'Please enter your name.', $text_domain_string );
		$has_error  = TRUE;
	}
	
	// check for mail and filter the mail
	if ( empty( $email ) ) {
		$email_error = __( 'Please enter your e-mail adress.', $text_domain_string );
		$has_error   = TRUE;
	} else if ( ! preg_match(
			"/^([a-z0-9äöü]+[-_\\.a-z0-9äöü]*)@[a-z0-9äöü]+([-_\.]?[a-z0-9äöü])+\.[a-z]{2,4}$/i",
			$email
		) ) {
		$email_error = __( 'Please enter a valid e-mail adress.', $text_domain_string );
		$has_error   = TRUE;
	}
	
	if ( empty( $message ) ) {
		$message_error = __( 'Please enter a message.', $text_domain_string );
		$has_error     = TRUE;
	}
	
	if ( ! isset( $has_error ) ) {
		
		// get IP
		if ( isset( $_SERVER ) ) {
			
			if ( isset( $_SERVER["HTTP_X_FORWARDED_FOR"] ) ) {
				$ip_addr = $_SERVER["HTTP_X_FORWARDED_FOR"];
			} elseif ( isset( $_SERVER["HTTP_CLIENT_IP"] ) ) {
				$ip_addr = $_SERVER["HTTP_CLIENT_IP"];
			} else {
				$ip_addr = $_SERVER["REMOTE_ADDR"];
			}
			
		} else {
			
			if ( getenv( 'HTTP_X_FORWARDED_FOR' ) ) {
				$ip_addr = getenv( 'HTTP_X_FORWARDED_FOR' );
			} elseif ( getenv( 'HTTP_CLIENT_IP' ) ) {
				$ip_addr = getenv( 'HTTP_CLIENT_IP' );
			} else {
				$ip_addr = getenv( 'REMOTE_ADDR' );
			}
			
		}
		$ip_addr = filter_var( $ip_addr, FILTER_VALIDATE_IP );
		
		// use mail adress from WP Admin
		$email_to = get_option( 'admin_email' );
		$subject  = __( 'Contact request from', $text_domain_string ) . ' ' . $from;
		$body     = __( 'Name:', $text_domain_string ) . ' ' . $from . "\n" . 
		            __( 'E-mail:', $text_domain_string ) . ' ' . $email . "\n" . 
		            __( 'IP:', $text_domain_string ) . ' ' . $ip_addr . "\n\n" . 
		            __( 'Message:', $text_domain_string ) . ' ' . $message;
		$headers  = 'From: ' . $from . ' <' . $email . '>' . "\r\n";
		if ( $cc ) // check for cc and include sender mail to reply
			$headers .= 'Reply-To: ' . $email;
		
		// send mail via wp mail function
		wp_mail( $email_to, $subject, $body, $headers );
		// check for cc and send to sender
		if ( $cc ) {
			wp_mail(
				$email,
				__( 'CC:', $text_domain_string ) . ' ' . $subject,
				$body,
				$headers
			);
		}
		
		// successfully mail shipping
		$email_sent = TRUE;
	}

}
?>

<form action="<?php the_permalink(); ?>" method="post">
	<fieldset>
		
		<?php
		if ( isset( $spam_error ) ) echo '<p class="alert">' . $spam_error . '</p>';
		if ( isset( $email_sent ) ) echo '<p class="alert">' . __( 'Thank you for leaving a message.', $text_domain_string ) . '</p>';
		?>
		
		<div class="field">
			<label for="name">
			<?php _e( 'Name', $text_domain_string ); ?> <small class="help-inline"><?php _e( '*required', $text_domain_string ); ?></small>
			</label>
			<input type="text" id="from" name="from" placeholder="<?php _e( 'Your name', $text_domain_string ); ?>" value="<?php if ( isset( $from ) ) echo $from; ?>" />
			<?php if ( isset( $from_error ) ) echo '<p class="alert">' . $from_error . '</p>'; ?>
		</div>	
		
		<div class="field">
			<label for="email">
				<?php _e( 'E-mail address', $text_domain_string ); ?> <small class="help-inline"><?php _e( '*required', $text_domain_string ); ?></small>
			</label>
			<input type="text" placeholder="<?php _e( 'john@doe.com', $text_domain_string ); ?>" id="email" name="email" value="<?php if ( isset( $email ) ) echo $email; ?>" />
			<?php if ( isset( $email_error ) ) echo '<p class="alert">' . $email_error . '</p>'; ?>
		</div>
		
		<div class="field">
			<label for="text">
				<?php _e( 'Message', $text_domain_string ); ?> <small class="help-inline"><?php _e( '*required', $text_domain_string ); ?></small>
			</label>
			<textarea id="text" name="text" placeholder="<?php _e( 'Your message &#x0085;', $text_domain_string ); ?>"><?php if ( isset( $message ) ) echo $message; ?></textarea>
			<?php if ( isset( $message_error ) ) echo '<p class="alert">' . $message_error . '</p>'; ?>
		</div>
		
		<div class="field">
			<input type="checkbox" id="cc" name="cc" value="1" <?php if ( isset( $cc ) ) checked('1', $cc ); ?> />
			<label for="cc" style="display: inline;">
				<?php _e( 'Receive a copy of this message?', $text_domain_string ); ?>
			</label>
		</div>
		
		<div class="field" style="display: none !important;">
			<label for="text">
				<?php _e( 'Spam protection', $text_domain_string ); ?>
			</label>
			<input name="spamcheck" class="spamcheck" type="text" />
		</div>
		
		<input class="submit" type="submit" name="submit" value="<?php _e( 'Send e-mail &rarr;', $text_domain_string ); ?>" />
	
	</fieldset>
</form>
