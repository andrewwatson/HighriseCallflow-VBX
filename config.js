if(typeof String.prototype.trim !== 'function') {
    String.prototype.trim = function() {
        return this.replace(/^\s+|\s+$/, ''); 
    }
}

var config_page = {
    initialize: function() {
        config_page.render();
    },

    submit_delete_creds: function() {
        $.post(
            base_url + 'config/HighriseCallflow-VBX?op=delete_credentials',
            function(resp) {
                resp = resp.match(/JSON_DATA\>(.*)\<\/JSON_DATA/)[1];
                $('input[name="highrise_url"]').val('');
                $('input[name="highrise_token"]').val('');
                $('input[name="highrise_password"]').val('');
                $('a.delete_creds_btn').css('display', 'none');
                $('div.system_msg').html('').css('color', 'inherit');
            },
            'text'
        );
    },

    submit_save_creds: function() {
        var url_el = $('input[name="highrise_url"]');
        var token_el = $('input[name="highrise_token"]');
        var password_el = $('input[name="highrise_password"]');
        var timezone = -((new Date()).getTimezoneOffset()/60);

        $('span[class$="_err"]').empty();
        $('div.system_msg').empty().css('color', 'inherit');

        var errors = [];
        if(url_el.val().trim() == '') errors.push({ name:'highrise_url', msg:'Highrise URL is required.' });
        else if(!url_el.val().match(/https:\/\/[a-z0-9]+\.highrisehq\.com/)) errors.push({ name:'highrise_url', msg:'Highrise URL nees to be like https://yoursite.highrisehq.com' });

        if(token_el.val().trim() == '') errors.push({ name:'highrise_token', msg:'Token is required.' });
        if(password_el.val().trim() == '') errors.push({ name:'highrise_password', msg:'Password is required.' });

        if(errors.length == 0) {
            $('div.system_msg').html('<a class="ajax_loader"></a> Testing your credentials.');
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

                        if(resp.key == 'SUCCESS') $('a.delete_creds_btn').css('display', 'inline-block');
                    } catch(e) { sys_msg = 'Cannot validate your credentials due to an exception error.'; sys_msg_type = 'error'; }

                    $('div.system_msg').html(sys_msg).css('color', sys_msg_type == 'error' ? 'red' : 'green');
                },
                'text'
            );
        } else {
            $('div.system_msg').html('Cannot test credentials because of form validation errors.').css('color', 'red');
            $.each(errors, function(k, v) {
                if(v.name == 'highrise_url') $('span.highrise_url_err').text(v.msg);
                else if(v.name == 'highrise_token') $('span.highrise_token_err').text(v.msg);
                else if(v.name == 'highrise_password') $('span.highrise_password_err').text(v.msg);
            });
        }
    },

    render: function(name) {
        var that = config_page; 

        switch(name) {
            case 'highrise_api_access':
                var section_el = $('#highrise_api_access');

                $('#save_cred_btn', section_el).click(function() {
                    that.submit_save_creds();
                });

                $('a.delete_creds_btn', section_el).click(function() {
                    if(confirm('Are you sure you want to delete your Highrise credentials?')) that.submit_delete_creds();
                });
                
                if($('input[name="highrise_token"]').val() == '') $('a.delete_creds_btn').css('display', 'none');
                break;

            case undefined: 
                that.render('highrise_api_access');
                break;
        }
    }
}

$(document).ready(function() {
   config_page.initialize(); 
});
