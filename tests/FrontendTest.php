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

	public function testPartialMenuUpdatesPreserveUnsubmittedFieldsAndOtherMenus(): void {
		$this->nav(['_new' => ['NewId' => 'top_navigation', 'Name' => 'Top', 'Active' => 0, 'Json' => self::TREE]]);
		$this->nav(['_new' => ['NewId' => 'footer_navigation', 'Name' => 'Footer', 'Json' => '[]']]);
		$before = $this->load()['top_navigation'];
		$this->nav([
			'top_navigation' => ['Active' => 0],
			'footer_navigation' => ['Json' => self::TREE],
		]);
		$all = $this->load();
		$this->assertSame($before, $all['top_navigation']);
		$this->assertSame('Footer', $all['footer_navigation']['Name']);
		$this->assertSame('Shop', $all['footer_navigation']['Tree'][0]['Title']);
		$this->nav(['top_navigation' => ['NewId' => 'renamed']]);
		$all = $this->load();
		$this->assertSame($before['Json'], $all['renamed']['Json']);
		$this->assertSame('Top', $all['renamed']['Name']);
		$this->assertEquals(0, $all['renamed']['Active']);
		$this->nav(['footer_navigation' => ['Json' => '[]']]);
		$this->assertSame([], $this->load()['footer_navigation']['Tree']);
		$this->assertSame($all['renamed'], $this->load()['renamed']);
		$this->nav(['footer_navigation' => '__DELETE__']);
		$this->assertSame(['renamed'], array_keys($this->load()));
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

	public function testNavigationMigrationPreservesExistingMenusAndLoadsLayoutData(): void {
		$C = new \fremeo\core\Container();
		$C->set('Frontend', $this->fe, 'fremeo/frontend');
		$C->set('Setting', new class { function get() { return 'DE|EN'; } });
		$D = ['C' => $C];
		$migration = include __DIR__ . '/../system/migration/20261106144000_navigation.php';
		$migration->up();
		$this->assertEqualsCanonicalizing(['footer_navigation', 'top_navigation'], array_keys($this->load()));
		$this->assertSame([], $this->load()['top_navigation']['Tree']);
		$this->assertSame([], $this->load()['footer_navigation']['Tree']);
		$this->nav(['top_navigation' => ['Name' => 'Angepasst', 'Active' => 0, 'Json' => self::TREE]]);
		$migration->up();
		$this->assertSame('Angepasst', $this->load()['top_navigation']['Name']);
		$this->assertEquals(0, $this->load()['top_navigation']['Active']);
		$this->nav(['_new' => ['NewId' => 'other', 'Name' => 'Andere', 'Json' => '[]']]);
		$R = [];
		include __DIR__ . '/../system/index__frontend.php';
		$this->assertEqualsCanonicalizing(['footer_navigation', 'top_navigation'], array_keys($D['MODULE']['D']['fremeo/frontend']['NAVIGATION']['D']));
		$this->assertSame('DE', $R['Frontend']['language']);
	}

	#[\PHPUnit\Framework\Attributes\WithoutErrorHandler]
	public function testLayoutRendersMenusSearchAndOneThemeAwareTopbar(): void {
		if (!defined('SCRIPT_NAME')) define('SCRIPT_NAME', '/test/');
		$smarty = new \Smarty();
		$smarty->setCompileDir($this->tmp);
		$smarty->error_reporting = E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED;
		$policy = new \Smarty_Security($smarty);
		$policy->secure_dir = [realpath(__DIR__ . '/../..')];
		$policy->php_functions = ['max', 'round', 'hrtime', 'rtrim', 'dirname', 'mail', 'base64_encode', 'mb_convert_encoding', 'hash', 'header', 'json_decode', 'json_encode', 'serialize', 'COUNT', 'file_get_contents', 'substr', 'array_key_exists', 'array_merge_recursive', 'array_diff_key', 'str_pad', 'strtotime', 'date', 'ceil', 'array_keys', 'current', 'count', 'strlen', 'strtolower', 'number_format', 'md5', 'in_array', 'is_array', 'time', 'nl2br', 'print_r', 'array_key_first', 'strpos', 'strrpos', 'str_replace', 'isset', 'empty', 'sizeof', 'trim', 'explode', 'implode'];
		$policy->php_modifiers = ['in_array', 'strlen', 'strstr', 'array_keys', 'count', 'COUNT', 'number_format'];
		$smarty->enableSecurity($policy);
		$C = new \fremeo\core\Container();
		$C->set('Setting', new class { function get() { return 'Test Shop'; } });
		$C->set('Security', new \fremeo\core\Security());
		$this->nav(['_new' => ['NewId' => 'top_navigation', 'Name' => 'Top', 'Json' => self::TREE]]);
		$this->nav(['_new' => ['NewId' => 'footer_navigation', 'Name' => 'Footer', 'Json' => '[{"Title":{"EN":"Contact","DE":"Kontakt"},"Url":"kontakt","Target":"_blank"}]']]);
		$smarty->assign('D', [
			'C' => $C, 'SEARCH' => '"><script>alert(1)</script>', 'SESSION' => ['UserId' => 'test'],
			'R' => ['Frontend' => ['language' => 'DE']],
			'MODULE' => ['D' => ['fremeo/frontend' => ['NAVIGATION' => ['D' => $this->load()]]]],
		]);
		$input = file_get_contents(__DIR__ . '/../../core/system/template/include/input.tpl');
		$layout = file_get_contents(__DIR__ . '/../system/template/index__frontend.tpl');
		$reporting = error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);
		try {
			$html = $smarty->fetch('string:' . $input . $layout);
		} finally {
			error_reporting($reporting);
		}
		$this->assertSame(1, substr_count($html, 'id="frontend-topbar"'));
		$this->assertStringContainsString('bg-body-tertiary border-bottom sticky-top', $html);
		$this->assertStringNotContainsString('bg-white', $html);
		$this->assertStringNotContainsString('readonly=', $html);
		$this->assertStringContainsString('method="get" action="/test/artikel"', $html);
		$this->assertStringContainsString('&lt;script&gt;', $html);
		$this->assertStringNotContainsString('<script>alert(1)</script>', $html);
		$this->assertStringContainsString('dropdown-menu', $html);
		$this->assertStringContainsString('Shop', $html);
		$this->assertStringContainsString('Neu', $html);
		$this->assertStringContainsString('Kontakt', $html);
		$this->assertStringContainsString('rel="noopener noreferrer"', $html);
		$this->assertStringContainsString('list-unstyled mb-0', $html);
		$this->assertStringContainsString('<span class="visually-hidden">Mein Konto</span>', $html);
		$this->assertStringContainsString('title="Mein Konto"', $html);
		$this->assertStringContainsString('mx-auto" style="max-width: 32rem;"', $html);
		$smarty->assign('D', ['C' => $C, 'NAVIGATION' => ['D' => []], 'R' => ['Languages' => ['DE']]]);
		$core = __DIR__ . '/../../core/system/template/';
		$admin = $smarty->fetch('extends:' . $core . 'index__index.tpl|' . $core . 'index__admin.tpl|' . __DIR__ . '/../system/template/admin__menu.list.tpl|' . $core . 'include/input.tpl');
		$this->assertStringContainsString('form="frontend-menu-form"', $admin);
		$this->assertStringContainsString('bg-body-tertiary border-top p-2', $admin);
		$this->assertLessThan(strpos($admin, 'form="frontend-menu-form"'), strpos($admin, '</form>'));
	}

	public function testAdminPageSavesAndDeletesViaForm(): void {
		$C = new \fremeo\core\Container();
		$C->set('CData', $this->db, 'fremeo/frontend');
		include __DIR__ . '/../init.php';
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
