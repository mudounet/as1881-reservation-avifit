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

		<style>
		{literal}
			* {font-family:Helvetica;}
			html {padding:0;margin:0;}
			body {background:#F0F0F0;padding:0;margin:0;background-image: linear-gradient(175deg,#F0F0F0 40%, #c92d39 40.1%,#c92d39 60%, #F0F0F0 60.1%);background-attachment: fixed;}
			header {text-align:center;font-weight:bold;padding:30px;color:#c92d39;font-size:20pt;}
			header .header-logo {margin:10px auto;height:300x;}
			a, a:active, a:visited {color:#c92d39;}
			.hidden {display:none;}
			.table	{display:table;}
			.table-row	{display:table-row;}
			.table-cell	{display:table-cell;}
			
			.content {width: 980px;margin : 10px auto;border: 4px solid #c92d39;padding: 20px 20px;border-top:0;border-bottom:0;border-radius:5px;box-sizing: border-box;background:#F9F9F9;box-shadow:1px 1px 3px #000;}
				.introduction {margin: 20px 0 20px 0;}
				.inscription {margin: 20px 0;}
				.form {padding:10px;text-align:center;}
				.form input {width:20%;border:2px solid #c92d39;padding:10px;background:#FFFFFF;color:#c92d39;border-radius:5px;}
				.form input[type=submit] {background:#c92d39;color:#FFFFFF;cursor:pointer;}
				.form input[type=submit]:hover {cursor:pointer;background:#FFFFFF;color:#c92d39;}
				
				.control-panel {text-align:left;font-size:8pt;margin-top:20px;border-top: 2px dashed #c92d39;padding-top:30px;}
				.control-panel a {margin-right:10px;padding:3px 7px;border-radius:5px;text-decoration:none;}						
					a.filter-shown, a.filter-shown:visited, a.filter-shown:active 	{border:1px solid #c92d39;font-weight:bold;background:#c92d39;color:white;}
					a.filter-hidden,a.filter-hidden:visited,a.filter-hidden:active	{border:1px solid grey;background:white;color:#c92d39;}
			
				.unlocked input {opacity:.3;border:0;background:#rgba:0000}
				
				.card {display:table;width:100%;margin:3px auto;border-spacing: 5px;border-collapse: separate;box-sizing: border-box;border-radius:5px;border:2px solid transparent;z-index:1;transition:all .5s}
					.cell 					{display:table-cell;padding:5px 2px;text-wrap:normal;word-wrap:break-word;vertical-align:middle;}
					.cell:not(:last-child) 	{border-right:2px solid #c92d39;}
					.date 					{width:20%;}
					.date .date-first-line	{font-size:80%;}
					.date .date-second-line	{}
					.inscrits 				{width:60%;font-size:80%;}
					.inscrits span.inscrit	{margin-left:5px;}
					.inscrits span.inscrit:not(:last-child) {border-right:1px solid grey;padding-right:5px;}
					.places 				{width:5%;font-weight:bold;text-align:center;padding:0 5px 0 5px;}
						.places .text-places-restantes {font-size:70%;color:grey;}
					.check 					{width:15%;font-size:90%;}
					.cmoi 					{font-weight:bold;color:white;background:#c92d39;padding:5px;border-radius:5px;}
					.check a, .check a:active,.check a:visited {display:block;background:#c92d39;border-radius:5px;color:white;text-align:center;padding:5px;text-decoration:none;font-weight:bold;border:1px solid transparent;}
					.check a.insc-insc {}
					.check a.insc-desinsc {background:white;color:#c92d39;border:1px solid #c92d39;font-size:8pt;}
					.check a.insc-listeattente {background:#5861D8;font-size:8pt;}
					.check a.insc-listeattente-me {background:white;border:1px solid #5861D8;color:#5861D8;font-size:8pt;}
					.check a:hover {transition: all .7s; box-shadow: inset -20em 0 0 0 #00000050;}
					
					.date-jour-type {font-weight:bold;}
					.date-jour		{font-weight:bold;color:#c92d39}
					.date-mois 		{font-weight:bold;color:#c92d39}						
					.date-annee		{}
					.date-heure		{font-weight:bold;color:#c92d39}
					
					.waitingListIntro {font-size:7pt;color:grey;}
					
					
				.card.Lundi		{}
				.card.Mercredi	{}
				.card.Vendredi	{}
				.card.type1		{background:transparent;}
				.card.type2		{background:#F0F0F0FA;}
				
				
				.card.inscFull 	{background:transparent;opacity:80%;}
				.card.inscMe 	{background:#c92d3905;border:2px solid #c92d3910; border-left:5px solid #c92d39;}
					
				.card:hover 							{transition: all .4s;border:2px solid #c92d39;border-left:5px solid #c92d39;transition: all .5s;}
				.card:not(.inscMe):hover				{border:2px solid #c92d39;}
					.card:hover .cell					{transition: all .4s}
					.card:hover	.cell:not(:last-child) 	{border-right:2px solid grey;}
				.card:hover .cell a,.card:hover .cell a:active, .card:hover .cell a:visited {}
				
			
				.notice {background:#F2F2F2;border:1px solid #000;border-radius:5px;margin:20px auto;padding:20px;box-sizing:border-box;font-size:80%;}
					.notice .table {display:table;width:100%;border-collapse: separate;box-sizing: border-box;border-spacing:10px;}
					.notice .check {display:table-cell;width:150px;border-collapse: separate;box-sizing: border-box;border-spacing:10px;}
					.notice .table-cell {font-size:90%;}

				
					
			hr.fancy-line {border: 0;height: 1px;}
			hr.fancy-line:before {top: -0.5em;height: 1em;}
			hr.fancy-line:after {content:'';height: 0.5em; top:1px;}
			hr.fancy-line:before, hr.fancy-line:after {content: '';position: relative; width: 100%;}
			hr.fancy-line, hr.fancy-line:before {background: radial-gradient(ellipse at center, rgba(0,0,0,0.1) 0%,rgba(0,0,0,0) 75%);}
			#bottom-line {margin-top:30px;}
		{/literal}{$listFiltersInCSS}{literal}
			@media screen and (max-width: 1080px) {
			  .content {width:100%;padding:5px;}
			  .content .form input {display:block;width:85%;margin:10px auto;}
			  .card {width:98%;}
			  .header-logo {height:100px;}
			  .inscrits {font-size:50%;}				
			}
		{/literal}
		</style>
		<script>
			history.replaceState('', 'AS1881 - Avifit - {$GP_name}', ' {$myURL}#anchor-form ');
		</script>			
	</head>
	<header> 			
		<img class="header-logo" src="logo.png" alt="logo-avifit"/>
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
						<li>Les séances du mois suivant sont débloquées le  {$dateDebloquante}</li>					
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
					<form action="/as1881-avifit/#anchor-form" method="post">
{if $GP_name}
Bienvenue <b>{$GP_name}</b> ({$GP_email}) ! <br/> <br/>
								Voici votre lien de connexion rapide, gardez-le en favoris :<br/> <a href="{$myURL}">{$myURL}</a><br/><br/>
								<a href="{$baseURL}">Se déconnecter</a>
{if $isAdmin}
<br/><br/><b>Vous êtes administrateur ! Vous pouvez supprimer des personnes dans les listes</b> !
{/if}
{else}
								<input name="name" placeholder="Nom" value="{$GP_name}" />
								<input name="email" type="email" placeholder="E-mail" value="{$GP_email}"/>
								<input type="submit" value="S'authentifier"/>

{/if}					</form>					
				
<div class="control-panel">Filtres : {$kCount} séances sont ouvertes aux inscriptions jusque <b> fin {$dateLimitMonthHuman}</b>. <br/><br/>

{foreach $listFilters as $filter}<a href="{$filter.url}" class="{$filter.class}">{$filter.text}</a>{/foreach}
{if $listFiltersI > 0}<a href="{$myURL}" >Réinitialiser les filtres</a>{/if}</div>
				</div>
				<!----
				<div class="card">
					<div class="cell date">Date</div>
					<div class="cell animateur">Animateur</div>
					<div class="cell inscrits">Liste des participants</div>
					<div class="cell check"></div>
				</div>	
				-->			
			</div>			
			
			<div class="inscription-passe">{$dateDisplay}</div>
			
		</div>
{/if}
	</body>
	
	<footer>
	</footer>
</html>