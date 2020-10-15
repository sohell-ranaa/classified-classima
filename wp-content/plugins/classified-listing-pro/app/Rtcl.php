<?php

require_once __DIR__ . './../vendor/autoload.php';

use Rtcl\Controllers\RtclWpmlController;
use Rtcl\Interfaces\LoggerInterface;
use Rtcl\Log\Logger;
use Rtcl\Models\Cart;
use Rtcl\Helpers\Cache;
use Rtcl\Models\Factory;
use Rtcl\Widgets\Widget;
use Rtcl\Helpers\Install;
use Rtcl\Helpers\Upgrade;
use Rtcl\Controllers\Query;
use Rtcl\Helpers\Functions;
use Rtcl\Models\RtclEmails;
use Rtcl\Controllers\Ajax\Ajax;
use Rtcl\Traits\SingletonTrait;
use Rtcl\Controllers\Shortcodes;
use Rtcl\Models\PaymentGateways;
use Rtcl\Controllers\PublicAction;
use Rtcl\Controllers\Hooks\Actions;
use Rtcl\Controllers\SessionHandler;
use Rtcl\ThemeSupports\ThemeSupports;
use Rtcl\Controllers\Hooks\AdminHooks;
use Rtcl\Controllers\Hooks\AppliedHooks;
use Rtcl\Controllers\Hooks\TemplateHooks;
use Rtcl\Controllers\Hooks\TemplateLoader;
use Rtcl\Controllers\Admin\AdminController;
use Rtcl\Controllers\Hooks\AfterSetupTheme;

/**
 * Class Rtcl
 */
final class Rtcl
{

    use SingletonTrait;

    /**
     * Query instance.
     *
     * @var Query
     */
    public $query = null;

    /**
     * Factory instance.
     *
     * @var Factory
     */
    public $factory = null;


    public $post_type = "rtcl_listing";
    public $post_type_cfg = "rtcl_cfg";
    public $post_type_cf = "rtcl_cf";
    public $post_type_payment = "rtcl_payment";
    public $post_type_pricing = "rtcl_pricing";
    public $category = "rtcl_category";
    public $location = "rtcl_location";
    public $nonceId = "__rtcl_wpnonce";
    public $nonceText = "rtcl_nonce_secret";
    private $listing_types_option = "rtcl_listing_types";
    private $cache_prefix = 'rtcl_cache';
    public $api = 'rtcl/v1';
    public $gallery = array();
    public $upload_directory = "classified-listing";

    /**
     * @var SessionHandler object
     */
    public $session = false;

    /**
     * @var Cart object
     */
    public $cart = false;


    /**
     * Cloning is forbidden.
     *
     * @since 1.0
     */
    public function __clone() {
        Functions::doing_it_wrong(__FUNCTION__, __('Cheatin&#8217; huh?', 'classified-listing'), '1.0');
    }

    /**
     * Unserializing instances of this class is forbidden.
     *
     * @since 1.0
     */
    public function __wakeup() {
        Functions::doing_it_wrong(__FUNCTION__, __('Cheatin&#8217; huh?', 'classified-listing'), '1.0');
    }

    /**
     * Auto-load in-accessible properties on demand.
     *
     * @param mixed $key Key name.
     *
     * @return mixed
     */
    public function __get($key) {
        if (in_array($key, array('init', 'payment_gateways', 'plugins_loaded'), true)) {
            return $this->$key();
        }
    }

    /**
     * Classified Listing Constructor.
     */
    public function __construct() {
        $this->define_constants();
        Widget::init();
        Cache::init();
        // Add Admin hook
        if ($this->is_request('admin')) {
            AdminHooks::init();
            Upgrade::init();
        }

        // add hook for both
        Actions::init();
        if ($this->is_request('frontend')) {
            $this->frontend_hook();
        }

        ThemeSupports::init();
        $this->query = new Query();
        $this->init_hooks();
    }

    private function frontend_hook() {
        AppliedHooks::init();
        TemplateHooks::init();
        add_action('init', [TemplateLoader::class, 'init']);
    }


    private function init_hooks() {

        register_activation_hook(RTCL_PLUGIN_FILE, [Install::class, 'activate']);
        register_deactivation_hook(RTCL_PLUGIN_FILE, array(Install::class, 'deactivate'));

        add_action('plugins_loaded', [$this, 'on_plugins_loaded'], -1);
        add_action('after_setup_theme', [AfterSetupTheme::class, 'template_functions'], 11);
        add_action('init', [$this, 'init'], 0);
        add_action('init', [Shortcodes::class, 'init_short_code']);// Init ShortCode
    }

    public function init() {
        do_action('rtcl_before_init');

        $this->load_plugin_textdomain();
        $this->factory = new Factory();
        new AdminController();
        new Ajax();
        new PublicAction();
        $this->load_url_message();
        $this->get_cart();
        $this->load_session();

        do_action('rtcl_init');
    }

    public function on_plugins_loaded() {
        do_action('rtcl_loaded');
    }

    private function load_session() {
        $this->session = new SessionHandler();
        $this->session->init();
    }

    public function db() {
        global $wpdb;

        return $wpdb;
    }

    /**
     * Get a shared logger instance.
     *
     * @return Logger
     * @see LoggerInterface
     *
     */
    public function logger() {
        static $logger = null;
        $class = apply_filters('rtcl_logging_class', Logger::class);

        if (null !== $logger && is_string($class) && is_a($logger, $class)) {
            return $logger;
        }

        $implements = class_implements($class);

        if (is_array($implements) && in_array(LoggerInterface::class, $implements, true)) {
            $logger = is_object($class) ? $class : new $class();
        } else {
            $logger = is_a($logger, Logger::class) ? $logger : new Logger();
        }

        return $logger;
    }


    /**
     * Load Localisation files.
     * Note: the first-loaded translation file overrides any following ones if the same translation is present.
     * Locales found in:
     *      - WP_LANG_DIR/classified-listing/classified-listing-LOCALE.mo
     *      - WP_LANG_DIR/plugins/classified-listing-LOCALE.mo
     */
    public function load_plugin_textdomain() {
        if (defined('DOING_AJAX') && DOING_AJAX) {
            global $sitepress;
            if (method_exists($sitepress, 'switch_lang') && isset($_GET['wpml_lang']) && $_GET['wpml_lang'] !== $sitepress->get_default_language()) {
                $sitepress->switch_lang($_GET['wpml_lang'], true); // Alternative	do_action( 'wpml_switch_language', $_GET['wpml_lang'] );
            }
        }
        if (function_exists('determine_locale')) {
            $locale = determine_locale();
        } else {
            // @todo Remove when start supporting WP 5.0 or later.
            $locale = is_admin() ? get_user_locale() : get_locale();
        }
        $locale = apply_filters('plugin_locale', $locale, 'classified-listing');
        unload_textdomain('classified-listing');
        load_textdomain('classified-listing', WP_LANG_DIR . '/classified-listing/classified-listing-' . $locale . '.mo');
        load_plugin_textdomain('classified-listing', false, plugin_basename(dirname(RTCL_PLUGIN_FILE)) . '/languages');
    }


    /**
     * Get gateways class.
     *
     * @return array
     */
    public function payment_gateways() {
        return PaymentGateways::instance()->payment_gateways;
    }


    /**
     * Email Class.
     *
     * @return bool|SingletonTrait|RtclEmails
     */
    public function mailer() {
        return RtclEmails::getInstance();
    }


    /**
     * What type of request is this?
     *
     * @param string $type admin, ajax, cron or frontend.
     *
     * @return bool
     */
    private function is_request($type) {
        switch ($type) {
            case 'admin':
                return is_admin();
            case 'ajax':
                return defined('DOING_AJAX');
            case 'cron':
                return defined('DOING_CRON');
            case 'frontend':
                return (!is_admin() || defined('DOING_AJAX')) && !defined('DOING_CRON');
        }
    }


    private function define_constants() {
        $plugin_data = get_file_data(RTCL_PLUGIN_FILE, array('author' => 'Author'), false);
        $this->define('RTCL_AUTHOR', $plugin_data['author']);
        $this->define('RTCL_PATH', plugin_dir_path(RTCL_PLUGIN_FILE));
        $this->define('RTCL_URL', plugins_url('', RTCL_PLUGIN_FILE));
        $this->define('RTCL_SLUG', basename(dirname(RTCL_PLUGIN_FILE)));
        $this->define('RTCL_SESSION_CACHE_GROUP', 'rtcl_session_id');
        $this->define('RTCL_TEMPLATE_DEBUG_MODE', false);
        $this->define('RTCL_NOTICE_MIN_PHP_VERSION', '7.0');
        $this->define('RTCL_NOTICE_MIN_WP_VERSION', '5.0');
        $this->define('RTCL_ROUNDING_PRECISION', 6);
    }

    /**
     * Define constant if not already set.
     *
     * @param string      $name  Constant name.
     * @param string|bool $value Constant value.
     */
    public function define($name, $value) {
        if (!defined($name)) {
            define($name, $value);
        }
    }


    /**
     * Get the template path.
     *
     * @return string
     */
    public function get_template_path() {
        return apply_filters('rtcl_template_path', 'classified-listing/');
    }

    /**
     * Get the plugin path.
     *
     * @return string
     */
    public function plugin_path() {
        return untrailingslashit(plugin_dir_path(RTCL_PLUGIN_FILE));
    }

    /**
     * @return mixed
     */
    public function version() {
        return RTCL_VERSION;
    }

    public function get_listing_types_option_id() {
        return $this->listing_types_option;
    }

    /**
     * @param $file
     *
     * @return string
     */
    public function get_assets_uri($file) {
        $file = ltrim($file, '/');

        return trailingslashit(RTCL_URL . '/assets') . $file;
    }

    public function wp_query() {
        global $wp_query;

        return $wp_query;
    }

    private function load_url_message() {
        if (isset($_GET['rtcl-type']) && in_array($_GET['rtcl-type'], [
                'success',
                'error'
            ]) && isset($_GET['message'])) {
            Functions::add_notice(trim(urldecode($_GET['message'])), trim($_GET['rtcl-type']));
        }
    }

    /**
     * @param String|array $id
     * @param String       $group
     * @param string       $sub_group
     *
     * @return string
     */
    function get_transient_name($id, $group, $sub_group = '') {
        $id = !empty($id) && is_array($id) ? md5(wp_json_encode($id)) : $id;
        $wpml_cache_prefix = defined('ICL_LANGUAGE_CODE') && ICL_LANGUAGE_CODE ? "_" . ICL_LANGUAGE_CODE : '';
        if (rtcl()->location === $group) {
            $transient_name = sprintf('%s_%s_%s_%s', $this->cache_prefix, rtcl()->location, $sub_group, $id);
        } else if (rtcl()->category === $group) {
            $transient_name = sprintf('%s_%s_%s_%s', $this->cache_prefix, rtcl()->category, $sub_group, $id);
        } else {
            $transient_name = sprintf('%s_%s', $this->cache_prefix, microtime());
        }
        return $transient_name . $wpml_cache_prefix;
    }


    /**
     * Get cart object instance for online learning market.
     *
     * @return Cart
     */
    public function get_cart() {
        if (!$this->cart) {
            $cart_class = apply_filters('rtcl_cart_class', Cart::class);
            if (is_object($cart_class)) {
                $this->cart = $cart_class;
            } else {
                if (class_exists($cart_class)) {
                    $this->cart = is_callable(array(
                        $cart_class,
                        'instance'
                    )) ? call_user_func(array($cart_class, 'instance')) : new $cart_class();
                }
            }
        }

        return $this->cart;
    }
}

/**
 * @return bool|SingletonTrait|Rtcl
 */
function rtcl() {
    return Rtcl::getInstance();
}

rtcl(); // Run classified listing Plugin