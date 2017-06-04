<?php
$cotacaoDolar = money_format('%.2n', ISB_Cotacao_Worker::getCotacao('USD'));
$cotacaoEuro = money_format('%.2n', ISB_Cotacao_Worker::getCotacao('EUR'));
$dataCotacao = ISB_Cotacao_Worker::getDataCotacao();
$ultimaAtualizacao = ISB_Cotacao_Worker::getUltimaAtualizacao();
$options = ISB_Cotacoes::getCotacoesOptions();
?>
<div class="wrap">
	<div id="icon-tools" class="icon32"></div>
	<div>
		<h1>Cotações de <?php echo $dataCotacao->format('d/m/Y H:i')?></h1>
		<?php settings_errors(); ?>

		<ul>
			<li><strong>Dólar:</strong> <?php echo $cotacaoDolar ?></li>
			<li><strong>Euro:</strong> <?php echo $cotacaoEuro ?></li>
			<li><em>Atualizado em: <?php echo $ultimaAtualizacao; ?></em></li>
		</ul>
	</div>
	<form class="" method="post">
		<table class="form-table">
			<tbody>
				<tr>
					<th><label>Atualizar cotações todo dia às</label></th>
					<td><input type="time" name="isb_cotacoes_options[hora_update_cotacao]" value="<?php echo $options['hora_update_cotacao']?>"></td>
				</tr>
				<tr>
					<th><label>Taxa de Importação (%): </label></th>
					<td><input type="number" class="regular-text" step="1" min="1" size="2" name="isb_cotacoes_options[taxa_importacao]" value="<?php echo $options['taxa_importacao']?>"></td>
				</tr>
				<tr>
					<th><label>Taxa de Exportacao (%): </label></th>
					<td><input type="number" class="regular-text" step="1" min="1" size="2" name="isb_cotacoes_options[taxa_exportacao])?>" value="<?php echo $options['taxa_exportacao']?>"></td>
				</tr>
			</tbody>
		</table>
		<p class="submitbox">
			<input type="submit" class="button-primary" value="<?php _e('Save Changes', 'textdomain') ?>" />
		</p>
	</form>
</div>