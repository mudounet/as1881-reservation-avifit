{$inscFull=$card.participantsMax - count($card.listInscrits)}
<li>
<time class="cbp_tmtime" datetime="2017-11-04T03:45">{if $displayDate}<span>{$card.jourFR|upper|truncate:3:""} {$card.dateJour}</span>{/if}<span>{$card.heureDebut} - {$card.heureFin}</span></time>
<div class="cbp_tmicon bg-orange"></div>
<div class="cbp_tmlabel">
{include file='bs-comp-disactivation.tpl'}
{include file='bs-comp-edition.tpl'}
  <div class="card-body">
  <h5 class="card-title"><span class="badge bg-secondary">Officiel</span> Séance d'avifit</h5>{if $card.referent ne ''}<h6 class="card-subtitle mb-2 text-muted">Animée par <b>{$card.referent}</b></h6>{/if}
    
	<p>Il est important de réserver sa place, de s'engager à venir, et de prévenir sur le groupe WhatsApp en cas d'impossibilité de dernière minute.</p>
</div>
{include file='bs-comp-subscription.tpl'}
</div>
</li>