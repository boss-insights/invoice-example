<?php
/**
 * @global array $commonData
 * @global Environment $twig
 */

use GuzzleHttp\Client;

require __DIR__ . '/vendor/autoload.php';

require __DIR__ . '/common.php';

$selfSigned = (bool)getenv('SELF_SIGNED_CERT');
$client = new Client(['verify' => !$selfSigned]);

$response = $client->request('POST', $commonData['ADMIN_URL'] . '/app/api.php', [
	'auth'    => [$commonData['API_KEY'], $commonData['API_SECRET']],
	'headers' => [
		'User-Agent'  => 'BossInsightsApiClient/1.0',
		'Accept'      => 'application/json',
		'Account-Key' => $commonData['ACCOUNT_KEY']
	],
	'body'    => json_encode([
		'action'      => 'allow_url',
		'url'         => 'https://' . getenv('DYNO') . '.herokuapp.com',
		'environment' => $commonData['ENVIRONMENT']
	], JSON_THROW_ON_ERROR)
]);

if ($response->getStatusCode() !== 200) {
	exit(1);
}