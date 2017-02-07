<?php
require './funcoes.php';
require 'vendor/autoload.php';

$app = new \Slim\App();

$app->get('/users', 'getUsers');

$app->run();
