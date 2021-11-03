<?php
/**
 * Plugin Name: Freemius Affiliate Form for Fluent Form
 * Plugin URI: https://www.brainstormforce.com
 * Description: Integrate Freemius Affiliate Form API with Fluent form submission. This offers creating an affiliate entry in the Freemius Affiliate account when the form is submiiited from our website.
 * Version: 1.0.0
 * Author: Brainstorm Force
 * Author URI: https://www.brainstormforce.com
 * Text Domain: bsf-freemius-affiliate-fluent-form
 *
 * @package bsf-freemius-affiliate-fluent-form
 */

defined( 'ABSPATH' ) or die;

/**
 * Set constants
 */
define( 'FAFF_FILE', __FILE__ );

require_once 'classes/class-faff-loader.php';
