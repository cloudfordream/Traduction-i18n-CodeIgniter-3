<?php

$route['^(en|fr)/(.+)$'] = '$2';
$route['^(en|fr)$'] = $route['default_controller'];
