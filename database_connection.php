<?php
class PDOs{
	private $host='';
	private $dbname='';
	private $username = '';
	private $password = '';
	private $connection;	
	private $state;
	private $NumOfRows;
	private $connected = false;
	
	function __construct(){
		try{
			$this->connection = new PDO("mysql:host=".$this->host.";dbname=".$this->dbname,$this->username,$this->password);
			$this->connected = true;
		}catch (PDOException $e){
			$this->connected = false;
		}
	}
	
	function getCon(){
		return $this->connection;
	}
	
	function isConnected(){
		return $this->connected;
	}
	
	function getPreparedQuery($sql,$array=NULL){
		$this->NumOfRows = 0;
		$this->state = $this->connection->prepare($sql);
		$this->state->execute($array);
		$this->NumOfRows = $this->state->rowCount();
		return $this->state;
	}
	
	function insert($sql,$array=NULL){
		$stateofin=true;
		$this->NumOfRows = 0;
		try{
			$this->state = $this->connection->prepare($sql);
			$this->state->execute($array);
			$this->NumOfRows = $this->state->rowCount();
		}catch(Exception $e){
			$stateofin=false;
		}
		return $stateofin;
	}
	
	function lastInsertId($sql,$array=NULL){
		if($this->insert($sql,$array))
			return $this->connection->lastInsertId();
		return null;
	}
	
	function getNumRows(){
		return $this->NumOfRows;
	}
	
	//FACEBOOK CONNECTIONS
	
	function insertOrUpdateFeed($array = null){
		if($array!=null){
			$sql = 'INSERT INTO feed (fb_id, title, price, place, gender, image, description, type, updated_time, created_time)
								VALUES ( :id, :title, :price, :place, :gender, :image, :description, :feed_type, :updated_time, :created_time)';
			if($this->FacebookIdExistsInFeed($array['id'])){
				$sql = 'UPDATE feed SET title= :title, price= :price, place= :place, gender= :gender, image= :image, description= :description, type= :feed_type, updated_time= :updated_time, created_time= :created_time WHERE fb_id= :id';
			}
			
			$this->insert($sql,$array);
		}
	}
	
	private function encodeArrayToUTF8(&$array = null){
		$array_keys = array_keys($array);
		$j = 0;
		foreach($array as $i){
			$array[$array_keys[$j++]] = utf8_encode($i);
		}
	}
	
	function FacebookIdExistsInFeed($collume_value=null){
		$sql = 'SELECT * FROM feed WHERE fb_id= :value';
		$this->getPreparedQuery($sql,['value'=>$collume_value]);
		if($this->getNumRows()==1)
			return true;
		return false;
	}
	
	function getPostsWithoutLimit($data){
		$this->SetUpSqlQithData($sql,$data);
		$this->getPreparedQuery($sql,$data);
		return $this->getNumRows();
	}
	function getFeedWithSettings($data){
		$this->SetUpSqlQithData($sql,$data);
		$sql .= ' LIMIT 10 OFFSET '.($data['offset']*10);
		return $this->getPreparedQuery($sql,$data);
	}
	
	private function SetUpSqlQithData(&$sql,$data){
		$sql = 'SELECT * FROM feed WHERE valid=1 ';
		$this->setUpPriceSearch($sql,$data);
		$this->setUpFeedType($sql,$data['feedType']);
		$this->setUpGender($sql,$data['gender']);
		$this->setUpMaxDate($sql,$data['maxDate']);
		$this->setUpOrder($sql,$data['order']);
	}
	
	private function setUpPriceSearch(&$sql,$data){
		if($data['priceMin']<=$data['priceMax'] && $data['priceMax']!=0){
			$sql .= ' AND price>'.$data['priceMin'].' AND price< '.$data['priceMax'];
		}elseif($data['priceMin']>0 && $data['priceMax']==0){
			$sql .= ' AND price>'.$data['priceMin'];
		}
	}
	
	private function setUpFeedType(&$sql,$feedType){
		if($feedType!=FEED_TYPE_BOTH){
			$sql .= ' AND type=\''.$feedType.'\'';
		}
	}
	
	private function setUpGender(&$sql,$gender){
		$sql .= ' AND gender IN '.$gender;
	}
	
	private function setUpMaxDate(&$sql,$maxDate){
		$sql .= ' AND created_time > \''.$maxDate.'\'';
	}
	
	private function setUpOrder(&$sql,$order){
		$sql .= ' ORDER BY ';
		if($order=='created_time' || $order=='updated_time')
			$sql .= $order.' DESC';
		elseif($order=='price_down')
			$sql .= 'price DESC';
		elseif($order=='price_up')
			$sql .= 'price ASC';
	}
	function getFeed(){
		$sql = 'SELECT * FROM feed WHERE 1';
		return $this->getPreparedQuery($sql);
	}
	
}
?>