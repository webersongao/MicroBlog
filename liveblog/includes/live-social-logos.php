<?php
/**
 * Get social logo.
 *
 * @see https://github.com/Automattic/social-logos
 *
 * @param string $social_logo The Name of the brand.
 * @return string
 */
function mlb_get_social_logo( $social_logo, $args = array() ) {
	$args = wp_parse_args(
		$args,
		array(
			'height' => '20',
			'width'  => '20',
			'class'  => 'mlb-liveblog-social-logo',
		)
	);

	$svg = '';
	switch ( $social_logo ) {
		case 'facebook':
			$svg = '<svg class="' . esc_attr( $args['class'] ) . ' mlb-liveblog-social-logo-facebook" fill="currentColor" height="' . esc_attr( $args['height'] ) . '" width="' . esc_attr( $args['width'] ) . '" xmlns="http:// www.w3.org/2000/svg" viewBox="0 0 24 24"><g><path d="M12 2C6.5 2 2 6.5 2 12c0 5 3.7 9.1 8.4 9.9v-7H7.9V12h2.5V9.8c0-2.5 1.5-3.9 3.8-3.9 1.1 0 2.2.2 2.2.2v2.5h-1.3c-1.2 0-1.6.8-1.6 1.6V12h2.8l-.4 2.9h-2.3v7C18.3 21.1 22 17 22 12c0-5.5-4.5-10-10-10z"/></g></svg>';
			break;
		case 'linkedin':
			$svg = '<svg class="' . esc_attr( $args['class'] ) . ' mlb-liveblog-social-logo-linkedin" fill="currentColor" height="' . esc_attr( $args['height'] ) . '" width="' . esc_attr( $args['width'] ) . '" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><g><path d="M19.7 3H4.3A1.3 1.3 0 003 4.3v15.4A1.3 1.3 0 004.3 21h15.4a1.3 1.3 0 001.3-1.3V4.3A1.3 1.3 0 0019.7 3zM8.339 18.338H5.667v-8.59h2.672v8.59zM7.004 8.574a1.548 1.548 0 11-.002-3.096 1.548 1.548 0 01.002 3.096zm11.335 9.764H15.67v-4.177c0-.996-.017-2.278-1.387-2.278-1.389 0-1.601 1.086-1.601 2.206v4.249h-2.667v-8.59h2.559v1.174h.037c.356-.675 1.227-1.387 2.526-1.387 2.703 0 3.203 1.779 3.203 4.092v4.711z"/></g></svg>';
			break;
		case 'mail':
			$svg = '<svg class="' . esc_attr( $args['class'] ) . ' mlb-liveblog-social-logo-mail" fill="currentColor" height="' . esc_attr( $args['height'] ) . '" width="' . esc_attr( $args['width'] ) . '" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><g><path d="M20 4H4a2 2 0 00-2 2v12a2 2 0 002 2h16a2 2 0 002-2V6a2 2 0 00-2-2zm0 4.236l-8 4.882-8-4.882V6h16v2.236z"/></g></svg>';
			break;
		case 'twitter':
			$svg = '<svg class="' . esc_attr( $args['class'] ) . ' mlb-liveblog-social-logo-twitter" fill="currentColor" height="' . esc_attr( $args['height'] ) . '" width="' . esc_attr( $args['width'] ) . '" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><g><path d="M19 3H5a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2V5a2 2 0 00-2-2zm-2.534 6.71c.004.099.007.198.007.298 0 3.045-2.318 6.556-6.556 6.556a6.52 6.52 0 01-3.532-1.035 4.626 4.626 0 003.412-.954 2.307 2.307 0 01-2.152-1.6 2.295 2.295 0 001.04-.04 2.306 2.306 0 01-1.848-2.259v-.029c.311.173.666.276 1.044.288a2.303 2.303 0 01-.713-3.076 6.54 6.54 0 004.749 2.407 2.305 2.305 0 013.926-2.101 4.602 4.602 0 001.463-.559 2.31 2.31 0 01-1.013 1.275c.466-.056.91-.18 1.323-.363-.31.461-.7.867-1.15 1.192z"/></g></svg>';
			break;
		case 'twitter-alt':
			$svg = '<svg class="' . esc_attr( $args['class'] ) . ' mlb-liveblog-social-logo-twitter-alt" fill="currentColor" height="' . esc_attr( $args['height'] ) . '" width="' . esc_attr( $args['width'] ) . '" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><g><path d="M22.23 5.924a8.212 8.212 0 01-2.357.646 4.115 4.115 0 001.804-2.27 8.221 8.221 0 01-2.606.996 4.103 4.103 0 00-6.991 3.742 11.647 11.647 0 01-8.457-4.287 4.087 4.087 0 00-.556 2.063 4.1 4.1 0 001.825 3.415 4.09 4.09 0 01-1.859-.513v.052a4.104 4.104 0 003.292 4.023 4.099 4.099 0 01-1.853.07 4.11 4.11 0 003.833 2.85 8.236 8.236 0 01-5.096 1.756 8.33 8.33 0 01-.979-.057 11.617 11.617 0 006.29 1.843c7.547 0 11.675-6.252 11.675-11.675 0-.178-.004-.355-.012-.531a8.298 8.298 0 002.047-2.123z"/></g></svg>';
			break;
        case 'x':
            $svg = '<svg class="' . esc_attr( $args['class'] ) . ' mlb-liveblog-social-logo-twitter" fill="currentColor" height="' . esc_attr( $args['height'] ) . '" width="' . esc_attr( $args['width'] ) . '" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><g><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"></path></g></svg>';
            break;
		case 'whatsapp':
			$svg = '<svg class="' . esc_attr( $args['class'] ) . ' mlb-liveblog-social-logo-whatsapp" fill="currentColor" height="' . esc_attr( $args['height'] ) . '" width="' . esc_attr( $args['width'] ) . '" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><g><path d="M2.048 22l1.406-5.136a9.894 9.894 0 01-1.323-4.955C2.133 6.446 6.579 2 12.042 2a9.848 9.848 0 017.011 2.906 9.85 9.85 0 012.9 7.011c-.002 5.464-4.448 9.91-9.91 9.91h-.004a9.913 9.913 0 01-4.736-1.206L2.048 22zm5.497-3.172l.301.179a8.214 8.214 0 004.193 1.148h.003c4.54 0 8.235-3.695 8.237-8.237a8.189 8.189 0 00-2.41-5.828 8.182 8.182 0 00-5.824-2.416c-4.544 0-8.239 3.695-8.241 8.237a8.222 8.222 0 001.259 4.384l.196.312-.832 3.04 3.118-.819zm9.49-4.554c-.062-.103-.227-.165-.475-.289-.248-.124-1.465-.723-1.692-.806-.227-.083-.392-.124-.557.124-.165.248-.64.806-.784.971-.144.165-.289.186-.536.062-.248-.124-1.046-.385-1.991-1.229-.736-.657-1.233-1.468-1.378-1.715-.144-.248-.015-.382.109-.505.111-.111.248-.289.371-.434.124-.145.165-.248.248-.413.083-.165.041-.31-.021-.434s-.557-1.343-.763-1.839c-.202-.483-.407-.417-.559-.425-.144-.007-.31-.009-.475-.009a.91.91 0 00-.66.31c-.226.248-.866.847-.866 2.066 0 1.219.887 2.396 1.011 2.562.124.165 1.746 2.666 4.23 3.739.591.255 1.052.408 1.412.522.593.189 1.133.162 1.56.098.476-.071 1.465-.599 1.671-1.177.206-.58.206-1.075.145-1.179z"/></g></svg>';
			break;
		default:
			$svg = 'Need to specify a social logo';
			break;
	}

	return $svg;
}
