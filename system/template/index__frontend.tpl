{*
	Layout des Frontends. Theme-Assets und Farbschema liefert der Core ueber die Template-Kette.
	Bloecke: title, favicon, head, layout_header, navigation_top, navigation_main, header, layout_main, inner_body, footer, body_end.
*}
{block name="title"}{$D.C.Setting->get('fremeo/frontend', 'page_meta_title')|escape}{/block}
{block name="favicon"}{if $D.R.Frontend.favicon_url|default:''}<link rel="icon" href="{$D.R.Frontend.favicon_url|escape}">{/if}{/block}
{block name="head" append}
	{if $D.R.Frontend.meta_description|default:''}<meta name="description" content="{$D.R.Frontend.meta_description|escape}">{/if}
	{$D.R.Frontend.head_html|default:''}
{/block}

{block name="body"}
	{function name=frontend_menu nodes=[] footer=false depth=0}
		{foreach $nodes as $node}
			<li class="{if $footer}mb-2{elseif $depth == 0}nav-item{if $node.Children} dropdown{/if}{/if}">
				<a class="{if $footer}link-body-emphasis text-decoration-none{elseif $depth == 0}nav-link{if $node.Children} dropdown-toggle{/if}{else}dropdown-item{/if}"
					href="{$node.Url|escape}"{if !$footer && $depth == 0 && $node.Children} data-bs-toggle="dropdown" aria-expanded="false"{/if}
					{if $node.Target == '_blank'} target="_blank" rel="noopener noreferrer"{/if}>
					{if is_array($node.Title)}
						{if isset($node.Title[$D.R.Frontend.language])}{$node.Title[$D.R.Frontend.language]|escape}{else}{foreach $node.Title as $title}{$title|escape}{break}{/foreach}{/if}
					{else}{$node.Title|escape}{/if}
				</a>
				{if $node.Children}
					<ul class="{if $footer}list-unstyled ps-3 mt-2{elseif $depth == 0}dropdown-menu{else}list-unstyled ps-3{/if}">
						{frontend_menu nodes=$node.Children footer=$footer depth=$depth+1}
					</ul>
				{/if}
			</li>
		{/foreach}
	{/function}
	<div class="bg-body text-body min-vh-100 d-flex flex-column">
		{block name="layout_header"}
		<div id="frontend-topbar" class="bg-body-tertiary border-bottom sticky-top">
			<div class="{$D.R.Frontend.layout_container|default:'container'|escape} d-flex align-items-center justify-content-between gap-3 py-2">
				<a class="fw-semibold link-body-emphasis text-decoration-none text-truncate" href="{SCRIPT_NAME}">
					{$D.R.Frontend.site_title|default:$D.C.Setting->get('fremeo/frontend', 'page_meta_title')|escape}
				</a>
				<nav class="d-flex align-items-center gap-3 ms-auto flex-shrink-0" aria-label="Benutzer">
					{block name="navigation_top"}
						{if $D.R.Frontend.show_login_links|default:true}
							{if $D.SESSION.UserId|default:null}
								<a class="link-body-emphasis text-decoration-none" href="{SCRIPT_NAME}account" title="Mein Konto"><i class="fas fa-user-circle" aria-hidden="true"></i><span class="visually-hidden">Mein Konto</span></a>
								<a class="link-body-emphasis text-decoration-none" href="{SCRIPT_NAME}logout" title="Abmelden"><i class="fas fa-sign-out-alt" aria-hidden="true"></i><span class="visually-hidden">Abmelden</span></a>
							{else}
								<a class="link-body-emphasis text-decoration-none" href="{SCRIPT_NAME}login"><i class="fas fa-sign-in-alt" aria-hidden="true"></i> Anmelden</a>
							{/if}
						{/if}
					{/block}
				</nav>
			</div>
		</div>
		{block name="header"}
			<header id="header" class="bg-body border-bottom">
				<div class="{$D.R.Frontend.layout_container|default:'container'|escape} py-3">
					<form method="get" action="{SCRIPT_NAME}artikel" role="search" class="d-flex gap-2 mx-auto" style="max-width: 32rem;">
						<label class="visually-hidden" for="frontend-search">Produkte suchen</label>
						{input p=['type' => 'search', 'id' => 'frontend-search', 'name' => 'D[SEARCH]', 'value' => $D.SEARCH|default:''|escape, 'placeholder' => 'Produkte suchen']}
						<button class="btn btn-primary flex-shrink-0" type="submit" aria-label="Suchen" title="Suchen"><i class="fas fa-search" aria-hidden="true"></i></button>
					</form>
				</div>
				<nav class="navbar navbar-expand-lg" aria-label="Hauptnavigation">
					<div class="{$D.R.Frontend.layout_container|default:'container'|escape}">
						{if $D.R.Frontend.logo_url|default:''}<a class="navbar-brand" href="{SCRIPT_NAME}"><img src="{$D.R.Frontend.logo_url|escape}" alt="{$D.R.Frontend.site_title|default:''|escape}" height="32"></a>{/if}
						<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#fe-nav" aria-controls="fe-nav" aria-expanded="false" aria-label="Menue"><span class="navbar-toggler-icon"></span></button>
						<div class="collapse navbar-collapse" id="fe-nav">
							<ul class="navbar-nav me-auto">
								{block name="navigation_main"}
									{if $D.MODULE.D['fremeo/frontend'].NAVIGATION.D.top_navigation.Active|default:false}
										{frontend_menu nodes=$D.MODULE.D['fremeo/frontend'].NAVIGATION.D.top_navigation.Tree}
									{/if}
								{/block}
							</ul>
						</div>
					</div>
				</nav>
			</header>
		{/block}
		{/block}
		{block name="layout_main"}
		<main class="{$D.R.Frontend.layout_container|default:'container'|escape} py-4 flex-grow-1">
			{block name="inner_body"}{/block}
		</main>
		{/block}
		{block name="footer"}
			<footer id="footer" class="bg-body-tertiary border-top py-4 mt-4">
				<div class="{$D.R.Frontend.layout_container|default:'container'|escape} d-flex flex-column flex-md-row justify-content-between gap-4">
					<div class="text-body-secondary">
						{if $D.R.Frontend.footer_text|default:''}<p class="mb-2">{$D.R.Frontend.footer_text|escape}</p>{/if}
						<p class="mb-0">&copy; {date('Y')} BoBa</p>
					</div>
					<nav aria-label="Footer">
						<ul class="list-unstyled mb-0">
							{if $D.MODULE.D['fremeo/frontend'].NAVIGATION.D.footer_navigation.Active|default:false}
								{frontend_menu nodes=$D.MODULE.D['fremeo/frontend'].NAVIGATION.D.footer_navigation.Tree footer=true}
							{/if}
						</ul>
					</nav>
				</div>
			</footer>
		{/block}
	</div>
{/block}
{block name="body_end"}{$D.R.Frontend.body_end_html|default:''}{/block}
