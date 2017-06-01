<?php
/*
Plugin Name: ISB Cotacao Dolar
Description: Cotação do dólar com cálculo de acréscimos estipulados pela empresa ISB
Version: 1.2
Author: Eduardo Tourinho
License: GPL2
*/
setlocale(LC_MONETARY, 'pt_BR');

define ( 'COTACAO_DOLAR_URL', plugins_url( '', __FILE__ ) );
define ( 'COTACAO_DOLAR_DIR', rtrim( plugin_dir_path( __FILE__ ), '/' ) );

require (COTACAO_DOLAR_DIR . '/inc/functions.php');
require (COTACAO_DOLAR_DIR . '/inc/class.widget.php');


// Init Simple Tags
function init_cotacao_dolar() {
	// Load client
	new ISB_Cotacao_Dolar_Widget();

	add_action( 'widgets_init', create_function( '', 'return register_widget("ISB_Cotacao_Dolar_Widget");' ) );
}

add_action( 'plugins_loaded', 'init_cotacao_dolar' );
