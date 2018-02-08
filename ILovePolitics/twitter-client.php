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

$user = 'barackobama';
$count = 100;
foreach(getTweetsByUser('barackobama', $count) as $tweet) 
  echo $tweet['created_at']."\t".$tweet['text']."\n";
?>