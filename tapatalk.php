<?php
/*
Plugin Name: Tapatalk Blog Api
Description: Tapatalk Blog Api.
Version: 1.0.0
Author: Tapatalk
Author URI: http://www.tapatalk.com/
Plugin URI: http://www.tapatalk.com/
License: MIT License
License URI: http://opensource.org/licenses/MIT
*/

class Tapatalk {
	
	public $version    = '1.0.0';  //plugin's version
	public $method; //request method;
	
	/**
	 * Set some smart defaults to class variables. Allow some of them to be
	 * filtered to allow for early overriding.
	 *
	 * @since tapatalk
	 * @access private
	 * @uses plugin_dir_path() To generate Tapatalk blog api plugin path
	 * @uses plugin_dir_url() To generate Tapatalk blog api plugin url
	 */
	private function setup_globals() 
	{
		/** Paths *************************************************************/

		// Setup some base path and URL information
		$this->file       = __FILE__;
		$this->basename   = plugin_basename( $this->file );
		$this->plugin_dir = plugin_dir_path( $this->file );
		$this->wp_dir     = dirname(dirname(dirname(dirname($this->file))));

		$this->plugin_url = apply_filters( 'bbp_plugin_dir_url',   plugin_dir_url ( $this->file ) );

		// Includes
		$this->includes_dir = trailingslashit( $this->plugin_dir . 'includes'  );
		$this->includes_url = trailingslashit( $this->plugin_url . 'includes'  );
		$this->method = isset($_REQUEST['tapatalk']) ? trim($_REQUEST['tapatalk']) : '';
	}
	
	/**
	 * include plugin's file
	 */
	private function includes()
	{
		/** Core **************************************************************/
		require( $this->includes_dir . 'common.php' );
		require( $this->includes_dir . 'functions.php' );
		
	}
	/**
	 * Setup the default hooks and actions
	 *
	 * @since tapatalk
	 * @access private
	 * @uses add_action() To add various actions
	 */
	public function steup_actions()
	{
		add_action('wp',array( $this, 'run' ));
	}
	
	/**
	 * init the plugins
	 */
	private function init()
	{
		$this->setup_globals();
		$this->includes();	
		define('WP_ROOT',$this->wp_dir);
	}
	
	/**
	 * output json str 
	 * @since tapatalk
	 * @access private
	 */
	public function run()
	{
		if(!isset($_REQUEST['tapatalk']))
		{
			return ;
		}
		header('Content-type: application/json; charset=UTF-8');
		$this->init();
		
		if (function_exists('ttwp_'.$this->method))
		{
		    call_user_func('ttwp_'.$this->method);
		}
		else
		{
		    tt_json_error(-32601);
		}
		exit();
	}
}

/*execute plugin*/
$tapatalk = new Tapatalk();
$tapatalk->steup_actions();


