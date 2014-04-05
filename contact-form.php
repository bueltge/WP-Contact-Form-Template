<?php
/**
 * Simple contact form template for WordPress
 * 
 * Use it in a template for pages in WordPress, 
 *   include it easily via get_template_part( 'contact', 'form' );
 * See the action and filter hooks for include or change output for your requirements
 * 
 * @author   Frank Bueltge <frank@bueltge.de>
 * @version  04/04/2014
 * 
 * 
 * -----------------------------------------------------------------------------
 * Settings
 * -----------------------------------------------------------------------------
 * 
 * Text domain string from theme for translation in theme language files
 *   or you use the language files inside the folder /contact-form-languages/
 *   and copy this folder include the files in your theme
 */
$text_domain_string = 'contact-form';
/* Make the Contact Form Template available for translation.
 * Translations can be added to the /contact-form-languages/ directory.
 */
load_theme_textdomain( $text_domain_string, get_stylesheet_directory() . '/contact-form-languages' );

// form processing if the input field has been set
if ( isset( $_POST['submit'] ) && wp_verify_nonce( $_POST['contact_form_nonce'], 'form_submit' ) ) {
	
	// define markup for error messages
	$error_tag = apply_filters( 'wp-contact-form-template_error_tag', 'p' );
	
	// output form values for debugging
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG )
		var_dump( $_POST );
	
	$spam    = filter_var( trim( $_POST['spamcheck'] ), FILTER_SANITIZE_STRING);
	$from    = filter_var( trim( strip_tags( $_POST['from'] ) ), FILTER_SANITIZE_STRING);
	$email   = trim( $_POST['email'] );
	$subject = filter_var( trim( $_POST['subject'] ), FILTER_SANITIZE_STRING);
	//$message = filter_var( trim( $_POST['text'] ), FILTER_SANITIZE_STRING);
	// Allow html in message
	$message = wp_kses_post( $_POST['text'] );
	
	if ( isset( $_POST['cc'] ) )
		$cc = intval( $_POST['cc'] );
	else
		$cc = FALSE;
	
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
	// alternative to filter_var a regex via preg_match( $filter, $email )
	// $filter = "/^([a-z0-9äöü]+[-_\\.a-z0-9äöü]*)@[a-z0-9äöü]+([-_\.]?[a-z0-9äöü])+\.[a-z]{2,4}$/i"
	// $filter = "/[a-z0-9!#$%&'*+\/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+\/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?/i"
	if ( empty( $email ) ) {
		$email_error = __( 'Please enter your e-mail adress.', $text_domain_string );
		$has_error   = TRUE;
	} else if ( ! filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
		$email_error = __( 'Please enter a valid e-mail address.', $text_domain_string );
		$has_error   = TRUE;
	}
	
	if ( empty( $subject ) ) {
		$subject_error = __( 'Please enter a subject.', $text_domain_string );
		$has_error     = TRUE;
	}
	
	if ( empty( $message ) ) {
		$message_error = __( 'Please enter a message.', $text_domain_string );
		$has_error     = TRUE;
	}
	
	if ( ! isset( $has_error ) ) {
		
		// get IP
		if ( isset( $_SERVER ) ) {
			
			if ( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
				$ip_addr = $_SERVER['HTTP_X_FORWARDED_FOR'];
			} elseif ( isset( $_SERVER['HTTP_CLIENT_IP'] ) ) {
				$ip_addr = $_SERVER['HTTP_CLIENT_IP'];
			} else {
				$ip_addr = $_SERVER['REMOTE_ADDR'];
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
		
		// use mail address from WP Admin
		$email_to = get_option( 'admin_email' );
		$subject  = $subject . ' ' . __( 'via Contact request from', $text_domain_string ) . ' ' . $from;
		$body     = __( 'Message:', $text_domain_string ) . ' ' . $message . "\n\n" .
		            __( 'Name:', $text_domain_string ) . ' ' . $from . "\n" . 
		            __( 'E-mail:', $text_domain_string ) . ' ' . $email . "\n" . 
		            __( 'IP:', $text_domain_string ) . ' ' . $ip_addr . "\n";
		$headers  = 'From: ' . $from . ' <' . $email . '>' . "\r\n";
		if ( $cc ) // check for cc and include sender mail to reply
			$headers .= 'Reply-To: ' . $email;
		
		// Filter hooks for enhance the mail; sorry for long strings ;)
		$email_to = apply_filters( 'wp-contact-form-template-mail_email_to', $email_to );
		$subject  = apply_filters( 'wp-contact-form-template-mail_subject', $subject );
		$body     = apply_filters( 'wp-contact-form-template-mail_body', $body );
		
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

do_action( 'wp-contact-form-template_form_before' ); ?>

<form action="<?php the_permalink(); ?>" method="post">
	<fieldset>
		
		<?php do_action( 'wp-contact-form-template_form_top' );
		
		if ( isset( $spam_error ) )
			echo apply_filters( 'wp-contact-form-template_spam_message', '<' . $error_tag . ' class="alert">' . $spam_error . '</' . $error_tag . '>' );
		if ( isset( $email_sent ) )
			echo apply_filters( 'wp-contact-form-template_thanks_message', '<' . $error_tag . ' class="alert">' . __( 'Thank you for leaving a message.', $text_domain_string ) . '</' . $error_tag . '>' );
		
		do_action( 'wp-contact-form-template_form_before_fields' ); ?>
		
		<div class="field">
			<label for="from">
			<?php _e( 'Name', $text_domain_string ); ?> <small class="help-inline"><?php _e( '*required', $text_domain_string ); ?></small>
			</label>
			<input type="text" id="from" name="from" placeholder="<?php esc_attr_e( 'Your name', $text_domain_string ); ?>" value="<?php if ( isset( $from ) && ! isset( $email_sent ) ) echo esc_attr( $from ); ?>" />
			<?php
			if ( isset( $from_error ) )
				echo '<' . $error_tag . ' class="alert">' . $from_error . '</' . $error_tag . '>';
			?>
		</div>
		
		<div class="field">
			<label for="email">
				<?php _e( 'E-mail address', $text_domain_string ); ?> <small class="help-inline"><?php _e( '*required', $text_domain_string ); ?></small>
			</label>
			<input type="text" placeholder="<?php esc_attr_e( 'john@doe.com', $text_domain_string ); ?>" id="email" name="email" value="<?php if ( isset( $email ) && ! isset( $email_sent ) ) echo esc_attr( $email ); ?>" />
			<?php
			if ( isset( $email_error ) )
				echo '<' . $error_tag . ' class="alert">' . $email_error . '</' . $error_tag . '>';
			?>
		</div>
		
		<div class="field">
			<label for="subject">
				<?php _e( 'Subject', $text_domain_string ); ?> <small class="help-inline"><?php _e( '*required', $text_domain_string ); ?></small>
			</label>
			<input type="text" placeholder="<?php _e( 'Question', $text_domain_string ); ?>" id="subject" name="subject" value="<?php if ( isset( $subject ) && ! isset( $email_sent ) ) echo esc_attr( $subject ); ?>" />
			<?php
			if ( isset( $subject_error ) )
				echo '<' . $error_tag . ' class="alert">' . $subject_error . '</' . $error_tag . '>';
			?>
		</div>
		
		<?php do_action( 'wp-contact-form-template_form_after_fields' ); ?>
		
		<div class="field">
			<label for="text">
				<?php _e( 'Message', $text_domain_string ); ?> <small class="help-inline"><?php _e( '*required', $text_domain_string ); ?></small>
			</label>
			<textarea id="text" name="text" placeholder="<?php esc_attr_e( 'Your message &#x0085;', $text_domain_string ); ?>"><?php if ( isset( $message ) && ! isset( $email_sent ) ) echo esc_textarea( $message ); ?></textarea>
			<?php
			if ( isset( $message_error ) )
				echo '<' . $error_tag . ' class="alert">' . $message_error . '</' . $error_tag . '>';
			?>
		</div>
		
		<div class="field">
			<input type="checkbox" id="cc" name="cc" value="1" <?php if ( isset( $cc ) ) checked( '1', intval($cc) ); ?> />
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
		
		<p class="form-submit">
			<input class="submit" type="submit" name="submit" value="<?php esc_attr_e( 'Send e-mail &rarr;', $text_domain_string ); ?>" />
		</p>
		<?php wp_nonce_field( 'form_submit', 'contact_form_nonce' ) ?>
		<?php do_action( 'wp-contact-form-template_form' ); ?>
		
	</fieldset>
</form>

<?php do_action( 'wp-contact-form-template_form_after' ); ?>
