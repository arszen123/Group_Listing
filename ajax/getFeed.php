<?php session_start();
$root = $_SERVER["DOCUMENT_ROOT"].'/grouplisting/'; 
require $root.'database_connection.php';
require $root.'FeedOutput.php';
$FO = new FeedOutput($_REQUEST);

//Working on it over 11 hour 
$PDO = new PDOs();
$result = $PDO->getFeedWithSettings($FO->getData());
foreach($result as $i){
	$link = explode('_',$i['fb_id']);
	$link = implode('/permalink/',$link);
	$feedType='UNKNOWN';
	$description = substr($i['description'],0,1700);
	if(strlen($i['description'])>=1700)
		$description.= ' ...';
	if($i['type']==FEED_TYPE_SEARCH)
		$feedType='KERES';
	if($i['type']==FEED_TYPE_GIVE)
		$feedType='KINÁL';
?>
	<div class="post">
		<div class="leftContainerDiv">
			<a href="https://www.facebook.com/groups/<?= $link?>" target="_blank"><img src="<?= $i['image']?>" /></a>
		</div>
		<div class="rightContainerDiv">
			<div class="title"><a href="https://www.facebook.com/groups/<?= $link?>" target="_blank"><?= $i['title']?></a></div>
			<div class="detailes">
				<div class="feedType"><?= $feedType?> | <?= $i['created_time']?></div>
				<div class="price"><?= $i['price']?> HUF</div>
			</div>
			<div class="description"><?= $description?></div>
		</div>
	</div>
<?php } ?>