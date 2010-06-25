<?php
$CI =& get_instance();
$plugin = OpenVBX::$currentPlugin;
$op = @$_REQUEST['op'];
$highrise_callflow_user = PluginData::get('highrise_callflow_user');

if(!function_exists('json_encode')) {
    include($plugin->plugin_path.'/vendors/json.php');
}

if($op == 'test_credentials') 
{ // {{{
    try {
        $token = @$_REQUEST['token'];
        $password = @$_REQUEST['password'];
        $url = @$_REQUEST['url'];
        $timezone = (int) @$_REQUEST['timezone'];

        $errors = array();
        if(empty($token)) $errors[] = array( 'msg'=>'Token is required.', 'name'=>'highrise_token' );
        if(empty($password)) $errors[] = array( 'msg'=>'Password is required.', 'name'=>'highrise_password' );
        if(empty($url)) $errors[] = array( 'msg'=>'URL to your Highrise is required.', 'name'=>'highrise_url' );
        else if(strpos($url, 'highrise') === FALSE) $errors[] = array( 'msg'=>'This is an invalid Highrise URL.', 'name'=>'highrise_url' );

        if(!empty($errors)) throw new Exception('FORM_VALIDATION_ERROR');

        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_URL => $url.'/account.xml',
            CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
            CURLOPT_HEADER => FALSE,
            CURLOPT_FOLLOWLOCATION => FALSE, // to differeniate from http and https
            CURLOPT_USERPWD => "$token:$password",
            CURLOPT_RETURNTRANSFER => TRUE
        ));
        $results = curl_exec($ch);
        $ch_info = curl_getinfo($ch);

        if(!$results) {
            $curl_error = curl_error($ch);
            if($curl_error != "couldn't connect to host") {
                throw new Exception('CANNOT_CONNECT_TO_HOST');
            }
        } else {
            if(strpos($results, 'Access denied') !== FALSE) throw new Exception('INVALID_CREDENTIALS');
            elseif(strpos($results, '<?xml version="1.0"') !== FALSE) throw new Exception('SUCCESS');
        }

        throw new Exception('EXCEPTION');
    } catch(Exception $e) {
        switch($e->getMessage()) {
            case 'CANNOT_CONNECT_TO_HOST':
                $results = array(
                    'msg' => "Cannot connect to $url.",
                    'key' => 'CANNOT_CONNECT_TO_HOST',
                    'type' => 'error',
                    'data' => array(
                        'url' => $url,
                        'errors' => array(
                            'name' => 'highrise_url',
                            'msg' => 'Cannot connect to this url.'
                        )
                    )
                );
                break;

            case 'FORM_VALIDATION_ERROR':
                $results = array(
                    'msg' => 'There are errors on the form. Please fix and try again.',
                    'key' => 'FORM_VALIDATION_ERROR',
                    'type' => 'error',
                    'data' => array(
                        'errors' => $errors
                    )
                );
                break;

            case 'INVALID_CREDENTIALS':
                $results = array(
                    'msg' => 'The credentials you entered are invalid.',
                    'key' => 'INVALID_CREDENTIALS',
                    'type' => 'error'
                );
                break;

            case 'OP_REQUIRED':
                $results = array(
                    'msg' => 'No operation selected.',
                    'key' => 'OP_REQUIRED',
                    'type' => 'error'
                );
                break;

            case 'SUCCESS':
                // If credentials are valid, store it to plugin store for this user
                PluginData::set('highrise_callflow_user', array(
                    'url' => $url,
                    'token' => $token,
                    'password' => $password,
                    'timezone' => $timezone
                ));

                $results = array(
                    'msg' => 'Awesome! Your credentials are valid.',
                    'key' => 'SUCCESS',
                    'type' => 'success'
                );
                break;

            default:
            case 'EXCEPTION':
                $results = array(
                    'msg' => 'An unexpected error occurred.',
                    'key' => 'EXCEPTION',
                    'type' => 'error'
                );
                break;
        }
    }
    echo '<JSON_DATA>'.json_encode($results).'</JSON_DATA>';
} // }}}

elseif($op == 'delete_credentials')
{ // {{{
    PluginData::set('highrise_callflow_user', '');
    $results = array(
        'msg' => 'Highrise credentials erased.',
        'key' => 'SUCCESS',
        'type' => 'success'
    );
    echo '<JSON_DATA>'.json_encode($results).'</JSON_DATA>';
} // }}}
?>

<?php if(empty($op)): ?>
<style>
span[class$="_err"] { color:red; }
a.ajax_loader { background:url(<?php echo base_url() ?>assets/i/ajax-loader.gif); display:inline-block; width:16px; height:11px; vertical-align:middle; }
div.system_msg { display:inline-block; line-height:30px; vertical-align:center; }
div.system_msg > * { vertical-align:middle; }
</style>

<div class="vbx-content-menu vbx-content-menu-top">
    <h2 class="vbx-content-heading">Highrise Callflow Settings</h2>
</div>

<div class="vbx-applet" style="background-color:white;">
    <div id="highrise_api_access" class="section">
        <h2>API Access Credentials</h2>
        <p>Please enter your access info so we can update Highrise with incoming messages.</p>

        <div class="vbx-input-container input" style="margin-bottom:10px;">
            <label>Highrise URL - The URL to your Highrise which is something like https://yoursite.highrisehq.com.</label>
            <input name="highrise_url" class="medium" type="text" value="<?php echo @$highrise_callflow_user->url ?>" />
            <span class="highrise_url_err"></span>
        </div>

        <div class="vbx-input-container input" style="margin-bottom:10px;">
            <label>Token - Can be found under My Info in Highrise</label>
            <input name="highrise_token" class="medium" type="text" value="<?php echo @$highrise_callflow_user->token ?>" />
            <span class="highrise_token_err"></span>
        </div>

        <div class="vbx-input-container input" style="margin-bottom:5px;">
            <label>Password - Your password used to login to Highrise</label>
            <input name="highrise_password" class="medium" type="password" value="<?php echo @$highrise_callflow_user->password ?>" />
            <span class="highrise_password_err"></span>
        </div>

        <div style="line-height:30px;">
            <button id="save_cred_btn" class="inline-button submit-button" style="margin-top:5px; vertical-align:center;">
                <span>Save</span>
            </button>
            <a class="delete_creds_btn" href="#">Delete</a>
            <div class="system_msg"></div>
        </div>

        <div style="clear:both;"></div>
    </div><!-- #highrise_api_access -->
</div>

<script>
var base_url = '<?php echo base_url() ?>';
</script>

<?php OpenVBX::addJS('config.js') ?>

<?php endif; ?>
