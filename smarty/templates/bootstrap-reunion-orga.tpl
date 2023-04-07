<li>
	<time class="cbp_tmtime" datetime="2017-11-04T03:45">{if $displayDate}<span>{$card.jourFR|upper|truncate:3:""} {$card.dateJour}</span>{/if}<span>{$card.heureDebut} - {$card.heureFin}</span></time>
	<div class="cbp_tmicon"></div>
	<div class="cbp_tmlabel">
		<div class="card-body">
			<h5 class="card-title">{$card.titre}<span class="badge rounded-pill bg-secondary">Organisation</span></h5>
		</div>
	</div>
</li>