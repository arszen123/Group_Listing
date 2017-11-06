<?php
class FeedProcessing{
	const ASSOC = "ASSOC";
	const NUM = "NUM";
	const FEED_TYPE_SEARCH = "FEED_TYPE_SEARCH";
	const FEED_TYPE_GIVE = "FEED_TYPE_GIVE";
	private $valid = false;
	private $id = null;
	private $plain_text = null;
	private $image = null;
	private $updated_time = null;
	private $created_time = null;
	private $price = null;
	private $title = null;
	private $place = null;
	private $gender = null;
	private $description = null;
	private $feed_type = null;
	private $phone_number = null;
	private $start_point_of_money = null;
	private $assoc_array = array("id","title","price","place","gender","image","description","updated_time","created_time", "feed_type");
	
	private $pattern_FEMALE = '/(\blany\b|lány)/i';
	private $pattern_MALE = '/(\bfiu\b|fiú)/i';
	private $pattern_Price = '([0-9]+[\,]+[0-9]+[\,]+[0-9]+[^A-z0-9]+(ft)+[^A-z0-9]+\-|([0-9]+[\,]+[0-9]+[^A-z0-9]+(ft)+[^A-z0-9]+\-))';
	private $pattern_Place = '/(<=>)\s*\b([a-zéáúóíöüűő]+)*\b|^\s*([a-zéáúóíöüűő]+)*\b/i';
	private $pattern_TypeSearch = '/(\bkeres(ek|ünk|nek|unk|)\b)/i';
	private $pattern_exception_TypeSearch = '/((t(á|a)rsa(kat|t) keres)|(b(é|e)rl(ő|o)(ket|t) keres)|(lak(a|á)sba keres)|(\bszoba kiad(ó|o\b))|(\bkiad(ó|o) szoba\b)|(t(á|a)rsa(kat|t))|(alb(é|e)rle(t|tbe) kiad(ó|o))|((nal|nel|nál|nél) kiad(ó|o))|szob(á|a)ja kiad(ó|o)|szobat(á|a)rs|lak(ó|o)t(á|a)rs)|lak(ó|o)j(a|át)/i';
	private $sellWords = '/(szob(a|(á|a)k)|lak(á|a)s|iroda|kiad(ó|o)|h(á|a)z|al(b(é|e)rlet|i))/i';
	
	
	function __construct($array = null){
		$this->setProcessingData($array);
	}
	
	public function setProcessingData($array = null){
		if($array != null){
			$this->id = $array['id'];
			$this->plain_text = $array['message'];
			$this->image = $array['full_picture'];
			$this->updated_time = $array['updated_time']->format('Y-m-d H:i:s');
			$this->created_time = $array['created_time']->format('Y-m-d H:i:s');
			$this->nullTheValues();
		}
	}
	
	private function nullTheValues(){
		$this->valid = null;
		$this->price = null;
		$this->title = null;
		$this->place = null;
		$this->description = null;
		$this->feed_type = null;
		$this->phone_number = null;
		$this->start_point_of_money = null;
	}

	public function getDataValidation(){
		return $this->valid;
	}
	
	public function getFeedType(){
		return $this->feed_type;
	}
	
	public function getData($return_type = ASSOC){
		$return_array;
		if($return_type == ASSOC){
			foreach($this->assoc_array as $i){
				$return_array[$i] = $this->$i;
			}
		}
		if($return_type == NUM){
			$j = 0;
			foreach($this->assoc_array as $i){
				$return_array[$j] = $this->$i;
			}
		}
		return $return_array;
	}
	
	public function process(){
		$this->setTempPrice();
		if($this->price!=null){
			$this->setTitle();
			$this->setPlace();
			$this->setDescription();
			if($this->containSellWords()){
				$this->setPrice();
				$this->setFeedType();
				$this->setGender();
				$this->valid = true;
			}
		}
	}
	
	private function setTempPrice(){
		$text = strtolower($this->plain_text);
		preg_match($this->pattern_Price, $text, $matches, PREG_OFFSET_CAPTURE);
		if($matches[0]!=null){
			$this->price = $matches[0][0];
			$this->start_point_of_money = $matches[0][1];
		}
	}
	
	private function setTitle(){
		$end_point = $this->start_point_of_money;
		$this->title = substr($this->plain_text,0,$end_point);
	}
	
	private function setPlace(){
		$start_point = $this->start_point_of_money+strlen($this->price)+1;
		$text = substr($this->plain_text,$start_point);
		preg_match($this->pattern_Place, $text, $matches, PREG_OFFSET_CAPTURE);
		$place = $matches[0][0];
		if(ctype_upper($place[0]))
			$this->place = $place;
	}
	
	private function setDescription(){
		$start_point = 0;
		if($this->place!=null)
			$start_point = 2;
		$start_point += $this->start_point_of_money+strlen($this->price)+1+strlen($this->place);
		$this->description = substr($this->plain_text,$start_point);
	}
	
	private function containSellWords(){
		$text = $this->description.$this->title;
		if(preg_match($this->sellWords, $text))
			return true;
		return false;
	}
	
	private function setPrice(){
		$price = $this->price;
		if(($tmp = explode('.',$price))){
			$tmp = explode(',',$price);
		}
		$price = implode('',$tmp);
		$this->price = $price+0;
	}
	
	private function setFeedType(){
		if($this->containSearchWordsFeedType()){
			$this->feed_type = FEED_TYPE_SEARCH;
			return;
		}
		$this->feed_type = FEED_TYPE_GIVE;
	}
	
	private function containSearchWordsFeedType(){
		if($this->containtPatternForSearchWords($this->pattern_TypeSearch)){
			if(!$this->containtPatternForSearchWords($this->pattern_exception_TypeSearch)){
				return true;
			}
		}
		return false;
	}
	
	private function containtPatternForSearchWords($pattern){
		$title = strtolower($this->title);
		$description = strtolower($this->description);
		return (preg_match($pattern, $description)||preg_match($pattern, $title));
	}
	
	private function setGender(){
		if($this->containtPatternForSearchWords($this->pattern_FEMALE)){
			$this->gender = 'FEMALE';
		}elseif($this->containtPatternForSearchWords($this->pattern_MALE)){
			$this->gender = 'MALE';
		}else{
			$this->gender = 'UNKNOWN';
		}
		
	}
}
?>