<?php
/**
 * Sentiment Analysis 2.0 starting client for PHP.
 *
 * In order to run this example, the license key must be included in the key variable.
 * If you don't know your key, check your personal area at MeaningCloud (https://www.meaningcloud.com/developer/account/)
 *
 * Once you have the key, edit the parameters and call "php sentimentclient-2.0.php"
 *
 * You can find more information at http://www.meaningcloud.com/developer/sentiment-analysis/doc/2.0
 *
 * @author     MeaningCloud
 * @contact    http://www.meaningcloud.com (http://www.daedalus.es)
 * @copyright  Copyright (c) 2015, MeaningCloud. All rights reserved.
 */
$api = 'http://api.meaningcloud.com/sentiment-2.0';
$parameters['key'] = '<<<your license key>>>';
$parameters['txt'] = "I could go on and on about this book. This was everything I wanted in a BLACK WIDOW comic. Nathan Edmondson comes out of the gate running. He has a great take on Natasha and reading this issue will immediately make you want more. Phil Noto's art is insanely good. He creates a fantastic mood full of energy and makes Natasha look great without over sexualizing her. This is a comic anyone can easily dive into. Buy an extra copy or two and give them to your friends or loved ones. This is the book Black Widow and comic fans deserve. How many days until issue two?";
$parameters['model'] = 'general_en';  // general_en / general_es / general_fr 
// We make the request and parse the response to an array
$response = sendPost($api, $parameters);
$json = json_decode($response, true);
// We print the aggregated values of the entities detected in the text (with the polarity and ontology type)
if(isset($json['sentimented_entity_list']) && count($json['sentimented_entity_list'])>0) {
  echo "\nEntities:\n";
  echo "============\n";
  foreach($json['sentimented_entity_list'] as $e) {
    echo '  - '.$e['form'];
    if(isset($e['score_tag'])) {
      echo ' - '.$e['score_tag'];
    }
    if(isset($e['type'])) {
      echo ' ('.$e['type'].')';
    }      
    echo "\n";
  }
}
// We print the aggregated values of the concepts detected in the text (with the polarity and ontology type)
if(isset($json['sentimented_concept_list']) && count($json['sentimented_concept_list'])>0) {
  echo "\nConcepts:\n";
  echo "============\n";
  foreach($json['sentimented_concept_list'] as $c) {
    echo '  - '.$c['form'];
    if(isset($c['score_tag'])) {
      echo ' - '.$c['score_tag'];
    }
    if(isset($c['type'])) {
      echo ' ('.$c['type'].')';
    }    
    echo "\n";
  }
}
echo "\n";
// Auxiliary function to make a post request 
function sendPost($api, $parameters) {
  $data = http_build_query($parameters); // management internal parameter
  $context = stream_context_create(array('http'=>array(
        'method'=>'POST',
        'header'=>
          'Content-type: application/x-www-form-urlencoded'."\r\n".
          'Content-Length: '.strlen($data)."\r\n",
        'content'=>$data)));
  
  $fd = fopen($api, 'r', false, $context);
  $response = stream_get_contents($fd);
  fclose($fd);
  return $response;
} // sendPost
?>
