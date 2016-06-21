{assign var="shopdata" value=$smarty.session.Shopware.shopData}
{foreach from=$Pages item=postions name="pagingLoop" key=page}
	<table cellpadding="0" cellspacing="0" border="0" class="main-table">
		<tbody valign="top">
			<tr class="thead">
				<td class="line-top line-left line-bottom"><img width="80" src="{$smarty.session.Shopware.pluginPath|cat:'cn22_logo.jpg'}" alt=""/></td>
				<td class="line-top line-bottom" valign="top" style="padding:5px;">
					<strong>{s name=yellowcube/print/cn22/declare1}Déclaration en douane / Zolldeklaration / Customs Declaration{/s}</strong><br />
					{s name=yellowcube/print/cn22/declare2}Peut être ouvert d‘office / Zollamtliche Prüfung gestattet / May be opened officially{/s}
				</td>
				<td class="line-top line-right line-bottom paddlr-5" align="right"><h1>CN 22</h1></td>
				<td rowspan="6" valign="bottom" class="tdadd">
					<u>{$shopdata.shopname} - {$shopdata.address|strip_tags:true}</u><br />
					{$User.shipping.company}<br />
					{$User.shipping.firstname} {$User.shipping.lastname}<br />
					{$User.shipping.street} {$User.shipping.streetnumber}<br />
					{$User.shipping.zipcode} {$User.shipping.city}<br />
					{if $User.shipping.state.shortcode}{$User.shipping.state.shortcode} - {/if}{$User.shipping.country.countryen}
				</td>
			</tr>
			<!-- pre-body information -->
			<tr>
				<td colspan="3" align="right" class="line-bottom padd-none line-left">
					<table cellpadding="" cellspacing="0" border="0">
						<tr>
							<td class="paddlr-5" width="120">{s name=yellowcube/print/cn22/param_1}Cadeau<br />Geschenk<br />Gift{/s}</td>
							<td class="paddlr-5">{s name=yellowcube/print/cn22/param_2}Echantillon commercial<br />Warenmuster<br />Commercial sample{/s}</td>
							<td class="paddlr-5" width="80">{s name=yellowcube/print/cn22/param_3}Documents<br />Dokumente<br />Documents{/s}</td>
							<td class="paddlr-5" width="" align="center"><h1>X</h1></td>
							<td class="paddlr-5 line-right">{s name=yellowcube/print/cn22/param_4}Autre<br />Andere<br />Other{/s}</td>
						</tr>
					</table>
				</td>
			</tr>
			<!-- article information -->
			<tr>
				<td colspan="3" class="line-bottom padd-none line-right line-left">
					<table cellpadding="0" cellspacing="0" border="0" width="100%" class="article-table">
						<tbody>
							<tr>
								<td class="line-bottom line-right paddlr-5">
									{s name=yellowcube/print/cn22/header_1}
									Quantité et description détaillée du contenu (1)<br />
									Menge und detaillierte Beschreibung des Inhalts<br />
									Quantity and detail description of contents
									{/s}
								</td>
								<td class="line-bottom line-right paddlr-5">
									{s name=yellowcube/print/cn22/header_2}
									Poids (2)<br />
									Gewicht (kg) <br />
									Weight(kg)
									{/s}
								</td>
								<td class="line-bottom line-right paddlr-5">
									{s name=yellowcube/print/cn22/header_3}
									Valeur (3)<br />
									Wert <br />
									Value
									{/s}
								</td>
								<td class="line-bottom paddlr-5">
									{s name=yellowcube/print/cn22/header_4}
									N° tarifaire du SH et origine (4)<br />
									Zolltarifnummer und Herkunft<br />
									HS tariff number and country of origin
									{/s}
								</td>
							</tr>
							{assign var="fTotalWeight" value=0.0}
							{foreach from=$postions item="position" key="number" name="positions"}
								{if $smarty.foreach.positions.iteration <= 5}
									<tr>
										<td class="line-bottom line-right paddlr-5">{$position.quantity} x {$position.name|nl2br}</td>
										<td class="line-bottom line-right paddlr-5">
											{math equation="x - y" x=$position.meta.weight|floatval y=$position.tara|floatval assign="fLineWeight"}
											{if $fLineWeight < 0}0.0{else}{$fLineWeight}{/if}kg

											{* calculate total weight in loop *}
											{math equation="x + y" x=$fTotalWeight y=$fLineWeight assign="fTotalWeight"}
										</td>
										<td class="line-bottom line-right paddlr-5" align="right">
											{if $Document.netto != true && $Document.nettoPositions != true}
												{$position.amount|currency}
											{else}
												{$position.amount_netto|currency}
											{/if}
										</td>
										<td class="line-bottom paddlr-5">
											{$position.tariff}
											<span class="marlft-110">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{$position.origin}</span>
										</td>
									</tr>
								{/if}
							{/foreach}

							<tr>
								<td class="line-bottom line-right paddlr-5">{s name=yellowcube/print/cn22/body_1}Verpackung und Versand / Shipping Costs{/s}</td>
								<td class="line-bottom line-right paddlr-5"></td>
								<td class="line-bottom line-right paddlr-5" align="right">{$Order._order.invoice_shipping_net|currency}</td>
							</tr>
							<tr>
								<td class="line-right paddlr-5">
								{s name=yellowcube/print/cn22/body_2}
									Poids total (5)<br />
									Gesamtgewicht (kg)<br />
									Total Weight (kg)
								{/s}
								</td>
								<td class="line-right paddlr-5" valign="middle">{if $fTotalWeight < 0}0.0{else}{$fTotalWeight}{/if}kg</td>
								<td class="line-right paddlr-5" align="right" valign="middle">
									{if $Document.netto == false}
										{$Order._amount|currency}
									{else}
										{$Order._amountNetto|currency}
									{/if}
								</td>
								<td class="line-top paddlr-5">
									{s name=yellowcube/print/cn22/body_3}
									Valeur totale (6) + monnaie<br />
									Gesamtwert + Währung<br />
									Total Value + Currency
									{/s}
								</td>
							</tr>
						</tbody>
					</table>
				</td>
			</tr>
			<!-- declaration -->
			<tr>
				<td colspan="3" class="line-bottom line-right line-left paddlr-5">
					{s name=yellowcube/print/cn22/declare}
					Je certifie que les renseignements donnés dans la présente déclaration sont exacts et que cet envoi ne contient<br>aucun objet dangereux ou interdit par la réglementation postale ou douanière. • Ich bestätige hiermit, dass die<br>Angaben in der vorliegenden Deklaration richtig sind und dass die Sendung keine durch die Post-oder<br>Zollvorschriften verbotenen oder gefährlichen Gegenstände enthält.• I, the undersigned, whose name and<br>address are given on the Item, certify that the particulars given in the declaration are correct and that this Item<br>does not contain any dangerous article or articles prohibited by legislation or by postal ort customs regulations.
					{/s}
				</td>
			</tr>
			<!-- date and signature -->
			<tr>
				<td colspan="3" class="line-left line-bottom line-right padd-none">
					<table cellpadding="0" cellspacing="0" border="0">
						<tr>
							<td width="180" class="paddlr-5">
								{s name=yellowcube/print/cn22/footer}
								Date, signature (7)<br />
								Datum, Unterschrift<br />
								Date, Senders signature
								{/s}
							</td>
							<td class="paddlr-5">{$smarty.now|date_format:"%d.%m.%Y"}</td>
							<td width="70" align="center" class="paddlr-5">
								<img width="40" src="{$smarty.session.Shopware.pluginPath|cat:'owner_signature.jpg'}" alt=""/>
							</td>
						</tr>
					</table>
				</td>
			</tr>
		</tbody>
	</table>
{/foreach}
