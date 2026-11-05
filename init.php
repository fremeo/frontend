<?php
#DB-----------------

$Pattern = include __DIR__ . '/system/default/default.pattern.php'; # NAVIGATION
/*
$Pattern['FRON'] = [
	'Active'		=> ['Type' => 'checkbox'],
	'Follow'		=> ['Type' => 'checkbox'], #1=follow; 0/null=nofollow + bei nofollow wird Inhalt mit base64 verschlüsselt
];
$Pattern['PAGE']['D']['LANGUAGE'] = [
	'Active'		=> ['Type' => 'checkbox'],
	'Title'			=> ['Type' => 'text'],
	'Text'			=> ['Type' => 'text'],
	'LINK'			=> ['Type' => 'id'], #ID für LINK je Sprache
];
*/
##$C['CData']->registerPattern($Pattern);


#$C['fremeo/frontend']['CData'] = new \phploader\CData( [ 'DB' => ['FILENAME' => PROJECT_ROOT.'data/fremeo~frontend/data.db', 'FILENAME_C' => PROJECT_ROOT.'data_c/fremeo~frontend/data.db' ] ] );
/*if (!isset($C['fremeo/frontend']['CData'])) {
    $C->set('CData', new \phploader\CData( [ 'DB' => ['FILENAME' => PROJECT_ROOT.'data/fremeo~frontend/data.db', 'FILENAME_C' => PROJECT_ROOT.'data_c/fremeo~frontend/data.db' ] ] ), 'fremeo/frontend');
}*/
$C['fremeo/frontend']['CData']->registerPattern($Pattern);

# Zwischenschicht zu CData, Instanz: $C['fremeo/frontend']['Frontend'] (Klasse über Composer PSR-4 aus composer.json: fremeo\frontend\ => system/core/)
$C->set('Frontend', fn () => new \fremeo\frontend\Frontend($C['fremeo/frontend']['CData']), 'fremeo/frontend');