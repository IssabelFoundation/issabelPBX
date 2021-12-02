var myCodeMirror;

$(document).ready(function() {
    if($('#ttstext').length>0) {
        myCodeMirror = CodeMirror.fromTextArea($('#ttstext')[0],{
          lineNumbers: true,
          lineWrapping: true,
          styleActiveLine: true,
          styleActiveSelected: true,
          mode: 'application/json',
          gutters: ["CodeMirror-lint-markers"],
          lint:true
        });
        myCodeMirror.setSize(null,200);

        if($('#ttsengine_engine').val()=='') {
            $('#ttsengine_engine').val('pico');
            updateTemplate(1);
        } else {
            if($('#ttstext').val()=='') {
                updateTemplate();
            }
        }

    }
});

$('#ttsengine_engine').change(function(){
    updateTemplate(0);
});

function updateTemplate(force) {
    engine = $('#ttsengine_engine').val();
    if(typeof(ttstemplate[engine])!='undefined') {
         content = atob(ttstemplate[engine]);
         if(content=='') {
             $('#templatecell').hide();
             $('.CodeMirror').hide();
         } else {
             $('#templatecell').show();
             $('.CodeMirror').show();
             myCodeMirror.refresh();
             $('#ttstext').val(content);
             myCodeMirror.setValue(content);
             myCodeMirror.refresh();
         }
    }
    if(typeof(ttscommand[engine])!='undefined') {
         content = atob(ttscommand[engine]);
         if($('#ttsenginecmd').val()=='' || $('#ttstext').val()) {
             $('#ttsenginecmd').val(content);
         }
    }
}
