<?php
/**
 * detect if using Heroku Dyno, add dyno url to allow list for CORS
 * this would not be used in production but is added as a convenience to accommodate Heroku domain names
 *
 * @global GuzzleHttp\Client $client
 * @global Twig\Environment $twig
 * @global array $commonData
 */

use GuzzleHttp\Exception\GuzzleException;

if (getenv('DYNO') !== false && getenv('ALLOW_URL') !== false && !file_exists('allow_url.tmp')) {
  try {
	$response = $client->request('POST', $commonData['ADMIN_URL'] . '/app/api.php', [
	  'auth'    => [$commonData['API_KEY'], $commonData['API_SECRET']],
	  'headers' => [
		'User-Agent'  => 'BossInsightsApiClient/1.0',
		'Accept'      => 'application/json',
		'Account-Key' => $commonData['ACCOUNT_KEY']
	  ],
	  'body'    => json_encode([
		'action'      => 'allow_url',
		'url'         => $commonData['SITE_URL'],
		'environment' => $commonData['ENVIRONMENT']
	  ], JSON_THROW_ON_ERROR)
	]);
  } catch (GuzzleException $e) {
	echo $twig->render('error.twig', array_merge($commonData, [
	  'errorType'        => 'Error',
	  'errorName'        => 'failed to add Dyno domain to allow list',
	  'errorDescription' => 'error when communicating with admin api: ' . $e->getMessage()
	]));
	throw new Exception('failed to add Dyno domain to allow list');
  }

  if ($response->getStatusCode() !== 200) {
	echo $twig->render('error.twig', array_merge($commonData, [
	  'errorType'        => 'Error',
	  'errorName'        => 'failed to add Dyno domain to allow list',
	  'errorDescription' => 'received status code ' . $response->getStatusCode() . ' when communicating with admin api'
	]));
	throw new Exception('failed to add Dyno domain to allow list');
  }
  if (!touch('allow_url.tmp')) {
	echo $twig->render('error.twig', array_merge($commonData, [
	  'errorType'        => 'Error',
	  'errorName'        => 'failed to create file to indicate that allow list has been modified',
	  'errorDescription' => 'this may be caused because the script doesnt have filesystem write permissions to the project folder'
	]));
	throw new Exception('failed to create file to indicate that allow list has been modified');
  }
}