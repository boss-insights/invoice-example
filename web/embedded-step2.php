<?php
/**
 * @global array $commonData
 * @global Environment $twig
 */

use Twig\Environment;

require __DIR__ . '/../common.php';



$accountUrl = $_SESSION['accountURL'];
$embedToken = $_SESSION['embedToken'];
$borrowerKey = $_SESSION['borrowerKey'];

echo $twig->render('embedded-step2.twig', array_merge($commonData, ['EMBED_TOKEN' => $embedToken, 'ACCOUNT_URL' => $accountUrl, 'BORROWER_KEY'=>$borrowerKey]));