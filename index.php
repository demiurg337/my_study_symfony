<?php
error_reporting(E_ALL);
//print_r($_COOKIES);
//try {
echo 'before';

var_dump(require_once __DIR__.'/Symfony/Component/HttpFoundation/ServerBag.php');

$b = new Symfony\Component\HttpFoundation\ServerBag();

print_r($b);
