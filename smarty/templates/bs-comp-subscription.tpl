{if isset($card.places_min) && $card.places_min > 0}
	{$inscMin=$card.places_min - count($card.listInscrits)}
{else}
	{$inscMin=null}
{/if}
{if isset($card.places_max) && $card.places_max > 0}
	{$inscMax=$card.places_max - count($card.listInscrits)}
{else}
	{$inscMax=null}
{/if}
{if isset($inscMin) || isset($inscMax)}
<div class="card-footer text-muted d-flex flex-row" id="event_{$card.cardId}">
	<div class="align-self-stretch flex-fill me-auto">
	{if $card.listInscrits}
	{if isset($inscMin) && $inscMin > 0}
		<span class="badge bg-danger" role="alert">Manque {$inscMin} inscription{if $inscMin > 1}s{/if} avant validation</span>
	{elseif isset($inscMin)}
		<span class="badge bg-success" role="alert">Créneau validé</span>
	{/if}
	{if isset($inscMax)}
		{count($card.listInscrits)} / {$card.places_max}
	{else}
		{count($card.listInscrits)}
	{/if} personnes inscrites : 
		
	{foreach $card.listInscrits as $inscrit}

{if $isAdmin}<a href="actions.php?act=adminRemove&targetId={$inscrit.id}&id={$card.cardId}">{/if}{if $card.inscMe && $inscrit@first}<b>{$inscrit.name}</b>{else}{$inscrit.name}{/if}{if $isAdmin}</a>{/if}{if not $inscrit@last}, {/if}{/foreach}
	{elseif isset($inscMin)}
	<div class="badge bg-danger" role="alert">Validation du créneau: il manque {$inscMin} personne{if $inscMin > 1}s{/if}</div>
	{elseif isset($inscMax)}
	Créneau ouvert avec {$card.places_max} places
	{else}
	Créneau ouvert
	{/if}
	</div>
	{if $card.listAttenteInscrits}
		<div><b>{count($card.listAttenteInscrits)}</b> personne{if count($card.listAttenteInscrits) > 1}s{/if} en liste d'attente{if $card.wlMe} <b> dont vous</b>{/if}{if isset($card.opening_date) && $card.opening_date}</br>Attribution {$card.opening_date}{/if}</div>
	{/if}
	{if !$GP_name}
		<button class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#preLoginWarning" aria-controls="preLoginWarning">Se connecter</button>
	{elseif $card.wlMe || $card.inscMe}
		<button class="btn btn-warning" onclick="sendRequest(this)" data-id="{$card.cardId}" data-op="remove">Annuler</button>
	{elseif isset($card.event_full) && $card.event_full}
		<button class="btn btn-primary" disabled>Pas de place</button>
	{else}
		<button class="btn btn-primary" onclick="sendRequest(this)" data-id="{$card.cardId}" data-op="add">S'engager</button>
	{/if}
</div>
{/if}