Event.observe(window, 'load', init, false);

function operator_DoFSCommand(command, args) {
	if(command == "newevent") {
		newevent(args);
	}
}

// Hook for Internet Explorer
if (navigator.appName && navigator.appName.indexOf("Microsoft") != -1 && navigator.userAgent.indexOf("Windows") != -1 && navigator.userAgent.indexOf("Windows 3.1") == -1) {
    document.write('<SCRIPT LANGUAGE=VBScript\> \n');
    document.write('on error resume next \n');
    document.write('Sub operator_FSCommand(ByVal command, ByVal args)\n');
    document.write('  call operator_DoFSCommand(command, args)\n');
    document.write('end sub\n');
    document.write('</SCRIPT\> \n');
}

function toObject (texto) {
    var pairs = texto.split('&');
    return pairs.inject({}, function(params, pairString) {
      var pair = pairString.split('=');
      params[pair[0]] = pair[1];
      return params;
    });
}

var handlerFunc = function(t) {
    var query = t.responseText;
	var queryObj = toObject(query);
	for (var i in queryObj)
	{
		if(i.indexOf("texto")==0) {
			var numero = i.substr(5);
			var label = document.getElementById("label"+numero);
			var boton = document.getElementById("boton"+numero);
			var texto = queryObj[i];
			label.innerHTML=texto;
			boton.style.display='block';
		}
		if(i.indexOf("icono")==0) {
			var numero = i.substr(5);
			var iconphone = document.getElementById("phone"+numero);
			var texto = queryObj[i];
			iconphone.className="phone"+texto;
		}
	}
}

var errFunc = function(t) {
    alert('Error ' + t.status + ' -- ' + t.statusText);
}

function UpdateTimer() {

   if(timerID) {
      clearTimeout(timerID);
   }

   for(var i in tiempos ){
	   var   tick = document.getElementById("tick"+i);
   	   var   tDate = new Date();
	   var   elapsedTime = tDate.getTime() - tiempos[i];

       // hours
       var hours = parseInt(elapsedTime/3600000);
       var remaining = elapsedTime-(hours*3600000);
       // minutes
       var minutes = parseInt(remaining/60000);
       remaining = remaining-(minutes*60000);
       // seconds
       var seconds = parseInt(remaining/1000);

	   if (hours < 0)  { hours   = Math.abs(hours) }
	   if (minutes < 0){ minutes = Math.abs(minutes) }
	   if (seconds < 0){ seconds = Math.abs(seconds) }
	   if (hours  <10) { hours   = "0"+hours;   }
	   if (minutes<10) { minutes = "0"+minutes; }
	   if (seconds<10) { seconds = "0"+seconds; }

       var texto = "" + hours + ":" + minutes + ":" + seconds;
       tick.innerHTML=texto;
   }

   timerID = setTimeout("UpdateTimer()", 1000);
}

function debug(texto)
{
    loglines.push(texto);
    if (loglines.length>35) {
		loglines.shift();
    }   
	var textofinal = ""; 
	for(a=0;a<35;a++) {
		textofinal = textofinal + loglines[a] + "<BR>";
		
	}       
	win.getContent().innerHTML=textofinal;
}

function newevent(params) {
	var partes = params.split("|");
	docommand(partes[0],partes[1],partes[2]);
}

function replace(string,text,by) {
// Replaces text with by in string
    var strLength = string.length, txtLength = text.length;
    if ((strLength == 0) || (txtLength == 0)) return string;

    var i = string.indexOf(text);
    if ((!i) && (text != string.substring(0,txtLength))) return string;
    if (i == -1) return string;

    var newstr = string.substring(0,i) + by;

    if (i+txtLength < strLength)
        newstr += replace(string.substring(i+txtLength,strLength),text,by);

    return newstr;
}


function docommand(nro,comando,texto) { 

	var boton = document.getElementById("boton"+nro);
	var clid  = document.getElementById("clid"+nro);
	var mwi   = document.getElementById("mwi"+nro);
	var mcount= document.getElementById("mcount"+nro);

	debug(nro+","+comando+"="+texto);

	if(comando=="state") {
		if(texto=="busy") {
			boton.className="busy";
		}
		if(texto=="free") {
			if(tipofree[nro] == undefined) {
				tipofree[nro]="free";
				boton.className="free";
			} else {
				boton.className=tipofree[nro];			
			}
		}
	}

    if(comando=="park") { 
        boton.className="park"; 
        clid.innerHTML=texto; 
    } 

	if(comando ==  "settext") {
		clid.innerHTML=texto;
	}

	if(comando.match(/^info/)) {
		var cola = comando.substring(4);
        var textodecode = decodeBase64(texto);
        var i = textodecode.indexOf("\n");
		textodecode = replace(textodecode,'\n','<BR>');
	    $('phonetip'+nro).innerHTML=textodecode;
    }

	if(comando == "settimer") {

		var tick  = document.getElementById("tick"+nro);
		var partes = texto.split("@");
		var seconds = partes[0];
		var type = partes[1];

		var sDate = new Date();
		if(type=="UP" || type =="IDLE") {
			tiempos[nro] = sDate.getTime() - parseInt(seconds) * 1000;
		}
		if(type=="STOP") {
			delete tiempos[nro];
			tick.innerHTML="&nbsp;";
		}
	}

	if(comando.indexOf("ocupado")>=0) {
		boton.className="busy";
		clid.innerHTML=texto;
	}
	if(comando == "corto") {
		if(tipofree[nro] == undefined) {
			tipofree[nro]="free";
			boton.className="free";
		} else {
			boton.className=tipofree[nro];			
		}
		clid.innerHTML="&nbsp;";
	}
	if(comando=="ringing") {
		boton.className="ringing";
		clid.innerHTML=texto;
		new Effect.Pulsate(boton);
	}
	if(comando=="noregistrado") {
		boton.className="notregistered";
		clid.innerHTML="";
	}
	if(comando.indexOf("changelabel1")>=0) {
		if(texto=="original") {	
			tipofree[nro]='free';
		} else {
			tipofree[nro]='agent';
		}
		boton.className=tipofree[nro];
	}
	if(comando == "voicemail") {
		if(texto=="1") {
			mwi.style.visibility='visible';
		} else {
			mwi.style.visibility='hidden';
		}
	}
	if(comando == "voicemailcount") {
	    $('mwitip'+nro).innerHTML=texto;
	}

}

