{extends file='frontend/register/billing_fieldset.tpl'}

{block name='frontend_register_index_form_billing_fieldset' prepend}
	{assign var="form_data" value=$register->billing->form_data}	
	<div class="panel register--address">
		<h2 class="panel--title is--underline">{s namespace="frontend/plugins/asign_yellowcube/register/billing_fieldset" name="sYellowcubeEoriNumber"}EORI Number{/s}</h2>
		<div class="panel--body is--wide">
			<input name="register[billing][text1]" type="text" maxlength="17"  id="register_billing_text1" value="{$form_data.text1|escape}" class="register--field" />
			<div class="register--password-description">
				{s namespace="frontend/plugins/asign_yellowcube/register/billing_fieldset" name="sYellowcubeEoriDescription"}EORI - Speziell beachten! Ohne Angabe Ihrer "Economic Operators Registration and Identification" System-Nummer (EORI) kann es bei der Auslieferung bei der lokalen Zollbehörde zu Rückfragen und Verzögerungen kommen. Wir bitten alle unsere Kunden mit Lieferungen ausserhalb der Schweiz Ihre 17-stellige EORI Nummer hier einzugeben.{/s}
			</div>
		</div>		
	</div>
{/block}