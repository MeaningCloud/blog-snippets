<?php
/************************************************************************
  #ILovePolitics Popularity Report
/************************************************************************/

require_once(__DIR__.'/simplepie_1.3.1.mini.php');

/***************************************************************
 * Reads items from an RSS feed
 * @param $url: RSS feed
 * @return set of items
 ***************************************************************/
function readFeed($url) {
  // array with results
  $items = array();

  // create and configure SimplePie object
  $feed = new SimplePie();
  $feed->set_cache_location(sys_get_temp_dir());
  $feed->set_feed_url($url);
  $feed->init();
  $feed->set_output_encoding('UTF-8');
  $feed->handle_content_type();
  if(empty($feed->error())) {
    // get all items
    foreach ($feed->get_items() as $item)
      $items[] = array('permalink' => $item->get_permalink(),
                       'title' => $item->get_title(),
                       'content' => $item->get_description(),
                       'date' => $item->get_date('Y-m-d H:i:s'));
  }
  // return results
  return $items;
} // readFeed

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
  $response = postRequest('http://api.meaningcloud.com/topics-1.2',
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


// List of entities to ignore
$STOPENTITIES = array(
  'Rep' => 1,
  'Gov' => 1,
  'Sen' => 1,
);

// List of aliases
$MATCHING = array(
  'Hillary Clinton' => 'Hillary Rodham Clinton',
  'Hillary' => 'Hillary Rodham Clinton',
  'Team Hillary' => 'Hillary Rodham Clinton',
  'Clinton' => 'Hillary Rodham Clinton',
  'Senator Bernie Sanders of Vermont' => 'Bernard Sanders',
  'Bernie Sanders' => 'Bernard Sanders',
  'Sanders' => 'Bernard Sanders',
  'Obama' => 'Barack Obama',
  'Trump' => 'Donald Trump',
  'Biden' => 'Joe Biden',
  'Biden Jr.' => 'Joe Biden',
  'Vice President Joe Biden' => 'Joe Biden',
  'Fiorina' => 'Carly Fiorina',
);

// topic info
$info = array();

// topic links
$links = array();

// number of items 
$items = 0;

// list of feeds
foreach(array('http://feeds.washingtonpost.com/rss/politics',
              'http://rss.nytimes.com/services/xml/rss/nyt/Politics.xml',
              'http://www.washingtontimes.com/rss/headlines/news/political-theater/',
              'http://www.washingtontimes.com/rss/headlines/news/politics/',
              'http://www.latimes.com/nation/politics/rss2.0.xml',
              'http://www.latimes.com/nation/politics/politicsnow/rss2.0.xml') as $feed) {

  // read feed
  foreach(readFeed($feed) as $item) {

    // increment item count
    $items++;

    // get topics
    $content = '<h1>'.$item['title'].'</h1>'.$item['content'];
    $topics = getEntities($content, 'en');

    // store topic data (info and links)
    foreach($topics as $form => $types) {
      if(isset($MATCHING[$form]))
        $form = $MATCHING[$form];
      if(!isset($STOPENTITIES[$form])) {
        if(!isset($info[$form])) 
          $info[$form] = array();
        foreach($types as $type)
          $info[$form][$type] = 1;
        if(!isset($links[$form]))
          $links[$form] = array();
        $links[$form][$item['permalink']] = $item['title'];
      }
    }
  }
}

// sort topics by frequency (number of links)
uasort($links, function($a, $b) use ($links) {
  return (sizeof($a)<=sizeof($b) ? 1 : -1);
});

// display information
foreach($links as $form => $data) {
  echo $form."\t".implode('|', array_keys($info[$form]))."\t".sizeof($data).' - '.round(100*sizeof($data)/$items, 2).'%'."\n";
  foreach($data as $url => $title) 
    echo "\t".$title."\t".$url."\n";
}
?>