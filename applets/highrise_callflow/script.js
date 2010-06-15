if(typeof String.prototype.trim !== 'function') {
    String.prototype.trim = function() {
        return this.replace(/^\s+|\s+$/, ''); 
    }
}

$(document).ready(function() {
    function submitTestHighriseCredForm(app) {
        var url_el = $('input[name="highrise_url"]', app);
        var token_el = $('input[name="highrise_token"]', app);
        var password_el = $('input[name="highrise_password"]', app);
        var timezone = -((new Date()).getTimezoneOffset()/60);

        $('span[class$="_err"]').empty();
        $('div.system_msg', app).empty().css('color', 'inherit');

        var errors = [];
        if(url_el.val().trim() == '') errors.push({ name:'highrise_url', msg:'Highrise URL is required.' });
        else if(!url_el.val().match(/https:\/\/[a-z0-9]+\.highrisehq\.com/)) errors.push({ name:'highrise_url', msg:'Highrise URL nees to be like https://yoursite.highrisehq.com' });

        if(token_el.val().trim() == '') errors.push({ name:'highrise_token', msg:'Token is required.' });
        if(password_el.val().trim() == '') errors.push({ name:'highrise_password', msg:'Password is required.' });

        if(errors.length == 0) {
            $('div.system_msg', app).html('<a class="ajax_loader"></a> Testing your credentials.');
            $.post(
                base_url + 'config/HighriseCallflow-VBX?op=test_credentials',
                { url:url_el.val(), token:token_el.val(), password:password_el.val(), timezone:timezone },
                function(resp) {
                    try {
                        resp = resp.match(/JSON_DATA\>(.*)\<\/JSON_DATA/)[1];
                        resp = eval("(" + resp + ")");
                        var sys_msg = '';
                        var sys_msg_type = 'error';
                        sys_msg = resp.msg;
                        sys_msg_type = resp.type;
                    } catch(e) { sys_msg = 'Cannot validate your credentials due to an exception error.'; }

                    $('div.system_msg', app).html(sys_msg).css('color', sys_msg_type == 'error' ? 'red' : 'green');
                },
                'text'
            );
        } else {
            $('div.system_msg', app).html('Cannot test credentials because of form validation errors.').css('color', 'red');
            $.each(errors, function(k, v) {
                if(v.name == 'highrise_url') $('span.highrise_url_err', app).text(v.msg);
                else if(v.name == 'highrise_token') $('span.highrise_token_err', app).text(v.msg);
                else if(v.name == 'highrise_password') $('span.highrise_password_err', app).text(v.msg);
            });
        }
    }

    var app = $('.vbx-applet.highrise_callflow_app');
    $('button.submit-button', app).live('click', function(e) {
        var instance = $(this).parent().parent().parent();
        submitTestHighriseCredForm(instance);
        e.preventDefault();
    });
});
