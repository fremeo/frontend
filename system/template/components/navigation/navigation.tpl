{function name=fremeo_frontend_navigation_items nodes=[] vertical=false depth=0 language="DE"}
	{foreach $nodes as $node}
		<li class="{if $vertical}mb-2{elseif $depth == 0}nav-item{if $node.Children|default:[]} dropdown{/if}{/if}">
			<a class="{if $vertical}link-body-emphasis text-decoration-none{elseif $depth == 0}nav-link{if $node.Children|default:[]} dropdown-toggle{/if}{else}dropdown-item{/if}"
				href="{$node.Url|escape}"{if !$vertical && $depth == 0 && $node.Children|default:[]} data-bs-toggle="dropdown" aria-expanded="false"{/if}
				{if ($node.Target|default:'_self') == '_blank'} target="_blank" rel="noopener noreferrer"{/if}>
				{if is_array($node.Title)}
					{if isset($node.Title[$language])}{$node.Title[$language]|escape}{else}{foreach $node.Title as $title}{$title|escape}{break}{/foreach}{/if}
				{else}{$node.Title|escape}{/if}
			</a>
			{if $node.Children|default:[]}
				<ul class="{if $vertical}list-unstyled ps-3 mt-2{elseif $depth == 0}dropdown-menu position-absolute{else}list-unstyled ps-3{/if}">
					{call name=fremeo_frontend_navigation_items nodes=$node.Children vertical=$vertical depth=$depth+1 language=$language}
				</ul>
			{/if}
		</li>
	{/foreach}
{/function}
{if $data.Active|default:false}
	<ul class="{if $options.variant == 'vertical'}list-unstyled mb-0{else}navbar-nav flex-row flex-wrap column-gap-3 me-auto{/if}">
		{call name=fremeo_frontend_navigation_items nodes=$data.Tree|default:[] vertical=($options.variant == 'vertical') language=$options.language}
	</ul>
{/if}
