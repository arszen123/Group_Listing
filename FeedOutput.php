<?php
session_start();
class FeedOutput{
	private $arrayKeys = ['priceMin','priceMax','feedType','gender','maxDate','order','offset'];
	private $priceMin = null;
	private $priceMax = null;
	private $feedType = null;
	private $gender = null;
	private $maxDate = null;
	private $order = null;
	private $offset = null;
	
	function __construct($data = null){
		if($data!=null)
			$this->processData($data);
	}
	
	private function processData($data){
		$this->processFeedType($data['feedType']);
		$this->processPrice($data['priceMin'],$data['priceMax']);
		$this->processGender($data['gender']);
		$this->processMaxDate($data['maxDate']);
		$this->processSort($data['order']);
		$this->offset= $data['offset'];
		//Set up last search session
		$_SESSION['lastSearch'] = $this->getData();
		
	}
	
	private function processPrice($priceMin,$priceMax){
		$priceMin += 0;
		$priceMax += 0;
		if($priceMin == 0 || $priceMin<0)
			$this->priceMin = 0;
		else
			$this->priceMin = $priceMin;
		if($priceMax <= 0 || $priceMax < $priceMin)
			$this->priceMax = 0;
		else
			$this->priceMax = $priceMax;
	}
	
	private function processFeedType($feedType){
		if($feedType[0] == 'search' && $feedType[1] == 'give')
			$this->feedType = FEED_TYPE_BOTH;
		elseif($feedType[0] == 'search' || $feedType[1] == 'search')
			$this->feedType = FEED_TYPE_SEARCH;
		elseif($feedType[0] == 'give' || $feedType[1] == 'give')
			$this->feedType = FEED_TYPE_GIVE;
		else{
			$this->feedType = FEED_TYPE_BOTH;
		}
	}
	
	private function processGender($gender){
		$res = '(';
		if($gender != null){
			if(in_array('female',$gender)){
				$res .= '\'FEMALE\',';
			}
			if(in_array('male',$gender)){
				$res .= '\'MALE\',';
			}
			if(in_array('unknown',$gender)){
				$res .= '\'UNKNOWN\',';
			}
			$res[strlen($res)-1] = ')';
		}
		if($gender==null || $res[0]==')'){
			$res= '(\'FEMALE\',\'MALE\',\'UNKNOWN\')';
		}
		$this->gender = $res;
	}
	
	private function processMaxDate($maxDate){
		if(DateTime::createFromFormat('Y-m-d', $maxDate) !== FALSE){
			$this->maxDate = $maxDate;
		}else{
			$date = new DateTime();
			$this->maxDate = date('Y-m-d',strtotime('-1 month', $date->getTimestamp()));
		}
	}
	
	private function processSort($order){
		$this->order = 'created_time';
		if($order=='updated_time')
			$this->order = 'updated_time';
		if($order=='price_down')
			$this->order = 'price_down';
		if($order=='price_up')
			$this->order = 'price_up';
	}
	
	public function getData(){
		$res;
		foreach($this->arrayKeys as $i){
			$res[$i] = $this->$i;
		}
		return $res;
	}
	
	public function checked($id){
		
		//Search Type
		if($id=='search'){
			if($_SESSION['lastSearch']['feedType'] == FEED_TYPE_BOTH || $_SESSION['lastSearch']['feedType'] == FEED_TYPE_SEARCH)
				return 'checked';
		}
		if($id=='give'){
			if($_SESSION['lastSearch']['feedType'] == FEED_TYPE_BOTH || $_SESSION['lastSearch']['feedType'] == FEED_TYPE_GIVE)
				return 'checked';
		}
		
		//Gender Type
		if($id=='female'){
			if(strpos($_SESSION['lastSearch']['gender'],'FEMALE'))
				return 'checked';
		}
		if($id=='male'){
			if(strpos($_SESSION['lastSearch']['gender'],'\'MALE\''))
				return 'checked';
		}
		if($id=='unknown'){
			if(strpos($_SESSION['lastSearch']['gender'],'UNKNOWN'))
				return 'checked';
		}
		
		//order Type
		if($id==$_SESSION['lastSearch']['order'])
			return 'selected';
		
		return '';
	}
}

?>