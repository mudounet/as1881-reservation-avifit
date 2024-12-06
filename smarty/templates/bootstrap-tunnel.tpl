<!DOCTYPE html>
<html lang="fr">
	<head>
		<title>Tunnel de confirmation</title>
		<meta charset="UTF-8">
		<meta name="description" content="AS1881 - Fil des évènements">
		<meta name="keywords" content="AS881, Avifit, Réservation, Strasbourg, Aviron, Aviron Strasbourg, Aviron Strasbourg 1881">
		<meta name="author" content="Guillaume MANCIET">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
		<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
		<script>
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
					url: "actions.php?id=" + id + "&act=" + operation,
					success: function(response) {
						// Change the button text and remove the spinner
						button.html("Opération réussie... Patienter...");
						button.removeClass('btn-primary').addClass('btn-success');
						setTimeout(function() {
							window.location.href = "index.php#event_" + id;
						}, 2000); // 2 secondes
					},
					error: function(response) {
						// Change the button text and remove the spinner
						button.html(response.responseText);
						button.removeClass('btn-primary').addClass('btn-danger');

						// Enable the button after a delay
						setTimeout(function() {
							window.location.href = "index.php#event_" + id;
						}, 5000); // 5 secondes
					}
				});
}
		</script>
	</head>
	<body>
    <div class="container mt-5">
{if isset($main_message)}
        <h4>{$main_message}:</h4>
{foreach $list_conditions as $condition}
        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" id="case{$condition@iteration}">
            <label class="form-check-label" for="case{$condition@iteration}">{$condition}</label>
        </div>
{/foreach}
{/if}
		<button class="btn btn-secondary" onclick="history.back()">Annuler</button>
        <button id="boutonAction" class="btn btn-primary" disabled type="submit" onclick="sendRequest(this)" data-id="{$id}" data-op="{$action}">{$button_message}</button>
	</body>

	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>
	<script>
	        // Récupérer les cases à cocher
        const checkboxes = document.querySelectorAll('.form-check-input');

        // Récupérer le bouton d'action
        const boutonAction = document.getElementById('boutonAction');

        // Écouter les événements de clic sur les cases
		checkboxes.forEach(checkbox => checkbox.addEventListener('click', checkButtonState));
		
		checkButtonState();

        // Fonction pour vérifier l'état du bouton
        function checkButtonState() {
            const allChecked = [...checkboxes].every(checkbox => checkbox.checked);
            // Vérifier si au moins deux cases sont cochées
            boutonAction.disabled = !allChecked && checkboxes.length > 0;
        }
	</script>
</html>