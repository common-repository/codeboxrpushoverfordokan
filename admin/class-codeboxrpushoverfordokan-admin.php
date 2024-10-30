<?php

/**
 * Plugin class. This class should ideally be used to work with the
 * administrative side of the WordPress site.

 * @author  wpboxr <info@wpboxr.com>
 */


class CodeboxrpushoverfordokanAdmin {


	protected static $instance           = null;
	protected $plugin_screen_hook_suffix = null;

	/**
	 * Initialize the plugin by loading admin scripts & styles and adding a
	 * settings page and menu.
	 *
	 * @since     1.0.0
	 */
	private function __construct() {


		$plugin = Codeboxrpushoverfordokan::get_instance();
		$this->plugin_slug = $plugin->get_plugin_slug();

		//add dokan setting
		add_filter('dokan_settings_sections', array( $this,'dokan_psuhover_settings_sections'));
		add_filter('dokan_settings_fields', array( $this,'dokan_psuhover_settings_fields'));


	}

	public  function dokan_psuhover_settings_fields($fields){
		$settings_fields = array(
			'dokan_pushover' => array(

				'codeboxrpushoverfordokan_site_api' => array(
					'name'        => 'codeboxrpushoverfordokan_site_api',
					'label'       => __( 'Site API Token', 'codeboxrpushoverfordokan' ),
					'description' => __( '', 'codeboxrpushoverfordokan' ),
					'type'        => 'text',
					'default'     => ''
				),

				'codeboxrpushoverfordokan_debug' => array(
					'name'        => 'codeboxrpushoverfordokan_debug',
					'label'       => __( 'Debug', 'codeboxrpushoverfordokan' ),
					'description' => __( 'Enable debug logging', 'codeboxrpushoverfordokan' ),
					'type'        => 'checkbox',
					'default'     => 'no'
				)
			)
		);

		$fields = array_merge($fields, $settings_fields);

		return $fields;
	}

	public function dokan_psuhover_settings_sections($sections){
		$sections = array_merge($sections,
								array(
										array(
											'id'    => 'dokan_pushover',
											'title' => __( 'Pushover Setting', 'codeboxrpushoverfordokan' )
										)
								)
			);

		return $sections;
	}



	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

}
