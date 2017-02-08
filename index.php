<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require './funcoes.php';
require 'vendor/autoload.php';

$app = new \Slim\App();

$app->get('/users', 'getUsers');
$app->get('/returnUsers/{cpf}/{id}', 'returnUsersStatus');

$app->run();
