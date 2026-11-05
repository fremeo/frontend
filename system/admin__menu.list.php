<?php
# Admin: Navigationen des Frontends (ID, Name, Active, Menübaum). Schreiben und Lesen nur über die Klasse Frontend.
# Löschen: das Template sendet D[NAVIGATION][D][<id>] = '__DELETE__', set_navigation übernimmt es (erst beim Speichern).
$FE = $C['fremeo/frontend']['Frontend'];
$R['Languages'] = array_values(array_filter(array_map(fn ($l) => strtoupper(trim($l)), explode('|', (string)$C['Setting']->get('fremeo/core', 'enable_languages'))))) ?: ['DE']; # Titel je aktiver Sprache

if (($R['ACTION'] ?? null) == 'save') $FE->set_navigation($D);
unset($D['NAVIGATION']); # gepostete Daten nicht mit denen aus der Datenbank vermischen

$f['NAVIGATION'] = []; # eigener Filter, $F wird in start.php für die Core-Datenbank verwendet
$FE->get_navigation($D, $f);
