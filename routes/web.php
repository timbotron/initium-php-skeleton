<?php

use App\Controllers\Home;
use FastRoute\RouteCollector;

// App routes. Core auth routes are mounted separately in public/index.php.
return function (RouteCollector $r) {
    $r->get('/', [Home::class, 'home_page']);
    $r->get('/logged-in-page', [Home::class, 'logged_in_page']);
};
