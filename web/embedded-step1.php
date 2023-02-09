<?php
/**
 * @global array $commonData
 * @global Environment $twig
 */

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Twig\Environment;

require __DIR__ . '/../common.php';

$selfSigned = (bool)getenv('SELF_SIGNED_CERT');
$client = new Client(['verify' => !$selfSigned]);

try {
    $response = $client->request('GET', $commonData['ADMIN_URL'] . '/app/api.php?action=embed_token', [
        'auth' => [$commonData['API_KEY'], $commonData['API_SECRET']],
        'headers' => [
            'User-Agent' => 'BossInsightsApiClient/1.0',
            'Accept' => 'application/json',
            'Account-Key'=> $commonData['ACCOUNT_KEY']
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
//var_dump(json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR));
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

} else {
    echo $twig->render('error.twig', array_merge($commonData, [
        'errorType' => 'Error',
        'errorName' => 'unable to communicate with data api',
        'errorDescription' => 'received status code ' . $response->getStatusCode() . ' when communicating with data api'
    ]));
    throw new Exception('unable to communicate with data api');
}
$_SESSION['embedToken'] = $result['token'];
$_SESSION['accountURL'] = preg_replace("(^https?://)", "", $commonData['ORG_URL'] );

if($_POST) {
    if (isset($_POST['accountKey']) && !empty($_POST['accountKey'])) {
        $_SESSION['borrowerKey'] = $_POST['accountKey'];
    }
}



echo $twig->render('embedded-step1.twig', array_merge($commonData, ['EMBED_TOKEN' => $_SESSION['embedToken'], 'ACCOUNT_URL' => $_SESSION['accountURL']]));

