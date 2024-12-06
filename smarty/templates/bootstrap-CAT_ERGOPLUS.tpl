<li>
<time class="cbp_tmtime" datetime="2017-11-04T03:45">{if $displayDate}<span>{$card.jourFR|upper|truncate:3:""} {$card.dateJour}</span>{/if}<span>{$card.heureDebut} - {$card.heureFin}</span></time>
<div class="cbp_tmicon bg-white"></div>
<div class="cbp_tmlabel avifit-event">
{include file='bs-comp-disactivation.tpl'}
{include file='bs-comp-edition.tpl'}
  <div class="card-body">
  <h5 class="card-title"><span class="badge bg-secondary">Officiel</span> Séance d'ergomètre sportif</h5>{if $card.referee ne ''}<h6 class="card-subtitle mb-2 text-muted">Animée par <b>{$card.referee}</b></h6>{/if}
    <p>Cette séance est réservée aux titulaires de plouf.</p>
	<p>Il est important de réserver sa place, de s'engager à venir, et de prévenir sur le groupe WhatsApp en cas d'impossibilité de dernière minute.</p>
</div>
{include file='bs-comp-subscription.tpl'}
</div>
</li>