
<li>
	<time class="cbp_tmtime" datetime="2017-11-04T03:45">{if $displayDate}<span>{$card.jourFR|upper|truncate:3:""} {$card.dateJour}</span>{/if}<span>{$card.heureDebut} - {$card.heureFin}</span></time>
	<div class="cbp_tmicon bg-green"> <i class="zmdi zmdi-settings zmdi-hc-spin"></i></div>
	<div class="cbp_tmlabel secondary-event">
		<div class="card-body">
			<h5 class="card-title">{$card.titre} <span class="badge rounded-pill bg-secondary">Organisation</span></h5>
			{if $card.referent ne ''}<h6 class="card-subtitle mb-2 text-muted">Contacter <b>{$card.referent}</b> pour plus d'informations</h6>{/if}
			{if $card.description && $card.description ne ''}<p>{$card.description}</p>{/if}
		</div>
	</div>
</li>