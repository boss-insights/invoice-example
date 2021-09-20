<?php
/**
 * @global array $commonData
 * @global Environment $twig
 */
use Twig\Environment;

require __DIR__ . '/../common.php';

// get domain and api key and secret from session

echo $twig->render('step2.twig', array_merge($commonData, []));