// ***************************************************
// ** Client-side library functions                     **
// ***************************************************

//this will hide or show all the <select> elements on a page
function hideSelects(b) {
      var allelems = document.all.tags('SELECT');
      if (allelems != null) {
              var i;
              for (i = 0; i < allelems.length; i++) {
	 			allelems[i].style.visibility = (b ? 'hidden' : 'inherit');
			}

      }
}

// these two 'do' functions are needed to assign to the onmouse events
function doHideSelects(event)
{
      hideSelects(true);
}
function doShowSelects(event) {
      hideSelects(false);
}

//call this function from forms that include module destinations
//numForms is the number of destination forms to process (usually 1)
function setDestinations(theForm,numForms) {
	for (var formNum = 0; formNum < numForms; formNum++) {
		var whichitem = 0;
		while (whichitem < theForm['goto'+formNum].length) {
			if (theForm['goto'+formNum][whichitem].checked) {
				theForm['goto'+formNum].value=theForm['goto'+formNum][whichitem].value;
			}
			whichitem++;
		}
	}
}

// ***************************************************
// ** CLIENT-SIDE FORM VALIDATION FUNCTIONS         **
// ***************************************************

// Defaults and Consts for validation functions
var whitespace = " \t\n\r";
var decimalPointDelimiter = ".";
var defaultEmptyOK = false;

//call this function to validate all your destinations
//numForms is the number of destinatino forms to process (usually 1)
//bRequired true|false if user must select something
function validateDestinations(theForm,numForms,bRequired) {
	var valid = true;

	for (var formNum = 0; formNum < numForms && valid == true; formNum++) {
		valid = validateSingleDestination(theForm,formNum,bRequired);
	}

	return valid;
}

// this will display a message, select the content of the relevent field and
// then set the focus to that field.  finally return FALSE to the 'onsubmit' event
// NOTE: <select> boxes do not support the .select method, therefore you cannot
// use this function on any <select> elements
function warnInvalid (theField, s) {
    if(theField){
		theField.focus();
    	theField.select();
    }
    alert(s);
    return false;
}

// ***************************************************
// ** String must be letters and numbers ONLY       **
// ***************************************************
// Note that UTF-8 letters are allowed by calling the isUnicodeLetter check

function isAlphanumeric (s) {
	var i;
    if (isEmpty(s))
       if (isAlphanumeric.arguments.length == 1) return defaultEmptyOK;
       else return (isAlphanumeric.arguments[1] == true);
    for (i = 0; i < s.length; i++) {
        var c = s.charAt(i);
        if (! (isUnicodeLetter(c) || isDigit(c) ) )
        return false;
    }

    return true;
}

// ***************************************************
// ** String must be unicode "letter"               **
// ***************************************************
// This is a big regex that whitelists unicode letters
function isUnicodeLetter(c) {
    return new RegExp(/[ a-zA-Z'\\\/\&\(\)\-\u3000-\u3002\uFF10-\uFF19\xAA\xB5\xBA\xC0-\xD6\xD8-\xF6\xF8-\u02C1\u02C6-\u02D1\u02E0-\u02E4\u02EC\u02EE\u0370-\u0374\u0376\u0377\u037A-\u037D\u0386\u0388-\u038A\u038C\u038E-\u03A1\u03A3-\u03F5\u03F7-\u0481\u048A-\u0527\u0531-\u0556\u0559\u0561-\u0587\u05D0-\u05EA\u05F0-\u05F2\u0620-\u064A\u066E\u066F\u0671-\u06D3\u06D5\u06E5\u06E6\u06EE\u06EF\u06FA-\u06FC\u06FF\u0710\u0712-\u072F\u074D-\u07A5\u07B1\u07CA-\u07EA\u07F4\u07F5\u07FA\u0800-\u0815\u081A\u0824\u0828\u0840-\u0858\u08A0\u08A2-\u08AC\u0904-\u0939\u093D\u0950\u0958-\u0961\u0971-\u0977\u0979-\u097F\u0985-\u098C\u098F\u0990\u0993-\u09A8\u09AA-\u09B0\u09B2\u09B6-\u09B9\u09BD\u09CE\u09DC\u09DD\u09DF-\u09E1\u09F0\u09F1\u0A05-\u0A0A\u0A0F\u0A10\u0A13-\u0A28\u0A2A-\u0A30\u0A32\u0A33\u0A35\u0A36\u0A38\u0A39\u0A59-\u0A5C\u0A5E\u0A72-\u0A74\u0A85-\u0A8D\u0A8F-\u0A91\u0A93-\u0AA8\u0AAA-\u0AB0\u0AB2\u0AB3\u0AB5-\u0AB9\u0ABD\u0AD0\u0AE0\u0AE1\u0B05-\u0B0C\u0B0F\u0B10\u0B13-\u0B28\u0B2A-\u0B30\u0B32\u0B33\u0B35-\u0B39\u0B3D\u0B5C\u0B5D\u0B5F-\u0B61\u0B71\u0B83\u0B85-\u0B8A\u0B8E-\u0B90\u0B92-\u0B95\u0B99\u0B9A\u0B9C\u0B9E\u0B9F\u0BA3\u0BA4\u0BA8-\u0BAA\u0BAE-\u0BB9\u0BD0\u0C05-\u0C0C\u0C0E-\u0C10\u0C12-\u0C28\u0C2A-\u0C33\u0C35-\u0C39\u0C3D\u0C58\u0C59\u0C60\u0C61\u0C85-\u0C8C\u0C8E-\u0C90\u0C92-\u0CA8\u0CAA-\u0CB3\u0CB5-\u0CB9\u0CBD\u0CDE\u0CE0\u0CE1\u0CF1\u0CF2\u0D05-\u0D0C\u0D0E-\u0D10\u0D12-\u0D3A\u0D3D\u0D4E\u0D60\u0D61\u0D7A-\u0D7F\u0D85-\u0D96\u0D9A-\u0DB1\u0DB3-\u0DBB\u0DBD\u0DC0-\u0DC6\u0E01-\u0E30\u0E32\u0E33\u0E40-\u0E46\u0E81\u0E82\u0E84\u0E87\u0E88\u0E8A\u0E8D\u0E94-\u0E97\u0E99-\u0E9F\u0EA1-\u0EA3\u0EA5\u0EA7\u0EAA\u0EAB\u0EAD-\u0EB0\u0EB2\u0EB3\u0EBD\u0EC0-\u0EC4\u0EC6\u0EDC-\u0EDF\u0F00\u0F40-\u0F47\u0F49-\u0F6C\u0F88-\u0F8C\u1000-\u102A\u103F\u1050-\u1055\u105A-\u105D\u1061\u1065\u1066\u106E-\u1070\u1075-\u1081\u108E\u10A0-\u10C5\u10C7\u10CD\u10D0-\u10FA\u10FC-\u1248\u124A-\u124D\u1250-\u1256\u1258\u125A-\u125D\u1260-\u1288\u128A-\u128D\u1290-\u12B0\u12B2-\u12B5\u12B8-\u12BE\u12C0\u12C2-\u12C5\u12C8-\u12D6\u12D8-\u1310\u1312-\u1315\u1318-\u135A\u1380-\u138F\u13A0-\u13F4\u1401-\u166C\u166F-\u167F\u1681-\u169A\u16A0-\u16EA\u1700-\u170C\u170E-\u1711\u1720-\u1731\u1740-\u1751\u1760-\u176C\u176E-\u1770\u1780-\u17B3\u17D7\u17DC\u1820-\u1877\u1880-\u18A8\u18AA\u18B0-\u18F5\u1900-\u191C\u1950-\u196D\u1970-\u1974\u1980-\u19AB\u19C1-\u19C7\u1A00-\u1A16\u1A20-\u1A54\u1AA7\u1B05-\u1B33\u1B45-\u1B4B\u1B83-\u1BA0\u1BAE\u1BAF\u1BBA-\u1BE5\u1C00-\u1C23\u1C4D-\u1C4F\u1C5A-\u1C7D\u1CE9-\u1CEC\u1CEE-\u1CF1\u1CF5\u1CF6\u1D00-\u1DBF\u1E00-\u1F15\u1F18-\u1F1D\u1F20-\u1F45\u1F48-\u1F4D\u1F50-\u1F57\u1F59\u1F5B\u1F5D\u1F5F-\u1F7D\u1F80-\u1FB4\u1FB6-\u1FBC\u1FBE\u1FC2-\u1FC4\u1FC6-\u1FCC\u1FD0-\u1FD3\u1FD6-\u1FDB\u1FE0-\u1FEC\u1FF2-\u1FF4\u1FF6-\u1FFC\u2071\u207F\u2090-\u209C\u2102\u2107\u210A-\u2113\u2115\u2119-\u211D\u2124\u2126\u2128\u212A-\u212D\u212F-\u2139\u213C-\u213F\u2145-\u2149\u214E\u2183\u2184\u2C00-\u2C2E\u2C30-\u2C5E\u2C60-\u2CE4\u2CEB-\u2CEE\u2CF2\u2CF3\u2D00-\u2D25\u2D27\u2D2D\u2D30-\u2D67\u2D6F\u2D80-\u2D96\u2DA0-\u2DA6\u2DA8-\u2DAE\u2DB0-\u2DB6\u2DB8-\u2DBE\u2DC0-\u2DC6\u2DC8-\u2DCE\u2DD0-\u2DD6\u2DD8-\u2DDE\u2E2F\u3005\u3006\u3031-\u3035\u303B\u303C\u3041-\u3096\u309D-\u309F\u30A1-\u30FA\u30FC-\u30FF\u3105-\u312D\u3131-\u318E\u31A0-\u31BA\u31F0-\u31FF\u3400-\u4DB5\u4E00-\u9FCC\uA000-\uA48C\uA4D0-\uA4FD\uA500-\uA60C\uA610-\uA61F\uA62A\uA62B\uA640-\uA66E\uA67F-\uA697\uA6A0-\uA6E5\uA717-\uA71F\uA722-\uA788\uA78B-\uA78E\uA790-\uA793\uA7A0-\uA7AA\uA7F8-\uA801\uA803-\uA805\uA807-\uA80A\uA80C-\uA822\uA840-\uA873\uA882-\uA8B3\uA8F2-\uA8F7\uA8FB\uA90A-\uA925\uA930-\uA946\uA960-\uA97C\uA984-\uA9B2\uA9CF\uAA00-\uAA28\uAA40-\uAA42\uAA44-\uAA4B\uAA60-\uAA76\uAA7A\uAA80-\uAAAF\uAAB1\uAAB5\uAAB6\uAAB9-\uAABD\uAAC0\uAAC2\uAADB-\uAADD\uAAE0-\uAAEA\uAAF2-\uAAF4\uAB01-\uAB06\uAB09-\uAB0E\uAB11-\uAB16\uAB20-\uAB26\uAB28-\uAB2E\uABC0-\uABE2\uAC00-\uD7A3\uD7B0-\uD7C6\uD7CB-\uD7FB\uF900-\uFA6D\uFA70-\uFAD9\uFB00-\uFB06\uFB13-\uFB17\uFB1D\uFB1F-\uFB28\uFB2A-\uFB36\uFB38-\uFB3C\uFB3E\uFB40\uFB41\uFB43\uFB44\uFB46-\uFBB1\uFBD3-\uFD3D\uFD50-\uFD8F\uFD92-\uFDC7\uFDF0-\uFDFB\uFE70-\uFE74\uFE76-\uFEFC\uFF21-\uFF3A\uFF41-\uFF5A\uFF66-\uFFBE\uFFC2-\uFFC7\uFFCA-\uFFCF\uFFD2-\uFFD7\uFFDA-\uFFDC-\u002F-\u005c]/).test(c);
}

// ***************************************************
// ** Is the length of the string OK for extensions **
// ***************************************************
// This is a special case function that is just used for extensions, due to the
// way that the validation is handled differently there compared to other modules.
// The correct string length is hardcoded at 50 to match the MySQL item size.
function isCorrectLengthExtensions(s) {
    return isCorrectLength(s,50);
}

// ***************************************************
// ** Is the length of the string OK                **
// ***************************************************
// Takes two arguments, the string and an integer, and uses lengthInUtf8Bytes
// function to see that the string doesn't exceed a certain length. This is
// mostly used to prevent MySQL from having to auto-chop strings that are too
// long when they are input into the DB
function isCorrectLength(s,l) {
    var i;
    if(lengthInUtf8Bytes(s) > l) return false;
    else return true;
}

// ***************************************************
// ** How many UTF8 bytes is this string ?          **
// ***************************************************
// Because multibyte characters like UTF8 don't get counted by the character in
// MySQL length limits, they are counted by bytes.
function lengthInUtf8Bytes(str) {
    var m = encodeURIComponent(str).match(/%[89ABab]/g);
    return str.length + (m ? m.length : 0);
}


// ***************************************************
// ** Is Whole Number ?                             **
// ***************************************************

function isInteger (s) {
    var i;

    if (isEmpty(s))
       if (isInteger.arguments.length == 1) return defaultEmptyOK;
       else return (isInteger.arguments[1] == true);

    for (i = 0; i < s.length; i++) {
        var c = s.charAt(i);

        if (!isDigit(c)) {
			return false;
		}
    }

    return true;
}

// ***************************************************
// ** Is Floating-point Number ?                    **
// ***************************************************

function isFloat (s) {
	var i;
    var seenDecimalPoint = false;

    if (isEmpty(s))
       if (isFloat.arguments.length == 1) return defaultEmptyOK;
       else return (isFloat.arguments[1] == true);

    if (s == decimalPointDelimiter) return false;

    for (i = 0; i < s.length; i++) {
        var c = s.charAt(i);

        if ((c == decimalPointDelimiter) && !seenDecimalPoint) seenDecimalPoint = true;
        else if (!isDigit(c)) return false;
    }

    return true;
}

// ***************************************************
// ** General number check                          **
// ***************************************************
//is this ever used?
function checkNumber(object_value) {

    if (object_value.length == 0)
        return true;

	var start_format = " .+-0123456789";
	var number_format = " .0123456789";
	var check_char;
	var decimal = false;
	var trailing_blank = false;
	var digits = false;

	check_char = start_format.indexOf(object_value.charAt(0))
	if (check_char == 1)
	    decimal = true;
	else if (check_char < 1)
		return false;

	for (var i = 1; i < object_value.length; i++)
	{
		check_char = number_format.indexOf(object_value.charAt(i))
		if (check_char < 0)
			return false;
		else if (check_char == 1)
		{
			if (decimal)
				return false;
			else
				decimal = true;
		}
		else if (check_char == 0)
		{
			if (decimal || digits)
				trailing_blank = true;
		}
	    else if (trailing_blank)
			return false;
		else
			digits = true;
	}

    return true
 }

// ***************************************************
// ** Simply check if there is nothing in the str   **
// ***************************************************

function isEmpty(s) {
	return ((s == null) || (s.length == 0));
}

// ***************************************************
// ** Checks for all known whitespace               **
// ***************************************************

function isWhitespace (s) {
	var i;

    if (isEmpty(s)) return true;

    for (i = 0; i < s.length; i++) {
        var c = s.charAt(i);

        if (whitespace.indexOf(c) == -1) {
			return false;
		}
    }

    return true;
}

// ***************************************************
// ** Valid URL check                               **
// ***************************************************
function isURL (s) {
	var i;
    if (isEmpty(s))
       if (isURL.arguments.length == 1) return defaultEmptyOK;
       else return (isURL.arguments[1] == true);

    for (i = 0; i < s.length; i++) {
        // Check that current character is number or letter.
        var c = s.charAt(i);
        if (! (isURLChar(c) || isDigit(c) ) )
        return false;
    }

    return true;
}

// ***************************************************
// ** List of PIN numbers followed by ','           **
// ***************************************************

function isPINList (s)

{   var i;

    if (isEmpty(s))
       if (isPINList.arguments.length == 1) return defaultEmptyOK;
       else return (isPINList.arguments[1] == true);

    for (i = 0; i < s.length; i++)
    {
        // Check that current character is number.
        var c = s.charAt(i);

        if (!isDigit(c) && c != ",") return false;
    }

    return true;
}

// ***************************************************
// ** Must be a valid Caller ID string              **
// ***************************************************

function isCallerID (s) {
	var i;
    if (isEmpty(s))
       if (isCallerID.arguments.length == 1) return defaultEmptyOK;
       else return (isCallerID.arguments[1] == true);


       for (i = 0; i < s.length; i++) {
        var c = s.charAt(i);
        if (! (isCallerIDChar(c) ) )
        return false;
    }

    return true;
}

// ***************************************************
// ** Must be a valid dialplan pattern string       **
// ***************************************************

function isDialpattern (s) {
	var i;

	if (isEmpty(s))
       if (isDialpattern.arguments.length == 1) return defaultEmptyOK;
       else return (isDialpattern.arguments[1] == true);

	for (i = 0; i < s.length; i++) {
		var c = s.charAt(i);
		if ( !isDialpatternChar(c) ) {
			if (c.charCodeAt(0) != 13 && c.charCodeAt(0) != 10) {
				return false;
			}
		}
	}

	return true;
}

// ***************************************************
// ** Must be a valid dial rule string              **
// ***************************************************

function isDialrule (s) {
	var i;

	if (isEmpty(s))
       if (isDialrule.arguments.length == 1) return defaultEmptyOK;
       else return (isDialrule.arguments[1] == true);

	for (i = 0; i < s.length; i++) {
		var c = s.charAt(i);
		if ( !isDialruleChar(c) ) {
			if (c.charCodeAt(0) != 13 && c.charCodeAt(0) != 10) {
				return false;
			}
		}
	}

	return true;
}

// ***************************************************
// ** Valid dial identifier -- for Dial cmd         **
// ***************************************************

function isDialIdentifier (s)
{
    var i;

    if (isEmpty(s))
       if (isDialIdentifier.arguments.length == 1) return defaultEmptyOK;
       else return (isDialIdentifier.arguments[1] == true);

    for (i = 0; i < s.length; i++) {
        var c = s.charAt(i);
        if ( !isDialDigitChar(c) && (c != "w") && (c != "W") ) return false;
    }

    return true;
}

// ***************************************************
// ** Valid dialable digit (i.e. on a keypad)       **
// ***************************************************

function isDialDigits(s) {
    var i;

    if (isEmpty(s))
       if (isDialDigits.arguments.length == 1) return defaultEmptyOK;
       else return (isDialDigits.arguments[1] == true);

    for (i = 0; i < s.length; i++) {
        var c = s.charAt(i);
        if (!isDialDigitChar(c)) return false;
    }

    return true;
}

// ***************************************************
// ** Valid IVR input, any keypad char plus some    **
// ** priority i or t if specified by themself's    **
// ***************************************************
//used by legecy ivr, not sure that we need to reimplement this
function isIVROption(s)
{
    var i;

    if (isEmpty(s))
       if (isIVROption.arguments.length == 1) return defaultEmptyOK;
       else return (isIVROption.arguments[1] == true);

    if (s.length == 1) { // could be i or t as only one char entered

    	var c = s.charAt(0);

    	if ( (!isDialDigitChar(c)) && (c != "i") && (c != "t") )
    		return false;

    } else { // numbers only

	    for (i = 0; i < s.length; i++)
	    {
	        var c = s.charAt(i);

	        if (!isDialDigitChar(c)) return false;
	    }

	}

    return true;
}

// ***************************************************
// ** Valid filename                                **
// ***************************************************
//used by recordings page.recordings.php:486
function isFilename(s)
{
    var i;

    if (isEmpty(s))
       if (isFilename.arguments.length == 1) return defaultEmptyOK;
       else return (isFilename.arguments[1] == true);

    for (i = 0; i < s.length; i++)
    {
        var c = s.charAt(i);

        if (!isFilenameChar(c)) return false;
    }

    return true;
}

// ***************************************************
// ** Check if string s contains char c             **
// ***************************************************

function isInside(s, c)
{
    var i;

    if (isEmpty(s)) {
	return false;
    }
    for (i = 0; i < s.length; i++)
    {
        var t = s.charAt(i);
	if (t == c)  return true;
    }
    return false;
}

// ***************************************************
// ** Check if valid email address                  **
// ***************************************************
function isEmail (s) {
	if (isEmpty(s)) {
		if (isEmail.arguments.length == 1) {
			return defaultEmptyOK;
		} else {
			return (isEmail.arguments[1] == true)
		}
	}
	var emailAddresses = s.split(",");

	var pattern = /(?:[a-z0-9!#$%&'*+/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+/=?^_`{|}~-]+)*|"(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21\x23-\x5b\x5d-\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f])*")@(?:(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?|\[(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?|[a-z0-9-]*[a-z0-9]:(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21-\x5a\x53-\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f])+)\])/i;

	var emailCount = 0;
	for(e in emailAddresses) {
		emailCount += (pattern.test(emailAddresses[e]) === true)?1:0;

	}

	if (emailAddresses.length == emailCount) {
		return true;
	}
	return false;
}

// ***************************************************
// ** HELPER FUNCTIONS FOR ABOVE VALIDATIONS        **
// ***************************************************

function isDigit (c) {
	return new RegExp(/[0-9]/).test(c);
}

function isLetter (c) {
	return new RegExp(/[ a-zA-Z'\&\(\)\-\/]/).test(c);
}

function isURLChar (c) {
	return new RegExp(/[a-zA-Z=:,%#\.\-\/\?\&]/).test(c);
}

function isCallerIDChar (c) {
	return new RegExp(/[ a-zA-Z0-9:_,-<>\(\)\"&@\.\+]/).test(c);
}

function isDialpatternChar (c) {
	return new RegExp(/[-0-9\[\]\+\.\|ZzXxNn\*\#_!\/]/).test(c);
}

function isDialruleChar (c) {
	return new RegExp(/[0-9\[\]\+\.\|ZzXxNnWw\*\#\_\/]/).test(c);
}

function isDialDigitChar (c) {
	return new RegExp(/[0-9\*#]/).test(c);
}

function isFilenameChar (c) {
	return new RegExp(/[-0-9a-zA-Z\_]/).test(c);
}

function validateSingleDestination(theForm,formNum,bRequired) {
	var gotoType = theForm.elements[ 'goto'+formNum ].value;

	if (bRequired && gotoType == '') {
		alert(ipbx.msg.framework.validateSingleDestination.required);
		return false;
	} else {
		// check the 'custom' goto, if selected
		if (gotoType == 'custom') {
			var gotoFld = theForm.elements[ 'custom'+formNum ];
			var gotoVal = gotoFld.value;
			if (gotoVal.indexOf('custom-') == -1) {
				alert(ipbx.msg.framework.validateSingleDestination.error);
				gotoFld.focus();
				return false;
			}
		}
	}

	return true;
}

function weakSecret() {
  var password = document.getElementById('devinfo_secret').value;
  var origional_password = document.getElementById('devinfo_secret_origional').value;

  if (password == origional_password) {
    return false;
  }

  if (password.length <= 5) {
    alert(ipbx.msg.framework.weakSecret.length);
    return true;
  }

  if (password.match(/[a-z].*[a-z]/i) == null || password.match(/\d\D*\d/) == null) {
    alert(ipbx.msg.framework.weakSecret.types);
    return true;
  }
  return false;
}

//set up query retreiver
$.urlParam = function(name){
    var match = new RegExp('[\\?&]' + name + '=([^&#]*)').exec(window.location.search);
    return match && decodeURIComponent(match[1].replace(/\+/g, ' '));
}

var popover_box;
var popover_box_class;
var popover_box_mod;
var popover_select_id;
//weird name, but should be self explanitor
function bind_dests_double_selects() {
	//destination double dropdown code
	$('.destdropdown').unbind().bind('change', function(e){
		var id		= $(this).data('id');
		var id 		= typeof id == 'undefined' ? '' : id; //ensure id isn't set to undefined
		var dest	= $(this).val();

		$('[data-id=' + id + '].destdropdown2').hide();
		dd2 = $('#' + dest + id + '.destdropdown2');
		cur_val = dd2.show().val();

		// This was added because a cancel can leave dd2 cur_val to popover
		// even when there are other choices so we force it to 'none'
		if (dd2.children().length > 1 && cur_val == 'popover') {
			dd2.val('');
			cur_val = '';
		}
		if (cur_val == 'popover') {
			dd2.trigger('change');
		}
	});

	$('.destdropdown2').unbind().bind('change', function(){
		//get last
		var dest = $(this).val();
		if (dest == "popover") {
			var urlStr = $(this).data('url') + '&fw_popover=1';
			var id = $(this).data('id');
			popover_select_id = this.id;
			popover_box_class = $(this).data('class');
			popover_box_mod = $(this).data('mod');
			popover_box = $('<div id="popover-box-id" data-id="'+id+'"></div>')
  			.html('<iframe data-popover-class="'+popover_box_class+'" id="popover-frame" frameBorder="0" src="'+urlStr+'" width="100%" height="95%"></iframe>')
					.dialog({
						title: 'Add',
						resizable: false,
						modal: true,
						position: ['center', 50],
						width: window.innerWidth - (window.innerWidth * .10),
						height: window.innerHeight - (window.innerHeight * .10),
						create: function() {
							$("body").scrollTop(0).css({ overflow: 'hidden' });
						},
						close: function (e) {
							//cheating by puttin a data-id on the modal box
							var id = $(this).data('id');
							//dropdown 1
							var par = $('#goto'+id).data('last');
							$('#goto'+id).val(par).change();
							if (par != '') { //Get dropdown2
								var par_id = par.concat(id);
								$('#'+par_id).val($('#'+par_id).data('last')).change();
							}
							$('#popover-frame').contents().find('body').remove();
							$('#popover-box-id').html('');
							$("body").css({ overflow: 'inherit' });
							$(e.target).dialog("destroy").remove();
						},
						buttons: [ {
							text: ipbx.msg.framework.save,
							click: function() {
								pform = $('#popover-frame').contents().find('.popover-form').first();
								if (pform.length == 0) {
									pform = $('#popover-frame').contents().find('form').first();
								}
								pform.submit();
							}
						}, {
							text: ipbx.msg.framework.cancel,
							click: function() {
								$(this).dialog("close");
							}
						} ]
				});
		} else {
			//if we arent a popover set it, so we have it saved
			var last = $.data(this, 'last', dest);
		}
	});

	//hacky way to ensure destinations dropdown is the same background-color as currently selected item
	$('.destdropdown').bind('change', function(){
		if($(this).find('option:selected').val()=='Error'){
			$(this).css('background-color','red');
		}else{
			$(this).css('background-color','white');
		}
	});
}

function closePopOver(drawselects) {
  var options = $('.' + popover_box_class + ' option', $('<div>' + drawselects + '</div>'));
	$('.' + popover_box_class).each(function(){
		if (this.id == popover_select_id) {
			$(this).empty().append(options.clone());
		} else {
			dv = $(this).val();
			$(this).empty().append(options.clone()).val(dv);
		}
	});

	// In the case of multi-category destinations, we may have other options to update as well. Example would be adding
	// an extension can result in voicemail destinations being added so we want to udpate those too.
	//
  if (popover_box_class != popover_box_mod) {
		var options = {};
		$('.' + popover_box_mod).each(function(){
			var data_class = $(this).data('class');
			if (data_class != popover_box_class) {
				if (typeof options[data_class] == 'undefined') {
  				options[data_class] = $('.' + data_class + ' option', $('<div>' + drawselects + '</div>'));
				}
				dv = $(this).val();
				$(this).empty().append(options[data_class].clone()).val(dv);
			}
		});
	}
	$("body").css({ overflow: 'inherit' });
	$('#popover-box-id').html('');
	popover_box.dialog("destroy");
}

/* popOverDisplay()
 * convert a normal module display page to a format suitable for a destination popOver display.
 * - remove the rnav
 * - hide the submit buttons
 * - insert a hidden input type for fw_popover_process into the form
 */
function popOverDisplay() {
	$('.rnav').hide();
	pform = $('.popover-form').first();
	if (pform.length == 0) {
		pform = $('form').first();
	}
	$('[type="submit"]', pform).hide();
	$('<input>').attr({
		type: 'hidden',
		name: 'fw_popover_process'
	}).val(parent.$('#popover-frame').data('popover-class'))
		.appendTo(pform);
}

/***************************************************
 *             ISSABELPBX RELOAD                      *
 ***************************************************/
/*
 * were using jquery-ui to show the reload dialod
 * we position all dialog boxes at the center (x) and 50px from the top (y)
 * were also carefull to cleanup all/any remaning dom eelemens that we created
 * while in most situations cleanup wont matter much (the dom will be cleared next
 * time the user click submit, and there is, arguable at least a 1:1 ratio of
 * submit's vs Apply Config's), nevertheless, certain wonkyness seems to be exhibited
 * if we dont cleanup, specificaly when showing the 'more info' of an error
 *
 */

//confirm reload if requested, otherwise just call ipbx_reload
function ipbx_reload_confirm() {
	if (!ipbx.conf.RELOADCONFIRM) {
		ipbx_reload();
	}

	$('<div></div>')
		.html('Reloading will apply all configuration changes made '
		+ 'in IssabelPBX to your PBX engine and make them active.')
		.dialog({
			title: 'Confirm reload',
			resizable: false,
			modal: true,
			position: ['center', 50],
			close: function (e) {
				$(e.target).dialog("destroy").remove();
			},
			buttons: [
				{
					text: ipbx.msg.framework.continuemsg,
					click: function() {
							$(this).dialog("destroy").remove();
							ipbx_reload();
					}

				},
				{
					text: ipbx.msg.framework.cancel,
					click: function() {
							$(this).dialog("destroy").remove();
						}
				}
				]
		});
}

//do the actual reload
function ipbx_reload() {
	$('<div></div>').progressbar({value: 100})
	var box = $('<div></div>')
		.html('<progress style="width: 100%">'
			+ 'Please wait...'
			+ '</progress>')
		.dialog({
			title: 'Reloading...',
			resizable: false,
			modal: true,
			height: 50,
			position: ['center', 50],
			close: function (e) {
				$(e.target).dialog("destroy").remove();
			}
		});
	$.ajax({
		type: 'POST',
		url: document.location.pathname,
		data: "handler=reload",
		dataType: 'json',
		success: function(data) {
			box.dialog('destroy').remove();
			if (!data.status) {
				// there was a problem
				var r = '<h3>' + data.message + '<\/h3>'
						+ '<a href="#" id="error_more_info">click here for more info</a>'
						+ '<pre style="display:none">' + data.retrieve_conf + "<\/pre>";
				if (data.num_errors) {
					r += '<p>' + data.num_errors + ipbx.msg.framework.reload_unidentified_error + "<\/p>";
				}
				issabelpbx_reload_error(r);
			} else {
				//unless ipbx.conf.DEVELRELOAD is true, hide the reload button
				if (ipbx.conf.DEVELRELOAD != 'true') {
					toggle_reload_button('hide');
				}
			}
		},
		error: function(reqObj, status) {
			box.dialog("destroy").remove();
				var r = '<p>' + ipbx.msg.framework.invalid_responce + '<\/p>'
					+ "<p>XHR response code: " + reqObj.status
					+ " XHR responseText: " + reqObj.resonseText
					+ " jQuery status: " + status  + "<\/p>";
					issabelpbx_reload_error(r);
		}
	});
}

//show reload error messages
function issabelpbx_reload_error(txt) {
	var box = $('<div></div>')
		.html(txt)
		.dialog({
			title: 'Error!',
			resizable: false,
			modal: true,
			minWidth: 600,
			position: ['center', 50],
			close: function (e) {
				$(e.target).dialog("destroy").remove();
			},
			buttons: [
					{
						text: ipbx.msg.framework.retry,
						click: function() {
								$(this).dialog("destroy").remove();
								ipbx_reload();
						}
					},
					{
						text: ipbx.msg.framework.cancel,
						click: function() {
								$(this).dialog("destroy").remove();
							}
					}
				]
		});
	$('#error_more_info').click(function(){
		$(this).next('pre').show();
		$(this).hide();
		return false;
	})
}

//show reload button if needed
function toggle_reload_button(action) {
	switch (action) {
		case 'show':
			//weird css is needed to keep the button from "jumping" a bit out of place
			$('#button_reload').show().css('display', 'inline-block');
			break;
		case 'hide':
			$('#button_reload').hide();
			break;
	}
}

/***************************************************
 *             GLOBAL JQUERY CODE                  *
 ***************************************************/
$(document).ready(function(){
	bind_dests_double_selects();

	//help tags
	$("a.info").each(function(){
		$(this).after('<span class="help">?<span>' + $(this).find('span').html() + '</span></span>');
		$(this).find('span').remove();
		$(this).replaceWith($(this).html())
	})

	$(".help").on('mouseenter', function(){
			side = ipbx.conf.text_dir == 'lrt' ? 'left' : 'right';
			var pos = $(this).offset();
	    	var offset = (200 - pos.side)+"px";
			//left = left > 0 ? left : 0;
			$(this).find("span")
					.css(side, offset)
					.stop(true, true)
					.delay(500)
					.animate({opacity: "show"}, 750);
		}).on('mouseleave', function(){
			$(this).find("span")
					.stop(true, true)
					.animate({opacity: "hide"}, "fast");
	});

	//show/hide a gui_eleements section
	$('.guielToggle').click(function() {
		var txt = $(this).find('.guielToggleBut');
		var el = $(this).data('toggle_class');
		var section = $.urlParam('display') + '#' + el;

		//true = hide
		//false = dont hide
		switch(txt.text().replace(/ /g,'')) {
			case '-':
				txt.text('+ ');
				$('.' + el).hide();

				//set cookie of hidden section
				guielToggle = $.parseJSON($.cookie('guielToggle')) || {};
				guielToggle[section] = false;
				$.cookie('guielToggle', JSON.stringify(guielToggle));
				break;
			case '+':
				txt.text('-  ');
				$('.'+el).show();

				//set cookie of hidden section
				guielToggle = $.parseJSON($.cookie('guielToggle')) || {};
				if (guielToggle.hasOwnProperty(section)){
					guielToggle[section] = true;
					$.cookie('guielToggle', JSON.stringify(guielToggle));
				}
				break;
		}
	})

	//set language on click
	$('#ipbx_lang > li').click(function(){
		$.cookie('lang', $(this).data('lang'));
		window.location.reload();
	})

	//new skin - work in progres!
	$('.rnav > ul').menu();
	$('.radioset').buttonset();
	$('.menubar').menubar().hide().show();


	//show menu on hover
	//this is far from perfect, and will hopefully be depreciated soon
	//HACK for low resolution displays where menu is cut off
	$('.module_menu_button').hover(function() {
		$(this).click();
		var sh = $(window).height();
		$('.menubar.ui-menu').each(function() {
			if ($(this).css('display') == 'block') {
				$(this).css('max-height', '');
				if ($(this).height() > sh) {
					$(this).css('max-height',sh - 50 +'px');
				}
			}
		});
	});

	//show reload button if neede
	if (ipbx.conf.reload_needed) {
		toggle_reload_button('show');
	}

	//style all sortables as menu's
  $('.sortable').menu();

  //allow click on checkbox and surrounding area
  $('.sortable li').click(function(event) {
    if ($(event.target).is(':checkbox')) {
      return true;
    }
    var checkbox = $(this).find('input');
    checkbox.prop('checked', !checkbox[0].checked);
    return false;
  });

  //stop propagation when clicking on a checkbox in an a link
  $('.audio-codecs').click(function(event) {
    event.stopPropagation();
  });

	//Links are disabled in menu for now. Final release will remove that
	$('.ui-menu-item').click(function(){
		go = $(this).find('a').attr('href');
		if(go && !$(this).find('a').hasClass('ui-state-disabled')) {
			document.location.href = go;
		}
	})

	//reload
	$('#button_reload').click(function(){
		if (ipbx.conf.RELOADCONFIRM == 'true') {
			ipbx_reload_confirm();
		} else {
			ipbx_reload();
		}

	});

	//logo icon
	$('#MENU_BRAND_IMAGE_TANGO_LEFT').click(function(){
		window.open($(this).data('brand_image_issabelpbx_link_left'),'_newtab');
	});

	//pluck icons out of the markup - no need for js to add them (for buttons)
	$('input[type=submit],input[type=button], button, input[type=reset]').each(function(){
		var prim = (typeof $(this).data('button-icon-primary') == 'undefined')
					? ''
					: ($(this).data('button-icon-primary'));
		var sec  = (typeof $(this).data('button-icon-secondary') == 'undefined')
					? ''
					: ($(this).data('button-icon-secondary'));
		var txt = 	(typeof $(this).data('button-text') == 'undefined')
					? 'true'
					: ($(this).data('button-text'));
		var txt = (txt == 'true') ? true : false;
		$(this).button({ icons: {primary: prim, secondary: sec}, text: txt});
	});

	/* Search for fields marked as class .extdisplay or the common button types. Add a span so we can warn
	   when they are using a duplicate extension, adding a duplicate class also for styling options.
		 TODO: get feedback on a different image
	 */
	var extselector = $('input.extdisplay,input[type=text][name=extension],input[type=text][name=extdisplay],input[type=text][name=account]').not('input.noextmap');
	if(extselector.length > 0) {
			extselector.after(" <span style='display:none'><a href='#'><img src='images/notify_critical.png'/></a></span>").keyup(function(){
			if (typeof extmap[this.value] == "undefined" || $(this).data('extdisplay') == this.value) {
				$(this).removeClass('duplicate-exten').next('span').hide();
			} else {
				$(this).addClass('duplicate-exten').next('span').show().children('a').attr('title',extmap[this.value]);
			}
		}).each(function(){
			/* we automatically add a data-extdisplay data tag to the element if it is not already there and set the value that was
			 * loaded at page load time. This allows modules who are trying to guess at an extension value to preset so we don't
			 * pre-determine a value is safe when the generating code may be flawed, such as ringgroups and vmblast groups.
			 */
			if (typeof $(this).data('extdisplay') == "undefined") {
				$(this).data('extdisplay', this.value);
			} else if (typeof extmap[this.value] != "undefined") {
				this.value++;
				while (typeof extmap[this.value] != "undefined") {
					this.value++;
				}
			}
		}).parents('form').submit(function(e){
			/* If there is a duplicate-exten class on an element then validation fails, don't let the form submit
			 */
			// If a previous submit handler has cancelled this don't bother
			if (e.isDefaultPrevented()) {
				return false;
			}
			exten = $('.duplicate-exten', this);
			if (exten.length > 0) {
				extnum = exten.val();
				alert(extnum + ipbx.msg.framework.validation.duplicate + extmap[extnum]);
				return false;
			}
			return true;
		});
	}

	//shortcut keys
	//show modules
	$(document).bind('keydown', 'meta+shift+a', function(){
		$('#modules_button').trigger('click');
	});

	//submit button
	$(document).bind('keydown', 'ctrl+shift+s', function(){
		$('input[type=submit][name=Submit]').click();
	});

	//reload
	$(document).bind('keydown', 'ctrl+shift+a', function(){
		ipbx_reload();
	});

	//logout button
	$('#user_logout').click(function(){
		url = window.location.pathname;
		$.get(url + '?logout=true', function(){
			$.cookie('PHPSESSID', null);
			window.location = url;
		});

	});

	/** Used in issabelpbx_helpers.php function ipbx_form_input_check() to enable/disable the text box as the check box is checked
   *  and retain the value and swap with the default value provided.
   */
	$(".input_checkbox_toggle_true, .input_checkbox_toggle_false").click(function(){
		checked = $(this).hasClass('input_checkbox_toggle_true') ? this.checked : ! this.checked;
		$(this).prev().prop('disabled', checked);
		if (checked) {
			$(this).data('saved', $(this).prev().val());
			$(this).prev().val($(this).data('disabled'));
		} else {
			$(this).prev().val($(this).data('saved'))
		}
  });

	//ajax spinner
	$(document).ajaxStart(function(){
		$('#ajax_spinner').show()
	});

	$(document).ajaxStop(function(){
		$('#ajax_spinner').hide()
	});

	$('#login_admin').click(function(){
		var form = $('#login_form').html();
		$('<div></div>')
			.html(form)
			.dialog({
				title: 'Login',
				resizable: false,
				modal: true,
				position: ['center', 'center'],
				close: function (e) {
					$(e.target).dialog("destroy").remove();
				},
				buttons: [
					{
						text: ipbx.msg.framework.continuemsg,
						click: function() {
								$(this)
									.find('form')
									.trigger('submit');
						}

					},
					{
						text: ipbx.msg.framework.cancel,
						click: function() {
								$(this).dialog("destroy").remove();
							}
					}
					],
			focus: function() {
        		$(':input', this).keyup(function(event) {
            		if (event.keyCode == 13) {
                		$('.ui-dialog-buttonpane button:first').click();
           			}
				})
			 }
        });
	});

	/* Remove all hidden secondary dropdown boxes for destinations if previous submit handlers have
	 * not already cancelled the submit so that we try to prevent the max input box limits of PHP.
	 * This should be kept as much last as possible so it gets fired after all other submit actions.
	 */
	$('form').submit(function(e){
		// If the page isn't going to submit then don't remove the elements
		if (!e.isDefaultPrevented()) {
			$('.destdropdown2').filter(':hidden').remove();
		}
	});
});
