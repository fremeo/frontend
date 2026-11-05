<?php
namespace fremeo\frontend;

use phploader\CData;

/**
 * Zwischenschicht zwischen Programm und CData des Moduls fremeo/frontend (Instanz: $C['fremeo/frontend']['Frontend']).
 *
 * Regel für alle Module mit Datenbank: eine Klasse mit dem Namen des Moduls (system/core/<Modul>.php), die vor dem Schreiben filtert
 * und zusammengehörige Daten ggf. auf mehrere Tabellen/Datenbanken verteilt. Die Seiten sprechen nur mit dieser Klasse, nicht direkt mit CData.
 * Die Parameter entsprechen denen von CData::set_object / get_object ($D = Datenbaum, $F = Filter).
 *
 * Datenbaum: $D['NAVIGATION']['D'][<id>] = ['Active' => 1, 'Name' => 'Haupt Menu', 'Json' => '<Baum>', 'NewId' => '<neue id>'] oder '__DELETE__'.
 * Neu anlegen: Schlüssel '_new' mit NewId. ID ändern: NewId ungleich Schlüssel (alle Felder senden, sie wandern unter die neue ID).
 * Json: [{"Title":{"DE":"..","EN":".."},"Url":"relative/url","Target":"_self|_blank","Children":[..]}] (Title je aktiver Sprache, ein einfacher Text gilt für alle)
 */
class Frontend {

	public function __construct(private CData $DB) {}

	/**
	 * Speichert Navigationen. Gefiltert wird nur, was die Datenbank nicht verträgt oder gefährlich ist (Zeichen der ID und des Namens, URL-Schema, Target);
	 * Pflicht ist nur eine ID. Einträge ohne ID, mit vergebener neuer ID oder unbekannter Navigation werden übersprungen.
	 * Neu, ändern und löschen (auch gemischt) stehen in einem Baum, set_object entscheidet selbst.
	 */
	public function set_navigation(array $D): void {
		$f['NAVIGATION'] = [];
		$this->DB->get_object($have, $f);
		$have = (array)($have['NAVIGATION']['D'] ?? []);

		$clean = fn ($v) => trim(preg_replace('/[\x00-\x1F<>]/u', '', (string)$v));
		$tree = function (array $nodes, int $depth = 0) use (&$tree, $clean): array {
			$out = [];
			foreach ($nodes as $n) {
				if (!is_array($n)) continue;
				$url = trim((string)($n['Url'] ?? ''));
				$out[] = [
					'Title'		=> is_array($n['Title'] ?? null) # je Sprache: {"DE":"..","EN":".."}
						? array_map($clean, array_filter($n['Title'], fn ($k) => preg_match('/^[A-Za-z]{2,8}$/', (string)$k), ARRAY_FILTER_USE_KEY))
						: $clean($n['Title'] ?? ''),
					'Url'		=> preg_match('/^\s*(javascript|data|vbscript):/i', $url) ? '' : $url,
					'Target'	=> ($n['Target'] ?? '') === '_blank' ? '_blank' : '_self',
					'Children'	=> $depth < 10 ? $tree((array)($n['Children'] ?? []), $depth + 1) : [],
				];
			}
			return $out;
		};

		$id = fn ($v) => substr(preg_replace('/[^A-Za-z0-9_-]/', '', (string)$v), 0, 64);
		$U = [];
		foreach ((array)($D['NAVIGATION']['D'] ?? []) as $old => $row) {
			$old = $id($old);
			if ($row === '__DELETE__' && $old !== '') {
				$U['NAVIGATION']['D'][$old] = '__DELETE__';
				unset($have[$old]); # die ID ist danach frei (im selben Baum neu anlegen ersetzt)
				continue;
			}
			$new = $id($row['NewId'] ?? $old);
			if (!is_array($row) || $new === '' || $new === '_new' || ($old !== '_new' && !isset($have[$old])) || ($new !== $old && isset($have[$new]))) continue;

			if ($new !== $old && $old !== '_new') $U['NAVIGATION']['D'][$old] = '__DELETE__'; # ID geändert: alte entfernen
			$U['NAVIGATION']['D'][$new] = [
				'Active'	=> (int)((int)($row['Active'] ?? 1) > 0),
				'Name'		=> $clean($row['Name'] ?? ''),
				'Json'		=> json_encode($tree((array)json_decode((string)($row['Json'] ?? ''), true)), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
			];
		}
		if ($U) $this->DB->set_object($U); # nur dieser Zweig, nie die ganze Anfrage
	}

	/** Lädt Navigationen wie CData::get_object ($F leer = alle). Zusätzlich je Navigation ['Tree'] = Baum als Array. */
	public function get_navigation(&$D = null, &$F = null): void {
		if (!$F) $F['NAVIGATION'] = [];
		$this->DB->get_object($D, $F);
		foreach ((array)($D['NAVIGATION']['D'] ?? []) as $id => $nav) {
			$D['NAVIGATION']['D'][$id]['Tree'] = json_decode((string)($nav['Json'] ?? ''), true) ?: [];
		}
	}
}
