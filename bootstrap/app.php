<?php

use Pecee\SimpleRouter\SimpleRouter;

//SimpleRouter::setBasePath('/stand-cars/public');

// carregar rotas
require ROUTES_PATH . '/web.php';

// iniciar router
SimpleRouter::start();
