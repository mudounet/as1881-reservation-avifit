
<li>
	<time class="cbp_tmtime" datetime="2017-11-04T03:45">{if $displayDate}<span>{$card.jourFR|upper|truncate:3:""} {$card.dateJour}</span>{/if}<span>{$card.heureDebut} - {$card.heureFin}</span></time>
	<div class="cbp_tmicon bg-green"> <i class="zmdi zmdi-settings zmdi-hc-spin"></i></div>
	<div class="cbp_tmlabel organisation-event">
		{include file='bs-comp-disactivation.tpl'}
		{include file='bs-comp-edition.tpl'}
		<div class="card-body">
			<h5 class="card-title"><span class="badge bg-secondary">Organisation</span> {$card.title}</h5>
			{if $card.referee ne ''}<h6 class="card-subtitle mb-2 text-muted">Contacter <b>{$card.referee}</b> pour plus d'informations</h6>{/if}
			{if $card.description && $card.description ne ''}<p>{$card.description}</p>{/if}
		</div>
		{include file='bs-comp-subscription.tpl'}
	</div>
</li>