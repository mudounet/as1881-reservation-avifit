<li>
<time class="cbp_tmtime" datetime="2017-11-04T03:45">{if $displayDate}<span>{$card.jourFR|upper|truncate:3:""} {$card.dateJour}</span>{/if}<span>{$card.heureDebut} - {$card.heureFin}</span></time>
<div class="cbp_tmicon"></div>
<div class="cbp_tmlabel tank-a-ramer-event">

		{include file='bs-comp-disactivation.tpl'}
		{include file='bs-comp-edition.tpl'}
<h5 class="card-title"><span class="badge bg-secondary">Officiel</span> Séance au tank à ramer </h5>{if $card.referee ne ''}<h6 class="card-subtitle mb-2 text-muted">Animée par <b>{$card.referee}</b></h6>{/if}
<div class="card-body d-grid d-md-flex">
<div class="col-9">

<p>Le centre nautique se trouve au 9 rue de Turenne, 67300 Schiltigheim, France. Le tank à ramer se trouve immédiatement à droite après être entré.</p>
<p>Important : prévenir <b>{if $card.referee ne ''}{$card.referee}{else}???{/if}</b> en cas d'arrivée après 19h.</p>
</div>
<iframe src="https://www.openstreetmap.org/export/embed.html?bbox=7.728305160999299%2C48.61171777423268%2C7.730606496334077%2C48.613001674268446&amp;layer=mapnik&amp;marker=48.61235972833147%2C7.729455828666687" frameborder="0" class="rounded col-3" allowfullscreen=""></iframe>
</div>
</li>