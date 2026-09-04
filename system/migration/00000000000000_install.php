<?php 
namespace fremeo\core;

return new class($D) {

	function __construct($D) {
		$this->C = $D['C'];
		$this->ModuleId = 'fremeo/frontend';
	}

	function up() {
		
		#frontend
		$D['LINK']['D'][hash("crc32b", 'account')] = ['Active' => 1, 'FromURL' => 'account', 'ToURL' => 'R[Page]=account__start'];
		
		$this->C[$this->ModuleId]['CData']->set_object($D);
	}

	function down() {
		#Lösche alle Links
		$D['LINK']['D'] = '__DELETE__';
		
		$this->C[$this->ModuleId]['CData']->set_object($D);
	}
};