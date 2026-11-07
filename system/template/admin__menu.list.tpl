{*
	Menübaum einer Navigation: Punkte und Unterpunkte anlegen/entfernen, per Drag&Drop am Griff verschieben
	(oberes/unteres Viertel eines Ziels = davor/danach einfügen, Mitte = als Unterpunkt). Gespeichert wird EIN Feld mit JSON
	[{"Title":"..","Url":"..","Target":"_self|_blank","Children":[..]}], das die Klasse Frontend prüft (set_navigation).
	field = Feldname, json = aktueller Baum. Das Skript steht einmal am Ende der Seite.
*}
{function name=menu_tree field="" json="[]"}
	<div class="menu-tree" data-langs="{json_encode($D.R.Languages)|escape}"><input type="hidden" ischanged="0" name="{$D.C.Security->registerSecurityD($field)}" value="{if $json}{$json|escape}{else}[]{/if}"></div>
{/function}

{block name="inner_body"}
	<form method="post" id="frontend-menu-form">
		<input type="hidden" name="R[ACTION]" value="save">

		{foreach from=$D.NAVIGATION.D key="kNAV" item="NAV"}
			<div class="card mb-3 nav-card">
				<div class="card-header d-flex align-items-center gap-2">
					<i class="fas fa-bars"></i> <strong>{$NAV.Name|escape}</strong> <span class="badge text-bg-secondary">{$kNAV|escape}</span>
					<input type="hidden" class="nav-delflag" name="{$D.C.Security->registerSecurityD("D[NAVIGATION][D][{$kNAV}]")}" value="__DELETE__" disabled>
					<button type="button" class="btn btn-outline-danger btn-sm ms-auto nav-delete" title="Löschen (erst beim Speichern)" data-title="{$NAV.Name|default:$kNAV|escape}"><i class="fa-solid fa-trash"></i></button>
				</div>
				<div class="card-body">
					<div class="row g-3 mb-3">
						<div class="col-md-3">
							<label class="form-label">ID</label>
							{input p=['name' => "D[NAVIGATION][D][{$kNAV}][NewId]", 'type' => 'text', 'value' => $kNAV, 'help' => 'z.B. top_navigation']}
						</div>
						<div class="col-md-6">
							<label class="form-label">Name</label>
							{input p=['name' => "D[NAVIGATION][D][{$kNAV}][Name]", 'type' => 'text', 'value' => $NAV.Name]}
						</div>
						<div class="col-md-3">
							<label class="form-label">Active</label>
							{input p=['name' => "D[NAVIGATION][D][{$kNAV}][Active]", 'type' => 'checkbox', 'value' => $NAV.Active]}
						</div>
					</div>
					{menu_tree field="D[NAVIGATION][D][{$kNAV}][Json]" json=$NAV.Json}
				</div>
			</div>
		{/foreach}

		<div class="card mb-3 border-primary">
			<div class="card-header"><i class="fas fa-plus"></i> Neue Navigation</div>
			<div class="card-body">
				<div class="row g-3 mb-3">
					<div class="col-md-3">
						<label class="form-label">ID</label>
						{input p=['name' => "D[NAVIGATION][D][_new][NewId]", 'type' => 'text', 'value' => '', 'help' => 'z.B. top_navigation']}
					</div>
					<div class="col-md-6">
						<label class="form-label">Name</label>
						{input p=['name' => "D[NAVIGATION][D][_new][Name]", 'type' => 'text', 'value' => '', 'placeholder' => 'z.B. Haupt Menu']}
					</div>
					<div class="col-md-3">
						<label class="form-label">Active</label>
						{input p=['name' => "D[NAVIGATION][D][_new][Active]", 'type' => 'checkbox', 'value' => 1]}
					</div>
				</div>
				{menu_tree field="D[NAVIGATION][D][_new][Json]" json="[]"}
			</div>
		</div>

	</form>
	{literal}
	<style>.tree-before{box-shadow:0 -3px 0 #0d6efd}.tree-after{box-shadow:0 3px 0 #0d6efd}.tree-child{outline:2px solid #0d6efd}</style>
	<script>
		$('.nav-delete').on('click', function() {
			if (!confirm('Navigation \'' + this.dataset.title + '\' wirklich löschen?\nDas Löschen erfolgt erst beim Speichern.')) { return; }
			var card = $(this).closest('.nav-card');
			card.find(':input').prop('disabled', true);
			card.find('.nav-delflag').prop('disabled', false);
			card.addClass('d-none');
		});

		$('.menu-tree').each(function() {
			var box = $(this), hidden = box.children('input[type=hidden]'), langs = box.data('langs') || ['DE'], drag = null, zone = '', root = $('<ul class="list-unstyled mb-2"></ul>');
			var dump = function(ul) {
				return ul.children('li').map(function() {
					var li = $(this), line = li.children('div'), title = {};
					line.find('.tree-title').each(function() { title[$(this).data('lang')] = $(this).val(); });
					return { Title: title, Url: line.find('.tree-url').val(), Target: line.find('.tree-target').val(), Children: dump(li.children('ul')) };
				}).get();
			};
			var save = function() { hidden.val(JSON.stringify(dump(root))).attr('ischanged', 1).trigger('change'); };
			var row = function(n) {
				var li = $('<li class="mb-1"></li>'), line = $('<div class="d-flex gap-1 align-items-center border rounded p-1 bg-body"></div>'), ul = $('<ul class="list-unstyled ms-4 mt-1 mb-0"></ul>');
				line.append('<span class="tree-handle text-muted px-1" style="cursor:move" title="Verschieben"><i class="fa-solid fa-grip-vertical"></i></span>');
				$.each(langs, function(i, l) {
					var t = typeof n.Title === 'string' ? (i === 0 ? n.Title : '') : ((n.Title || {})[l] || '');
					line.append($('<input type="text" class="form-control form-control-sm tree-title">').attr({ placeholder: 'Titel ' + l, title: 'Titel ' + l }).data('lang', l).val(t));
				});
				line.append($('<input type="text" class="form-control form-control-sm tree-url" placeholder="URL (relativ, z.B. shop/kategorie)">').val(n.Url || ''));
				line.append($('<select class="form-select form-select-sm tree-target" title="Link öffnen"><option value="_self">Im gleichen Fenster</option><option value="_blank">In neuem Fenster</option></select>').val(n.Target === '_blank' ? '_blank' : '_self'));
				line.append('<button type="button" class="btn btn-outline-secondary btn-sm tree-add" title="Unterpunkt hinzufügen"><i class="fa-solid fa-plus"></i></button>');
				line.append('<button type="button" class="btn btn-outline-danger btn-sm tree-del" title="Entfernen"><i class="fa-solid fa-trash"></i></button>');
				$.each(n.Children || [], function(i, c) { ul.append(row(c)); });
				return li.append(line, ul);
			};
			var data = [];
			try { data = JSON.parse(hidden.val() || '[]') || []; } catch (e) { data = []; }
			$.each(data, function(i, n) { root.append(row(n)); });
			box.append(root, $('<button type="button" class="btn btn-outline-primary btn-sm tree-add-root"><i class="fa-solid fa-plus"></i> Menüpunkt</button>'));
			var clear = function() { box.find('.tree-drop').removeClass('tree-drop tree-before tree-after tree-child'); };
			box.on('input change', '.tree-title, .tree-url, .tree-target', save);
			box.on('click', '.tree-add-root', function() { root.append(row({})); save(); });
			box.on('click', '.tree-add', function() { $(this).closest('li').children('ul').append(row({})).children().last().find('.tree-title').first().focus(); save(); });
			box.on('click', '.tree-del', function() { $(this).closest('li').remove(); save(); });
			box.on('mousedown', '.tree-handle', function() { $(this).closest('li').attr('draggable', 'true'); });
			box.on('dragstart', 'li', function(e) { e.stopPropagation(); drag = this; e.originalEvent.dataTransfer.setData('text/plain', 'tree'); });
			box.on('dragend', 'li', function() { $(this).removeAttr('draggable'); drag = null; clear(); });
			box.on('dragover', 'li > div', function(e) {
				var li = $(this).parent()[0];
				if (!drag || drag === li || $.contains(drag, li)) { return; }
				e.preventDefault();
				var r = this.getBoundingClientRect(), y = (e.originalEvent.clientY - r.top) / r.height;
				zone = y < 0.25 ? 'before' : (y > 0.75 ? 'after' : 'child');
				clear();
				$(this).addClass('tree-drop tree-' + zone);
			});
			box.on('drop', 'li > div', function(e) {
				var li = $(this).parent();
				if (!drag || drag === li[0] || $.contains(drag, li[0])) { return; }
				e.preventDefault();
				if (zone == 'before') { li.before(drag); } else if (zone == 'after') { li.after(drag); } else { li.children('ul').append(drag); }
				clear();
				save();
			});
		});
	</script>
	{/literal}
{/block}

{block name="inner_footer"}
	<div class="bg-body-tertiary border-top p-2">
		<button type="submit" form="frontend-menu-form" class="btn btn-primary btn-sm">Save</button>
	</div>
{/block}
