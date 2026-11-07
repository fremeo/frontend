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
$section['Template']['D']['Frontend']['D']['theme'] = 1;
$setting['theme'] = [
	'InputType' => 'select', 'Value' => 'basic', 'Label' => 'Theme', 'AllowEmpty' => false,
	'Description' => 'Template-Herkunft und Theme fuer die Frontend-Layouts gemeinsam auswaehlen.',
	'Options' => fn ($C) => $C['Template']->getThemeOptions('fremeo/frontend'),
];
foreach (glob(__DIR__ . '/../template/theme/*', GLOB_ONLYDIR) as $dir) {
	$name = basename($dir);
	if (str_contains($name, '__')) {
		continue;
	}
	$section['Template']['D']['Frontend']['D']["theme_css_$name"] = 1;
	$setting["theme_css_$name"] = [
		'InputType' => 'textarea', 'Value' => '', 'Label' => "Custom CSS: $name",
		'Description' => 'Wird nach allen Theme-Dateien geladen und gilt fuer die gesamte Theme-Familie.',
	];
}

foreach ([
	'theme_minify_css' => ['CSS minimieren', 'CSS-Dateien beim Veroeffentlichen verkleinern.'],
	'theme_minify_js' => ['JavaScript minimieren', 'JavaScript-Dateien einzeln verkleinern; die Ladereihenfolge bleibt erhalten.'],
	'theme_combine_css' => ['CSS zusammenfassen', 'Kompatible CSS-Dateien in ihrer Ladereihenfolge zusammenfassen.'],
] as $key => [$label, $description]) {
	$section['Template']['D']['Frontend']['D']['Performance']['D'][$key] = 1;
	$setting[$key] = ['InputType' => 'checkbox', 'Value' => 0, 'Label' => $label, 'Description' => $description];
}

return [
	'SECTION' => $section,
	'SETTING' => $setting,
];