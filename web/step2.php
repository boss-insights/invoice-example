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
}

$selfSigned = (bool)getenv('SELF_SIGNED_CERT');
$client = new Client(['verify' => !$selfSigned]);

// get embed_token
try {
	$response = $client->request('GET', $commonData['ADMIN_URL'] . '/app/api.php?action=embed_token&account_key=' . $_SESSION['account_key'], [
		'auth'    => [$commonData['API_KEY'], $commonData['API_SECRET']],
		'headers' => [
			'User-Agent'  => 'BossInsightsApiClient/1.0',
			'Accept'      => 'application/json',
			'Account-Key' => $commonData['ACCOUNT_KEY']
		]
	]);
} catch (Exception $e) {
	echo $twig->render('error.twig', array_merge($commonData, [
		'errorType'        => 'Error',
		'errorName'        => 'failed to communicate with the data api',
		'errorDescription' => 'Exception: ' . $e->getMessage()
	]));
	throw new Exception($e->getMessage(), $e->getCode(), $e);
}
//var_dump(json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR));
if ($response->getStatusCode() === 200) {
	$result = json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
	if ($result === null) {
		echo $twig->render('error.twig', array_merge($commonData, [
			'errorType'        => 'Error',
			'errorName'        => 'invalid data api response',
			'errorDescription' => 'Response: ' . var_export($response->getBody()->getContents())
		]));
		throw new Exception('invalid data api response');
	}
} else {
	echo $twig->render('error.twig', array_merge($commonData, [
		'errorType'        => 'Error',
		'errorName'        => 'unable to communicate with data api',
		'errorDescription' => 'received status code ' . $response->getStatusCode() . ' when communicating with data api'
	]));
	throw new Exception('unable to communicate with data api');
}
$embedToken = $result['token'];

// get domain from session
$accountDomain = $_SESSION['account_domain'];

echo $twig->render('step2.twig', array_merge($commonData, ['EMBED_TOKEN' => $embedToken, 'ACCOUNT_DOMAIN' => $accountDomain]));