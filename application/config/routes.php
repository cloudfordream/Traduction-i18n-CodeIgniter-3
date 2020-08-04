<?php

$route['^(en|fr|ru)/(.+)$'] = '$2';
$route['^(en|fr|ru)$'] = $route['default_controller'];
