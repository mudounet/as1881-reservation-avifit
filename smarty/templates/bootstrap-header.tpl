<nav class="navbar navbar-expand-lg bd-navbar sticky-xl-top">
	<div class="container-fluid">
		<a class="navbar-brand" href="#">
			<img src="./img/logo-as1881.svg" height="50" aria-label="AS1881" alt="AS1881" />
		</a>
		<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
			<span class="navbar-toggler-icon"></span>
		</button>
		<div class="collapse navbar-collapse" id="navbarSupportedContent">
			<ul class="navbar-nav me-auto mb-2 mb-lg-0">
				<li class="nav-item">
					{if $GP_name}
					<p>Bienvenue <b>{$GP_name}</b> ({$GP_email}) !{if $isAdmin} <i class="bi bi-star-fill"></i>{/if}</p>
					<p>Lien à enregistrer: <a href="{$loginURL}">{$loginURL}</a></p>
					{else}
					<a class="nav-link active" aria-current="page" href="#">Fil des évènements de l'AS 1881</a>
					{/if}
				</li>
			</ul>

			<hr class="d-lg-none text-white-50" />

			<ul class="navbar-nav flex-row flex-wrap ms-md-auto">
				<li class="nav-item col-6 col-lg-auto">
					<button type="button" class="btn btn-link nav-link py-2 px-0 px-lg-2 d-flex" data-bs-toggle="offcanvas" data-bs-target="#filterCanvas" aria-controls="filterCanvas">
						<i class="bi bi-funnel"></i>
						<span class="d-lg-none ms-2">Filtrer l'affichage</span>
					</button>
				</li>
				{if $isAdmin}
				<li class="nav-item col-6 col-lg-auto">
					<button type="button" class="btn btn-link nav-link py-2 px-0 px-lg-2 d-flex" data-bs-toggle="offcanvas" data-bs-target="#addEvent" aria-controls="addEvent">
						<i class="bi bi-calendar-event"></i>
						<span class="d-lg-none ms-2">Ajouter des évènements</span>
					</button>
				</li>
				{/if}

				<li class="nav-item py-2 py-lg-1 col-12 col-lg-auto">
					<div class="vr d-none d-lg-flex h-100 mx-lg-2 text-white"></div>
					<hr class="d-lg-none my-2 text-white-50" />
				</li>

				<li class="nav-item">
					{if $GP_name}
					<a type="button" class="nav-link py-2 px-0 px-lg-2" aria-expanded="false" href="{$baseURL}">
						<i class="bi bi-box-arrow-right"></i>
						<span class="d-lg-none ms-2">Se déconnecter</span>
					</a>
					{else}
					<button type="button" class="btn btn-link nav-link py-2 px-0 px-lg-2 d-flex" data-bs-toggle="offcanvas" data-bs-target="#loginCanvas" aria-controls="loginCanvas">
						<i class="bi bi-box-arrow-in-right"></i>
						<span class="d-lg-none ms-2">Se connecter</span>
					</button>
					{/if}
				</li>
			</ul>
		</div>
	</div>
</nav>

{if $isAdmin}
{include file='bootstrap-admin.tpl'}
{/if}

<div class="offcanvas offcanvas-start" tabindex="-1" id="filterCanvas" aria-labelledby="filterCanvas">
	<div class="offcanvas-header">
		<h5 class="offcanvas-title">Désactiver l'affichage de...</h5>
		<button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
	</div>
	<div class="offcanvas-body">
		<form class="form" method="get" action="{$urlWithFilters}">
			{$oneFilterActive=0}{foreach $listFilters as $filter}
			<div class="form-check form-switch">
				<input class="form-check-input" type="checkbox" role="switch" name="{$filter.categorie}" {if $filter.actif}checked{$oneFilterActive=$oneFilterActive+1}{/if}>
				<label class="form-check-label">{$filter.text}</label>
			</div>
			{/foreach}
			<input type="submit" value="Valider les informations" class="btn btn-primary" />
			{if $oneFilterActive > 0}<a href="{$loginURL}" class="btn btn-warning">Réinitialiser les {$oneFilterActive} filtre(s)</a>{/if}
		</form>
	</div>
</div>

<div class="offcanvas offcanvas-start" tabindex="-1" id="loginCanvas" aria-labelledby="loginCanvas">
	<div class="offcanvas-header">
		<h5 class="offcanvas-title">Identification</h5>
		<button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
	</div>
	<div class="offcanvas-body">
		<form class="form">
			<div class="mb-3">
				<label class="form-label">Le nom qui apparaîtra aux autres</label>
				<input type="text" class="form-control" placeholder="Nom d'utilisateur" value="{$GP_name}" name="name" />
			</div>
			<div class="mb-3">
				<label class="form-label">L'email pour la liste d'attente</label>
				<input type="email" class="form-control" placeholder="Email" name="email" value="{$GP_email}" />
			</div>
			<input type="submit" value="Valider les informations" class="btn btn-primary" />
		</form>
	</div>
</div>
