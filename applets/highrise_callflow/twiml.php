<?php
include('plugins/Highrise-VBX/highrise.php');

$CI =& get_instance();
$flow = @AppletInstance::getFlow();

$highrise_callflow_user = $CI->db->get_where('plugin_store', array('key' => 'highrise_callflow_user'))->row();
$highrise_callflow_user = json_decode($highrise_callflow_user->value);

define('HIGHRISE_URL', $highrise_callflow_user->url);
define('HIGHRISE_TOKEN', $highrise_callflow_user->token);
define('HIGHRISE_PASSWORD', $highrise_callflow_user->password);

$highrise_next = AppletInstance::getDropZoneUrl('highrise_next');
$nonhighrise_next = AppletInstance::getDropZoneUrl('nonhighrise_next');

$response = new Response();

$chk_people = highrise_client('/people/search.xml?criteria[phone]='.$_REQUEST['Caller']);

// If person is in highrise
if(!empty($chk_people->person)) {
    if(!empty($highrise_next)) $response->addRedirect($highrise_next);
// If person is not in highrise
} else {
    if(!empty($nonhighrise_next)) $response->addRedirect($nonhighrise_next);
}

$response->Respond();
?>
