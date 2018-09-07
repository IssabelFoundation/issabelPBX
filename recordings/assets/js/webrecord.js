
var URL = window.URL || window.webkitURL;
var gumStream;                         //stream from getUserMedia()
var rec;                             //Recorder.js object
var input;                             //MediaStreamAudioSourceNode we'll be recording


// shim for AudioContext when it's not avb. 
var AudioContext = window.AudioContext || window.webkitAudioContext;
var audioContext //audio context to help us record


//webkitURL is deprecated but nevertheless
$('#recordButton').on('click', function() { startRecording(); });
$('#stopButton').on('click', function() { stopRecording(); });
$('#pauseButton').on('click', function() { pauseRecording(); });

$(document).ready(function() {
    $('#stopButton').prop('disabled',true);
    $('#pauseButton').prop('disabled',true);
    $("#tabs" ).tabs(); 
});

function startRecording() {
    console.log("recordButton clicked");

    /*
        Simple constraints object, for more advanced audio features see
        https://addpipe.com/blog/audio-constraints-getusermedia/
    */
    
    var constraints = { audio: true, video:false }

     /*
        Disable the record button until we get a success or fail from getUserMedia() 
    */

    $('#recordButton').prop('disabled',true);
    $('#stopButton').prop('disabled',false);
    $('#pauseButton').prop('disabled',false);

    /*
        We're using the standard promise based getUserMedia() 
        https://developer.mozilla.org/en-US/docs/Web/API/MediaDevices/getUserMedia
    */

    navigator.mediaDevices.getUserMedia(constraints).then(function(stream) {
        console.log("getUserMedia() success, stream created, initializing Recorder.js ...");

        /*
            create an audio context after getUserMedia is called
            sampleRate might change after getUserMedia is called, like it does on macOS when recording through AirPods
            the sampleRate defaults to the one set in your OS for your playback device

        */
        audioContext = new AudioContext();

        //update the format 
        document.getElementById("formats").innerHTML="Format: 1 channel pcm @ "+audioContext.sampleRate/1000+"kHz"

        /*  assign to gumStream for later use  */
        gumStream = stream;
        
        /* use the stream */
        input = audioContext.createMediaStreamSource(stream);

        /* 
            Create the Recorder object and configure to record mono sound (1 channel)
            Recording 2 channels  will double the file size
        */
        rec = new Recorder(input,{numChannels:1})

        //start the recording process
        rec.record()

        console.log("Recording started");

    }).catch(function(err) {
          //enable the record button if getUserMedia() fails
        $('#recordButton').prop('disabled',false);
        $('#stopButton').prop('disabled',true);
        $('#pauseButton').prop('disabled',true);
    });
}

function pauseRecording(){
    console.log("pauseButton clicked rec.recording=",rec.recording );
    if (rec.recording){
        //pause
        rec.stop();
        $('#pauseButton').html("Resume");
    }else{
        //resume
        rec.record()
        $('#pauseButton').html("Pause");

    }
}

function stopRecording() {
    console.log("stopButton clicked");

    //disable the stop button, enable the record too allow for new recordings
    $('#recordButton').prop('disabled',false);
    $('#stopButton').prop('disabled',true);
    $('#pauseButton').prop('disabled',true);
    //reset button just in case the recording is stopped while paused
    $('#pauseButton').html("Pause");
    
    //tell the recorder to stop the recording
    rec.stop();

    //stop microphone access
    gumStream.getAudioTracks()[0].stop();

    //create the wav blob and pass it on to createDownloadLink
    rec.exportWAV(createDownloadLink);
}

function createDownloadLink(blob) {
    
    var url = URL.createObjectURL(blob);
    var au = document.createElement('audio');
    var li = document.createElement('li');
    var link = document.createElement('a');

    //name of .wav file to use during upload and download (without extendion)
    var filename = formatDate();

    //add controls to the <audio> element
    au.controls = true;
    au.src = url;

    //save to disk link
    link.href = url;
    link.download = filename+".wav"; //download forces the browser to donwload the file using the  filename
    link.innerHTML = "Save to disk";

    //add the new audio element to li
    li.appendChild(au);
    
    //add the filename to the li
//    li.appendChild(document.createTextNode(filename+".wav "))

    //add the save to disk link to li
    //li.appendChild(link);
 
    var fileid = filename.replace(/[^a-zA-Z0-9]/g,'');  
 
    var name = document.createElement('input');
    name.setAttribute("type", "hidden");
    name.setAttribute("id", fileid);
 
    //upload link
    var upload = document.createElement('a');
    upload.href="#";
    upload.innerHTML = "Upload";
    upload.setAttribute("class","btn btn-primary");
    upload.setAttribute("style","color:#fff;");
    upload.addEventListener("click", function(event){
          var xhr=new XMLHttpRequest();
          xhr.onload=function(e) {
              if(this.readyState === 4) {
                  window.location.search += "&fname="+filename;
                  //window.location.reload(true); 
              }
          };
          var fd=new FormData();
          fd.append("ivrfile",blob, filename+".wav");
          fd.append("display", "recordings");
          fd.append("fname", filename);
          xhr.open("POST",window.location.href,true);
          xhr.send(fd);
          $('body').css('opacity','0.5');
    })
    li.appendChild(upload)//add the upload link to li

    //add the li element to the ol
    recordingsList.appendChild(li);
}

function formatDate() {
    var d = new Date(),
        month = '' + (d.getMonth() + 1),
        day = '' + d.getDate(),
        year = d.getFullYear();

    if (month.length < 2) month = '0' + month;
    if (day.length < 2) day = '0' + day;

    return [year, month, day].join('-');
}
