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
            case '--languageCode':
                $language = trim($argv[$x+1]);
            break;
            case '--name':
                $name = trim($argv[$x+1]);
            break;
            case '--ssmlGender':
                $gender = trim($argv[$x+1]);
            break;
            case '--file':
                $filename = trim($argv[$x+1]);
            break;
            case '--credentials':
                $credentials = trim($argv[$x+1]);
            break;
        }
    }
}

if(!isset($name)) { die("No --name supplied"); }
if(!isset($filename)) { die("No --file supplied"); }
if(!isset($credentials)) { die("No --credentials supplied"); }

if(!isset($language)) { $language="en-US"; }
if(!isset($gender)) { $gender="MALE"; }
if(!isset($name)) { $name="en-US-Standard-A"; }

wavenet($text);

die();

function wavenet($text) {
    global $language;
    global $gender;
    global $name;
    global $filename;
    global $credentials;

    $token = `GOOGLE_APPLICATION_CREDENTIALS=$credentials gcloud auth application-default print-access-token`;
    $token = chop(trim($token));
    $params = [
      'input'=>[
        'text'=>"$text"
      ],
      'voice'=>[
        'languageCode'=>"$language",
        'name'=>"$name",
        'ssmlGender'=>"$gender"
      ],
      'audioConfig'=>[
        'audioEncoding'=>'ALAW',
        'sampleRateHertz'=>8000
      ]
    ];

    $data = json_encode($params);
    $len=strlen($data);
    $headers = array( "Authorization: Bearer $token","Content-Type: application/json","Content-Length: ".strlen($data));
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_VERBOSE, false);
    curl_setopt($ch, CURLOPT_URL, "https://texttospeech.googleapis.com/v1/text:synthesize");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    $response = curl_exec($ch);
    curl_close($ch);
    $responseDecoded = json_decode($response, true);

    if($responseDecoded['audioContent']){
        $fp = fopen("${filename}.alaw", 'w');
        fwrite($fp,base64_decode($responseDecoded['audioContent']));
        fclose($fp);
    } 

}
