<?php
set_time_limit(0);
$time = date("Y-m-d H:i:s");
$root = $_SERVER["DOCUMENT_ROOT"].'/grouplisting/';
require $root.'database_connection.php';
$PDO = new PDOs();
$counter = 0;
$DONTUPDATE;
$updated=0;
$k=1;
$result = $PDO->getPreparedQuery('SELECT * FROM feed WHERE valid=1 ORDER BY created_time DESC')->fetchAll(PDO::FETCH_ASSOC);
foreach($result as $i){
	$text1 = $i['description'].$i['title'].$i['price'];
	$InsideResult = $PDO->getPreparedQuery('SELECT * FROM feed WHERE valid=1 AND id!= :id ORDER BY created_time DESC',['id'=>$i['id']])->fetchAll(PDO::FETCH_ASSOC);
	foreach($InsideResult as $j){
		$text2 = $j['description'].$j['title'].$j['price'];
		similar_text($text1, $text2, $percentage);
		if($percentage>=95 && !in_array($j['id'],$DONTUPDATE)){
			$PDO->insert('UPDATE feed SET valid=4 WHERE id= :id',['id'=>$j['id']]);
			$updated=1;
		}
	}
	if($updated==1){
		$DONTUPDATE[$counter++]=$i['id'];
	}
}
$PDO->insert('INSERT INTO log (executedFrom, ecexutedTime) VALUES ( :from, :time)',['from'=>'DeleteSimilarPosts','time'=>$time]);
?>