<?php
/*
Plugin Name: Tapatalk for WordPress
Description: Tapatalk for WordPress Plugin enables Tapatalk Community Reader to integrate WordPress Blogs and Forums into a single mobile app.
Version: 1.1.2
Author: Tapatalk
Author URI: http://www.tapatalk.com/
Plugin URI: http://www.tapatalk.com/activate_tapatalk.php
License: GNU General Public License v2
License URI: http://www.gnu.org/licenses/license-list.html#GPLCompatibleLicenses
*/

class Tapatalk {

    public $version    = '1.1.2';  //plugin's version
    public $method; //request method;
    public $file;
    public $basename;
    public $plugin_dir;
    public $wp_dir;
    public $includes_dir;

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

        // Includes
        $this->includes_dir = trailingslashit( $this->plugin_dir . 'includes' );
        $this->method       = isset($_REQUEST['tapatalk']) ? trim($_REQUEST['tapatalk']) : '';
    }

    /**
     * include plugin's file
     */
    private function includes()
    {
        /** Core **************************************************************/
        require_once( $this->includes_dir . 'common.php' );
        require_once( $this->includes_dir . 'functions.php' );
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
        $this->setup_globals();
        require_once $this->plugin_dir.'options/tapatalk_option.php';
        add_action('wp', array( $this, 'run' ));
        remove_action('bbp_head', 'tapatalkdetect');
        remove_action('bbp_footer','tapatalk_footer');
        add_action('wp_head', array($this, 'tapatalkdetect'));
        add_action('wp_footer', array($this,'tapatalkfooter'));
    }

    /**
     * init the plugins
     */
    private function init()
    {
        $this->setup_globals();
        $this->includes();
    }

    /**
     * output json str
     * @since tapatalk
     * @access private
     */
    public function run()
    {
        $this->init();
        $useragent = $_SERVER["HTTP_USER_AGENT"];
        if(!empty($_GET['welcome']) && preg_match('/iPhone|iPod|iPad|Silk|Android|IEMobile|Windows Phone|Windows RT.*?ARM/i', $useragent))
        {
            define('IN_MOBIQUO',1);
            require $this->plugin_dir . 'smartbanner/app.php';
            exit;
        }
        if(!isset($_REQUEST['tapatalk']))
        {
            return ;
        }
        
        header('Content-type: application/json; charset=UTF-8');
             

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

    public function tapatalkdetect()
    {
        $app_head_include = '';
        if(file_exists($this->plugin_dir . 'smartbanner/head.inc.php' ))
        {
            $tapatalk_general = get_option('tapatalk_general');
            $api_key = isset($tapatalk_general['api_key']) ? $tapatalk_general['api_key'] : '';
            $app_forum_name = get_option('blogname');;
            $tapatalk_dir_url = "./tapatalk";
            $board_url = site_url();
            $pid = get_the_ID();
            $app_location_url = preg_replace('/https?:\/\//i', 'tapatalk://', $board_url);
            $app_location_url .= "?location=blog";
            if (!empty($pid)){
                $app_location_url .= "&pid=$pid";
                $page_type = 'post';
            }
            $app_ads_enable = isset($tapatalk_general['mobile_welcome_screen']) && $tapatalk_general['mobile_welcome_screen'];
            $app_banner_enable = isset($tapatalk_general['mobile_smart_banner']) && $tapatalk_general['mobile_smart_banner'];
            require ($this->plugin_dir . 'smartbanner/head.inc.php');
        }
        echo $app_head_include;
    }

    public function tapatalkfooter()
    {
        echo '<!-- Tapatalk Detect body start --> 
        <script type="text/javascript">
        if(typeof(tapatalkDetect) == "function") {
            tapatalkDetect();
        }
        </script>
        <!-- Tapatalk Detect banner body end -->';
    }
}

/*execute plugin*/
$tapatalk = new Tapatalk();
$tapatalk->steup_actions();


