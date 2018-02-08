<?php
// MeaningCloud Key
$MEANINGCLOUD_KEY = '<<< INSERT YOUR LICENSE KEY HERE >>>';

/***************************************************************
 * Makes a POST request to an API and reads response.
 * @param $url: API entry point
 * @param $data: array of parameters
 * @param $timeout: network timeout
 * @return read data
 ***************************************************************/
function postRequest($url, $data, $timeout=5) {
  $request = http_build_query($data);
  $context = stream_context_create(
    array('http' =>
           array('method' => 'POST',
                 'timeout' => $timeout,
                 'Content-type: application/x-www-form-urlencoded'."\r\n".
                 'Content-length: '.strlen($request)."\r\n",
                 'content' => $request)));
  $fd = @fopen($url, 'r', false, $context);
  if($fd) {
    $response = stream_get_contents($fd);
    fclose($fd);
  } else 
    $response = '';
  return $response;
} // postRequest


/***************************************************************
 * Calls MeaningCloud Topics API
 * @param $text: input text
 * @param $language: text language
 * @return set of topics 
 ***************************************************************/
function getEntities($text, $language) {
  // make a POST request with the necessary parameters
  $response = postRequest('http://api.meaningcloud.com/topics-2.0',
                          array('key' => $GLOBALS['MEANINGCLOUD_KEY'],
                                'lang' => $language,
                                'tt' => 'e',
                                'txt' => $text, 
                                'txtf' => 'markup'));
  // read result into JSON
  $result = json_decode($response, true);
  // array of entities
  $topics = array();
  foreach($result['entity_list'] as $topic) {
    if(!isset($topics[$topic['form']]))
      $topics[$topic['form']] = array();
    $topics[$topic['form']][] = $topic['sementity']['type'];
  }  
  return $topics;
} // getEntities

$content = 'Barack Obama is the current president of United States of America';
$topics = getEntities($content, 'en');
print_r($topics);
?>