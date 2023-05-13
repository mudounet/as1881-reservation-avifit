{if isset($card.participantsMax)}
{$inscFull=$card.participantsMax - count($card.listInscrits)}
<div class="card-footer text-muted d-flex flex-row">
	<div class="align-self-stretch flex-fill me-auto">
	{if $card.listInscrits}
	{$card.participantsMax - $inscFull} / {$card.participantsMax} personnes inscrites : {foreach $card.listInscrits as $inscrit}

{if $isAdmin}<a href="{$urlWithFilters}&act=adminRemove&targetName={$inscrit.name}&targetEmail={$inscrit.email}&id={$card.cardId}">{/if}{if $card.inscMe && $inscrit@first}<b>{$inscrit.name}</b>{else}{$inscrit.name}{/if}{if $isAdmin}</a>{/if}{if not $inscrit@last}, {/if}{/foreach}
	{else}
	Créneau ouvert avec {$card.participantsMax} places
	{/if}
	</div>
	{if $card.listAttenteInscrits}
	<div><b>{count($card.listAttenteInscrits)}</b> personne{if count($card.listAttenteInscrits) > 1}s{/if} en liste d'attente{if $card.wlMe} <b> dont vous</b>{/if}.</div>
	{/if} {if $GP_name} {if $card.wlMe}
	<button class="btn btn-warning" onclick="sendRequest(this)" data-id="{$card.cardId}" data-op="waitingListRemove">Se retirer de la liste</button>
	{elseif $card.inscMe}
	<button class="btn btn-success" onclick="sendRequest(this)" data-id="{$card.cardId}" data-op="remove">Se désinscrire</button>
	{elseif $inscFull<=0}
	<button class="btn btn-primary" onclick="sendRequest(this)" data-id="{$card.cardId}" data-op="waitingListAdd">S'ajouter à la liste</button>
	{else}
	<button class="btn btn-primary" onclick="sendRequest(this)" data-id="{$card.cardId}" data-op="add">S'inscrire</button>
	{/if} {else}
	<button class="btn btn-secondary" title="Il faut être connecté" data-bs-toggle="offcanvas" data-bs-target="#loginCanvas" aria-controls="loginCanvas">S'inscrire</button>
	{/if}
</div>
{/if}