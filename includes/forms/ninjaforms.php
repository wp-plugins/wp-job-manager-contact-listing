<?php
/**
 * Ninja Forms support.
 *
 * @since WP Job Manager - Contact Listing 1.0.0
 *
 * @return void
 */
class Astoundify_Job_Manager_Contact_Listing_Form_NinjaForms extends Astoundify_Job_Manager_Contact_Listing_Form {

	/**
	 * Load the base form class.
	 *
	 * @since WP Job Manager - Contact Listing 1.0.0
	 *
	 * @return void
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Hook into processing and attach our own things.
	 *
	 * @since WP Job Manager - Contact Listing 1.0.0
	 *
	 * @return void
	 */
	public function setup_actions() {
		add_action( 'job_manager_contact_listing_form_ninjaforms', array( $this, 'output_form' ) );
		add_action( 'ninja_forms_email_admin', array( $this, 'notification_email' ), 10, 3 );
	}

	/**
	 * Output the shortcode.
	 *
	 * @since WP Job Manager - Contact Listing 1.0.0
	 *
	 * @return void
	 */
	public function output_form($form) {
		$args = apply_filters( 'job_manager_contact_listing_ninjaforms_apply_form_args', '' );

		echo do_shortcode( sprintf( '[ninja_forms_display_form id="%s" %s]', $form, $args ) );
	}

	/**
	 * Set the notification email when sending an email.
	 *
	 * @since WP Job Manager - Contact Listing 1.0.0
	 *
	 * @return string The email to notify.
	 */
	public function notification_email($one = null, $two = null, $three = null) {
		global $ninja_forms_processing;

		$form_id = $ninja_forms_processing->get_form_ID();

		$object = $field_id = null;
		$fields = $ninja_forms_processing;

		foreach ( $fields->data[ 'field_data' ] as $field ) {
			if ( 'Listing ID' == $field[ 'data' ][ 'label' ] ) {
				$field_id = $field[ 'id' ];

				break;
			}
		}

		$object = get_post( $ninja_forms_processing->get_field_value( $field_id ) );

		if ( ! array_search( $form_id, $this->forms[ $object->post_type ] ) ) {
			return;
		}

		$this->_proper_ninja_email = $object->_application ? $object->_application : $object->_candidate_email;

		add_filter( 'wp_mail', array( $this, 'proper_email' ) );
	}

	/**
	 * @missing
	 *
	 * @since WP Job Manager - Contact Listing 1.0.0
	 *
	 * @return $mail
	 */
	public function proper_email( $mail ) {
		if ( filter_var( $this->_proper_ninja_email, FILTER_VALIDATE_EMAIL ) ) {
			$mail[ 'to' ] = $this->_proper_ninja_email;
		}

		remove_filter( 'wp_mail', array( $this, 'proper_email' ) );

		return $mail;
	}

	/**
	 * Get all forms and return in a simple array for output.
	 *
	 * @since WP Job Manager - Contact Listing 1.0.0
	 *
	 * @return void
	 */
	public function get_forms() {
		$forms  = array( 0 => __( 'Please select a form', 'wp-job-manager-contact-listing' ) );

		$_forms = ninja_forms_get_all_forms();

		if ( ! empty( $_forms ) ) {

			foreach ( $_forms as $_form ) {
				$forms[ $_form['id'] ] = $_form['data']['form_title'];
			}
		}

		return $forms;
	}

}