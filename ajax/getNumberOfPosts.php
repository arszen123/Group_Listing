<?php
$root = $_SERVER["DOCUMENT_ROOT"].'/grouplisting/'; 
require $root.'database_connection.php';
require $root.'FeedOutput.php';
$FO = new FeedOutput($_REQUEST);

//Working on it over 11 hour 
$PDO = new PDOs();
echo $PDO->getPostsWithoutLimit($FO->getData());
?>