<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

$xml = simplexml_load_string(utf8_encode(file_get_contents('./leyes/ley18331.xml')));
//$xml = simplexml_load_file('./ley18441_utf8.xml');

$json = json_encode($xml);
print utf8_decode($xml->norma->referencias);
print '<pre>';
print_r($xml);
print_r($json);

?>