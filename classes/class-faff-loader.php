<?php
/**
 * Loader.
 *
 * @package bsf-freemius-affiliate-fluent-form
 * @since 1.0.0
 */

if ( ! class_exists( 'FAFF_Loader' ) ) :

	/**
	 * FAFF_Loader
	 *
	 * @since 1.0.0
	 */
	class FAFF_Loader {

		/**
		 * Instance
		 *
		 * @access private
		 * @var object Class Instance.
		 * @since 1.0.0
		 */
		private static $instance;

		/**
		 * Initiator
		 *
		 * @since 1.0.0
		 * @return object initialized object of class.
		 */
		public static function get_instance() {
			if ( ! isset( self::$instance ) ) {
				self::$instance = new self;
			}
			return self::$instance;
		}

		/**
		 * Constructor
		 *
		 * @since 1.0.0
		 */
		public function __construct() {

			$this->define_constants();

			add_action( 'plugins_loaded', array( $this, 'load_plugin' ) );

		}

		/**
		 * Define constants
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function define_constants() {

			define( 'FAFF_BASE', plugin_basename( FAFF_FILE ) );
			define( 'FAFF_DIR', plugin_dir_path( FAFF_FILE ) );
			define( 'FAFF_URL', plugins_url( '/', FAFF_FILE ) );
			define( 'FAFF_VER', '1.0.0' );
		}

		/**
		 * Load plugin
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function load_plugin() {

			$is_fform_callable = ( defined( 'FLUENTFORM' ) && function_exists( 'wpFluentForm' ) ) ? true : false;

			if ( ( ! did_action( 'fluentform_loaded' ) ) || ( ! $is_fform_callable ) ) {
				add_action( 'admin_notices', [ $this, 'fform_not_installed_activated' ] );
				add_action( 'network_admin_notices', [ $this, 'fform_not_installed_activated' ] );
				return;
			}
			
			$this->include_files();

			add_action( 'fluentform_submission_inserted', array( $this, 'your_custom_after_submission_function' ), 20, 3 );
			
		}

		/**
		 * Prints the admin notics when Fluent form is not installed or activated.
		 * @since 1.0.0
		 */
		public function fform_not_installed_activated() {

			$screen = get_current_screen();
			if ( isset( $screen->parent_file ) && 'plugins.php' === $screen->parent_file && 'update' === $screen->id ) {
				return;
			}

			if ( ! did_action( 'fluentform_loaded' ) ) {
				// Check user capability.
				if ( ! ( current_user_can( 'activate_plugins' ) && current_user_can( 'install_plugins' ) ) ) {
					return;
				}

				/* TO DO */
				$class = 'notice notice-error';
				/* translators: %s: html tags */
				$message = sprintf( __( 'The %1$sFreemius Affiliate Form for Fluent Form%2$s plugin requires %1$sFluent Forms%2$s plugin installed & activated.', 'bsf-freemius-affiliate-fluent-form' ), '<strong>', '</strong>' );

				$plugin = 'fluentform/fluentform.php';

				if ( file_exists( WP_PLUGIN_DIR . '/fluentform/fluentform.php' ) ) {

					$action_url   = wp_nonce_url( 'plugins.php?action=activate&amp;plugin=' . $plugin . '&amp;plugin_status=all&amp;paged=1&amp;s', 'activate-plugin_' . $plugin );
					$button_label = __( 'Activate Fluent Forms', 'bsf-freemius-affiliate-fluent-form' );

				} else {

					$action_url   = wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=fluentform' ), 'install-plugin_fluentform' );
					$button_label = __( 'Install Fluent Forms', 'bsf-freemius-affiliate-fluent-form' );
				}

				$button = '<p><a href="' . esc_url( $action_url ) . '" class="button-primary">' . esc_html( $button_label ) . '</a></p><p></p>';

				printf( '<div class="%1$s"><p>%2$s</p>%3$s</div>', esc_attr( $class ), wp_kses_post( $message ), wp_kses_post( $button ) );
			}
		}

		/**
		 * Include files
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public function include_files() {
			
			require_once FAFF_DIR . 'freemius/FreemiusBase.php';
			require_once FAFF_DIR . 'freemius/Freemius.php';

		}

		public function your_custom_after_submission_function( $entryId, $formData, $form ) {
			// DO your stuffs here.
			if( "3" !== $form->id ) {
				return;
			}

			define( 'FS__API_SCOPE', 'developer' );
			define( 'FS__API_DEV_ID', 12259 );
			define( 'FS__API_PUBLIC_KEY', 'pk_438d1129514fc40bd5863610e84c8' );
			define( 'FS__API_SECRET_KEY', 'sk_qfY?H5~J=~rqX^t^$CrVrEjR&$3yh' );

			$api = new Freemius_Api_Custom( FS__API_SCOPE, FS__API_DEV_ID, FS__API_PUBLIC_KEY, FS__API_SECRET_KEY );

			// You can get the product's affiliate program terms ID from the AFFILIATION section, it's stated right in the 1st tab.
			$api->Api("/plugins/5371/aff/608/affiliates.json", 'POST', array(
				'name'                         => $formData['name_field'],
				'email'                        => $formData['email'],
				'paypal_email'                 => $formData['email_1'],
				// Should not include an HTTP/S protocol.
				'domain'                       => $formData['url'],
				// An optional param to include additional domains/sub-domains where the applicant will promote your product.
				// 'additional_domains'           => array('affiliate-2nd-site.com', 'affiliate-3rd-site.com'),
				// Optional comma-separated combination of the following: 'social_media' and 'mobile_apps'.
				// This is useful if by default you don't allow promoting through mobile or social, to manually (& optionally) create custom terms for the applicant after approval.
				// 'promotional_methods'          => 'social_media,mobile_apps',
				// An optional free text where an applicant can provide some stats data about their reach.
				// 'stats_description'            => '100k monthly PVs. 1,000 Instagram followers. I manage a FB group of 20,000 members.',
				// An optional free text when an applicant can explain how they are planning to promote your product.
				'promotion_method_description' => $formData['description'],
				// An option applicant state. Defaults to 'active'. One of the following states: 'active', 'pending', 'rejected', 'suspended', 'blocked'.
				'state'                        => 'pending',
			));

		}

	}

	/**
	 * Kicking this off by calling 'get_instance()' method
	 */
	FAFF_Loader::get_instance();

endif;

