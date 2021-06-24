<?php

/**
 * Forminator Extra Fields
 *
 * @package           ForminatorExtraFields
 * @author:           IQnection
 * @copyright         2021 IQnection
 * @license:          GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       Forminator Extra Fields
 * Description:       Provides extra/extended fields to Forminator.
 * Version:           1.0.0
 * Requires at least: 5.3
 * Requires PHP:      7.1
 * Author:            IQnection
 * Author URI:        https://www.iqnection.com
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       forminator-extra-fields
 */

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

require_once(__DIR__.'/updates/check-requirements.php');
require_once (__DIR__.'/updates/update.php');

define( 'FORMINATOR_EXTRA_FIELDS_VERSION', '1.0.0' );

if (!defined('FORMINATOR_EXTRA_ASSETS_DIR')) {
	define('FORMINATOR_EXTRA_ASSETS_DIR', __DIR__.'/assets');
}

if (!defined('FORMINATOR_EXTRA_FIELDS_DIR')) {
	define('FORMINATOR_EXTRA_FIELDS_DIR', __DIR__.'/lib/fields');
}

if ( ! function_exists( 'forminator_extra_plugin_url' ) ) {
	/**
	 * Return plugin URL
	 *
	 * @since 1.0
	 * @return string
	 */
	function forminator_extra_plugin_url() {
		return trailingslashit( plugin_dir_url( __FILE__ ) );
	}
}


if (!function_exists('forminator_extra_has_forminator_plugin')) {
	/**
	 * Makes sure the Forminator plugin is installed and activated
	 * This plugin will not work without Forminator
	 */
	function forminator_extra_has_forminator_plugin() {
		if ( is_admin() && current_user_can( 'activate_plugins' ) &&  !is_plugin_active( 'forminator/forminator.php' ) ) {
			add_action( 'admin_notices', 'forminator_extra_missing_forminator_notice' );

			deactivate_plugins( plugin_basename( __FILE__ ) );

			if ( isset( $_GET['activate'] ) ) {
				unset( $_GET['activate'] );
			}
		}
	}
}

if (!function_exists('forminator_extra_missing_forminator_notice')) {
	function forminator_extra_missing_forminator_notice(){
		?><div class="error"><p>Sorry, but the Forminator Extra Fields requires Forminator to be installed and activated.</p></div><?php
	}
}


/**
 * Filter function to add the extra fields to the Frominator array of field objects
 *
 * @param $fields
 * @return array
 */
function forminator_extra_fields($fields) {
	$fields = array_merge($fields, get_forminator_extra_fields());
	return $fields;
}
add_filter( 'forminator_fields', 'forminator_extra_fields' );


/**
 * Provides the directory path to the extra fields class files
 *
 * @return string
 */
function forminator_extra_fields_directory() {
	return FORMINATOR_EXTRA_FIELDS_DIR;
}

/**
 * Collects all extra fields and returns an instance of each in an array
 *
 * @return array
 */
function get_forminator_extra_fields() {
	$objects = [];
	foreach(new DirectoryIterator(FORMINATOR_EXTRA_FIELDS_DIR) as $item) {
		if ( (!$item->isDot()) && ($item->isFile()) && ($item->getExtension() == 'php') ) {
			include $item->getPathname();

			$className = str_replace('-','_', $item->getBasename('.php'));
			$className = ucwords($className);
			$className = sprintf('Forminator_Extra_%s', $className);
			$object = new $className();
			$objects[] = $object;
		}
	}
	return $objects;
}