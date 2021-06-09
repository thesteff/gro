<?php namespace App\Controllers;

use App\Models\Members_model;
use App\Models\Message_model;
use App\Models\Discussion_model;
use CodeIgniter\I18n\Time;

class Ajax_discussion extends BaseController {

	
	/***********************   DISCUSSIONS   *************************/
	public function get_discussions() {
		
		$memberId = trim($_POST['memberId']);
		
		$message_model = new Message_model();
		$discussion_model = new Discussion_model();
		
		$discList = $discussion_model->get_discussions($memberId, true, true, true);
		
		$return_data = array(
			'state' => true,
			'data' => $discList
		);
		$output = json_encode($return_data);
		echo $output;
		
	}
	
	
	/***********************   MESSAGES   *************************/
	public function send_message() {
		
		log_message("debug","******** Ajax_discussion :: send_message **********");
		
		$memberId = trim($_POST['memberId']);
		$message = $_POST['message'];
		$targetTag = trim($_POST['targetTag']);
		$targetIdArray = json_decode(trim($_POST['targetId']));
		
		//log_message("debug","targetIdArray : ".json_encode($targetIdArray));
		
		$message_model = new Message_model();
		$discussion_model = new Discussion_model();
		
		// NOUVELLE DISCUSSION 
		if ($targetTag == 2) {
			// On vérifie que la discussion n'existe pas déjà
			//$disc = $discussion_model->get_discussion($targetIdArray);
			
			// On créé la nouvelle discussion
			$targetId = $discussion_model->new_discussion($memberId, $targetIdArray);
			
			// On reset le targetTag
			$targetTag = 6;
			
			//log_message("debug","Nouvelle discussion ".json_encode($targetId));
		}
		
		// Discussion existante ou mode différent
		else  {
			$targetId = $targetIdArray;
		}
		
		//log_message("debug","Nouveau message targetTag : ".$targetTag." targetId : ".json_encode($targetId));

		$data = [
				'membresId' => $memberId,
				'text' => $message,
				'targetTag' => $targetTag,
				'targetId' => $targetId
			];

		// On insère le message dans la BD
		$insertId = $message_model->insert($data);

		// On récupère les infos à afficher (created_at) et on les normalize
		$message = $message_model->find($insertId);
		$data["id"] = $insertId;
		$data["created_at"] = date_create()->format('Y-m-d H:i:s');
		$data["updated_at"] = date_create()->format('Y-m-d H:i:s');
		$data["deleted_at"] = "0000-00-00 00:00:00";
		$message = $data;
		
		$message = $message_model->normalize_dates($message);

		// On détermine si c'est un nouveau fil de la discussion utile pour l'ui des discussion
		if ($targetTag == 6) $message["newThread"] = $message_model->is_new_thread($insertId);

		//log_message("debug","Message normalized : ".json_encode($message));

		$return_data = array(
			'state' => true,
			'data' => $message
		);
		$output = json_encode($return_data);
		echo $output;
		
	}
	
	public function get_messages() {
		
		$targetTag = trim($_POST['targetTag']);
		$targetId = trim($_POST['targetId']);
		$order = trim($_POST['order']);
		
		// On actualise le read_at de la discussion si besoin
		if ($targetTag == 6) {
			
			$memberId = trim($_POST['memberId']);
			$discussion_model = new Discussion_model();
			
			$data = [
				'discussionId' => $targetId,
				'membresId' => $memberId,
				'read_at' => date('Y-m-d G:i:s')
			];
			
			$discussion_model->update_read_at($data);
		}
		
		$message_model = new Message_model();
		$messages = $message_model->get_messages($targetTag, $targetId, $order);
		
		$return_data = array(
			'state' => true,
			'data' => $messages
		);
		$output = json_encode($return_data);
		echo $output;
		
	}
	
	
	public function update_message() {
		
		$messageId = trim($_POST['messageId']);
		$message = $_POST['message'];
		
		$message_model = new Message_model();

		$data = [
			'text' => $message
		];
		
		log_message("debug", "update_message : ".$messageId."  '".$message."'");


		$state = $message_model->update($messageId, $data);

		$return_data = array(
			'state' => $state,
			'data' => $state ? "update_message OK" : "error : update_message"
		);
		$output = json_encode($return_data);
		echo $output;
		
	}
	
	
	public function delete_message() {
		
		$messageId = trim($_POST['messageId']);
		
		$message_model = new Message_model();
		$message_model->delete($messageId);

		$return_data = array(
			'state' => true,
			'data' => ''
		);
		$output = json_encode($return_data);
		echo $output;
		
	}

	
}
?>