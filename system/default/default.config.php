<?php

$section['global']['D']['metadaten']['D']['page_meta_title'] = 1;
#$section['global']['D']['metadaten']['D']['page_favicon'] = 1;

$setting['page_meta_title'] = [
            'InputType' => 'text',
            'Value' => '',
            'Label' => 'Page Title',
            'Description' => 'Give page Title',
        ];
/*
$setting['page_favicon'] = [
            'InputType' => 'image',
            'Value' => '',
            'Label' => 'Page Favicon',
            'Description' => 'Set page favicon',
        ];
*/
return [
	'SECTION' => $section,
	'SETTING' => $setting,
];