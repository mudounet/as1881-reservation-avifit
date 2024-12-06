<!DOCTYPE html>
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
	</head>
	<body>
<main>
<table class="table" style="max-width: 100%; width: auto;">
  <thead>
    <tr>
      <th scope="col">#</th>
      <th scope="col">Date de réservation</th>
      <th scope="col">Nom</th>
      <th scope="col">Nbre de réservations</th>
	  <th scope="col">Resa'score</th>
    </tr>
  </thead>
  <tbody>
{foreach $users as $user}
    <tr class="{if $user.waiting_list}text-bg-warning{else}text-bg-success{/if} p-3">
      <th scope="row">{$user@iteration}</th>
      <td>{$user.subscription_date}</td>
      <td>{if $user.anonymise}???{else}{$user.display_name}{/if}</td>
      <td>{$user.total} / {$qty_seances}</td>
	  <td><img src="/img/nutriscore-{$user.resascore}.svg" alt="Note : {$user.total} / {$qty_seances}" width="80px"/></td>
    </tr>
{/foreach}
  </tbody>
</table>
</main>
	<footer>
	</footer>
	</body>

	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>
</html>