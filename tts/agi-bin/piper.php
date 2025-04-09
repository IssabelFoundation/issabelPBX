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
        }
    }
}

if(!isset($voice)) { die("No --voice supplied"); }
if(!isset($filename)) { die("No --file supplied"); }

if(!isset($voice)) { $voice="es_MX-claude-high.onnx"; }

piper_tts($text);

die();

function piper_tts($text) {
    global $voice;
    global $filename;

    $escapedText = escapeshellarg($text);
    $escapedVoice = escapeshellarg($voice);
    
    // Construct the command
    $command = sprintf('echo %s | piper --model /usr/local/piper/voices/%s --output_file %s',
        $escapedText,
        $escapedVoice,
        escapeshellarg($filename).".wav"
    );

    $output = [];
    $return_var = 0;
    exec($command . ' 2>&1', $output, $return_var);

    if ($return_var !== 0) {
        throw new Exception("Command failed with error: " . implode("\n", $output));
    }

}
