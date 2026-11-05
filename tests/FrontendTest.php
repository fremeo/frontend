<?php
namespace fremeo\frontend\tests;

use fremeo\frontend\Frontend;
use PHPUnit\Framework\TestCase;
use phploader\CData;

/**
 * Klasse Frontend (Zwischenschicht zu CData) und Seite admin__menu.list.php mit temporärer Datenbank.
 */
class FrontendTest extends TestCase {

	private string $tmp;
	private CData $db;
	private Frontend $fe;

	private const TREE = '[{"Title":"Shop","Url":"shop","Target":"_self","Children":[{"Title":"Neu","Url":"shop/neu","Target":"_blank","Children":[]}]}]';

	protected function setUp(): void {
		$this->tmp = sys_get_temp_dir() . '/fremeo_frontend_' . uniqid();
		mkdir($this->tmp);
		$this->db = new CData(['DB' => ['FILENAME' => "{$this->tmp}/data.db", 'FILENAME_C' => "{$this->tmp}/data_c.db"]]);
		$this->db->registerPattern(include __DIR__ . '/../system/default/default.pattern.php');
		$this->fe = new Frontend($this->db);
	}

	protected function tearDown(): void {
		foreach (glob("{$this->tmp}/*") ?: [] as $f) @unlink($f);
		@rmdir($this->tmp);
	}

	private function load(array $f = []): array {
		$d = null;
		$this->fe->get_navigation($d, $f);
		return (array)($d['NAVIGATION']['D'] ?? []);
	}

	private function nav(array $rows): void {
		$this->fe->set_navigation(['NAVIGATION' => ['D' => $rows]]);
	}

	public function testNewNavigationIsSavedAndLoadedWithTree(): void {
		$this->nav(['_new' => ['NewId' => 'top_navigation', 'Name' => 'Haupt Menu', 'Json' => self::TREE]]);

		$all = $this->load();
		$this->assertSame(['top_navigation'], array_keys($all));
		$this->assertSame('Haupt Menu', $all['top_navigation']['Name']);
		$this->assertEquals(1, $all['top_navigation']['Active']);
		$this->assertSame('_blank', $all['top_navigation']['Tree'][0]['Children'][0]['Target']);
		$this->assertSame('shop/neu', $all['top_navigation']['Tree'][0]['Children'][0]['Url']);
	}

	public function testForbiddenCharactersAreFilteredAndIncompleteEntriesSkipped(): void {
		$this->nav([
			'_new' => ['NewId' => '', 'Name' => '', 'Json' => '[]'],
		]);
		$this->assertSame([], $this->load());

		$this->nav(['_new' => ['NewId' => 'bad id!', 'Name' => '<b>Menu</b>', 'Json' => 'kein json']]);
		$all = $this->load();
		$this->assertSame(['badid'], array_keys($all));
		$this->assertSame('bMenu/b', $all['badid']['Name']);
		$this->assertSame([], $all['badid']['Tree']);

		$this->nav(['_new' => ['NewId' => 'ok', 'Name' => '  ', 'Json' => '[]']]);
		$all = $this->load();
		$this->assertEqualsCanonicalizing(['badid', 'ok'], array_keys($all)); # ohne Name ist erlaubt, nur die ID ist Pflicht
		$this->assertSame('', (string)($all['ok']['Name'] ?? ''));
	}

	public function testRenameKeepsDataAndTakenIdIsNotOverwritten(): void {
		$this->nav(['_new' => ['NewId' => 'a', 'Name' => 'A', 'Json' => self::TREE]]);
		$this->nav(['_new' => ['NewId' => 'b', 'Name' => 'B', 'Json' => '[]']]);

		$this->nav(['a' => ['NewId' => 'b', 'Name' => 'A2', 'Json' => '[]'], '_new' => ['NewId' => 'b', 'Name' => 'Doppelt']]);
		$all = $this->load();
		$this->assertSame('A', $all['a']['Name']);
		$this->assertSame('B', $all['b']['Name']);

		$this->nav(['a' => ['NewId' => 'footer_navigation', 'Name' => 'A', 'Active' => 0, 'Json' => self::TREE]]);
		$all = $this->load();
		$this->assertEqualsCanonicalizing(['b', 'footer_navigation'], array_keys($all));
		$this->assertEquals(0, $all['footer_navigation']['Active']);
		$this->assertSame('Shop', $all['footer_navigation']['Tree'][0]['Title']);
	}

	public function testCreateUpdateAndDeleteInOneTree(): void {
		$this->nav(['_new' => ['NewId' => 'a', 'Name' => 'A', 'Json' => self::TREE]]);
		$this->nav(['_new' => ['NewId' => 'b', 'Name' => 'B', 'Json' => '[]']]);

		$this->nav([
			'a'    => '__DELETE__',
			'b'    => ['NewId' => 'b', 'Name' => 'B2', 'Json' => '[]'],
			'_new' => ['NewId' => 'c', 'Name' => 'C', 'Json' => '[]'],
		]);
		$all = $this->load();
		$this->assertEqualsCanonicalizing(['b', 'c'], array_keys($all));
		$this->assertSame('B2', $all['b']['Name']);
	}

	public function testOnlyTheNavigationBranchIsWritten(): void {
		$this->fe->set_navigation(['LINK' => ['D' => ['x' => ['Active' => 1, 'FromURL' => 'boese', 'ToURL' => 'y']]], 'NAVIGATION' => ['D' => ['_new' => ['NewId' => 'a', 'Name' => 'A']]]]);
		$this->db->registerPattern(['LINK' => ['Active' => ['Type' => 'checkbox'], 'FromURL' => ['Type' => 'text'], 'ToURL' => ['Type' => 'text']]]);
		$d = null; $f['LINK'] = [];
		$this->db->get_object($d, $f);
		$this->assertEmpty($d['LINK']['D'] ?? []);
		$this->assertSame(['a'], array_keys($this->load()));
	}

	public function testTreeIsCleaned(): void {
		$json = json_encode([
			['Title' => 'Ok', 'Url' => 'javascript:alert(1)', 'Target' => 'popup', 'Children' => [['Title' => '', 'Url' => 'x'], ['Title' => 'Kind', 'Url' => ' a/b ']]],
			['Title' => '  ', 'Url' => 'weg'],
		]);
		$this->nav(['_new' => ['NewId' => 't', 'Name' => 'T', 'Json' => $json]]);
		$tree = $this->load()['t']['Tree'];
		$this->assertCount(2, $tree); # Punkte ohne Titel bleiben erhalten (z.B. gerade angelegt)
		$this->assertSame('', $tree[0]['Url']);
		$this->assertSame('_self', $tree[0]['Target']);
		$this->assertCount(2, $tree[0]['Children']);
		$this->assertSame('a/b', $tree[0]['Children'][1]['Url']);
	}

	public function testTitleIsStoredPerLanguageAndFiltered(): void {
		$json = json_encode([['Title' => ['DE' => 'Kontakt', 'en' => ' <b>Contact</b> ', 'x y' => 'weg', 'FR' => ''], 'Url' => 'kontakt', 'Children' => [['Title' => 'Alt']]]]);
		$this->nav(['_new' => ['NewId' => 'm', 'Name' => 'M', 'Json' => $json]]);
		$tree = $this->load()['m']['Tree'];
		$this->assertSame(['DE' => 'Kontakt', 'en' => 'bContact/b', 'FR' => ''], $tree[0]['Title']);
		$this->assertSame('Alt', $tree[0]['Children'][0]['Title']); # einfacher Text gilt weiter
	}

	public function testGetNavigationUsesFilterLikeCData(): void {
		$this->nav(['_new' => ['NewId' => 'a', 'Name' => 'A', 'Json' => '[]']]);
		$this->nav(['_new' => ['NewId' => 'b', 'Name' => 'B', 'Json' => '[]']]);
		$f['NAVIGATION']['W'][0]['ID'] = ['b'];
		$this->assertSame(['b'], array_keys($this->load($f)));
	}

	public function testAdminPageSavesAndDeletesViaForm(): void {
		$C = new \fremeo\core\Container();
		$C->set('Frontend', $this->fe, 'fremeo/frontend');
		$C->set('Setting', new class { function get() { return 'DE|EN'; } });
		$run = function (array $D, array $R = []) use ($C): array {
			include __DIR__ . '/../system/admin__menu.list.php';
			return [$D, $R];
		};

		[$D, $R] = $run(['NAVIGATION' => ['D' => ['_new' => ['NewId' => 'top', 'Name' => 'Haupt Menu', 'Json' => self::TREE]]]], ['ACTION' => 'save']);
		$this->assertSame(['DE', 'EN'], $R['Languages']);
		$this->assertSame(['top'], array_keys($D['NAVIGATION']['D']));

		[$D] = $run(['NAVIGATION' => ['D' => ['top' => '__DELETE__', '_new' => ['NewId' => 'top', 'Name' => 'Neu', 'Json' => '[]']]]], ['ACTION' => 'save']);
		$this->assertSame('Neu', $D['NAVIGATION']['D']['top']['Name']); # gelöscht und im selben Baum neu angelegt = ersetzt
	}
}
