<?php
require __DIR__ . '/vendor/autoload.php';

$isHTTPS = (!empty($_SERVER['HTTPS'] ?? '') && $_SERVER['HTTPS'] !== 'off');
$commonData = array(
    'ORG_NAME' => getenv('ORG_NAME'), 
    'ORG_URL' => getenv('ORG_URL'), 
    'API_KEY' => getenv('API_KEY'), 
    'API_SECRET' => getenv('API_SECRET'), 
    'SITE_URL' =>  ($isHTTPS?'https://':'http://').$_SERVER['HTTP_HOST'],
    'ADMIN_URL' => getenv('ADMIN_URL')
);

foreach($commonData as $commonKey => $commonValue){
    if(empty($commonValue)) throw new Exception('environment variable '.$commonKey.' cannot be empty');
}

$twigLoader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/templates/');
$twig = new \Twig\Environment($twigLoader);