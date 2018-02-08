<?php
require_once('TwitterAPIExchange.php');

$TWITTER_AUTH = array('oauth_access_token' => '<<< Your Access Token >>>',
                      'oauth_access_token_secret' => '<<< Your Access Token Secret >>>',
                      'consumer_key' => '<<< Your Consumer Key >>>',
                      'consumer_secret' => '<<< Your Consumer Secret >>>');
                      

function getTweetsByUser($screenName, $count) {
  $twitter = new TwitterAPIExchange($GLOBALS['TWITTER_AUTH']);
  $response = $twitter->setGetfield('?screen_name='.$screenName.'&count='.$count)
                ->buildOauth('https://api.twitter.com/1.1/statuses/user_timeline.json', 'GET')
                ->performRequest();
  $result = json_decode($response, true);
  return $result;
} // getTweetsByUser


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


// the list of Twitter accounts
foreach(array('barackobama', 
              'hillaryclinton',
              'realDonaldTrump',
              'JebBush') as $person) {

  echo '================== '.$person.' =================='."\n";

  // array of categories, and counts
  $categories = array();
  $countAll = 0;
  $countNotUnknown = 0;

  // get tweets
  foreach(getTweetsByUser($person, 100) as $tweet) {
    // get categories of each tweet
    $iptc = getCategories($tweet['text'], 'IPTC', 'en');
    echo implode('|', $iptc)."\t".$tweet['text']."\n";

    $countAll++;

    // for not "unknown" tweets
    if($iptc[0]!='unknown') {
      $countNotUnknown++;
      // reduce category to 1st level only
      $iptc1st = array();
      foreach($iptc as $category)
        $iptc1st[] = preg_replace('/^(.+?)( -.+?)$/sui', '\1', $category);
      $iptc1st = array_unique($iptc1st);

      // aggregate the information
      foreach($iptc1st as $category) {
        if(!isset($categories[$category]))
          $categories[$category] = array();
        $categories[$category][] = $tweet['text'];
      }
    }
  }
  echo "\n";
  
  // sort categories by frequency (number of tweets)
  uasort($categories, function($a, $b) {
    return (sizeof($a)<=sizeof($b) ? 1 : -1);
  });
  
  // display information
  foreach($categories as $category => $tweets) {
    echo $category."\t".sizeof($tweets).' - '.round(100*sizeof($tweets)/$countNotUnknown, 2).'% - '.round(100*sizeof($tweets)/$countAll, 2).'%'."\n";
    // prints individual tweets
    foreach($tweets as $tweet)
      echo "\t".$tweet."\n";
  }
  echo "\n";
}
?>