<li>
<time class="cbp_tmtime" datetime="2017-11-04T03:45">{if $displayDate}<span>{$card.jourFR|upper|truncate:3:""} {$card.dateJour}</span>{/if}<span>{$card.heureDebut} - {$card.heureFin}</span></time>
<div class="cbp_tmicon"></div>
<div class="cbp_tmlabel" style="background: linear-gradient(to right, rgba(220,220,220,0.8), rgba(220,220,220,0.08));border-color:rgba(220, 220, 220, 0.8)">
	{include file='bs-comp-disactivation.tpl'}
	{include file='bs-comp-edition.tpl'}
	<span class="badge bg-secondary">Officiel</span> Séance d'aviron loisir{if $card.referent ne ''}<span class="text-muted"> animée par <b>{$card.referent}</b></span>{/if}
</div>
</li>