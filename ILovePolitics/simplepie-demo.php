<?php
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


// display the items of Google News in United States
$items = readFeed('http://news.google.com/news?cf=all&hl=en&pz=1&ned=us&output=rss');
foreach($items as $item) 
  echo $item['permalink']."\t".$item['title']."\t".$item['date']."\n";
?>