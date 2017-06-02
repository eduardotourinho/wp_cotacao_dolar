<?php
//define("ISB_COTACAO_ORIGEM_URL", "http://www.bcb.gov.br/pt-br/#!/home");
define("ISB_COTACAO_ORIGEM_URL", "http://www.bcb.gov.br/api/conteudo/pt-br/PAINEL_INDICADORES/cambio");

/**
 * Created by PhpStorm.
 * User: Eduardo
 * Date: 31/05/2017
 * Time: 09:38
 */
class ISB_Cotacao_Dolar_Widget extends WP_Widget {

	/**
	 * Sets up the widgets name etc
	 */
	public function __construct() {
		$widget_ops = array(
			'classname' => 'cotacao_dolar_widget',
			'description' => 'Mostra a cotação do dolar para importação/exportação',
		);
		parent::__construct( 'cotacao_dolar_widget', 'Cotação do Dolar', $widget_ops );
	}

	/**
	 * Outputs the content of the widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {
		$taxaImportacao = $instance['taxa_importacao'];
		$taxaExportacao = $instance['taxa_exportacao'];

		try {
			$cotacoes = $this->getCotacoesFromBCB();

			$dataCotacaoDolar = new DateTime($cotacoes['dolar']->dataIndicador);

			$cotacaoDolar = $cotacoes['dolar']->valorVenda;
			$cotacaoEuro = $cotacoes['euro']->valorVenda;

			$cotacaoDolarImportacao = round($cotacaoDolar + ($cotacaoDolar * ($taxaImportacao/100)), 2);
			$cotacaoEuroImportacao = round($cotacaoEuro + ($cotacaoEuro * ($taxaImportacao/100)), 2);

			$cotacaoDolarExportacao = round($cotacaoDolar + ($cotacaoDolar * ($taxaExportacao/100)), 2);
			$cotacaoEuroExportacao = round($cotacaoEuro + ($cotacaoEuro * ($taxaExportacao/100)), 2);

			$htmlCotacao = '
		<section class="section cotacoes">
			<h2>'.$dataCotacaoDolar->format('d/m/Y').'</h2>
			<div class="row">
				<div class="container">
					<h3>Importação</h3>
					<div class="cotacao_dolar_widget">
						<p class="lead cotacao_dolar_titulo">USD: <span class="cotacao_dolar_valor"> '. money_format('%.2n', $cotacaoDolarImportacao) . '</span></p>			
					</div>
					<div class="cotacao_euro_widget">
						<p class="lead cotacao_euro_titulo">EUR: <span class="cotacao_euro_valor">'. money_format('%.2n', $cotacaoEuroImportacao) .'</span></p>			
					</div>	
				</div>
			</div>
			<div class="row">
				<div class="container">
					<h3>Exportação</h3>
					<div class="cotacao_dolar_widget">
						<p class="lead cotacao_dolar_titulo">USD: <span class="cotacao_dolar_valor"> '. money_format('%.2n', $cotacaoDolarExportacao) . '</span></p>			
					</div>
					<div class="cotacao_euro_widget">
						<p class="lead cotacao_euro_titulo">EUR: <span class="cotacao_euro_valor">'. money_format('%.2n', $cotacaoEuroExportacao) .'</span></p>			
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

	private function getCotacoesFromBCB() {
		$cURL = curl_init();
		curl_setopt($cURL, CURLOPT_URL, ISB_COTACAO_ORIGEM_URL);
		curl_setopt($cURL, CURLOPT_HTTPGET, true);
		curl_setopt($cURL, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($cURL, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/json',
			'Accept: application/json'
		));

		$cotacoesContent = curl_exec($cURL);;
		curl_close($cURL);

		$cotacoesJson = json_decode($cotacoesContent);

		$cotacoesBcb = array();
		foreach ($cotacoesJson->conteudo as $moeda) {
			if (strtolower($moeda->tipoCotacao) == 'fechamento') {
				continue;
			}

			$cotacoesBcb = array_merge($cotacoesBcb, array(strtolower(removeDiacritics($moeda->moeda)) => $moeda));
		}

		return $cotacoesBcb;
	}

	/**
	 * Outputs the options form on admin
	 *
	 * @param array $instance The widget options
	 * @return string|void
	 */
	public function form( $instance ) {

		// outputs the options form on admin
		$taxaImportacao = ($instance && array_key_exists('taxa_importacao', $instance)) ? $instance['taxa_importacao'] : 10;
		$taxaExportacao = ($instance && array_key_exists('taxa_exportacao', $instance)) ? $instance['taxa_exportacao'] : 15;

		echo "
		<div>
			<label for=\"{$this->get_field_id('taxa_importacao')}\">Taxa de Importação: </label>
			<input type=\"number\" step='1' min='1' id=\"{$this->get_field_id('taxa_importacao')}\" name=\"{$this->get_field_name('taxa_importacao')}\" value=\"{$taxaImportacao}\">
		</div>
		<div>
			<label for=\"{$this->get_field_id('taxa_exportacao')}\">Taxa de Exportacao: </label>
			<input type=\"number\" step='1' min='1' id=\"{$this->get_field_id('taxa_exportacao')}\" name=\"{$this->get_field_name('taxa_exportacao')}\" value=\"{$taxaExportacao}\">
		</div>
		";
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
		return $new_instance;
	}
}