<li>
<time class="cbp_tmtime" datetime="2017-11-04T03:45">{if $displayDate}<span>{$card.jourFR|upper|truncate:3:""} {$card.dateJour}</span>{/if}<span>{$card.heureDebut} - {$card.heureFin}</span></time>
<div class="cbp_tmicon bg-orange"></div>
<div class="cbp_tmlabel card">
  <div class="card-header"><h5 class="card-title">Séance d'avifit <span class="badge rounded-pill bg-secondary">Avifit</span></h5>{if $card.animateur ne ''}<h6 class="card-subtitle mb-2 text-muted">Animée par <b>{$card.animateur}</b></h6>{/if}
  </div>
  <div class="card-body">
    
	<p>Il est important de réserver sa place, de s'engager à venir, et de prévenir sur le groupe WhatsApp en cas d'impossibilité de dernière minute.</p>
</div>
<div class="card-footer text-muted d-grid d-md-flex">
	<div class="col-7 text-muted">
	{if $card.listInscrits}
	{$card.participantsMax - $inscFull} / {$card.participantsMax} personnes inscrites : {foreach $card.listInscrits as $inscrit}

{if $isAdmin}<a href="{$urlWithFilters}&act=adminRemove&targetName={$inscrit.name}&targetEmail={$inscrit.email}&id={$card.cardId}">{/if}{if $card.inscMe && $inscrit@first}<b>{$inscrit.name}</b>{else}{$inscrit.name}{/if}{if not $inscrit@last},  {/if}{if $isAdmin}</a>{/if}{/foreach}
	{else}
	Créneau ouvert avec {$card.participantsMax} places
	{/if}
	</div>
	<div  class="col-3 text-muted">
	{if $card.listAttenteInscrits}<b>{count($card.listAttenteInscrits)}</b> personne{if count($card.listAttenteInscrits) > 1}s{/if} en liste d'attente{if $card.wlMe} <b> dont vous</b>{/if}.
	{else}Pas de liste d'attente{/if}
	</div>
	{if $GP_name}
{if $card.wlMe}
	<a role="button" class="btn btn-warning btn-sm col-2" href="{$urlWithFilters}&id={$card.cardId}&act=waitingListRemove">Se retirer de la liste</a>
{elseif $card.inscMe}
	<a role="button" class="btn btn-success btn-sm col-2" href="{$urlWithFilters}&id={$card.cardId}&act=remove">Se désinscrire</a>
{elseif $inscFull <= 0}
	<a role="button" class="btn btn-primary btn-sm col-2" href="{$urlWithFilters}&id={$card.cardId}&act=waitingListAdd">S'ajouter à la liste</a>
{else}
	<a role="button" class="btn btn-primary btn-sm col-2" href="{$urlWithFilters}&id={$card.cardId}&act=add">S'inscrire</a>
{/if}
	{else}
	<a role="button" class="btn btn-secondary btn-sm col-2" disabled>S'inscrire</a>
	{/if}
  </div>
<div>

</div>
</div>
</li>