<li>
<time class="cbp_tmtime" datetime="2017-11-04T03:45">{if $displayDate}<span>{$card.jourFR|upper|truncate:3:""} {$card.dateJour}</span>{/if}<span>{$card.heureDebut} - {$card.heureFin}</span></time>
<div class="cbp_tmicon"></div>
<div class="cbp_tmlabel aviron-loisir-event">
	{include file='bs-comp-disactivation.tpl'}
	{include file='bs-comp-edition.tpl'}
	<span class="badge bg-secondary">Officiel</span> Séance d'aviron loisir{if $card.referee ne ''}<span class="text-muted"> animée par <b>{$card.referee}</b></span>{/if}
</div>
</li>