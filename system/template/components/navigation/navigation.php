<?php

return static function (array $provider, array $context): array {
	$menus = $provider;
	if (isset($context['C']['fremeo/frontend']['Frontend'])) {
		$context['C']['fremeo/frontend']['Frontend']->get_navigation($menus);
	}
	$menus = $menus['NAVIGATION']['D'] ?? [];
	$choices = [['value' => '', 'label' => 'Navigation auswaehlen']];
	foreach ($menus as $id => $menu) {
		$choices[] = [
			'value' => (string)$id,
			'label' => ($menu['Name'] ?? '') !== '' ? $menu['Name'] . ' (' . $id . ')' : (string)$id,
		];
	}
	return [
		'settings' => ['menu' => ['choices' => $choices]],
		'data' => static function (array $options, array $data) use ($menus): array {
			if (array_key_exists('items', $options)) {
				throw new RuntimeException('Eigene Navigationspunkte werden nicht mehr unterstuetzt. Navigation in der Menueverwaltung pflegen und auswaehlen.');
			}
			if ($options['menu'] === '') return $data;
			if (!isset($menus[$options['menu']])) throw new RuntimeException('Ausgewaehlte Navigation ist nicht mehr verfuegbar.');
			return $menus[$options['menu']];
		},
	];
};
