<?php
set_time_limit(0);
$time = date("Y-m-d H:i:s");
$root = $_SERVER["DOCUMENT_ROOT"].'/grouplisting/';
require $root.'Facebook.php';
require $root.'database_connection.php';
$fb = new FB();
$PDO = new PDOs();
$result = $PDO->getPreparedQuery('SELECT * FROM feed WHERE valid=1')->fetchAll(PDO::FETCH_ASSOC);
foreach($result as $i){
	$id = $i['fb_id'];
	try{
		$fb->makeNodeRequest($id);
		IfItemSoldUpdateDatabase($fb->getFeed(),$id);
	}catch(Exception $e){
		$PDO->insert('UPDATE feed SET valid=3 WHERE fb_id= :id',['id'=>$id]);
	}
}
$PDO->insert('INSERT INTO log (executedFrom, ecexutedTime) VALUES ( :from, :time)',['from'=>'DeleteFeed','time'=>$time]);

function IfItemSoldUpdateDatabase($array,$id){
	global $PDO;
	if(preg_match('/sold|eladva/i',$array['message'])){
		$PDO->insert('UPDATE feed SET valid=5 WHERE fb_id= :id',['id'=>$id]);
	}
}
?>