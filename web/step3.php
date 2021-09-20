<?php
/**
 * @global array $commonData
 * @global Environment $twig
 */
use Twig\Environment;

require __DIR__ . '/../common.php';

// pull in data via API and show list of invoices


echo $twig->render('step3.twig', array_merge($commonData, []));