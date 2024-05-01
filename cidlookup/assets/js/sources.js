prev_source ='';

function edit_onsubmit() {
    defaultEmptyOK = false;
    if (!$('#form_description').val().trim().length)
        return warnInvalid($('#form_description'), errInvalidDescription);
    if ($('#sourcetype').val() == 'http' || $('#sourcetype').val() == 'https')    {
        if (!$.trim($('#http_host').val()).length)
            return warnInvalid($('#http_host'), errInvalidHTTPHost);
    }
    if ($('#sourcetype').val() == 'mysql')    {
        if (!$.trim($('#mysql_host').val()).length)
            return warnInvalid($('#mysql_host'), errInvalidMysqlHost);

        if (!$.trim($('#mysql_dbname').val()).length)
            return warnInvalid($('#mysql_dbname'), errInvalidMysqlDatabase);

        if (!$.trim($('#mysql_query').val()).length)
            return warnInvalid($('#mysql_query'), errInvalidMysqlQuery);

        if (!$.trim($('#mysql_username').val()).length)
            return warnInvalid($('#mysql_username'), errInvalidMysqlUsername);
    }
    if ($('#sourcetype').val() == 'opencnam' && $('#opencnam_professional_tier').is(':checked'))    {
        if (!$.trim($('#opencnam_account_sid').val()).length)
            return warnInvalid($('#opencnam_account_sid'), errInvalidAccountSID);

        if (!$.trim($('#opencnam_auth_token').val()).length)
            return warnInvalid($('#opencnam_auth_token'), errInvalidAuthToken);
    }
    $.LoadingOverlay('show');
    return true;
}

function displayInitalSourceParameters() {
    console.log('display initial');
    $.each(cid_modules, function(index, value) {
        $('#'+value).hide();
    });
    source = $('#sourcetype').val();
    source = (source == 'https') ? 'http' : source;
    $('#'+source).show();
}

//$(function() {
up.compiler('.content', function() {
    console.log('ready');
    $('#form_description').trigger('focus');
    $('#form_description').alphanum();
    displayInitalSourceParameters();

    // By default, don't display OpenCNAM professional stuff unless needed.
    if(!$('#opencnam_professional_tier').is(':checked')) {
        $('.opencnam_pro').hide()
    }

    $('#sourcetype').on('chosen:showing_dropdown', function(evt,params) {
    prev_source = $(this).val();
    prev_source = (prev_source == 'https') ? 'http' : prev_source;
    });

    $('#opencnam_professional_tier').on('change',function() {
        if($(this).is(':checked')) {
            $('.opencnam_pro').show();
        } else {
            $('.opencnam_pro').hide();
        }
    });

    $('#sourcetype').on('focus',function () {
        prev_source = $(this).val();
    }).on('change',function() {
        source = $(this).val();
        source = (source == 'https') ? 'http' : source;
        if($('#'+prev_source).length>0) {
            $('#'+prev_source).fadeOut("slow",function() { $('#'+source).fadeIn("slow"); });
        } else {
            $('#'+source).fadeIn("slow");
        }

        prev_source = source;
    });

})
