<?php
$C['fremeo/frontend']['Frontend']->get_navigation($D['MODULE']['D']['fremeo/frontend']);
$R['Frontend']['language'] = strtoupper(explode('|', (string)$C['Setting']->get('fremeo/core', 'enable_languages'))[0] ?: 'DE');
