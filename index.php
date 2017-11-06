<?php session_start(); 
$root = $_SERVER["DOCUMENT_ROOT"].'/grouplisting/';
require $root.'database_connection.php';
require $root.'FeedOutput.php';
$lastSearch = $_SESSION['lastSearch'];
$FO = new FeedOutput($_POST);
?>
<html>
<head>
<link type="text/css" href="assets/css/main.css" rel="stylesheet">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<script src="assets/js/ajaxFunctions.js"></script>
<script>
var offset=0;
var ajaxDone = 0;
$(document).ready(function(){
	loadFirstFeed();
	$("#search_form").on('submit',(function(e) {
		e.preventDefault();
		offset = 0;
		loadFirstFeed();
	}));
	$(window).scroll(function(){
		if(ajaxDone==1 && ((window.innerHeight + window.scrollY) >= document.body.offsetHeight*80/100)){
			data = $('#search_form').serialize()+'&offset='+offset;
			appendFeed("ajax/getFeed.php",data,'.feed');
			ajaxDone = 0;
		}
	});
	function loadFirstFeed(){
		data = $('#search_form').serialize()+'&offset='+offset;
		loadFeed("ajax/getFeed.php",data,'.feed');
	}
});
</script>
</head>
<body>
<div class="fixedLeft">
<div id="numberOfPosts">
</div>
<form id="search_form" action="" method="POST">
	<input type="checkbox" name="feedType[]" value="search" id="search" <?= $FO->checked('search')?>><label for="search">KERES</label></br>
	<input type="checkbox" name="feedType[]" value="give" id="give" <?= $FO->checked('give')?>><label for="give">KINÁL</label></br>
	<input type="checkbox" name="gender[]" value="female" id="female" <?= $FO->checked('female')?>><label for="female">Lanynak</label></br>
	<input type="checkbox" name="gender[]" value="male" id="male" <?= $FO->checked('male')?>><label for="male">Fiunak</label></br>
	<input type="checkbox" name="gender[]" value="unknown" id="unknown" <?= $FO->checked('unknown')?>><label for="unknown">Nem meghatarozott</label></br>
	A legregebbi post (alapertelmezetten: 1 honap visszamenoleg):</br>
	<input type="date" name="maxDate" id="maxDate" value="<?= $lastSearch['maxDate']?>"></br>
	<input type="number" name="priceMin" id="price" placeholder="Min. Ar" value="<?= $lastSearch['priceMin']?>">-<input type="number" name="priceMax" id="price" placeholder="Max. Ar" value="<?= $lastSearch['priceMax']?>"></br>
	<select name="order">
		<option value="created_time" <?= $FO->checked('created_time')?>>Keszitesi ido</option>
		<option value="updated_time" <?= $FO->checked('updated_time')?>>Frissitesi ido</option>
		<option value="price_down" <?= $FO->checked('price_down')?>>Ar szerint csokkeno</option>
		<option value="price_up" <?= $FO->checked('price_up')?>>Ar szerint novekvo</option>
	</select>
	<input type="submit" name="submit" value="KERES">
</form>
</div>
<div class="feed">
<?php
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
<?php }?>
</div>
</body>
</html>