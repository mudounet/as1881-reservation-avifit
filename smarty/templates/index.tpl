{$urlWithFilters=$loginURL}
{foreach $listFilters as $filter}{if $filter.actif}{$urlWithFilters=$urlWithFilters|cat:'&'|cat:$filter.categorie|cat:'=hide'}{/if}{/foreach}
<html>
	<head>
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.3/jquery.min.js"></script>
		<title>AS1881 - Séances d'Avifit BETA</title>
		<meta charset="UTF-8">
		<meta name="description" content="AS1881 - Séances d'Avifit BETA">
		<meta name="keywords" content="AS881, Avifit, Réservation, Strasbourg, Aviron, Aviron Strasbourg, Aviron Strasbourg 1881">
		<meta name="author" content="Alexis JENNY">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
	</head>
		<link rel="stylesheet" type="text/css" href="styles.css" />
		<script>
			history.replaceState('', 'AS1881 - Avifit - {$GP_name}', ' {$loginURL} ');
		</script>
	</head>
	<header>
		<img class="header-logo" src="logo-as1881.svg" alt="logo-avifit"/>
	</header>
	<body>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>
{if isset($error_subscribe_db_message)}
<div class="alert alert-danger" role="alert">Erreur fatale : {$error_subscribe_db_message}</div>
{elseif isset($error_wait_list_db_message)}
<div class="alert alert-danger" role="alert">Erreur fatale : {$error_wait_list_db_message}</div>
{elseif isset($error_user_message)}
<div class="content">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Vous avez fait une bétise</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>{$error_user_message}</p>
      </div>
    </div>
  </div>
</div>
{else}
		<div class="content">
			<div class="introduction">
				Bienvenue sur la page d'inscriptions aux sessions d'avifit du club Aviron Strasbourg 1881. <br/>
			</div>
			<div class="notice">
				Les séances sont listées ci-dessous avec l'heure du début :
					<ul>
						<li>Les séances durent 1 heure maximum</li>
						<li>Evitez de venir en retard, si vous n'êtes pas là au début, votre place pourra être donnée à un membre arrivant</li>
						<li>Les animateurs sont donnés à titre d'information et une modification peut-être apportée</li>
						<li>Les séances du mois suivant sont débloquées 15 jours à l'avance.</li>
					</ul>
				Le nombre de places restantes est indiqué à droite.<br/>
				<div class="table">
					<div class="table-row">
						<div class="check"><a href="javascript:void(0);" class="insc-insc" >S'inscrire</a></div>
						<div class="table-cell">S'il reste des places disponibles, ce bouton s'affiche, vous pouvez vous inscrire. Vous ne recevrez aucune confirmation par mail, le tout est instantanée.</div>
					</div>
					<div class="table-row">
						<div class="check"><a href="javascript:void(0);" class="insc-desinsc" >Se désinscrire</a></div>
						<div class="table-cell">Une fois inscrit, vous pouvez vous désinscrire si vous ne pouvez pas venir à la séance, libérant une place pour un autre membre. <br/><b>Attention : </b> Si vous ne vous désinscrivez pas d'une session que vous allez manquer, l'administrateur peut vous désinscrire des séances suivantes, libérant des places pour d'autres membres.</div>
					</div>
					<div class="table-row">
						<div class="check"><a href="javascript:void(0);" class="insc-listeattente" >S'inscrire sur la <br/>Liste d'attente</a></div>
						<div class="table-cell">Si une session est complète, vous pouvez vous mettre en liste d'attente et vous recevrez un mail lorsqu'une place se libère</div>
					</div>
					<div class="table-row">
						<div class="check"><a href="javascript:void(0);" class="insc-listeattente-me" >Se retirer de la <br/> Liste d'attente</a></div>
						<div class="table-cell">Si vous ne désirez plus être prévenu qu'une place se libère, vous pouvez cliquer sur ce bouton pour vous désinscrire des notifications pour cette session</div>
					</div>
				</div>
			</div>
			<hr class="fancy-line"/>
			<div class="inscription">
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
			<div class="inscription-passe">
			{$last_month=""}
			{$last_day=""}
			{$last_year=""}
			{foreach $dateDisplay as $card}
				{$inscFull=$card.participantsMax - count($card.listInscrits)}
				{if $last_month ne $card.moisFR}<div class="annee">{$card.moisFR} {$card.annee}</div>{$last_month=$card.moisFR}{/if}
				{if $last_day ne $card.dateJour}<div class="annee"><b>{$card.jourFR|upper|truncate:3:""} {$card.dateJour}</b></div>{$last_day=$card.dateJour}{/if}
			<div class="card {$card.categorie}">
							<div class="cell date">{if $card.animateur ne ''}<span class="animateur">Animé par <b>{$card.animateur}</b><br/></span>{/if}
								<span class="heure-debut">{$card.heureDebut}</span> - <span class="heure-fin">{$card.heureFin}<span>
							</div>
							<div class="cell inscrits">
							{foreach $card.listInscrits as $inscrit}{if $isAdmin}<a href="{$urlWithFilters}&act=adminRemove&targetName={$inscrit.name}&targetEmail={$inscrit.email}&id={$card.cardId}">{/if}{if $card.inscMe && $inscrit@first}<b>{$inscrit.name}</b>{else}{$inscrit.name}{/if}{if not $inscrit@last},  {/if}{if $isAdmin}</a>{/if}{foreachelse}Créneau ouvert{/foreach}
							{if $card.listAttenteInscrits}<br/><span>Liste attente ({count($card.listAttenteInscrits)}) : {foreach $card.listAttenteInscrits as $wlInscrit}{if $isAdmin}<a href="{$urlWithFilters}&act=adminRemove&targetName={$wlInscrit.name}&targetEmail={$wlInscrit.email}&id={$card.cardId}">{/if}{if $card.inscMe}<b>{$wlInscrit.name} | </b>{else}{$wlInscrit.name}{/if}{if $isAdmin}</a>{/if}{/foreach}</span>{/if}
							</div>
							<div class="cell places">{$inscFull}/{$card.participantsMax}<br/> <span class="text-places-restantes">places restantes</span></div>
							<div class="cell check">{if $GP_name}
								<a href="{$urlWithFilters}&id={$card.cardId}&act=
								{if	$card.wlMe}waitingListRemove" class="insc-listeattente-me">Se retirer de <br/>Liste d'attente</a>
								{elseif	$card.inscMe}remove" class="insc-desinsc">Se désinscrire</a>
								{elseif	$inscFull <= 0}waitingListAdd" class="insc-listeattente">S'inscrire sur <br/>Liste d'attente</a>
								{else}add" class="insc-insc">S'inscrire</a>
								{/if}
							{else}&nbsp;
							{/if}
							</div>
						</div>
			{foreachelse}
			<div>Les filtres sont trop restrictifs : il n'y a rien à afficher.</div>
			{/foreach}
			</div>
		</div>
{/if}
	</body>
	<footer>
	</footer>
</html>