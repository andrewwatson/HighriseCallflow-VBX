<?php
/*
    Function: highrise_client
        Client for Highrise CRM

    Parameters:
        $path - path of API and starts with /
        $method - GET, POST, PUT, DELETE
        $params - parameters to send to Highrise
*/
function highrise_client($path, $method='GET', $xml = '') 
{ // {{{
    $ch = curl_init();
    curl_setopt_array($ch, array(
        CURLOPT_URL => HIGHRISE_URL.$path,
        CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
        CURLOPT_HEADER => FALSE,
        CURLOPT_FOLLOWLOCATION => TRUE,
        CURLOPT_USERPWD => HIGHRISE_TOKEN.':'.HIGHRISE_PASSWORD,
        CURLOPT_RETURNTRANSFER => TRUE
    ));

    switch($method) {
        case 'GET':
            curl_setopt($ch, CURLOPT_HTTPGET, TRUE);
            break;

        case 'POST':
            curl_setopt($ch, CURLOPT_HTTPHEADER, Array("Content-Type: text/xml"));
            curl_setopt($ch, CURLOPT_POST, TRUE);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
            break;

        case 'PUT':
            curl_setopt($ch, CURLOPT_HTTPHEADER, Array("Content-Type: text/xml"));
            curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            break;

        case 'DELETE':
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
            break;

        default:
            return FALSE;
    }

    $results = curl_exec($ch);
    $ch_info = curl_getinfo($ch);
    $ch_error = curl_error($ch);

    if($ch_error) {
        error_log('CURL failed due to '.$ch_error);
        return FALSE;
    } else {
        if($ch_info['http_code'] >= 200 && $ch_info['http_code'] <= 300) {
            if(!empty($results) && $obj = simplexml_load_string($results)) {
                if(is_object($obj)) return $obj;

                return TRUE;
            } else {
                return $ch_info;
            }
        } else {
            return $results;
        }
    }

    return FALSE;
} // }}}

/*
    Function: highrise_phone_call
        Creates a new note for incoming calls
*/
function highrise_phone_call($person_id, $data) 
{ // {{{
    $xml =
        '<note>'.
            '<body>'.
                'Incoming Phone Call '.format_phone($data['Caller']).' on '.gmdate('M D g:i a', gmmktime() + (60*60*HIGHRISE_TIMEZONE)).':'."\n".
                $data['TranscriptionText']."\n".
                $data['RecordingUrl'].'.mp3'.
            '</body>'.
            '<subject-id type="integer">'.$person_id.'</subject-id>'.
            '<subject-type>Party</subject-type>'.
        '</note>';

    $new_note = highrise_client("/people/{$person_id}/notes.xml", 'POST', $xml); 

    return $new_note;
} // }}}
?>
