<?php
/*
Plugin Name: ISB Cotação Importação/Exportação
Description: Cotação do dólar e euro com cálculo de acréscimos estipulados pela empresa ISB
Version: 1.4
Author: Eduardo Tourinho
License: GPL2
*/
setlocale(LC_MONETARY, 'pt_BR');

define('ISB_COTACAO_UPDATE_TIME', 'T10:50');

define ( 'ISB_COTACOES_PLUGIN_URL', plugins_url( '', __FILE__ ) );
define ( 'ISB_COTACOES_PLUGIN_DIR', rtrim( plugin_dir_path( __FILE__ ), '/' ) );

require (ISB_COTACOES_PLUGIN_DIR . '/inc/functions.php');
require (ISB_COTACOES_PLUGIN_DIR . '/inc/class.widget.php');
require (ISB_COTACOES_PLUGIN_DIR . '/inc/class.worker.php');

class ISB_Cotacoes {

	public static $defaults = array(
		'hora_update_cotacao' => '10:50',
		'taxa_importacao' => 10,
		'taxa_exportacao' => 15
	);

	public static function init() {
		add_action( 'isb_update_cotacao_job', array(__CLASS__, 'taskUpdateCotacoes'));
		add_action( 'widgets_init', array(__CLASS__, 'initWidget'));
		add_action ( 'admin_init', array( __CLASS__, 'registerCotacoesOptions' ) );
		add_action('admin_menu', array(__CLASS__, 'cotacoesOptions'));
		add_shortcode( 'isb_cotacoes', array( __CLASS__, 'shortcode' ) );
	}

	public static function deactivate() {
		self::deactivateUpdateTask();
		self::unregisterCotacoesOptions();
	}

	public static function initWidget() {
		register_widget("isb_cotacoes_widget");
	}

	public static function initUpdateTask() {
		if (! wp_next_scheduled ('isb_update_cotacao_job')) {
			$options = ISB_Cotacoes::getCotacoesOptions();
			$updateTime = $options['hora_update_cotacao'];

			$time = new DateTime($updateTime, new DateTimeZone(get_option('timezone_string')));
			$timeToRun = $time->format('U');
			if ($timeToRun !== false) {
				wp_schedule_event($timeToRun, 'daily', 'isb_update_cotacao_job');
			}
		}
	}

	public static function deactivateUpdateTask() {
		wp_clear_scheduled_hook('isb_update_cotacao_job');
	}

	public static function taskUpdateCotacoes() {
		ISB_Cotacao_Worker::updateCotacoes();
	}

	/**
	 * Function add options button to admin panel
	 *
	 * @access public
	 *
	 * @return void
	 */
	public static function cotacoesOptions() {
		add_submenu_page( 'tools.php', 'ISB Cotações', 'ISB Cotações', 'manage_options', 'isb_cotacoes_options', array(__CLASS__, 'cotacoesFormOpcoes') );
	}

	public static function cotacoesFormOpcoes() {
		self::save_settings();

		include ISB_COTACOES_PLUGIN_DIR . '/templates/admin_form_options.php';
	}

	public static function getCotacoesOptions() {
		return get_option('isb_cotacoes_options', self::$defaults);
	}

	public static function registerCotacoesOptions() {
		register_setting('isb_cotacoes_options', 'isb_cotacoes_options');
	}

	public static function unregisterCotacoesOptions() {
		delete_option( 'isb_cotacoes_options' );
	}

	protected static function save_settings () {
		if( current_user_can( 'manage_options' ) ) {
			if (isset($_POST['isb_cotacoes_options'])) {
				$options = ISB_Cotacoes::getCotacoesOptions();
				$updateTimeOption = $options['hora_update_cotacao'];

				update_option('isb_cotacoes_options', $_POST['isb_cotacoes_options']);
				if ($updateTimeOption != $_POST['isb_cotacoes_options']['hora_update_cotacao']) {
					self::deactivateUpdateTask();
					self::initUpdateTask();
				}
				//echo json_encode($_POST['isb_cotacoes_options']);
			}
		}
	}

	public static function shortcode( $atts = array() ) {
		$atts = apply_filters( 'isb_cotacoes_shortcode_options', $atts );
		the_widget( 'isb_cotacoes_widget', $atts);
	}
}

register_activation_hook(__FILE__, array('ISB_Cotacoes', 'initUpdateTask'));
register_deactivation_hook(__FILE__, array('ISB_Cotacoes', 'deactivate'));
add_action('plugins_loaded', array('ISB_Cotacoes', 'init'));



