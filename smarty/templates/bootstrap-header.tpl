<nav class="navbar navbar-expand-lg bd-navbar sticky-xl-top">
	<div class="container-fluid">
		<a class="navbar-brand" href="#">
			<img src="logo-as1881.svg" height="50" aria-label="AS1881" alt="AS1881" />
		</a>
		<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
			<span class="navbar-toggler-icon"></span>
		</button>
		<div class="collapse navbar-collapse" id="navbarSupportedContent">
			<ul class="navbar-nav me-auto mb-2 mb-lg-0">
				<li class="nav-item">
					{if $GP_name}
					<p>Bienvenue <b>{$GP_name}</b> ({$GP_email}) !</p>
					<p>Lien à enregistrer: <a href="{$loginURL}">{$loginURL}</a></p>
					{else}
					<a class="nav-link active" aria-current="page" href="#">Home</a>{/if}
				</li>
			</ul>
			<div class="btn-group" role="Outils" aria-label="Vertical button group">
				<div class="btn-group">
					<button type="button" class="btn btn-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
						<img src="./img/filter.svg" height="24"/>
					</button>
					<ul class="dropdown-menu">
						<li>
							<h6 class="dropdown-header">Affichage des catégories</h6>
						</li>
						<li>
							<a class="dropdown-item" href="#"><img src="./img/add.svg" height="20"/> Filtre 1</a>
						</li>
						<li>
							<a class="dropdown-item active" href="#"><img src="./img/remove.svg" height="20"/> Filtre 2</a>
						</li>
						<li>
							<hr class="dropdown-divider" />
						</li>
						<li>
							<a class="dropdown-item" href="#"> <img src="./img/filter-off.svg" height="20"/> Supprimer 4 filtres </a>
						</li>
					</ul>
				</div>
				{if $GP_name}
				<div class="btn-group" role="group">
					<button type="button" class="btn btn-primary" aria-expanded="false">
						<img src="./img/logout.svg"/>
					</button>
				</div>
				{else}
				<div class="btn-group" role="group">
					<button type="button" class="btn btn-primary" data-bs-toggle="offcanvas" data-bs-target="#offcanvasExample" aria-controls="offcanvasExample">
						<img src="./img/login.svg"/>
					</button>
				</div>
				{/if}
			</div>
		</div>
	</div>
</nav>

<div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasExample" aria-labelledby="offcanvasExample">
  <div class="offcanvas-header">
    <h5 class="offcanvas-title" id="offcanvasLabel">Identification</h5>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body">
<form class="form">
<div class="mb-3">
  <label for="formFile" class="form-label">Le nom qui apparaîtra aux autres</label>
  <input type="text" class="form-control" placeholder="Nom d'utilisateur" value="{$GP_name}" name="name" />
</div>
<div class="mb-3">
  <label for="formFile" class="form-label">L'email pour la liste d'attente</label>
  <input type="email" class="form-control" placeholder="Email" name="email" value="{$GP_email}" />
</div>
<input type="submit" value="Valider les informations" class="btn btn-primary"/>
				</form>
  </div>
</div>