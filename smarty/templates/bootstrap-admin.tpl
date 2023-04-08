<div class="offcanvas offcanvas-start" tabindex="-1" id="addEvent" aria-labelledby="addEvent">
	<div class="offcanvas-header">
		<h5 class="offcanvas-title">Ajouter un nouvel évènement</h5>
		<button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
	</div>
	<div class="offcanvas-body">
		<form class="form" method="post" action="{$urlWithFilters}">
			<div class="mb-3">
				<label for="categorie" class="form-label">Catégorie:</label>

				<select class="form-select" aria-label="Catégorie" name="cat" required>
					<option selected value="">Sélectionner la catégorie</option>
					{foreach $listFilters as $filter}
					<option value="{$filter.categorie}">{$filter.text}</option>
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
				<label for="endTime" class="form-label">Nombre de places minimum (-1 ou 0 = illimité):</label>
				<input type="number" name="placesMin" disabled />
			</div>
			<div class="mb-3">
				<label for="endTime" class="form-label">Nombre de places maximum (-1 ou 0 = illimité):</label>
				<input type="number" name="places" />
			</div>
			<div class="mb-3">
				<label for="endTime" class="form-label">Personne(s) à contacter (avec des "," comme séparateur):</label>
				<input type="text" name="referee" />
			</div>
			<div class="mb-3">
				<label for="exampleFormControlInput1" class="form-label">Titre de l'évènement</label>
				<input type="text" class="form-control" placeholder="Titre" name="title" required />
			</div>
			<div class="mb-3">
				<label for="exampleFormControlTextarea1" class="form-label">Description:</label>
				<textarea class="form-control" rows="5" name="desc" required></textarea>
			</div>
			<input type="hidden" id="act" name="act" value="event_add" />
			<input type="submit" value="Valider les informations" class="btn btn-primary" />
		</form>
	</div>
</div>
