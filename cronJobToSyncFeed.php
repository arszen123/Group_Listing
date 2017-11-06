<?php
$time = date("Y-m-d H:i:s");
$root = $_SERVER["DOCUMENT_ROOT"].'/grouplisting/';
require $root.'sync_feed.php';
$sf = new syncFeed();
$PDO = $sf->getPDO();
$result = $PDO->getPreparedQuery('SELECT * FROM fb_group WHERE 1')->fetchAll(PDO::FETCH_ASSOC);
foreach($result as $i){
	$sf->setId($i['id']);
	$sf->syncPageFeed(1);
}
$PDO->insert('INSERT INTO log (executedFrom, ecexutedTime) VALUES ( :from, :time)',['from'=>'SyncFeed','time'=>$time]);
?>