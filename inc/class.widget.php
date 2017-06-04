<?php
//define("ISB_COTACAO_ORIGEM_URL", "http://www.bcb.gov.br/pt-br/#!/home");
define("ISB_COTACAO_ORIGEM_URL", "http://www.bcb.gov.br/api/conteudo/pt-br/PAINEL_INDICADORES/cambio");

/**
 * Created by PhpStorm.
 * User: Eduardo
 * Date: 31/05/2017
 * Time: 09:38
 */
class ISB_Cotacoes_Widget extends WP_Widget {

	/**
	 * Sets up the widgets name etc
	 */
	public function __construct() {
		$widget_ops = array(
			'classname' => 'isb_cotacoes_widget',
			'description' => 'Mostra a cotação do dolar para importação/exportação',
		);
		parent::__construct( 'isb_cotacoes_widget', 'Cotação do Euro e Dólar para Importação e Exportação', $widget_ops );
	}

	/**
	 * Outputs the content of the widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {

		$instance = ISB_Cotacoes::getCotacoesOptions();
		$taxaImportacao = $instance['taxa_importacao'];
		$taxaExportacao = $instance['taxa_exportacao'];

		try {
			$dataCotacaoDolar = ISB_Cotacao_Worker::getDataCotacao();
			$cotacaoDolar = ISB_Cotacao_Worker::getCotacao('USD');
			$cotacaoEuro = ISB_Cotacao_Worker::getCotacao('EUR');
			$ultimaAtualizacao =  ISB_Cotacao_Worker::getUltimaAtualizacao();

			if ($ultimaAtualizacao) {
				$date = new DateTime($ultimaAtualizacao,  new DateTimeZone(get_option('timezone_string')));
				$ultimaAtualizacao = $date->format('d/m/Y H:i:s');
			}

			if (!$cotacaoDolar || !$cotacaoEuro) {
				$cotacoes = ISB_Cotacao_Worker::updateCotacoes();

				$dataCotacaoDolar = new DateTime($cotacoes['data_cotacao']);
				$cotacaoDolar = $cotacoes['cotacao_dolar'];
				$cotacaoEuro = $cotacoes['cotacao_euro'];
			}

			$cotacaoDolarImportacao = round($cotacaoDolar + ($cotacaoDolar * ($taxaImportacao/100)), 2);
			$cotacaoEuroImportacao = round($cotacaoEuro + ($cotacaoEuro * ($taxaImportacao/100)), 2);

			$cotacaoDolarExportacao = round($cotacaoDolar + ($cotacaoDolar * ($taxaExportacao/100)), 2);
			$cotacaoEuroExportacao = round($cotacaoEuro + ($cotacaoEuro * ($taxaExportacao/100)), 2);

			$htmlCotacao = '
		<section class="section cotacoes">
			<h2>'.$dataCotacaoDolar->format('d/m/Y').'</h2>			
			<div class="container">
				<div class="row">
					<div class="col-md-12">
						<h3>Importação</h3>
						<div class="cotacao_dolar_widget">
							<p class="lead cotacao_dolar_titulo">USD: <span class="cotacao_dolar_valor"> '. money_format('%.2n', $cotacaoDolarImportacao) . '</span></p>			
						</div>
						<div class="cotacao_euro_widget">
							<p class="lead cotacao_euro_titulo">EUR: <span class="cotacao_euro_valor">'. money_format('%.2n', $cotacaoEuroImportacao) .'</span></p>			
						</div>
					</div>
				</div>
			</div>			
			<div class="container">
				<div class="row">
					<div class="col-md-12">
						<h3>Exportação</h3>
						<div class="cotacao_dolar_widget">
							<p class="lead cotacao_dolar_titulo">USD: <span class="cotacao_dolar_valor"> '. money_format('%.2n', $cotacaoDolarExportacao) . '</span></p>			
						</div>
						<div class="cotacao_euro_widget">
							<p class="lead cotacao_euro_titulo">EUR: <span class="cotacao_euro_valor">'. money_format('%.2n', $cotacaoEuroExportacao) .'</span></p>			
						</div>	
					</div>
				</div>
			</div>
			<div class="container">
				<div class="row">				
					<div class="col-md-12">
						<p class="text-right"><small>Última atualização: '. $ultimaAtualizacao .'</small></p>
					</div>
				</div>
			</div>			
		</section>';

		} catch (Exception $e) {
			$htmlCotacao = '<div class="alert"><p>Desculpe, a cotação está indisponível no momento</p></div>';
		}

		echo '<div class="container isb-cotacoes">
	<div class="row">
		<div class="col-md-12">
		' . $htmlCotacao. '
		</div>
	</div>
</div>';

	}

	/**
	 * Outputs the options form on admin
	 *
	 * @param array $instance The widget options
	 * @return string|void
	 */
	public function form( $instance ) {
/*
		$instance = array_merge($this->defaults, $instance);
		$taxaImportacao = $instance['taxa_importacao'];
		$taxaExportacao = $instance['taxa_exportacao'];

		$dataCotacao = ISB_Cotacao_Worker::getDataCotacao();
		$cotacaoDolar = ISB_Cotacao_Worker::getCotacao('USD');
		$cotacaoEuro = ISB_Cotacao_Worker::getCotacao('EUR');
		$ultimaAtualizacao = ISB_Cotacao_Worker::getUltimaAtualizacao();

		echo "
		<div>
			<p>Cotações de {$dataCotacao->format('d/m/Y H:i')}</p>
			<p><strong>Dólar:</strong> {$cotacaoDolar}</p>
			<p><strong>Euro:</strong> {$cotacaoEuro}</p>
			<p>Atualizado em: {$ultimaAtualizacao}</p>
		</div>
		<div>
			<label for='{$this->get_field_id('taxa_importacao')}'>Taxa de Importação (%): </label>
			<input type='number' class=\"widefat\" step='1' min='1' size='2' id='{$this->get_field_id('taxa_importacao')}' name='{$this->get_field_name('taxa_importacao')}' value='{$taxaImportacao}'>
		</div>
		<div>
			<label for='{$this->get_field_id('taxa_exportacao')}'>Taxa de Exportacao (%): </label>
			<input type='number' class=\"widefat\" step='1' min='1' size='2' id='{$this->get_field_id('taxa_exportacao')}' name='{$this->get_field_name('taxa_exportacao')}' value='{$taxaExportacao}'>
		</div>
		";
*/
	}

	/**
	 * Processing widget options on save
	 *
	 * @param array $new_instance The new options
	 * @param array $old_instance The previous options
	 * @return array|void
	 */
	public function update( $new_instance, $old_instance ) {
		// processes widget options to be saved
		//return $new_instance;
	}
}