<?php

/**
 * Created by PhpStorm.
 * User: Eduardo
 * Date: 02/06/2017
 * Time: 16:37
 */
class ISB_Cotacao_Worker
{
	public static $defaults = array(
		'cotacao_dolar' => 0,
		'cotacao_euro' => 0,
		'data_cotacao' => '',
		'ultima_atualizacao' => null
	);

	static public function getCotacoesFromBCB() {
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

	static public function updateCotacoes() {
		$cotacoes = self::getCotacoesFromBCB();

		$dataCotacaoDolar = new DateTime($cotacoes['dolar']->dataIndicador);
		$cotacaoDolar = $cotacoes['dolar']->valorVenda;
		$cotacaoEuro = $cotacoes['euro']->valorVenda;

		$options = array_merge(self::$defaults, array(
			'cotacao_dolar' => $cotacaoDolar,
			'cotacao_euro' => $cotacaoEuro,
			'data_cotacao' => $dataCotacaoDolar->format('Y-m-d H:i:s'),
			'ultima_atualizacao' => date('Y-m-d H:i:s')
		));

		update_option('isb_cotacoes', $options);

		return $options;
	}

	static protected function getOtions() {
		$options = get_option('isb_cotacoes', self::$defaults);
		return array_merge(self::$defaults, $options);
	}

	static public function getCotacao($moeda = 'USD') {
		if (in_array($moeda, array('USD', 'EUR'))) {
			$options = self::getOtions();

			if ($moeda == 'EUR') {
				return $options['cotacao_euro'];
			} else {
				return $options['cotacao_dolar'];
			}
		}

		return null;
	}

	static public function getDataCotacao() {
		$options = self::getOtions();
		return $options['data_cotacao'] ? new DateTime($options['data_cotacao']) : new DateTime();
	}

	static public function getUltimaAtualizacao() {
		$options = self::getOtions();
		return $options['ultima_atualizacao'];
	}
}