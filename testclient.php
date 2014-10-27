<?php

ini_set("display_errors",ON);

require_once 'class.tx_shscoutnetwebservice_sn.php';


$sn = new tx_shscoutnetwebservice_sn();

// Read Api
echo '---------- read kalender elements -----------------'."\n";
$events = $sn->get_events_for_global_id_with_filter('4',array('limit'=>'1','before'=>'12.01.2012'));

echo 'got '.count($events).' events'."\n";

foreach ($events as $event)
	echo $event['Title']."\n";

echo '---------- read index elements -----------------'."\n";
$indexes = $sn->get_index_for_global_id_with_filter('4',array('deeps'=>'2'));

echo 'got '.count($indexes).' indexes'."\n";

$diozese = $indexes['4'];

echo $diozese['name'].' ('.$diozese['number'].')'."\n";

foreach ($diozese->getChildren() as $bezirk) {
	echo '-'.$bezirk['name'].' ('.$bezirk['number'].')'."\n";

	foreach ($bezirk->getChildren() as $stamm) {
		echo '--'.$stamm['name'].' ('.$stamm['number'].')'."\n";
	}
}

// Write Api
echo '---------- write get scoutnetConnect button -----------------'."\n";

// set the credentials
$ScoutnetProviderName = '<YOUR PROVIDER NAME HERE>';
$AES_key = '<YOUR AES KEY HERE>';
$AES_iv = '<YOUR AES IV HERE>'; 

$sn->set_scoutnet_connect_data($ScoutnetProviderName, $AES_key, $AES_iv);

// ask for login button
// must be post otherwise the server does not like this
$return_url = 'http://localhost/testclient.php';

echo $sn->get_scoutnetConnectLoginButton($return_url,true)."\n";

echo '---------- write get user api key -----------------'."\n";
// for testing purpose we got this auth back:
// normaly we get it via get parameter: $_GET['auth']
$_GET['auth'] = 'VxyPg-OevS4OSCM6w1w-FstjPx1aMPxEoEeju0Fe44Sfcm2paXyIjRveFy4qN52vAZINKcH7B8i76XXK6WcTTHUekrzyBaFrv9p-hWR17nfwPV4MtShCu_DURRquVEpPtTNLHiAwOUitpWdunk9Rx9CEE6m6EMwhjyiYD5XLFQjuhG4A8-FlMDVoJYKEPQAskOO1ODI5Fe2k6jFqYOS2UwPRSOTNzBenQ-_zF49kZO2PwJsVMm68qS4RsnLfwqaWL2XgQXdghNXkeCkiPMyLAs3kd-4KVgcjmjtUGqkhMg3GFgEiixDFcWOrAWQiNZVfxT0V8eL0LlvRwSaJ3TxjZ95SJTJTfTn9b1_myZyJXBO_4UtnxIhhv0fX8TnGW3O0fNyqfdL3jT7IQG4f5N-pPoUFVUV_75gKKLmIMaEb_YVnIzj4oWSuXLdlPz1tvIcRXCbxYm_m-ooG14QApssD8YBsGDkXIOcmAt2IgTpvTgKa_wPf84Z4rUQEXnhnlIGIHpljH4AxVcg9crPpfY1jkKwisjZrGLf3rTig-5pDMyShnnXSNU5n33JcQTK8YvmboLGCLSnavGn348OcoOOypg~~';

// this function gets the data from $_GET['auth']
$data = $sn->getApiKeyFromData();

$scoutnetUser = $data['user'];
$api_key = $data['api_key'];

echo 'Welcome '.$scoutnetUser.'. Your Api key is: '.$api_key."\n";

// Save this data here

echo '---------- write check user rights -----------------'."\n";

// this is connected to the aes key!
$ssid = 17;

// check if we have right to edit our kalender
$rights = $sn->has_write_permission_to_calender($ssid, $scoutnetUser, $api_key);

// code == 0 -> user has access
// code == 1 -> user has no access

if ($rights['code'] !== 0) {
	echo "you do not have permission to access this calender. We ask for rights.\n";
	// ask for rights
	$sn->request_write_permissions_for_calender($ssid, $scoutnetUser, $api_key);

	// this sends an email to the admin and asks for rights
} else {
	echo "you can access the kalender.\n";
}

echo '---------- write get Kalender Meta tags -----------------'."\n";

$kalenders = $sn->get_kalender_by_global_id(array($ssid));
// only use first
$kalender = $kalenders[0];

// this contains all used and forced kategories
//print_r($kalender);

echo '---------- write create event -----------------'."\n";

$testEvent = Array(
	'ID' => -1, // id of event to update -1 for new event
	'SSID' => $ssid,
	'Title' => 'F+F Mitgliederversammlung',
	'Organizer' => 'Freundes- und Förderkreis',
	'Target_Group' => 'Freunde',
	'Start' => 1354294800,
	'End' => 1354294800,
	'All_Day' => false,
	'ZIP' => '',
	'Location' => 'Tagungs- und Gästehaus Rolandstr.',
	'URL_Text' => '',
	'URL' => '',
	'Description' => '',
	'Stufen' => Array (),
	'Keywords' => Array (
		'193' => 1,
		'543' => 1,
	)
);

$testEvent = $sn->write_event($testEvent['ID'], $testEvent, $scoutnetUser, $api_key);
echo "event written. It has ID ".$testEvent['ID']."\n";

echo '---------- write delete event -----------------'."\n";
$sn->delete_event($ssid, $testEvent['ID'], $scoutnetUser, $api_key);
echo 'event deleted.'."\n";
