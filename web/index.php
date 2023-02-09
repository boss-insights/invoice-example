<?php
/**
 * @global array $commonData
 * @global Environment $twig
 */

use Twig\Environment;

require __DIR__ . '/../common.php';
echo $twig->render('index.twig');
