<?php
#DB-----------------

$Pattern = [];
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


$C['fremeo/frontend']['CData'] = new \phploader\CData( [ 'DB' => ['FILENAME' => PROJECT_ROOT.'data/fremeo~frontend/data.db', 'FILENAME_C' => PROJECT_ROOT.'data_c/fremeo~frontend/data.db' ] ] );
$C['fremeo/frontend']['CData']->registerPattern($Pattern);