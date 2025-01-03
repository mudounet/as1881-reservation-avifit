<script>

function editEvent(button) {
	// Get the button element
	var button = $(button);
	var id = button.data('id');
	
	var oldHtml = $('#event-edit-body').html();
	
	// Add spinner to the form
	$('#event-edit-body').html('<span class="spinner-border spinner-border-sm" role="status"></span> chargement...');

	// Send the request to the server
	$.ajax({
		url: "actions.php?id=" + id + "&act=event_get_raw",
		success: function(response) {
			// Change the button text and remove the spinner
			
			$('#event-edit-body').html(oldHtml);
			var form = $('#form-fields');
			form.html('');
			
			button.html('Opération réussie');
			button.removeClass('btn-primary').addClass('btn-success');
			
			$.each(response, function(key, value) {
				// Create label and input
				
				var label = $('<div class="mb-3"><label class="form-label"></label>').text(key);
				if (key == 'CDATA') {
					var input = $('<textarea class="form-control" rows="3"></textarea></div>').attr('name', key).val(value);
				} else {
					var input = $('<input class="form-control"></input></div>').attr('name', key).attr('value', value);
				}

				// Add label and input to form
				form.append(label).append(input);
			});
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

<div class="offcanvas offcanvas-start" tabindex="-1" id="addEvent" aria-labelledby="addEvent">
	<div class="offcanvas-header">
		<h5 class="offcanvas-title">Ajouter un nouvel évènement</h5>
		<button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
	</div>
	<div class="offcanvas-body">
		<form class="form" method="post" action="actions.php">
			<div class="mb-3">
				<label for="categorie" class="form-label">Catégorie:</label>

				<select class="form-select" aria-label="Catégorie" name="cat" required>
					<option selected value="">Sélectionner la catégorie</option>
					{foreach $listCategories as $category}
					<option value="{$category.textual_id}">{$category.description}</option>
					{/foreach}
				</select>
			</div>
			<div class="mb-3">
				<label for="startDate" class="form-label">Date:</label>
				<input class="form-control" type="date" name="startDate" required />
			</div>
			<div class="mb-3">
				<label for="startTime" class="form-label">Début:</label>
				<input type="time" name="startTime" required />
			</div>
			<div class="mb-3">
				<label for="endTime" class="form-label">Fin:</label>
				<input type="time" name="endTime" required />
			</div>
			<div class="mb-3">
				<label for="places_min" class="form-label">Nombre de places minimum (-1 ou 0 = illimité):</label>
				<input type="number" name="places_min"/>
			</div>
			<div class="mb-3">
				<label for="places_max" class="form-label">Nombre de places maximum (-1 ou 0 = illimité):</label>
				<input type="number" name="places_max" />
			</div>
			<div class="mb-3">
				<label for="referee" class="form-label">Personne(s) à contacter (avec des "," comme séparateur):</label>
				<input type="text" name="referee" />
			</div>
			<div class="mb-3">
				<label for="attente" class="form-label">Liste d'attente:</label>
				<input type="checkbox" name="tense_activity" />
			</div>
			<div class="mb-3">
				<label for="exampleFormControlInput1" class="form-label">Titre de l'évènement</label>
				<input type="text" class="form-control" placeholder="Titre" name="title" required />
			</div>
			<div class="mb-3">
				<label for="exampleFormControlTextarea1" class="form-label">Description:</label>
				<textarea class="form-control" rows="5" name="desc" required></textarea>
			</div>
			<input type="hidden" name="act" value="event_add" />
			<input type="submit" value="Valider les informations" class="btn btn-primary" />
		</form>
	</div>
</div>

<div class="offcanvas offcanvas-start" tabindex="-1" id="editEvent" aria-labelledby="editEvent">
	<div class="offcanvas-header">
		<h5 class="offcanvas-title">Editer un évènement</h5>
		<button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
	</div>
	<div class="offcanvas-body" id="event-edit-body">
		<form class="form" method="post" action="actions.php">
			<div id="form-fields"></div>
			<input type="hidden" name="act" value="event_edit" />
			<input type="submit" value="Valider les informations" class="btn btn-primary" />
		</form>
	</div>
</div>