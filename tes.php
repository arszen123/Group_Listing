<?php
$time = date("Y-m-d H:i:s");
$root = $_SERVER["DOCUMENT_ROOT"].'/grouplisting/';
require_once $root.'Feed.php';
require_once $root.'Facebook.php';
$fb = new FB();
$fp = new FeedProcessing();
$fb->makeNodeRequest('104776596538743_499567213726344?fields=full_picture,message,id,updated_time,created_time');
$feed = $fb->getFeed();
$fp->setProcessingData($feed);
$fp->process();
$data_in_array = $fp->getData();
print_r($data_in_array);
$PDO->insert('INSERT INTO log (executedFrom, ecexutedTime) VALUES ( :from, :time)',['from'=>'test','time'=>$time]);
?>