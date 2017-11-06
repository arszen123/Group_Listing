<?php
require_once 'src/Facebook/autoload.php';
		use Facebook\FacebookRequest;
		use Facebook\Facebook;
		use Facebook\FacebookApp;
class FB{
	
	private $fb = null;
	private $graphNode = null;
	private $request = null;
	private $app_id = '';
	private $app_secret = '';
	private $default_acces_token = '';
	function __construct(){
		$this->fb = new Facebook([
			'app_id' => $app_id,
			'app_secret' => $app_secret,
			'defalut_graph_version' => 'v2.10']);
		$this->fb->setDefaultAccessToken($default_acces_token);
	}
	
	function makeEdgeRequest($link = '104776596538743/feed?fields=full_picture,message,id,updated_time,created_time'){
		$this->makeRequest($response,$link);
		$this->graphNode = $response->getGraphEdge();
	}
	
	function makeNodeRequest($link = '104776596538743'){
		$this->makeRequest($response,$link);
		$this->graphNode = $response->getGraphNode();
	}
	
	private function makeRequest(&$response,$link){
		$request = $this->fb->request('GET',$link);
		
		// Send the request to Graph
		try {
		  $response = $this->fb->getClient()->sendRequest($request);
		} catch(Facebook\Exceptions\FacebookResponseException $e) {
		  // When Graph returns an error
		  echo 'Graph returned an error: ' . $e->getMessage();
		  exit;
		} catch(Facebook\Exceptions\FacebookSDKException $e) {
		  // When validation fails or other local issues
		  echo 'Facebook SDK returned an error: ' . $e->getMessage();
		  exit;
		}
	}
	
	public function getNextFeed(){
		$this->nextFeed();
		return $this->graphNode;
	}
	
	public function nextFeed(){
		$this->graphNode = $this->fb->next($this->graphNode);
	}
	
	public function getFeed(){
		return $this->graphNode;
	}
	
	
}
?>