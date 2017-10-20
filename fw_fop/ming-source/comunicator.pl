#!/usr/bin/perl -w
#  Flash Operator Panel.    http://www.asternic.org
#
#  Copyright (c) 2004 Nicolas Gudino. All rights reserved.
#
#  Nicolas Gudino <nicolas@house.com.ar>
#
#  This program is free software, distributed under the terms of
#  the GNU General Public License.
#
#  THIS SOFTWARE IS PROVIDED BY THE CONTRIBUTORS ``AS IS'' AND ANY EXPRESS OR
#  IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES
#  OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.  
#  IN NO EVENT SHALL THE CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
#  INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT
#  NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, 
#  DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY
#  OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING 
#  NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, 
#  EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

use SWF qw(:ALL);
use SWF::Constants qw(:Text :Button :DisplayItem :Fill);

SWF::setScale(1);
SWF::useSWFVersion(6);
my $movie = new SWF::Movie();
$movie->setDimension(100, 100);
$movie->setBackground(0xFF, 0xFF, 0xFF);
$movie->setRate(30);
$movie->add(new SWF::Action(<<"EndOfActionScript"));

var dummyVar=(getTimer()+random(100000));

if(context != undefined) {
	context = context.toUpperCase();
	colaEvento(0,"debug","contexto leido "+context);
	if(context == "DEFAULT") {	context=""; }
} else {
	colaEvento(0,"debug","context undefined");
	context="";
}

var archivo = "variables"+context+".txt?rand="+dummyVar;

vr = new LoadVars ();

vr.onLoad = function (success)
{ 
	if (success == true) { 
		colaEvento(0,"debug","Ok al leer "+archivo);
		nextFrame();
	} else {
		colaEvento(0,"debug","Fallo al leer "+archivo);
		stop();
	}
};

vr.load(archivo);

EndOfActionScript

$movie->nextFrame(); 
$movie->add(new SWF::Action(<<"EndOfActionScript"));

function conecta() {
	_global.sock = new XMLSocket;
	_global.sock.onConnect = handleConnect;
	_global.sock.onClose = handleDisconnect;
	_global.sock.onXML = handleXML;
    if(_global.port   == undefined) {
        _global.port = 4445;
    }
    if(_global.server == undefined) {
	    _global.sock.connect(null, _global.port);
        _global.server_print = "default";
    } else {
	    _global.sock.connect(_global.server, _global.port);
        _global.server_print = _global.server;
    }
}

function handleConnect(connectionStatus){

    if (connectionStatus) {
		colaEvento(0,"debug","Conectado! con contexto: "+context);
        _global.reconecta = 0;
		if(_global.enable_crypto==1) {
        	envia_comando("contexto", 0, 0);
		} else {
        	envia_comando("contexto", 1, 0);
		}
		if(restrict != undefined) {
			envia_comando("restrict",restrict,0);
		}
    } else {
		colaEvento(0,"debug","No pude conectar!");
        _global.reconecta = 1;
    }
}

function handleXML(doc){
 	var e = doc.firstChild;
	if (e != null) {
		if (e.nodeName == "response") {
		    var numeroboton = e.attributes.btn; // btn is the button number
			var comando     = e.attributes.cmd;
			var textofinal  = e.attributes.data;

			if (_global.key != undefined) {
				if(_global.enable_crypto == 1) {
					comando    = decrypt(comando,    _global.key);
					if (textofinal.length > 0) {
						textofinal = decrypt(textofinal, _global.key);
					} 
				} 
			} 


			var botonparte = numeroboton.split("@");
			var boton_numero = botonparte[0];
			var boton_contexto = botonparte[1];
			var timernumber = 0;


			if (boton_contexto == undefined) {
				boton_contexto = "";
			}
			if (_root.context == undefined) {
				_root.context = "";
			}


			if (comando == "key") {
				_global.key = textofinal;
				return;
			}

			if (comando == "restrict") {
				_global.restrict = numeroboton;
                _global.mybutton = numeroboton;
			}

			if (comando == "version") {
				if(textofinal != _global.swfversion) 
				{
					_global.statusline=vr.version_mismatch;
				} else {
					_global.statusline="";
				}
			}

			if (_root.context == boton_contexto) {
				if(_global.restrict != undefined) {
					if(_global.restrict == boton_numero) {
						colaEvento(boton_numero,comando,textofinal);
					}
				} else {
					colaEvento(boton_numero,comando,textofinal);
				}
			}
			// endif root.context
		}
		// endif == response
	}
	// endiff e != null
}

function colaEvento(boton_numero,comando,textofinal) {

	if(_global.VersionFlash == "MAC") {
		// GetURL method for MAC
		jsQueue.push("javascript: docommand('"+boton_numero+"','"+comando+"','"+textofinal+"')");
	} else {
		// FSCommand method for anything else
		jsQueue.push("newevent;"+boton_numero+"|"+comando+"|"+textofinal);
	}
}


function handleDisconnect(){
        delete _global.key;
        _global.reconecta = 1;
		colaEvento(0,"debug","Attempt reconnection");
}


Inicia_Variables = function () {
	_global.jsQueue = new Array();
	var flashVersion = System.capabilities.version;
    var datos = flashVersion.split(" ");
	_global.VersionFlash = datos[0];
	_global.server = vr.server;
	_global.port = vr.port;
	_global.enable_crypto = Number(vr.enable_crypto);
	if (isNaN(_global.enable_crypto)) {
		_global.enable_crypto=0;
	} else {
		if(_global.enable_crypto != 0) {
			_global.enable_crypto = 1;
		}
	}
};


recarga = function () {
  	if(_global.restart == 1) {
		// Send command to restart Asterisk
		envia_comando("restart","1","1");
	} else {
		// Reloads FLASH client
		delete _global.key;
		var incontext = context;
		var inbutton = mybutton;
		var inrestrict = _global.restrict;
		var indial = dial;
		var innohighlight = nohighlight;
		for (var a in _root) {
			if (typeof (_root[a]) == "object") {
				removeMovieClip(_root[a]);
			}
			if (typeof (_root[a]) == "movieclip") {
				removeMovieClip(_root[a]);
			}
		}
		_global.context = incontext;
		_global.mybutton = inbutton;
		_global.restrict = inrestrict;
		_global.dial = indial;
		_global.nohighlight = inhighlight;
		stop();
		gotoAndPlay(1);
		}
};


_root.onEnterFrame = function() {

		if(_global.jsQueue.length>0) {
			if(_global.VersionFlash == "MAC") {
				getURL(jsQueue.shift());
			} else {
				var partes = jsQueue.shift().split(";");
				var comando=partes[0];
				var params=partes[1];
				getURL("FSCommand:"+comando,params);
			}
		}

};

Timers = function () {

	if (_global.reconecta == 1) {
		delete setInterval;
		delete _global.key;
		recarga();
		return;
	}
};

setInterval(Timers, 10000);

function ExtraeNumeroClip(name) {
	var destino = "";
	name = name._name;
	for (var s = 0; s<name.length; s++) {
		var c = name.charAt(s);
		if (c<"0" || c>"9") {
		} else {
			destino = destino+""+c;
		}
		if (c == ".") {
			destino = "";
		}
	}
	return destino;
}

envia_comando = function (comando, origen, destino) {
	if (comando != "bogus" && comando != "contexto" && comando != "restrict") {
		if (_global.restrict!=0) {
			if(comando == "cortar") {
				origen_number = ExtraeNumeroClip(origen);
			} else {
				origen_number = origen;
			}
			if(_global.restrict != undefined) {
			    if (_global.restrict == origen_number ) {
   			    } else {
				return;
			    }
			} 
		}
	}
	message = new XML();
	message_data = message.createElement("msg");
	if (_root.context.length>0) {
		agrega_contexto = "@"+context;
	}
	if (agrega_contexto == undefined) {
		agrega_contexto = "";
	}
	if (_level0.claveinput.secret == undefined) {
		_level0.claveinput.secret = "";
	}
	if (_global.claveingresada == undefined && ( comando != "contexto" && comando != "bogus" && comando != "dial" && comando != "restrict")) {
		_root.codebox._visible = true;
		Selection.setFocus(_root.codebox.claveform);
		_root.codebox.swapDepths(_root.log);
		return;
	}
	// var clave=_level0.claveinput.secret+_global.key;
	var clave = _global.claveingresada+_global.key;
	var md5clave = "";
	var md5clave = calcMD5(clave);
	if (comando == "contexto" || comando == "restrict") {
		md5clave = "";
	}
	message_data.attributes.data = origen+agrega_contexto+"|"+comando+destino+"|"+md5clave;
	message.appendChild(message_data);
	_global.sock.send(message);
	var clave = "";
};

function LTrim(str) {
	var whitespace = new String(" \t\n\r");
	var s = new String(str);
	if (whitespace.indexOf(s.charAt(0)) != -1) {
		var j = 0, i = s.length;
		while (j<i && whitespace.indexOf(s.charAt(j)) != -1) {
			j++;
		}
		s = s.substring(j, i);
	}
	return s;
}

function RTrim(str) {
	var whitespace = new String(" \t\n\r");
	var s = new String(str);
	if (whitespace.indexOf(s.charAt(s.length-1)) != -1) {
		var i = s.length-1;
		// Get length of string
		while (i>=0 && whitespace.indexOf(s.charAt(i)) != -1) {
			i--;
		}
		s = s.substring(0, i+1);
	}
	return s;
}

function Trim(str) {
	return RTrim(LTrim(str));
}

function setDND(obj, item) {
	var nroboton = ExtraeNumeroClip(obj);
	envia_comando("dnd", nroboton, nroboton);
}

function genera_selecttimeout() {

	_global.positionselect = 0;
	test = attachMovie("option","optionselected", getNextHighestDepth(),  {_x:800, _y:6});
	test._visible = true;
	test.legend = "No timeout";

	test.onPress = function() {
	     _root.despliega_select();
	};


	 for (a=0; a<5; a++) {
		var b=a+1;
		if (_global.opcionesTimeout[a] != undefined) {

			testa = attachMovie("option","option"+a, getNextHighestDepth(),  {_x:800, _y:(b*22)+6});
			testa.legend = _global.opcionesTimeout[a];
			testa._visible = false;


			testa.onRollOver = function() {
           	 	this.legend = "* "+this.legend;
        	};

   		 	testa.onRollOut = function() {
	            this.legend = this.legend.substring(2, this.legend.length);
    		};

			testa.onPress = function() {
	            this.legend = this.legend.substring(2, this.legend.length);
				var posicion = ExtraeNumeroClip(this);
				_global.timeout_value = _global.opcionesTimeoutSecs[posicion];
				_root.muestra_selecttimeout(0);
				_root.selectbox1.gotoAndStop(1);
				_root.optionselected._visible=true;
				_root.optionselected.legend = this.legend;
			};
		}
	}
};

function muestra_selecttimeout(value) {
	 for (a=0; a<5; a++) {
	 	var v = eval("_root.option"+a);
		if(value) {
			v._visible = true;
		} else {
			v._visible = false;
		}
	 }
};

function despliega_select() {
	_root.optionselected._visible=false;
	_root.selectbox1.gotoAndStop(2);
	_root.muestra_selecttimeout(1);

};

function base64_decode(opString) {
	if ( opString == undefined ) {
		return;
	} 
	var str = opString;
	var base64s = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/";
	var bits, bit1, bit2, bit3, bit4, i = 0;
	var decOut = "";
	for (i=0; i<str.length; i += 4) {
		bit1 = (base64s.indexOf(str.charAt(i)) & 0xff) << 18 ;
		bit2 = (base64s.indexOf(str.charAt(i+1)) & 0xff) << 12 ;
		bit3 = (base64s.indexOf(str.charAt(i+2)) & 0xff) << 6 ;
		bit4 = (base64s.indexOf(str.charAt(i+3)) & 0xff);
		bits = bit1 | bit2 | bit3 | bit4;
		decOut += String.fromCharCode((bits & 0xff0000) >> 16, (bits & 0xff00) >> 8, bits & 0xff);
	}
	if (str.charCodeAt(i-2) == 61) {
		return decOut.substring(0, decOut.length-2);
	} else if (str.charCodeAt(i-1) == 61) {
		return decOut.substring(0, decOut.length-1);
	} else {
		return decOut.substring(0, decOut.length);
	}
};



// MD5 ROUTINE
/*
 * Convert a 32-bit number to a hex string with ls-byte first
 */
var hex_chr = "0123456789abcdef";
// 
// somehow the expression (bitAND(b, c) | bitAND((~b), d)) didn't return coorect results on Mac
// for: 
// b&c = a8a20450, ((~b)&d) = 0101c88b, (bitAND(b, c) | bitAND((~b), d)) = a8a20450 <-- !!!
// looks like the OR is not executed at all.
//
// let's try to trick the P-code compiler into working with us... Prayer beads are GO!
// 
function bitOR(a, b) {
	var lsb = (a & 0x1) | (b & 0x1);
	var msb31 = (a >>> 1) | (b >>> 1);
	return (msb31 << 1) | lsb;
}
//  
// will bitXOR be the only one working...?
// Nope. XOR fails too if values with bit31 set are XORed. 
//
// Note however that OR (and AND and XOR?!) works alright for the statement
//   (msb31 << 1) | lsb
// even if the result of the left-shift operation has bit 31 set.
// So there might be an extra condition here (Guessmode turned on):
// Mac Flash fails (OR, AND and XOR) if either one of the input operands has bit31 set
// *and* both operands have one or more bits both set to 1. In other words: when both
// input bit-patterns 'overlap'.
// Stuff to munch on for the MM guys, I guess...
//
function bitXOR(a, b) {
	var lsb = (a & 0x1) ^ (b & 0x1);
	var msb31 = (a >>> 1) ^ (b >>> 1);
	return (msb31 << 1) | lsb;
}
// 
// bitwise AND for 32-bit integers. This uses 31 + 1-bit operations internally
// to work around bug in some AS interpreters. (Mac Flash!)
// 
function bitAND(a, b) {
	var lsb = (a & 0x1) & (b & 0x1);
	var msb31 = (a >>> 1) & (b >>> 1);
	return (msb31 << 1) | lsb;
	// return (a & b);
}
// 
// Add integers, wrapping at 2^32. This uses 16-bit operations internally
// to work around bugs in some AS interpreters. (Mac Flash!)
// 
function addme(x, y) {
	var lsw = (x & 0xFFFF)+(y & 0xFFFF);
	var msw = (x >> 16)+(y >> 16)+(lsw >> 16);
	return (msw << 16) | (lsw & 0xFFFF);
}
function rhex(num) {
	str = "";
	for (j=0; j<=3; j++) {
		str += hex_chr.charAt((num >> (j*8+4)) & 0x0F)+hex_chr.charAt((num >> (j*8)) & 0x0F);
	}
	return str;
}
/*
 * Convert a string to a sequence of 16-word blocks, stored as an array.
 * Append padding bits and the length, as described in the MD5 standard.
 */
function str2blks_MD5(str) {
	nblk = ((str.length+8) >> 6)+1;
	// 1 + (len + 8)/64
	blks = new Array(nblk*16);
	for (i=0; i<nblk*16; i++) {
		blks[i] = 0;
	}
	/*
				Input: 
				
				'willi' without the quotes.
				
				trace() Output on Intel (and MAC now?):
				
				see TXT files: *.Output.txt
				
				*/
	for (i=0; i<str.length; i++) {
		blks[i >> 2] |= str.charCodeAt(i) << (((str.length*8+i)%4)*8);
	}
	blks[i >> 2] |= 0x80 << (((str.length*8+i)%4)*8);
	var l = str.length*8;
	blks[nblk*16-2] = (l & 0xFF);
	blks[nblk*16-2] |= ((l >>> 8) & 0xFF) << 8;
	blks[nblk*16-2] |= ((l >>> 16) & 0xFF) << 16;
	blks[nblk*16-2] |= ((l >>> 24) & 0xFF) << 24;
	return blks;
}
/*
 * Bitwise rotate a 32-bit number to the left
 */
function rol(num, cnt) {
	return (num << cnt) | (num >>> (32-cnt));
}
/*
 * These functions implement the basic operation for each round of the
 * algorithm.
 */
function cmn(q, a, b, x, s, t) {
	return addme(rol((addme(addme(a, q), addme(x, t))), s), b);
}

function ff(a, b, c, d, x, s, t) {
	return cmn(bitOR(bitAND(b, c), bitAND((~b), d)), a, b, x, s, t);
}

function gg(a, b, c, d, x, s, t) {
	return cmn(bitOR(bitAND(b, d), bitAND(c, (~d))), a, b, x, s, t);
}

function hh(a, b, c, d, x, s, t) {
	return cmn(bitXOR(bitXOR(b, c), d), a, b, x, s, t);
}

function ii(a, b, c, d, x, s, t) {
	return cmn(bitXOR(c, bitOR(b, (~d))), a, b, x, s, t);
}
/*
 * Take a string and return the hex representation of its MD5.
 */
function calcMD5(str) {
	x = str2blks_MD5(str);
	a = 1732584193;
	b = -271733879;
	c = -1732584194;
	d = 271733878;
	var step;
	for (i=0; i<x.length; i += 16) {
		olda = a;
		oldb = b;
		oldc = c;
		oldd = d;
		step = 0;
		a = ff(a, b, c, d, x[i+0], 7, -680876936);
		d = ff(d, a, b, c, x[i+1], 12, -389564586);
		c = ff(c, d, a, b, x[i+2], 17, 606105819);
		b = ff(b, c, d, a, x[i+3], 22, -1044525330);
		a = ff(a, b, c, d, x[i+4], 7, -176418897);
		d = ff(d, a, b, c, x[i+5], 12, 1200080426);
		c = ff(c, d, a, b, x[i+6], 17, -1473231341);
		b = ff(b, c, d, a, x[i+7], 22, -45705983);
		a = ff(a, b, c, d, x[i+8], 7, 1770035416);
		d = ff(d, a, b, c, x[i+9], 12, -1958414417);
		c = ff(c, d, a, b, x[i+10], 17, -42063);
		b = ff(b, c, d, a, x[i+11], 22, -1990404162);
		a = ff(a, b, c, d, x[i+12], 7, 1804603682);
		d = ff(d, a, b, c, x[i+13], 12, -40341101);
		c = ff(c, d, a, b, x[i+14], 17, -1502002290);
		b = ff(b, c, d, a, x[i+15], 22, 1236535329);
		a = gg(a, b, c, d, x[i+1], 5, -165796510);
		d = gg(d, a, b, c, x[i+6], 9, -1069501632);
		c = gg(c, d, a, b, x[i+11], 14, 643717713);
		b = gg(b, c, d, a, x[i+0], 20, -373897302);
		a = gg(a, b, c, d, x[i+5], 5, -701558691);
		d = gg(d, a, b, c, x[i+10], 9, 38016083);
		c = gg(c, d, a, b, x[i+15], 14, -660478335);
		b = gg(b, c, d, a, x[i+4], 20, -405537848);
		a = gg(a, b, c, d, x[i+9], 5, 568446438);
		d = gg(d, a, b, c, x[i+14], 9, -1019803690);
		c = gg(c, d, a, b, x[i+3], 14, -187363961);
		b = gg(b, c, d, a, x[i+8], 20, 1163531501);
		a = gg(a, b, c, d, x[i+13], 5, -1444681467);
		d = gg(d, a, b, c, x[i+2], 9, -51403784);
		c = gg(c, d, a, b, x[i+7], 14, 1735328473);
		b = gg(b, c, d, a, x[i+12], 20, -1926607734);
		a = hh(a, b, c, d, x[i+5], 4, -378558);
		d = hh(d, a, b, c, x[i+8], 11, -2022574463);
		c = hh(c, d, a, b, x[i+11], 16, 1839030562);
		b = hh(b, c, d, a, x[i+14], 23, -35309556);
		a = hh(a, b, c, d, x[i+1], 4, -1530992060);
		d = hh(d, a, b, c, x[i+4], 11, 1272893353);
		c = hh(c, d, a, b, x[i+7], 16, -155497632);
		b = hh(b, c, d, a, x[i+10], 23, -1094730640);
		a = hh(a, b, c, d, x[i+13], 4, 681279174);
		d = hh(d, a, b, c, x[i+0], 11, -358537222);
		c = hh(c, d, a, b, x[i+3], 16, -722521979);
		b = hh(b, c, d, a, x[i+6], 23, 76029189);
		a = hh(a, b, c, d, x[i+9], 4, -640364487);
		d = hh(d, a, b, c, x[i+12], 11, -421815835);
		c = hh(c, d, a, b, x[i+15], 16, 530742520);
		b = hh(b, c, d, a, x[i+2], 23, -995338651);
		a = ii(a, b, c, d, x[i+0], 6, -198630844);
		d = ii(d, a, b, c, x[i+7], 10, 1126891415);
		c = ii(c, d, a, b, x[i+14], 15, -1416354905);
		b = ii(b, c, d, a, x[i+5], 21, -57434055);
		a = ii(a, b, c, d, x[i+12], 6, 1700485571);
		d = ii(d, a, b, c, x[i+3], 10, -1894986606);
		c = ii(c, d, a, b, x[i+10], 15, -1051523);
		b = ii(b, c, d, a, x[i+1], 21, -2054922799);
		a = ii(a, b, c, d, x[i+8], 6, 1873313359);
		d = ii(d, a, b, c, x[i+15], 10, -30611744);
		c = ii(c, d, a, b, x[i+6], 15, -1560198380);
		b = ii(b, c, d, a, x[i+13], 21, 1309151649);
		a = ii(a, b, c, d, x[i+4], 6, -145523070);
		d = ii(d, a, b, c, x[i+11], 10, -1120210379);
		c = ii(c, d, a, b, x[i+2], 15, 718787259);
		b = ii(b, c, d, a, x[i+9], 21, -343485551);
		a = addme(a, olda);
		b = addme(b, oldb);
		c = addme(c, oldc);
		d = addme(d, oldd);
	}
	return rhex(a)+rhex(b)+rhex(c)+rhex(d);
}


// TEA2

c2b = new Object();
c2b['\\000'] = 0;
c2b["\001"] = 1;
c2b["\002"] = 2;
c2b["\003"] = 3;
c2b["\004"] = 4;
c2b["\005"] = 5;
c2b["\006"] = 6;
c2b["\007"] = 7;
c2b["\010"] = 8;
c2b["\011"] = 9;
c2b["\012"] = 10;
c2b["\013"] = 11;
c2b["\014"] = 12;
c2b["\015"] = 13;
c2b["\016"] = 14;
c2b["\017"] = 15;
c2b["\020"] = 16;
c2b["\021"] = 17;
c2b["\022"] = 18;
c2b["\023"] = 19;
c2b["\024"] = 20;
c2b["\025"] = 21;
c2b["\026"] = 22;
c2b["\027"] = 23;
c2b["\030"] = 24;
c2b["\031"] = 25;
c2b["\032"] = 26;
c2b["\033"] = 27;
c2b["\034"] = 28;
c2b["\035"] = 29;
c2b["\036"] = 30;
c2b["\037"] = 31;
c2b["\040"] = 32;
c2b["\041"] = 33;
c2b['\042'] = 34;
c2b["\043"] = 35;
c2b["\044"] = 36;
c2b["\045"] = 37;
c2b["\046"] = 38;
c2b["\047"] = 39;
c2b["\050"] = 40;
c2b["\051"] = 41;
c2b["\052"] = 42;
c2b["\053"] = 43;
c2b["\054"] = 44;
c2b["\055"] = 45;
c2b["\056"] = 46;
c2b["\057"] = 47;
c2b["\060"] = 48;
c2b["\061"] = 49;
c2b["\062"] = 50;
c2b["\063"] = 51;
c2b["\064"] = 52;
c2b["\065"] = 53;
c2b["\066"] = 54;
c2b["\067"] = 55;
c2b["\070"] = 56;
c2b["\071"] = 57;
c2b["\072"] = 58;
c2b["\073"] = 59;
c2b["\074"] = 60;
c2b["\075"] = 61;
c2b["\076"] = 62;
c2b["\077"] = 63;
c2b["\100"] = 64;
c2b["\101"] = 65;
c2b["\102"] = 66;
c2b["\103"] = 67;
c2b["\104"] = 68;
c2b["\105"] = 69;
c2b["\106"] = 70;
c2b["\107"] = 71;
c2b["\110"] = 72;
c2b["\111"] = 73;
c2b["\112"] = 74;
c2b["\113"] = 75;
c2b["\114"] = 76;
c2b["\115"] = 77;
c2b["\116"] = 78;
c2b["\117"] = 79;
c2b["\120"] = 80;
c2b["\121"] = 81;
c2b["\122"] = 82;
c2b["\123"] = 83;
c2b["\124"] = 84;
c2b["\125"] = 85;
c2b["\126"] = 86;
c2b["\127"] = 87;
c2b["\130"] = 88;
c2b["\131"] = 89;
c2b["\132"] = 90;
c2b["\133"] = 91;
var pepe="\";
c2b[pepe] = 92;
var pepe="]";
c2b[pepe] = 93;
c2b["\136"] = 94;
c2b["\137"] = 95;
c2b["\140"] = 96;
c2b["\141"] = 97;
c2b["\142"] = 98;
c2b["\143"] = 99;
c2b["\144"] = 100;
c2b["\145"] = 101;
c2b["\146"] = 102;
c2b["\147"] = 103;
c2b["\150"] = 104;
c2b["\151"] = 105;
c2b["\152"] = 106;
c2b["\153"] = 107;
c2b["\154"] = 108;
c2b["\155"] = 109;
c2b["\156"] = 110;
c2b["\157"] = 111;
c2b["\160"] = 112;
c2b["\161"] = 113;
c2b["\162"] = 114;
c2b["\163"] = 115;
c2b["\164"] = 116;
c2b["\165"] = 117;
c2b["\166"] = 118;
c2b["\167"] = 119;
c2b["\170"] = 120;
c2b["\171"] = 121;
c2b["\172"] = 122;
c2b["\173"] = 123;
c2b["\174"] = 124;
c2b["\175"] = 125;
c2b["\176"] = 126;
c2b["\177"] = 127;
c2b["\200"] = 128;
c2b["\201"] = 129;
c2b["\202"] = 130;
c2b["\203"] = 131;
c2b["\204"] = 132;
c2b["\205"] = 133;
c2b["\206"] = 134;
c2b["\207"] = 135;
c2b["\210"] = 136;
c2b["\211"] = 137;
c2b["\212"] = 138;
c2b["\213"] = 139;
c2b["\214"] = 140;
c2b["\215"] = 141;
c2b["\216"] = 142;
c2b["\217"] = 143;
c2b["\220"] = 144;
c2b["\221"] = 145;
c2b["\222"] = 146;
c2b["\223"] = 147;
c2b["\224"] = 148;
c2b["\225"] = 149;
c2b["\226"] = 150;
c2b["\227"] = 151;
c2b["\230"] = 152;
c2b["\231"] = 153;
c2b["\232"] = 154;
c2b["\233"] = 155;
c2b["\234"] = 156;
c2b["\235"] = 157;
c2b["\236"] = 158;
c2b["\237"] = 159;
c2b["\240"] = 160;
c2b["\241"] = 161;
c2b["\242"] = 162;
c2b["\243"] = 163;
c2b["\244"] = 164;
c2b["\245"] = 165;
c2b["\246"] = 166;
c2b["\247"] = 167;
c2b["\250"] = 168;
c2b["\251"] = 169;
c2b["\252"] = 170;
c2b["\253"] = 171;
c2b["\254"] = 172;
c2b["\255"] = 173;
c2b["\256"] = 174;
c2b["\257"] = 175;
c2b["\260"] = 176;
c2b["\261"] = 177;
c2b["\262"] = 178;
c2b["\263"] = 179;
c2b["\264"] = 180;
c2b["\265"] = 181;
c2b["\266"] = 182;
c2b["\267"] = 183;
c2b["\270"] = 184;
c2b["\271"] = 185;
c2b["\272"] = 186;
c2b["\273"] = 187;
c2b["\274"] = 188;
c2b["\275"] = 189;
c2b["\276"] = 190;
c2b["\277"] = 191;
c2b["\300"] = 192;
c2b["\301"] = 193;
c2b["\302"] = 194;
c2b["\303"] = 195;
c2b["\304"] = 196;
c2b["\305"] = 197;
c2b["\306"] = 198;
c2b["\307"] = 199;
c2b["\310"] = 200;
c2b["\311"] = 201;
c2b["\312"] = 202;
c2b["\313"] = 203;
c2b["\314"] = 204;
c2b["\315"] = 205;
c2b["\316"] = 206;
c2b["\317"] = 207;
c2b["\320"] = 208;
c2b["\321"] = 209;
c2b["\322"] = 210;
c2b["\323"] = 211;
c2b["\324"] = 212;
c2b["\325"] = 213;
c2b["\326"] = 214;
c2b["\327"] = 215;
c2b["\330"] = 216;
c2b["\331"] = 217;
c2b["\332"] = 218;
c2b["\333"] = 219;
c2b["\334"] = 220;
c2b["\335"] = 221;
c2b["\336"] = 222;
c2b["\337"] = 223;
c2b["\340"] = 224;
c2b["\341"] = 225;
c2b["\342"] = 226;
c2b["\343"] = 227;
c2b["\344"] = 228;
c2b["\345"] = 229;
c2b["\346"] = 230;
c2b["\347"] = 231;
c2b["\350"] = 232;
c2b["\351"] = 233;
c2b["\352"] = 234;
c2b["\353"] = 235;
c2b["\354"] = 236;
c2b["\355"] = 237;
c2b["\356"] = 238;
c2b["\357"] = 239;
c2b["\360"] = 240;
c2b["\361"] = 241;
c2b["\362"] = 242;
c2b["\363"] = 243;
c2b["\364"] = 244;
c2b["\365"] = 245;
c2b["\366"] = 246;
c2b["\367"] = 247;
c2b["\370"] = 248;
c2b["\371"] = 249;
c2b["\372"] = 250;
c2b["\373"] = 251;
c2b["\374"] = 252;
c2b["\375"] = 253;
c2b["\376"] = 254;
c2b["\377"] = 255;
b2c = new Object();
for (b in c2b) {
	b2c[c2b[b]] = b;
}


// ascii to 6-bit bin to ascii
a2b = new Object();
a2b["A"] = 0;
a2b["B"] = 1;
a2b["C"] = 2;
a2b["D"] = 3;
a2b["E"] = 4;
a2b["F"] = 5;
a2b["G"] = 6;
a2b["H"] = 7;
a2b["I"] = 8;
a2b["J"] = 9;
a2b["K"] = 10;
a2b["L"] = 11;
a2b["M"] = 12;
a2b["N"] = 13;
a2b["O"] = 14;
a2b["P"] = 15;
a2b["Q"] = 16;
a2b["R"] = 17;
a2b["S"] = 18;
a2b["T"] = 19;
a2b["U"] = 20;
a2b["V"] = 21;
a2b["W"] = 22;
a2b["X"] = 23;
a2b["Y"] = 24;
a2b["Z"] = 25;
a2b["a"] = 26;
a2b["b"] = 27;
a2b["c"] = 28;
a2b["d"] = 29;
a2b["e"] = 30;
a2b["f"] = 31;
a2b["g"] = 32;
a2b["h"] = 33;
a2b["i"] = 34;
a2b["j"] = 35;
a2b["k"] = 36;
a2b["l"] = 37;
a2b["m"] = 38;
a2b["n"] = 39;
a2b["o"] = 40;
a2b["p"] = 41;
a2b["q"] = 42;
a2b["r"] = 43;
a2b["s"] = 44;
a2b["t"] = 45;
a2b["u"] = 46;
a2b["v"] = 47;
a2b["w"] = 48;
a2b["x"] = 49;
a2b["y"] = 50;
a2b["z"] = 51;
a2b["0"] = 52;
a2b["1"] = 53;
a2b["2"] = 54;
a2b["3"] = 55;
a2b["4"] = 56;
a2b["5"] = 57;
a2b["6"] = 58;
a2b["7"] = 59;
a2b["8"] = 60;
a2b["9"] = 61;
a2b["+"] = 62;
a2b["_"] = 63;
b2a = new Object();
for (b in a2b) {
	b2a[a2b[b]] = ''+b;
}

function binary2ascii(s) {
	return bytes2ascii(blocks2bytes(s));
}
function binary2str(s) {
	return bytes2str(blocks2bytes(s));
}
function ascii2binary(s) {
	return bytes2blocks(ascii2bytes(s));
}
function str2binary(s) {
	return bytes2blocks(str2bytes(s));
}
function str2bytes(s) {
	var is = 0;
	var ls = s.length;
	var b = new Array();
	while (1) {
		if (is>=ls) {
			break;
		}
		var pepe=s.charAt(is);
		if (c2b[s.charAt(is)] == null) {
			b[is] = 0xF7;
		} else {
			b[is] = c2b[s.charAt(is)];
		}
		is++;
	}
	return b;
}
function bytes2str(b) {
	var ib = 0;
	var lb = b.length;
	var s = '';
	while (1) {
		if (ib>=lb) {
			break;
		}
		if (b2c[0xFF & b[ib]]!=undefined) {
			s += b2c[0xFF & b[ib]];
		}
		ib++;
	}
	return s;
}
function ascii2bytes(a) {
	var ia = -1;
	var la = a.length;
	var ib = 0;
	var b = new Array();
	var carry;
	while (1) {
		// reads 4 chars and produces 3 bytes
		while (1) {
			ia++;
			if (ia>=la) {
				return b;
			}
			if (a2b[a.charAt(ia)] != null) {
				break;
			}
		}
		b[ib] = a2b[a.charAt(ia)] << 2;
		while (1) {
			ia++;
			if (ia>=la) {
				return b;
			}
			if (a2b[a.charAt(ia)] != null) {
				break;
			}
		}
		carry = a2b[a.charAt(ia)];
		b[ib] |= carry >>> 4;
		ib++;
		carry = 0xF & carry;
		if (carry == 0 && ia == (la-1)) {
			return b;
		}
		b[ib] = carry << 4;
		while (1) {
			ia++;
			if (ia>=la) {
				return b;
			}
			if (a2b[a.charAt(ia)] != null) {
				break;
			}
		}
		carry = a2b[a.charAt(ia)];
		b[ib] |= carry >>> 2;
		ib++;
		carry = 3 & carry;
		if (carry == 0 && ia == (la-1)) {
			return b;
		}
		b[ib] = carry << 6;
		while (1) {
			ia++;
			if (ia>=la) {
				return b;
			}
			if (a2b[a.charAt(ia)] != null) {
				break;
			}
		}
		b[ib] |= a2b[a.charAt(ia)];
		ib++;
	}
	return b;
}
function bytes2ascii(b) {
	var ib = 0;
	var lb = b.length;
	var s = '';
	var b1;
	var b2;
	var b3;
	var carry;
	while (1) {
		// reads 3 bytes and produces 4 chars
		if (ib>=lb) {
			break;
		}
		b1 = 0xFF & b[ib];
		s += b2a[63 & (b1 >>> 2)];
		carry = 3 & b1;
		ib++;
		if (ib>=lb) {
			s += b2a[carry << 4];
			break;
		}
		b2 = 0xFF & b[ib];
		s += b2a[(0xF0 & (carry << 4)) | (b2 >>> 4)];
		carry = 0xF & b2;
		ib++;
		if (ib>=lb) {
			s += b2a[carry << 2];
			break;
		}
		b3 = 0xFF & b[ib];
		s += b2a[(60 & (carry << 2)) | (b3 >>> 6)]+b2a[63 & b3];
		ib++;
		if (ib%36 == 0) {
			s += "\n";
		}
	}
	return s;
}
function bytes2blocks(bytes) {
	var blocks = new Array();
	var ibl = 0;
	var iby = 0;
	var nby = bytes.length;
	while (1) {
		blocks[ibl] = (0xFF & bytes[iby]) << 24;
		iby++;
		if (iby>=nby) {
			break;
		}
		blocks[ibl] |= (0xFF & bytes[iby]) << 16;
		iby++;
		if (iby>=nby) {
			break;
		}
		blocks[ibl] |= (0xFF & bytes[iby]) << 8;
		iby++;
		if (iby>=nby) {
			break;
		}
		blocks[ibl] |= 0xFF & bytes[iby];
		iby++;
		if (iby>=nby) {
			break;
		}
		ibl++;
	}
	return blocks;
}
function blocks2bytes(blocks) {
	var bytes = new Array();
	var iby = 0;
	var ibl = 0;
	var nbl = blocks.length;
	while (1) {
		if (ibl>=nbl) {
			break;
		}
		bytes[iby] = 0xFF & (blocks[ibl] >>> 24);
		iby++;
		bytes[iby] = 0xFF & (blocks[ibl] >>> 16);
		iby++;
		bytes[iby] = 0xFF & (blocks[ibl] >>> 8);
		iby++;
		bytes[iby] = 0xFF & blocks[ibl];
		iby++;
		ibl++;
	}
	return bytes;
}
function digest_pad(bytearray) {
	var newarray = new Array();
	var ina = 0;
	var iba = 0;
	var nba = bytearray.length;
	var npads = 15-(nba%16);
	newarray[ina] = npads;
	ina++;
	while (iba<nba) {
		newarray[ina] = bytearray[iba];
		ina++;
		iba++;
	}
	var ip = npads;
	while (ip>0) {
		newarray[ina] = 0;
		ina++;
		ip--;
	}
	return newarray;
}
function pad(bytearray) {
	var newarray = new Array();
	var ina = 0;
	var iba = 0;
	var nba = bytearray.length;
	var npads = 7-(nba%8);
	newarray[ina] = (0xF8 & rand_byte()) | (7 & npads);
	ina++;
	while (iba<nba) {
		newarray[ina] = bytearray[iba];
		ina++;
		iba++;
	}
	var ip = npads;
	while (ip>0) {
		newarray[ina] = rand_byte();
		ina++;
		ip--;
	}
	return newarray;
}
function rand_byte() {
	return Math.floor(256*Math.random());
	if (!rand_byte_already_called) {
		var now = new Date();
		seed = now.milliseconds;
		rand_byte_already_called = true;
	}
	seed = (1029*seed+221591)%1048576;
	return Math.floor(seed/4096);
}
function unpad(bytearray) {
	var iba = 0;
	var newarray = new Array();
	var ina = 0;
	var npads = 0x7 & bytearray[iba];
	iba++;
	var nba = bytearray.length-npads;
	while (iba<nba) {
		newarray[ina] = bytearray[iba];
		ina++;
		iba++;
	}
	return newarray;
}
function asciidigest(str) {
	return binary2ascii(binarydigest(str));
}
function binarydigest(str, keystr) {
	var key = new Array();
	key[0] = 0x61626364;
	key[1] = 0x62636465;
	key[2] = 0x63646566;
	key[3] = 0x64656667;
	var c0 = new Array();
	c0[0] = 0x61626364;
	c0[1] = 0x62636465;
	var c1 = new Array();
	c1 = c0;
	var v0 = new Array();
	var v1 = new Array();
	var swap;
	var blocks = new Array();
	blocks = bytes2blocks(digest_pad(str2bytes(str)));
	var ibl = 0;
	var nbl = blocks.length;
	while (1) {
		if (ibl>=nbl) {
			break;
		}
		v0[0] = blocks[ibl];
		ibl++;
		v0[1] = blocks[ibl];
		ibl++;
		v1[0] = blocks[ibl];
		ibl++;
		v1[1] = blocks[ibl];
		ibl++;
		c0 = tea_code(xor_blocks(v0, c0), key);
		c1 = tea_code(xor_blocks(v1, c1), key);
		swap = c0[0];
		c0[0] = c0[1];
		c0[1] = c1[0];
		c1[0] = c1[1];
		c1[1] = swap;
	}
	var concat = new Array();
	concat[0] = c0[0];
	concat[1] = c0[1];
	concat[2] = c1[0];
	concat[3] = c1[1];
	return concat;
}
function encrypt(str, keystr) {
	var key = new Array();
	key = binarydigest(keystr);
	var blocks = new Array();
	blocks = bytes2blocks(pad(str2bytes(str)));
	var ibl = 0;
	var nbl = blocks.length;
	// Initial Value for CBC mode = "abcdbcde". Retain for interoperability.
	var c = new Array();
	c[0] = 0x61626364;
	c[1] = 0x62636465;
	var v = new Array();
	var cblocks = new Array();
	var icb = 0;
	while (1) {
		if (ibl>=nbl) {
			break;
		}
		v[0] = blocks[ibl];
		ibl++;
		v[1] = blocks[ibl];
		ibl++;
		c = tea_code(xor_blocks(v, c), key);
		cblocks[icb] = c[0];
		icb++;
		cblocks[icb] = c[1];
		icb++;
	}
	return binary2ascii(cblocks);
}
function decrypt(ascii, keystr) {
	var key = new Array();
	key = binarydigest(keystr);
	var cblocks = new Array();
	cblocks = ascii2binary(ascii);
	var icbl = 0;
	var ncbl = cblocks.length;
	var lastc = new Array();
	lastc[0] = 0x61626364;
	lastc[1] = 0x62636465;
	var v = new Array();
	var c = new Array();
	var blocks = new Array();
	var ibl = 0;
	while (1) {
		if (icbl>=ncbl) {
			break;
		}
		c[0] = cblocks[icbl];
		icbl++;
		c[1] = cblocks[icbl];
		icbl++;
		v = xor_blocks(lastc, tea_decode(c, key));
		blocks[ibl] = v[0];
		ibl++;
		blocks[ibl] = v[1];
		ibl++;
		lastc[0] = c[0];
		lastc[1] = c[1];
	}
	return bytes2str(unpad(blocks2bytes(blocks)));
}
function xor_blocks(blk1, blk2) {
	var blk = new Array();
	blk[0] = blk1[0] ^ blk2[0];
	blk[1] = blk1[1] ^ blk2[1];
	return blk;
}
function tea_code(v, k) {
	var v0 = v[0];
	var v1 = v[1];
	var k0 = k[0];
	var k1 = k[1];
	var k2 = k[2];
	var k3 = k[3];
	var sum = 0;
	var n = 32;
	while (n-->0) {
		sum -= 1640531527;
		// TEA magic number 0x9e3779b9 
		sum = sum | 0;
		v0 += ((v1 << 4)+k0) ^ (v1+sum) ^ ((v1 >>> 5)+k1);
		v1 += ((v0 << 4)+k2) ^ (v0+sum) ^ ((v0 >>> 5)+k3);
	}
	var w = new Array();
	w[0] = v0 | 0;
	w[1] = v1 | 0;
	return w;
}
function tea_decode(v, k) {
	var v0 = v[0];
	var v1 = v[1];
	var k0 = k[0];
	var k1 = k[1];
	var k2 = k[2];
	var k3 = k[3];
	var sum = 0;
	var n = 32;
	sum = -957401312;
	while (n-->0) {
		v1 -= ((v0 << 4)+k2) ^ (v0+sum) ^ ((v0 >>> 5)+k3);
		v0 -= ((v1 << 4)+k0) ^ (v1+sum) ^ ((v1 >>> 5)+k1);
		sum += 1640531527;
		sum = sum | 0;
	}
	var w = new Array();
	w[0] = v0 | 0;
	w[1] = v1 | 0;
	return w;
}

Key.addListener(Key);

Key.onKeyDown = function(){
	var incremento = 1;
	var tecla = Key.getCode();

	if(tecla == 16) {
		_global.shift = 1;
	}

	if(tecla == 40) // DOWN
	{
		// Select next button DOWN
    	myapaga = eval('_root.resaltado'+_global.rectanguloprendido);
       	dif1 = (_global.rectanguloprendido) % _root.cuantas_filas;
		incremento = 1;
		if(dif1 == 0) {
			// It changed the column, increment it again
			incremento = incremento - _root.cuantas_filas;
		}
        proximo = _global.rectanguloprendido + incremento;
        var myresa = eval('_root.resaltado'+proximo);
		if(_global.rectanguloprendido != _global.restrict) {
        myapaga._visible = false;
		}
        myresa._visible = true;
        _global.rectanguloprendido = proximo;
        _root.makeStatus(proximo);
	}
	if(tecla == 38) // UP
	{
		// Select next button UP
    	myapaga = eval('_root.resaltado'+_global.rectanguloprendido);
       	dif1 = (_global.rectanguloprendido-1) % _root.cuantas_filas;
		incremento = -1;
		if(dif1 == 0) {
			// It changed the column, increment it again
			incremento = incremento + _root.cuantas_filas;
		}
        proximo = _global.rectanguloprendido + incremento;
        var myresa = eval('_root.resaltado'+proximo);
		if(_global.rectanguloprendido != _global.restrict) {
        myapaga._visible = false;
		}
        myresa._visible = true;
        _global.rectanguloprendido = proximo;
        _root.makeStatus(proximo);
	}
	if(tecla == 37) // LEFT
	{

		if (_root.superdetails._visible == true) {
			var tab = _root.superdetails.tab1._currentframe;
		    if(tab == 2) {
		        _root.superdetails.tab1.gotoAndStop(1);
		        _root.superdetails.tab2.gotoAndStop(2);
		        _root.superdetails.texto = _global.superdetailstexttab1;
		    } else {
		        _root.superdetails.tab1.gotoAndStop(2);
		        _root.superdetails.tab2.gotoAndStop(1);
		        _root.superdetails.texto = _global.superdetailstexttab2;
		    }
		} else {
			// Select next button on the LEFT
	    	myapaga = eval('_root.resaltado'+_global.rectanguloprendido);
	       	incremento = _root.cuantas_filas;
			diferencia = _global.rectanguloprendido % _root.cuantas_filas;
	        proximo = _global.rectanguloprendido - incremento;

        	if(proximo < 1) {
				proximo = ((_root.cuantas_columnas - 1) * _root.cuantas_filas)+diferencia;
       	 	}
        	var myresa = eval('_root.resaltado'+proximo);
			if(_global.rectanguloprendido != _global.restrict) {
        	myapaga._visible = false;
			}
        	myresa._visible = true;
        	_global.rectanguloprendido = proximo;
        	_root.makeStatus(proximo);
		}
	}

	if(tecla == 39) // RIGHT
	{
		if(_root.detail._visible == true) {
			_root.superdetails._visible = true;
			_root.detail._visible = false;
		} else if (_root.superdetails._visible == true) {
			var tab = _root.superdetails.tab1._currentframe;
		    if(tab == 2) {
		        _root.superdetails.tab1.gotoAndStop(1);
		        _root.superdetails.tab2.gotoAndStop(2);
		        _root.superdetails.texto = _global.superdetailstexttab1;
		    } else {
		        _root.superdetails.tab1.gotoAndStop(2);
		        _root.superdetails.tab2.gotoAndStop(1);
		        _root.superdetails.texto = _global.superdetailstexttab2;
		    }
		} else {
			// Select next button on the RIGHT
	    	myapaga = eval('_root.resaltado'+_global.rectanguloprendido);
	        total = _root.cuantas_filas * _root.cuantas_columnas;
        	incremento = _root.cuantas_filas;
			diferencia = _global.rectanguloprendido % _root.cuantas_filas;
	        proximo = _global.rectanguloprendido + incremento;

	        if(proximo > total) {
	            proximo = 1+diferencia-1;
	        }
	        var myresa = eval('_root.resaltado'+proximo);
			if(_global.rectanguloprendido != _global.restrict) {
	        myapaga._visible = false;
			}
	        myresa._visible = true;
	        _global.rectanguloprendido = proximo;
	        _root.makeStatus(proximo);
		}
	}
	if(tecla == 9) // TAB
	{
		myapaga = eval('_root.resaltado'+_global.rectanguloprendido);
		total = _root.cuantas_filas * _root.cuantas_columnas;
		if(_global.shift == 1) {
			incremento = -1;
		} else {
			incremento = 1;
		}
		proximo = _global.rectanguloprendido + incremento;

	 	if(proximo > total) {
			proximo = 1;
		}
		if(proximo < 1) {
			proximo = total;
		}
		var myresa = eval('_root.resaltado'+proximo);
		if(_global.rectanguloprendido == _global.restrict) {
	  	myapaga._visible = false;
		}
	   	myresa._visible = true;
	   	_global.rectanguloprendido = proximo;
	   	_root.makeStatus(proximo);
	}

	if(tecla == 27)	// ESC
	{
		_root.codebox._visible = false;
		_root.log._visible = false;
		_root.detail._visible = false;
		_root.superdetails._visible = false;
	}

	if(tecla == 18) // ALT
	{
		var myon = _global.rectanguloprendido;
		if(myon>0) {
			var myclip = eval('_level0.rectangulo'+myon+'.flecha'+myon);
			_root.displaydetails(myclip);
		}
	}

	if(tecla == 13) // ENTER
	{
		if(_root.codebox._visible == true) {
			// The security code box is visible, sends code and hides it
			_global.claveingresada = _root.codebox.claveform.text;
			_root.codebox._visible = false;
			_root.envia_comando('bogus', 0, 0);
		} else {
			// The security code is not visible, open detail windows of
			// highlighted button
			var myon = _global.rectanguloprendido;
			if(myon>0) {
				var myclip = eval('_level0.rectangulo'+myon+'.flecha'+myon);
				_root.displaydetails(myclip);
			}
		}
	}


};

Key.onKeyUp = function(){
	var tecla = Key.getCode();
	if(tecla == 16) {
		_global.shift = 0;
	}
};

Inicia_Variables();
conecta();

EndOfActionScript

# Saves the movie
$movie->nextFrame();
$movie->save("comunicator.swf",9);
