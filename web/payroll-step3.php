<?php
/**
 * @global array $commonData
 * @global Environment $twig
 */

use GuzzleHttp\Client;
use Twig\Environment;

require __DIR__ . '/../common.php';

if (!isset($_SESSION['account_key'])) {
    header('Location: step1.php');
    exit;
}


// pull in data via Payroll API
$selfSigned = (bool)getenv('SELF_SIGNED_CERT');
$client = new Client(['verify' => !$selfSigned, 'base_uri' => 'https://' . $_SESSION['account_domain']]);

$payrollData = [];

    try {
        $response = $client->request('GET', '/api/payroll', [
            'auth' => ['admin', $_SESSION['password']],
            'headers' => [
                'User-Agent' => 'BossInsightsApiClient/1.0',
                'Accept' => 'application/json'
            ]
        ]);
    } catch (Exception $e) {
        echo $twig->render('error.twig', array_merge($commonData, [
            'errorType' => 'Error',
            'errorName' => 'failed to communicate with the data api',
            'errorDescription' => 'Exception: ' . $e->getMessage()
        ]));
        throw new Exception($e->getMessage(), $e->getCode(), $e);
    }

    if ($response->getStatusCode() === 200) {
        $result = json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);

        if ($result === null) {
            echo $twig->render('error.twig', array_merge($commonData, [
                'errorType' => 'Error',
                'errorName' => 'invalid data api response',
                'errorDescription' => 'Response: ' . var_export($response->getBody()->getContents())
            ]));
            throw new Exception('invalid data api response');
        }

        foreach ($result as $payroll) {
             $payrollData[] = $payroll;

        }
    } else {
        echo $twig->render('error.twig', array_merge($commonData, [
            'errorType' => 'Error',
            'errorName' => 'unable to communicate with data api',
            'errorDescription' => 'received status code ' . $response->getStatusCode() . ' when communicating with data api'
        ]));
        throw new Exception('unable to communicate with data api');
    }

echo $twig->render('payroll-step3.twig',array_merge($commonData, ['LEGAL_NAME' => $_SESSION['legal_name'], 'payrollData' => $payrollData]));