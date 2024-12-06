<div class="position-absolute top-0 end-0 d-grid gap-2 d-sm-flex p-2">
	{if $isAdmin}<button type="button" class="btn btn-outline-light btn-sm"data-bs-toggle="offcanvas" data-bs-target="#editEvent" aria-controls="editEvent" onclick="editEvent(this)" data-id="{$card.cardId}"><i class="bi bi-pencil-square"></i> Éditer</button>{/if}
	{if isset($card.places_max) && $card.places_max > 0}<a type="button" href="/stats.php?id={$card.cardId}" target="_blank" class="btn btn-light btn-sm"><i class="bi bi-info-lg"></i>{/if}</a>
</div>