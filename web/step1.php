<?php
/**
 * @global array $commonData
 * @global Environment $twig
 */

use GuzzleHttp\Client;
use Twig\Environment;

require __DIR__ . '/../common.php';

if ($_POST) {

  $selfSigned = (bool)getenv('SELF_SIGNED_CERT');
  $client = new Client(['verify' => !$selfSigned]);

  /*
  detect if using Heroku Dyno, add dyno url to allow list for CORS
  this would not be used in production but is added as a convenience to accommodate Heroku app names
  */
  if(getenv('DYNO') !== false && !file_exists('allow_url.tmp')){
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

	if ($response->getStatusCode() !== 200) {
	  die('failed to add Dyno domain to allow list');
	}
	touch('allow_url.tmp');
  }


  // validate input, send an API request to create account, store result in session, redirect to step 2
  $email = filter_var(strtolower(trim($_POST['email'])), FILTER_VALIDATE_EMAIL);
  $legalName = filter_var(trim($_POST['legalName']), FILTER_SANITIZE_STRING);
  try {
	$newPassword = bin2hex(random_bytes(32));
  } catch (Exception $e) {
	error_log('failed to generate password');
	die('Error: failure to generate password');
  }

  // generate a unique account subdomain
  $subdomain = strtolower(substr(filter_var($legalName, FILTER_SANITIZE_URL), 0, 16)) . random_int(1,99999);

  $response = $client->request('POST', $commonData['ADMIN_URL'] . '/app/api.php', [
	'auth' => [$commonData['API_KEY'], $commonData['API_SECRET']],
	'headers' => [
	  'User-Agent' => 'BossInsightsApiClient/1.0',
	  'Accept' => 'application/json',
	  'Account-Key' => $commonData['ACCOUNT_KEY']
	],
	'body' => json_encode([
	  'email_admin' => $email,
	  'subdomain' => $subdomain,
	  'type' => 'standard',
	  'name' => $legalName,
	  'active' => 1,
	  'datashare' => 1,
	  'password' => $newPassword
	], JSON_THROW_ON_ERROR)
  ]);

  $result = [];
  if($response->getStatusCode() === 200){
	$result = json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
	if($result === null){
	  throw new Exception('invalid api response');
	}
  }else
  {
	throw new Exception('unable to communicate with admin api');
  }

  $_SESSION['admin_email'] = $email;
  $_SESSION['legal_name'] = $legalName;
  $_SESSION['password'] = $newPassword;
  $_SESSION['account_domain'] = $result['account_domain'];
  $_SESSION['subdomain'] = $result['subdomain'];
  $_SESSION['account_key'] = $result['account_key'];

  header('Location: step2.php');
  exit;
}

echo $twig->render('step1.twig', array_merge($commonData, []));

