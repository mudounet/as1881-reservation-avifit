{$urlWithFilters=$loginURL}
{foreach $listFilters as $filter}{if $filter.actif}{$urlWithFilters=$urlWithFilters|cat:'&'|cat:$filter.categorie|cat:'=hide'}{/if}{/foreach}
<html>
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
			history.replaceState('', 'AS1881 - Fil des évènements - {$GP_name}', ' {$loginURL} ');
			
			function sendRequest(button) {
				// Get the button element
				var button = $(button);
				var id = button.data('id');
				var operation = button.data('op');

				// Disable the button
				button.prop('disabled', true);

				// Add spinner to the button
				button.html('<span class="spinner-border spinner-border-sm" role="status"></span> chargement...');

				// Send the request to the server
				$.ajax({
					url: "{$loginURL}&id=" + id + "&act=" + operation,
					success: function(response) {
						// Change the button text and remove the spinner
						button.html('Opération réussie');
						button.removeClass('btn-primary').addClass('btn-success');
					},
					error: function(response) {
						// Change the button text and remove the spinner
						button.html(response.responseText);
						button.removeClass('btn-primary').addClass('btn-danger');

						// Enable the button after a delay
						setTimeout(function() {
							button.prop('disabled', false);
							button.html('Réessayer');
							button.removeClass('btn-danger').addClass('btn-primary');
						}, 3000); // 3 seconds
					}
				});
}
		</script>
	</head>
	<body>

{include file='bootstrap-header.tpl'}
<main>
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
			<div class="card container-fluid d-none">
				<div class="form {$unlockStyle}" id="anchor-form">
					<form action="{$urlWithFilters}" method="post">
{if $GP_name}
Bienvenue <b>{$GP_name}</b> ({$GP_email}) ! <br/> <br/>
								Voici votre lien de connexion rapide, gardez-le en favoris :<br/> <a href="{$loginURL}">{$loginURL}</a><br/><br/>
								<a href="{$baseURL}">Se déconnecter</a>
{if $isAdmin}
<br/><br/><b>Vous êtes administrateur ! Vous pouvez supprimer des personnes dans les listes</b> !
{/if}
{else}
								<input name="name" placeholder="Nom" value="{$GP_name}" />
								<input name="email" type="email" placeholder="E-mail" value="{$GP_email}"/>
								<input type="submit" value="S'authentifier"/>
{/if}					</form>
<div class="control-panel">{$oneFilterActive=0}{foreach $listFilters as $filter}{if $filter.actif}{$oneFilterActive=$oneFilterActive+1}<a href="{$urlWithFilters}&{$filter.categorie}=" class="filter-hidden">{$filter.text}</a>{else}<a href="{$urlWithFilters}&{$filter.categorie}=hide" class="filter-shown">{$filter.text}</a>{/if}{/foreach}
{if $oneFilterActive > 0}<a href="{$loginURL}" >Réinitialiser les {$oneFilterActive} filtre(s)</a>{/if}</div>
				</div>
			</div>
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
			{if $card.categorie eq 'CAT_AFT'}
{include file='bootstrap-seance-avifit.tpl'}
			{elseif $card.categorie eq 'CAT_TNK'}
{include file='bootstrap-seance-tank.tpl'}
			{elseif $card.categorie eq 'CAT_CMT'}
{include file='bootstrap-comite.tpl'}
			{elseif $card.categorie eq 'CAT_ORG'}
{include file='bootstrap-reunion-orga.tpl'}
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