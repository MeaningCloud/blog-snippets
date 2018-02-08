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
 * Calls MeaningCloud Class API
 * @param $text: input text
 * @param $model: categorization model
 * @param $text: text language
 * @return set of categories 
 ***************************************************************/
function getCategories($text, $model, $language) {
  // make a POST request with the necessary parameters
  $response = postRequest('http://api.meaningcloud.com/class-1.1',
                          array('key' => $GLOBALS['MEANINGCLOUD_KEY'],
                                'model' => $model.'_'.$language,
                                'txt' => $text,
                                'title' => $text));
  // read result into JSON
  $result = json_decode($response, true);
  // read array of categories
  $categories = array();
  if(isset($result['category_list'])) {
    foreach($result['category_list'] as $category)
      $categories[] = $category['label'];
  }
  if(empty($categories))
    $categories[] = 'unknown';
  return $categories;
} // getEntities

$content = 'RT @WhiteHouse: Watch @POTUS go behind the camera in Alaska to talk about the impacts of climate change: http://t.co/eAVC9RjeMV https://t.c…';
$iptc = getCategories($content, 'IPTC', 'en');
print_r($iptc);
?>