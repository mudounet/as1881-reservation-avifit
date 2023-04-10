{$inscFull=$card.participantsMax - count($card.listInscrits)}
<li>
<time class="cbp_tmtime" datetime="2017-11-04T03:45">{if $displayDate}<span>{$card.jourFR|upper|truncate:3:""} {$card.dateJour}</span>{/if}<span>{$card.heureDebut} - {$card.heureFin}</span></time>
<div class="cbp_tmicon bg-orange"></div>
<div class="cbp_tmlabel">

  {if isset($card.disactivation) && $card.disactivation != ''}<div class="overlay"><h1 class="position-absolute top-50 start-50 translate-middle text-danger">{$card.disactivation}</h1></div>{/if}
  {if $isAdmin}<div class="position-absolute top-0 end-0" style="padding:10px"><button type="button" class="btn btn-outline-light"data-bs-toggle="offcanvas" data-bs-target="#editEvent" aria-controls="editEvent" onclick="editEvent(this)" data-id="{$card.cardId}"><i class="bi bi-pencil-square"></i> Éditer</button></div>{/if}
  <div class="card-body">
  <h5 class="card-title">Séance d'avifit <span class="badge rounded-pill bg-secondary">Avifit</span></h5>{if $card.referent ne ''}<h6 class="card-subtitle mb-2 text-muted">Animée par <b>{$card.referent}</b></h6>{/if}
    
	<p>Il est important de réserver sa place, de s'engager à venir, et de prévenir sur le groupe WhatsApp en cas d'impossibilité de dernière minute.</p>
</div>
{include file='bs-comp-subscription.htm'}
</div>
</li>