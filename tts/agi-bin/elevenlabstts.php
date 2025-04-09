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
            case '--voice':
                $voice = trim($argv[$x+1]);
            break;
            case '--file':
                $filename = trim($argv[$x+1]);
            break;
            case '--elevenlabskey':
                $key = trim($argv[$x+1]);
            break;
        }
    }
}

if(!isset($voice)) { die("No --voice supplied"); }
if(!isset($filename)) { die("No --file supplied"); }
if(!isset($key)) { die("No --key supplied"); }

eleven_tts($text, $key, $filename, $voice);

function eleven_tts($text, $key, $filename, $voice_id) {

    $url = "https://api.elevenlabs.io/v1/text-to-speech/$voice_id?output_format=ulaw_8000";

    $headers = array(
      'Content-Type: application/json',
      "xi-api-key: $key"
    );

    $data = array(
        "text"=> $text,
        "model_id"=> "eleven_multilingual_v2",
        "voice_settings"=>array(
            "stability"=>0.5,
            "similarity_boost"=>0.5
        )
    );

    $payload = json_encode($data);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_VERBOSE, false);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    $content = curl_exec($ch);
    curl_close($ch);

    $fp = fopen($filename.".ulaw", 'w');
    fwrite($fp, $content);
    fclose($fp);

}

