{$inscFull=$card.participantsMax - count($card.listInscrits)}
<li>
<time class="cbp_tmtime" datetime="2017-11-04T03:45">{if $displayDate}<span>{$card.jourFR|upper|truncate:3:""} {$card.dateJour}</span>{/if}<span>{$card.heureDebut} - {$card.heureFin}</span></time>
<div class="cbp_tmicon bg-orange"></div>
<div class="cbp_tmlabel">

  <div class="card-body">
  <h5 class="card-title">Séance d'avifit <span class="badge rounded-pill bg-secondary">Avifit</span></h5>{if $card.referent ne ''}<h6 class="card-subtitle mb-2 text-muted">Animée par <b>{$card.referent}</b></h6>{/if}
    
	<p>Il est important de réserver sa place, de s'engager à venir, et de prévenir sur le groupe WhatsApp en cas d'impossibilité de dernière minute.</p>
</div>
<div class="card-footer text-muted d-flex flex-row">
	<div class=" align-self-stretch flex-fill me-auto">
	{if $card.listInscrits}
	{$card.participantsMax - $inscFull} / {$card.participantsMax} personnes inscrites : {foreach $card.listInscrits as $inscrit}

{if $isAdmin}<a href="{$urlWithFilters}&act=adminRemove&targetName={$inscrit.name}&targetEmail={$inscrit.email}&id={$card.cardId}">{/if}{if $card.inscMe && $inscrit@first}<b>{$inscrit.name}</b>{else}{$inscrit.name}{/if}{if not $inscrit@last},  {/if}{if $isAdmin}</a>{/if}{/foreach}
	{else}
	Créneau ouvert avec {$card.participantsMax} places
	{/if}
	</div>{if $card.listAttenteInscrits}
	<div>
	<b>{count($card.listAttenteInscrits)}</b> personne{if count($card.listAttenteInscrits) > 1}s{/if} en liste d'attente{if $card.wlMe} <b> dont vous</b>{/if}.
	</div>{/if}
	{if $GP_name}
{if $card.wlMe}
	<button class="btn btn-warning" onclick="sendRequest(this)" data-id="{$card.cardId}" data-op="waitingListRemove">Se retirer de la liste</button>
{elseif $card.inscMe}
	<button class="btn btn-success" onclick="sendRequest(this)" data-id="{$card.cardId}" data-op="remove">Se désinscrire</button>
{elseif $inscFull<=0}
	<button class="btn btn-primary" onclick="sendRequest(this)" data-id="{$card.cardId}" data-op="waitingListAdd">S'ajouter à la liste</button>
{else}
	<button class="btn btn-primary" onclick="sendRequest(this)" data-id="{$card.cardId}" data-op="add">S'inscrire</button>
{/if}
	</form>
	{else}
	<a role="button" class="btn btn-secondary" title="Il faut être connecté" data-bs-toggle="offcanvas" data-bs-target="#loginCanvas" aria-controls="loginCanvas">S'inscrire</a>
	{/if}
  </div>
<div>

</div>
</div>
</li>
