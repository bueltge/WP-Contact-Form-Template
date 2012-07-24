<?php
/**
 * Simple contact form template for WordPress
 * 
 * Use it in a template for pages in WordPress, include it easy via get_template_part( 'contact', 'form' );
 * 
 * @author   Frank Bueltge <frank@bueltge.de>
 * @version  07/24/2012
 */

// settings
// text domain string from theme for translation in theme language files
$text_domain_string = 'default';

// form processing, if the input field was set
if ( isset( $_POST['submit'] ) ) {
	
	// for debug output the form values
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
		$spam_error = __( 'Spamer? Das Spamschutzfeld muss leer sein.', $text_domain_string );
		$has_error  = TRUE;
	}
	
	// check sender name, string
	if ( empty( $from ) ) {
		$from_error = __( 'Bitte gib einen Namen ein.', $text_domain_string );
		$has_error  = TRUE;
	}
	
	// check for mail and filter the mail
	if ( empty( $email ) ) {
		$email_error = __( 'Bitte hinterlege deine E-Mail Adresse.', $text_domain_string );
		$has_error   = TRUE;
	} else if ( ! preg_match(
			"/^([a-z0-9äöü]+[-_\\.a-z0-9äöü]*)@[a-z0-9äöü]+([-_\.]?[a-z0-9äöü])+\.[a-z]{2,4}$/i",
			$email
		) ) {
		$email_error = __( 'Bitte gib eine valide E-Mail Adresse an.', $text_domain_string );
		$has_error   = TRUE;
	}
	
	if ( empty( $message ) ) {
		$message_error = __( 'Bitte hinterlege eine Mitteilung.', $text_domain_string );
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
		
		
		$email_to = get_option( 'admin_email' );
		$subject  = __( 'Kontakt von', $text_domain_string ) . ' ' . $from;
		$body     = __( 'Name:', $text_domain_string ) . ' ' . $from . "\n" . 
		            __( 'E-Mail:', $text_domain_string ) . ' ' . $email . "\n" . 
		            __( 'IP:', $text_domain_string ) . ' ' . $ip_addr . "\n\n" . 
		            __( 'Mitteilung:', $text_domain_string ) . ' ' . $message;
		$headers  = 'From: ' . $from . ' <' . $email_to . '>' . "\r\n" . 'Reply-To: ' . $email;
		
		// send mail via wp mail function
		wp_mail( $email_to, $subject, $body, $headers );
		// check for cc and send to sender
		if ( $cc ) {
			wp_mail(
				$email,
				__( 'In CC zu', $text_domain_string ) . ' ' . $subject,
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
		if ( isset( $email_sent ) ) echo '<p class="alert">' . __( 'Vielen Dank für deine Mitteilung.', $text_domain_string ) . '</p>';
		?>
		
		<div class="field">
			<label for="name">
			<?php _e( 'Name', $text_domain_string ); ?> <small class="help-inline"><?php _e( '*Pflichtfeld', $text_domain_string ); ?></small>
			</label>
			<input type="text" id="from" name="from" placeholder="<?php _e( 'Dein Name', $text_domain_string ); ?>" value="<?php if ( isset( $from ) ) echo $from; ?>" />
			<?php if ( isset( $from_error ) ) echo '<p class="alert">' . $from_error . '</p>'; ?>
		</div>	
		
		<div class="field">
			<label for="email">
				<?php _e( 'E-Mail Adresse', $text_domain_string ); ?> <small class="help-inline"><?php _e( '*Pflichtfeld', $text_domain_string ); ?></small>
			</label>
			<input type="text" placeholder="<?php _e( 'john@doe.com', $text_domain_string ); ?>" id="email" name="email" value="<?php if ( isset( $email ) ) echo $email; ?>" />
			<?php if ( isset( $email_error ) ) echo '<p class="alert">' . $email_error . '</p>'; ?>
		</div>
		
		<div class="field">
			<label for="text">
				<?php _e( 'Mitteilung', $text_domain_string ); ?> <small class="help-inline"><?php _e( '*Pflichtfeld', $text_domain_string ); ?></small>
			</label>
			<textarea id="text" name="text" placeholder="<?php _e( 'Deine Mitteilung &#x0085;', $text_domain_string ); ?>"><?php if ( isset( $message ) ) echo $message; ?></textarea>
			<?php if ( isset( $message_error ) ) echo '<p class="alert">' . $message_error . '</p>'; ?>
		</div>
		
		<div class="field">
			<input type="checkbox" id="cc" name="cc" value="1" <?php if ( isset( $cc ) ) checked('1', $cc ); ?> />
			<label for="cc" style="display: inline;">
				<?php _e( 'Kopie erhalten?', $text_domain_string ); ?>
			</label>
		</div>
		
		<div class="field" style="display: none !important;">
			<label for="text">
				<?php _e( 'Spamschutzfeld', $text_domain_string ); ?>
			</label>
			<input name="spamcheck" class="spamcheck" type="text" />
		</div>
		
		<input class="submit" type="submit" name="submit" value="<?php _e( 'E-Mail versenden &rarr;', $text_domain_string ); ?>" />
	
	</fieldset>
</form>
