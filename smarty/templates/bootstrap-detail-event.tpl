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
	</head>
	<body>
<main><table class="table">
  <thead>
    <tr>
      <th scope="col">#</th>
      <th scope="col">First</th>
      <th scope="col">Last</th>
      <th scope="col">Handle</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <th scope="row">1</th>
      <td>Mark</td>
      <td>Otto</td>
      <td>@mdo</td>
    </tr>
    <tr>
      <th scope="row">2</th>
      <td>Jacob</td>
      <td>Thornton</td>
      <td>@fat</td>
    </tr>
    <tr>
      <th scope="row">3</th>
      <td colspan="2">Larry the Bird</td>
      <td>@twitter</td>
    </tr>
  </tbody>
</table>
</main>
	<footer>
	</footer>
	</body>

	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>
</html>