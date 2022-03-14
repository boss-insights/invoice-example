<?php
/**
 * @global array $commonData
 * @global Environment $twig
 */
use Twig\Environment;
require __DIR__ . '/../common.php';
echo $twig->render('error.twig', array_merge($commonData, ['errorType'=>'Debug']));