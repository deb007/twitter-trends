<?php
ini_set('display_errors', 1);
require_once('TwitterAPIExchange.php');
require_once('dbclass.php');

$settings = array(
    'oauth_access_token' => "2424271-OAwptASAw6FCqz4G9ZO3llJAOoxpN9EpSjvAhzdvzT",
    'oauth_access_token_secret' => "YlGMuTAocgQk8W0GVQ6YE7FzdRodb5z9HST8SZU9ZPYih",
    'consumer_key' => "PMFfyEd88zMzsyAIKfEN8Q",
    'consumer_secret' => "CwVV27JVGhsB7kALIt2kztqUL8zdzEIapZXbcZpHY"
);


/** Perform a GET request and echo the response **/
$url = 'http://api.twitter.com/1.1/trends/place.json';
$getfield = '?id=23424848';
$requestMethod = 'GET';
$twitter = new TwitterAPIExchange($settings);
$trends = $twitter->setGetfield($getfield)
             ->buildOauth($url, $requestMethod)
             ->performRequest();


$arr_trends = array();
$arr_trends = json_decode($trends, true);
echo "<pre>";
//print_r($arr_trends);



$opts   = array();
$str    = '';

 $opts = array(
    'host'    => 'localhost',
    'user'    => 'root',
    'pass'    => '',
    'db'      => 'twitter-trends',
    'charset' => 'latin1'
 );
 $db = new SafeMySQL($opts);

 foreach ($arr_trends[0]['trends'] as $key => $valuearr) {

    $keyword    = $valuearr['name'];
    $str .= $keyword . "<br>";
    $id         = '';
    $id         = $db->getOne('SELECT id FROM keywords WHERE keyword = ?s',$keyword);
    if($id == '') {
        //echo "==NA<br>";
        $data = array();
        $data = array(
            'keyword' => $keyword, 
            'url' => $valuearr['url'], 
            'promoted_content' => $valuearr['promoted_content'], 
            'query' => $valuearr['query'], 
            'events' => $valuearr['events'], 
            'cnt_appearance' => 1, 
            'cnt_consecutive' => 1
            );
        $sql  = "INSERT INTO keywords SET ?u";
        $db->query($sql, $data);
        $id = $db->insertId();

    } else {
        $data   = array();
        $data   = $db->getCol("SELECT id FROM history LIMIT ?i", 10);

        if(@in_array($id, $data)) {
            //echo "found";
            $sql  = "INSERT INTO keywords SET keyword=?s ON DUPLICATE KEY UPDATE cnt_appearance = cnt_appearance + 1, cnt_consecutive = cnt_consecutive + 1";
            $db->query($sql,$keyword);
        } else {
            //echo "not found";
            $sql  = "INSERT INTO keywords SET keyword=?s ON DUPLICATE KEY UPDATE cnt_appearance = cnt_appearance + 1";
            $db->query($sql,$keyword);
        }

    }

    $data   = array();
    $data   = array('keyword_id' => $id);
    $sql  = "INSERT INTO history SET recorddate=CURDATE(), ?u";
    $db->query($sql, $data);
  }

echo $str;
echo "done";

?>