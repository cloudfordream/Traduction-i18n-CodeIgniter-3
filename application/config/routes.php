<?php

$route['default_controller'] = 'welcome';

$route['fr'] = 'welcome';
$route['en'] = 'welcome';

$route['fr/(:any)'] = 'welcome';
$route['en/(:any)'] = 'welcome';

$route['(:any)'] = 'welcome';

$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;
