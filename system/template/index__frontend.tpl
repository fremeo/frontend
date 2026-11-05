{*
	Layout des Frontends (nur Frontend-Seiten, nicht der Admin-Bereich). Einstellungen: $D.R.Frontend (system/index__frontend.php, default.config.php).
	Blöcke für andere Module/Templates: title, favicon, head (append), navigation_top, navigation_main, header, inner_body, footer, body_end.
*}
{block name="title"}{$D.R.Frontend.page_title|escape}{/block}

{block name="favicon"}{if $D.R.Frontend.favicon_url}<link rel="icon" href="{$D.R.Frontend.favicon_url|escape}">{/if}{/block}

{block name="head" append}
	{if $D.R.Frontend.meta_description}<meta name="description" content="{$D.R.Frontend.meta_description|escape}">{/if}
	{if $D.R.Frontend.css}<style>{$D.R.Frontend.css}</style>{/if}
	{$D.R.Frontend.head_html}
{/block}

{block name="body"}
<div class="bg-body text-body min-vh-100 d-flex flex-column" data-bs-theme="{$D.R.Frontend.theme}"{if $D.R.Frontend.theme == 'auto'} id="fe-theme-auto"{/if}>

	{block name="header"}
		<header id="header" class="bg-body border-bottom{if $D.R.Frontend.header_sticky} sticky-top{/if}">
			<nav class="navbar navbar-expand-lg">
				<div class="{$D.R.Frontend.layout_container}">
					<a class="navbar-brand" href="{$D.BasePath}">
						{if $D.R.Frontend.logo_url}<img src="{$D.R.Frontend.logo_url|escape}" alt="{$D.R.Frontend.site_title|escape}" height="32">{else}{$D.R.Frontend.site_title|escape}{/if}
					</a>
					<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#fe-nav" aria-controls="fe-nav" aria-expanded="false" aria-label="Menu"><span class="navbar-toggler-icon"></span></button>
					<div class="collapse navbar-collapse" id="fe-nav">
						<ul class="navbar-nav me-auto">{block name="navigation_main"}{/block}</ul>
						<div class="navbar-text">
							{block name="navigation_top"}
								{if $D.R.Frontend.show_login_links}
									{if $D.SESSION.UserId}
										<a href="{$D.BasePath}account" title="Account"><i class="fas fa-user-circle"></i></a>
										<a href="{$D.BasePath}logout" title="Logout"><i class="fas fa-sign-out-alt"></i></a>
									{else}
										<a href="{$D.BasePath}login" title="Login"><i class="fas fa-sign-in-alt"></i></a>
									{/if}
								{/if}
							{/block}
						</div>
					</div>
				</div>
			</nav>


			{*Navigationsbeispiel*}
			<div class="border-bottom mb-3">
					<nav class="navbar navbar-expand-lg navbar-light bg-white p-0 ">
						<div class="container">
							<button class="navbar-toggler mb-2" type="button" data-bs-toggle="collapse" data-bs-target="#navbarScroll" aria-controls="navbarScroll" aria-expanded="false" aria-label="Toggle navigation">
								<span>☰ Menu</span>
							</button>
							
							<div class="collapse navbar-collapse" id="navbarScroll">
								<ul class="navbar-nav me-auto my-2 my-lg-0 navbar-nav-scroll" style="--bs-scroll-height: 100px;">
																												<li class="nav-item dropdown ">
											<a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#">Artikel</a>
																							<ul class="dropdown-menu p-1" style="margin-top: -4px;" aria-labelledby="navbarScrollingDropdown">
													<li><a class="dropdown-item rounded-2" href="schalter">Schalter</a></li>
												</ul>
																					</li>
																			<li class="nav-item  ">
											<a class="nav-link " href="Industrie">Industrie</a>
																					</li>
																		
								</ul>								
							</div>

						</div>
					</nav>
				</div>

		</header>
	{/block}

	<main class="{$D.R.Frontend.layout_container} py-4 flex-grow-1">
		{block name="inner_body"}{/block}
	</main>

	{block name="footer"}
		<footer id="footer" class="border-top py-3 mt-4">
			<div class="{$D.R.Frontend.layout_container}">
				<p class="mb-0 text-body-secondary">{$D.R.Frontend.footer_text|escape}</p>
			</div>
		</footer>
	{/block}
</div>
{/block}

{block name="body_end"}
	{if $D.R.Frontend.theme == 'auto'}
		<script>document.getElementById('fe-theme-auto').dataset.bsTheme = matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';</script>
	{/if}
	{$D.R.Frontend.body_end_html}
{/block}
