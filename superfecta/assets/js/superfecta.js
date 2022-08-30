Sortable.create(rnavul, {
    animation: 150,
    onEnd: function(ui) {
        var scheme_json="[";
        $('#rnavul>li>a').each(function() { 
            console.log($(this).attr('href'));
            var urlParams = new URLSearchParams($(this).attr('href').split('?')[1]);
            var key = urlParams.get('extdisplay');
            scheme_json = scheme_json + '"'+ key +'"'
            scheme_json = scheme_json + ',';
        });
        scheme_json = scheme_json.slice(0,-1);
        scheme_json = scheme_json + "]";
        console.log(scheme_json);

        $.ajaxSetup({ cache: false });
        $.getJSON("config.php?quietmode=1&handler=file&module=superfecta&file=ajax.html.php&type=update_schemes", 
        {
            data: scheme_json
        },
        function(json) {
        });
    }
});
