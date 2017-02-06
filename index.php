<?php
require './funcoes.php';
require 'vendor/autoload.php';

$app = new \Slim\app();
$app->get('/users', 'getUsers');
$app->run();
