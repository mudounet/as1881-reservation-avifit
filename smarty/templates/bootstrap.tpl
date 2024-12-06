<!DOCTYPE html>
{$urlWithFilters=$loginURL}
{foreach $listFilters as $filter}{if $filter.actif}{$urlWithFilters=$urlWithFilters|cat:'&'|cat:$filter.categorie|cat:'=hide'}{/if}{/foreach}
<html lang="fr">
	<head>
		<title>AS1881 - Fil des évènements</title>
		<meta charset="UTF-8">
		<meta name="description" content="AS1881 - Fil des évènements">
		<meta name="keywords" content="AS881, Avifit, Réservation, Strasbourg, Aviron, Aviron Strasbourg, Aviron Strasbourg 1881">
		<meta name="author" content="Alexis JENNY, Guillaume MANCIET">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/material-design-iconic-font/2.2.0/css/material-design-iconic-font.min.css">
		<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
		<link rel="stylesheet" type="text/css" href="timeline.css" />
		<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

		<script>
			function sendRequest(button) {
				// Get the button element
				var button = $(button);
				var id = button.data('id');
				var operation = button.data('op');

				// Disable the button
				button.prop('disabled', true);
				
				window.location.href = "tunnel.php?id=" + id + "&act=" + operation;
				return false;
			}
		</script>
	</head>
	<body>
<div class="toast-container position-fixed bottom-0 end-0 p-3">
	<div class="toast align-items-center text-bg-primary border-0 bg-success" role="alert" aria-live="assertive" aria-atomic="true" data-delay="4000" id="refresh_proposal">
		<div class="d-flex">
			<div class="toast-body">L'opération demandée est un succès... Vous pouvez rafraîchir la page une fois que vous avez terminé...</div>
			<button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
		</div>
	</div>
</div>
{include file='bootstrap-header.tpl'}
<main>
<!-- Modal for information on how to connect -->
<div class="modal fade" id="preLoginWarning" tabindex="-1" aria-labelledby="preLoginWarningLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="preLoginWarningLabel">Connexion requise</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>Vous devez être connectés pour terminer cette opération. Une fois connecté, il faudra rééssayer pour que l'action désirée soit prise en compte...</p>
		<p>Pour votre information, l'icône pour se connecter est <strong>située en haut à droite</strong>...</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Laissez-moi essayer</button>
      </div>
    </div>
  </div>
</div>
<div class="container">

{if isset($error_user_message) or isset($error_wait_list_db_message) or isset($error_subscribe_db_message)}
<div class="content">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">{if $error_user_message}Vous avez fait une bétise{else}Le site n'est vraiment pas au point{/if}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>{$error_user_message}{$error_wait_list_db_message}{$error_subscribe_db_message}</p>
		<p>Merci de signaler ce problème s'il continue à se produire...</p>
      </div>
    </div>
  </div>
</div>
{else}
			<div>
<div class="row">
<div class="col-md-10">
<ul class="cbp_tmtimeline">
<li>
<time class="cbp_tmtime" datetime="2017-11-04T18:30"></time>
<div class="cbp_tmiconsmonth">&nbsp;</div>
<div class="cbp_tmlabel today-event">Aujourd'hui, nous sommes le <b>{$todayDateFr}</b> (Heure de Strasbourg)</div>
</li>
			{$last_month=""}
			{$last_day=""}
			{$last_year=""}
			{foreach $dateDisplay as $card}
				{$displayDate=false}
				{if $last_month ne $card.moisFR or $last_year ne $card.annee}<li>
<time class="cbp_tmtime" datetime="2017-11-04T18:30"><span class="month">{$card.moisFR} {$card.annee}</span></time>
<div class="cbp_tmiconsmonth">&nbsp;</div>
<div class="cbp_tmlabel month">&nbsp;</div>
</li>
				{$last_month=$card.moisFR}{$last_year=$card.annee}{/if}
				{if $last_day ne $card.dateJour}{$displayDate=true}{$last_day=$card.dateJour}{/if}

			{if $card.categorie eq 'CAT_AFT'}{include file='bootstrap-seance-avifit.tpl'}
			{elseif $card.categorie eq 'CAT_AVR_LSR'}{include file='bootstrap-seance-aviron-loisir.tpl'}
			{elseif $card.categorie eq 'CAT_TNK'}{include file='bootstrap-seance-tank.tpl'}
			{elseif $card.categorie eq 'CAT_CMT'}{include file='bootstrap-comite.tpl'}
			{elseif $card.categorie eq 'CAT_ORG'}{include file='bootstrap-reunion-orga.tpl'}
			{elseif $card.categorie eq 'CAT_EVT_CLB'}{include file='bootstrap-evenement-club.tpl'}
			{elseif $card.categorie eq 'CAT_EVT_EXT'}{include file='bootstrap-evenement-externe.tpl'}

			{else}
			<div class="alert alert-danger" role="alert">La catégorie suivante est inconnue du template : {$card.categorie}. Merci de signaler cette erreur afin que l'on puisse la corriger pour la prochaine fois...</div>
			{/if}
			{foreachelse}
			<div>Les filtres sont trop restrictifs : il n'y a rien à afficher.</div>
			{/foreach}
</ul>
</div>
</div>
{/if}
</div>
</main>
	<footer>
	</footer>
	</body>

	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>
</html>