<?php
$root = $_SERVER["DOCUMENT_ROOT"].'/grouplisting/';
require_once $root.'Feed.php';
require_once $root.'Facebook.php';
require_once $root.'database_connection.php';
class syncFeed{
	private $id = null;
	private $fb = null;
	private $fp = null;
	private $name = null;
	private $PDO = null;
	
	public function __construct($id = null){
		$this->id=$id;
		$this->fb = new FB();
		$this->fp = new FeedProcessing();
		$this->PDO = new PDOs();
		$this->setName();
	}
	
	public function setId($id=null){
		$this->id = $id;
		$this->setName();
	}
	
	private function setName(){
		if($this->id!=null){
			$this->fb->makeNodeRequest($this->id);
			$name = $this->fb->getFeed();
			$this->name = $name['name'];
		}
	}
	
	public function syncPageFeed($page_to_sync = 1){
		$this->fb->makeEdgeRequest($this->id.'/feed?fields=full_picture,message,id,updated_time,created_time');
		$feed = $this->fb->getFeed();
		for($i=0;$i<$page_to_sync;$i++){
			foreach($feed as $i){
				$this->fp->setProcessingData($i);
				$this->fp->process();
				if($this->fp->getDataValidation()){
					$data_in_array = $this->fp->getData();
					$this->PDO->insertOrUpdateFeed($data_in_array);
				}
			}
			$feed = $this->fb->getNextFeed();
		}
	}
	
	public function getName(){
		return $this->name;
	}
	
	public function getPDO(){
		return $this->PDO;
	}
}

?>