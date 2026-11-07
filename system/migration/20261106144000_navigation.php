<?php
return new class($D) {
	private $C;

	function __construct($D) {
		$this->C = $D['C'];
	}

	function up() {
		$frontend = $this->C['fremeo/frontend']['Frontend'];
		$frontend->get_navigation($have);
		$menus = [
			'top_navigation' => 'Hauptnavigation',
			'footer_navigation' => 'Footer-Navigation',
		];
		foreach ($menus as $id => $name) {
			if (isset($have['NAVIGATION']['D'][$id])) continue;
			$frontend->set_navigation(['NAVIGATION' => ['D' => ['_new' => [
				'NewId' => $id, 'Name' => $name, 'Active' => 1, 'Json' => '[]',
			]]]]);
		}
	}

	function down() {
		$this->C['fremeo/frontend']['Frontend']->set_navigation(['NAVIGATION' => ['D' => [
			'top_navigation' => '__DELETE__', 'footer_navigation' => '__DELETE__',
		]]]);
	}
};
