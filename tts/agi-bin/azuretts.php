#!/usr/bin/env php
<?php

if( php_sapi_name() != 'cli' ) die('Access denied.');

$argv = $_SERVER['argv'];

$totalArgv = count($argv);
if( $totalArgv > 1 ){
    for( $x = 1; $x < $totalArgv; $x++ ) {
        switch($argv[$x])
        {
            case '--text':
                $text = trim($argv[$x+1]);
            break;
            case '--lang':
                $language = trim($argv[$x+1]);
            break;
            case '--voice':
                $voice = trim($argv[$x+1]);
            break;
            case '--gender':
                $gender = trim($argv[$x+1]);
            break;
            case '--file':
                $filename = trim($argv[$x+1]);
            break;
            case '--azurekey':
                $key = trim($argv[$x+1]);
            break;
            case '--region':
                $region = trim($argv[$x+1]);
            break;
        }
    }
}

if(!isset($voice)) { die("No --voice supplied"); }
if(!isset($filename)) { die("No --file supplied"); }
if(!isset($key)) { die("No --key supplied"); }

if(!isset($language)) { $language="en-US"; }
if(!isset($gender)) { $gender="Female"; }
if(!isset($voice)) { $voice="en-US-AriaRUS"; }
if(!isset($region)) { $region="eastus"; }

azure_tts($text);

die();

function azure_tts($text) {
    global $key;
    global $region;
    global $language;
    global $gender;
    global $voice;
    global $filename;

    $pregunta = 0;
    $body = "<speak version='1.0' xml:lang='en-US'> <voice xml:lang='$language' xml:gender='$gender' name='$voice'>";
    if($pregunta==1) {
        $body .= "<prosody contour=\"(60%, -11%) (85%, +85%)\" >";
    }
    $body.=$text;
    if($pregunta==1) {
        $body.="</prosody>";
    }
    $body.= "</voice></speak>";

    $headers = array(
     "Ocp-Apim-Subscription-Key: $key" ,
     'Content-Type: application/ssml+xml' ,
     'X-Microsoft-OutputFormat: raw-8khz-8bit-mono-alaw' ,
     'User-Agent: curl' ,
     'Content-Type: text/plain' ,
    );

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_VERBOSE, false);
    curl_setopt($ch, CURLOPT_URL, "https://$region.tts.speech.microsoft.com/cognitiveservices/v1");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    $content = curl_exec($ch);
    curl_close($ch);
    
    $fp = fopen("${filename}.alaw", 'w');
    fwrite($fp, $content);
    fclose($fp);

}
