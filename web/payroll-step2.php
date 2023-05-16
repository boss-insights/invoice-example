<?php
/**
 * @global array $commonData
 * @global Environment $twig
 */

use Twig\Environment;

require __DIR__ . '/../common.php';

if (!isset($_SESSION['account_key'])) {
    header('Location: payroll-step1.php');
}

// get domain and api key and secret from session
$basicAuth = base64_encode('admin:' . $_SESSION['password']); // 'YWRtaW46c2VjcmV0'
$accountDomain = $_SESSION['account_domain'];

echo $twig->render('payroll-step2.twig', array_merge($commonData, ['AUTH_BASIC' => $basicAuth, 'ACCOUNT_DOMAIN' => $accountDomain]));