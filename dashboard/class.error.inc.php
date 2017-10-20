<?php 
if (!defined('ISSABELPBX_IS_AUTH')) { die('No direct script access allowed'); }
//	License for all code of this IssabelPBX module can be found in the license file inside the module directory
//	Copyright 2013 Schmooze Com Inc.
//
class Error {

	// Array which holds the error messages
	var $arrErrorList 	= array();
	// current number of errors encountered
	var $errors 		= 0;

	/**
	*
	*  addError()
	*  
	*  @param	strCommand	string		Command, which cause the Error
	*  @param	strMessage	string		additional Message, to describe the Error
	*  @param	intLine		integer		on which line the Error occours
	*  @param	strFile		string		in which File the Error occours
	*
	*  @return	-
	*
	**/
	function addError( $strCommand, $strMessage, $intLine, $strFile ) {
		$this->arrErrorList[$this->errors]['command'] = $strCommand;
		$this->arrErrorList[$this->errors]['message'] = $strMessage;
		$this->arrErrorList[$this->errors]['line']    = $intLine;
		$this->arrErrorList[$this->errors]['file']    = basename( $strFile );
		$this->errors++;
	}

	/**
	*
	* ErrorsAsHTML()
	*
	* @param	-
	*
	* @return	string		string which contains a HTML table which can be used to echo out the errors
	*
	**/
	function ErrorsAsHTML() {
		$strHTMLString = "";
		$strWARNString = "";
		$strHTMLhead = "<table width=\"100%\" border=\"0\">\n"
		 . " <tr>\n"
		 . "  <td><font size=\"-1\"><b>File</b></font></td>\n"
		 . "  <td><font size=\"-1\"><b>Line</b></font></td>\n"
		 . "  <td><font size=\"-1\"><b>Command</b></font></td>\n"
		 . "  <td><font size=\"-1\"><b>Message</b></font></td>\n"
		 . " </tr>\n";
		$strHTMLfoot = "</table>";

		if( $this->errors > 0 ) {
			foreach( $this->arrErrorList as $arrLine ) {
				if( $arrLine['command'] == "WARN" ) {
					$strWARNString .= "<font size=\"-1\"><b>WARNING: " . htmlspecialchars( $arrLine['message'] ) . "</b></font><br/>\n";
				} else {
					$strHTMLString .= " <tr>\n"
                          . "  <td><font size=\"-1\">" . htmlspecialchars( $arrLine['file'] ) . "</font></td>\n"
					                . "  <td><font size=\"-1\">" . $arrLine['line'] . "</font></td>\n"
					                . "  <td><font size=\"-1\">" . htmlspecialchars( $arrLine['command'] ) . "</font></td>\n"
					                . "  <td><font size=\"-1\">" . htmlspecialchars( $arrLine['message'] ) . "</font></td>\n"
					                . " </tr>\n";
				}
			}
		}
    
		if( !empty( $strHTMLString ) ) {
			$strHTMLString = $strWARNString . $strHTMLhead . $strHTMLString . $strHTMLfoot;
		} else {
			$strHTMLString = $strWARNString;
		}

		return $strHTMLString;
	}

	/**
	*
	* ErrorsAsText()
	*
	* @param	-
	*
	* @return	string		string which contains a table which can be used to echo out the errors
	*
	**/
	function ErrorsAsText() {
		$strHTMLString = "";
		$strWARNString = "";
		$strHTMLhead = sprintf("%24s %6s %40s %s\n","File", "Line", "Command", "Message");
		$strHTMLfoot = "\n";

		if( $this->errors > 0 ) {
 			foreach( $this->arrErrorList as $arrLine ) {
				if( $arrLine['command'] == "WARN" ) {
					$strWARNString .= "WARNING: " . $arrLine['message'] . "\n";
				} else {
					$strHTMLString .= sprintf("%24s %6s %40s %s\n",$arrLine['file'], $arrLine['line'], $arrLine['command'], $arrLine['message'] );
				}
			}
		}
    
    if( !empty( $strHTMLString ) ) {
      $strHTMLString = $strWARNString . $strHTMLhead . $strHTMLString . $strHTMLfoot;
    } else {
      $strHTMLString = $strWARNString;
    }
    
    return $strHTMLString;
  }

	/**
	*
	* ErrorsExist()
	*
	* @param	-
	*
	* @return 	true	there are errors logged
	*		false	no errors logged
	*
	**/
	function ErrorsExist() {
		if( $this->errors > 0 ) {
			return true;
		} else {
			return false;
		}
	}
}
?>
