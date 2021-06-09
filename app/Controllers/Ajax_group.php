<?php namespace App\Controllers;


class Ajax_group extends BaseController {


	public function index() {
	}

	
	// Ajouter un instrument à un membre
	public function delete_news() {
		
		$newsId = trim($_POST['newsId']);
		
		// On supprime l'instrument du membre
		$state = $this->news_model->delete_news($newsId);
		
		$return_data = array(
			'state' => $state,
			'data' => ""
		);
		$output = json_encode($return_data);
		echo $output;
	}
	

}
?>