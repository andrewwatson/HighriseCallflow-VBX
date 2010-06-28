<?php
$CI =& get_instance();
$plugin_info = $plugin->getInfo();
$highrise_callflow_user = PluginData::get('highrise_callflow_user');
?>
<style>
a.ajax_loader { background:url(<?php echo base_url() ?>assets/i/ajax-loader.gif); display:inline-block; width:16px; height:11px; vertical-align:middle; }
div.system_msg { display:inline-block; line-height:30px; vertical-align:center; }
div.system_msg > * { vertical-align:middle; }
div.vbx-applet div.section { margin-bottom:20px; }
span[class$="err"] { color:red; }
</style>

<div class="vbx-applet highrise_callflow_app">
    <?php if(empty($highrise_callflow_user)): ?>
    <div id="highrise_api_access" class="section">
        <h2>Highrise API Access</h2>
        <p>It looks like you are setting up for the first time. Please enter your access credentials so we can sync with Highrise.</p>

        <div class="vbx-input-container input" style="margin-bottom:10px;">
            <label>Highrise URL - The URL to your Highrise which is something like https://yoursite.highrisehq.com.</label>
            <input name="highrise_url" class="medium" type="text" value="" />
            <span class="highrise_url_err"></span>
        </div>

        <div class="vbx-input-container input" style="margin-bottom:10px;">
            <label>Token - Can be found under My Info in Highrise</label>
            <input name="highrise_token" class="medium" type="text" value="" />
            <span class="highrise_token_err"></span>
        </div>

        <div class="vbx-input-container input" style="margin-bottom:5px;">
            <label>Password - Your password used to login to Highrise</label>
            <input name="highrise_password" class="medium" type="password" value="" />
            <span class="highrise_password_err"></span>
        </div>

        <div style="line-height:30px;">
            <button class="inline-button submit-button" style="margin-top:5px; vertical-align:center;">
                <span>Test</span>
            </button>
            <div class="system_msg"></div>
        </div>

        <div style="clear:both;"></div>
    </div>
    <?php endif; ?>

    <div class="section">
        <h2>If Incoming Caller is in Highrise</h2>
        <?php echo AppletUI::dropZone('highrise_next', 'Drop item here'); ?>
    </div>

    <div class="section">
        <h2>If Incoming Caller is not in Highrise</h2>
        <?php echo AppletUI::dropZone('nonhighrise_next', 'Drop item here'); ?>
    </div>
</div>

<script>
var base_url = '<?php echo base_url() ?>';
var plugin_dir = '<?php echo $plugin_info['dir_name'] ?>';
</script>
