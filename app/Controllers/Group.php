<?php namespace App\Controllers;

use App\Models\News_model;
use App\Models\Members_model;

class Group extends BaseController {

	public function index() {
		$this->view();
	}

	/********************* VIEW **************************/
	public function view() {

	}
	
	
	/********************* CREATE **************************/
	public function create() {
	
		
	}
	
	
	/********************* UPDATE **************************/
	public function update($idPlaylist) {
	
	}
	
	
	//***********************************************************************//
	//***************************    MEMBERS      ***************************//
	//***********************************************************************//
	
	
	
	//****************** SEND MAIL  ********************//
	public function send_mail() {
		
		log_message("debug","GROUP::send_mail");
			
		if($this->session->admin == "1") {

			// On lance la vue pour une modal box (pop-up)
			echo view('group/send_mail');
		}
	}
	
	
	
	//****************** AJAX SEND MAIL  ********************//
	public function ajax_send_mail() {
		
		if($this->session->admin == "1") {
		
			$members_model = new Members_model();

			
			// On récupère les infos du mail
			$subject = trim($_POST['subject']);
			$text_html = trim($_POST['text_html']);
			/*$name = trim($_POST['name']);
			$from = trim($_POST['from']);
			$reply_to = trim($_POST['reply_to']);*/
		
			// On récupère les infos du membre admin qui envoie le mail
			/*$sender = $this->members_model->get_members_by_id($this->session->userdata('id'));
			log_message("DEBUG",json_encode($sender));*/
		
			$members_array = $members_model->get_members();
			$adresses_array = [];
			foreach ($members_array as $member_item) {
				// On ne garde que les membres allowing mail
				if ($member_item->allowMail == '1') {
					$adresses_array[] = $member_item->email;
				}
			}
			//pour debug
			//$adresses_array[] = "contact@le-gro.com";
			
			if (env('app.has_net')) {
				
				// On envoie les emails
				$this->email->from('contact@le-gro.com','Grenoble Reggae Orchestra');
				$this->email->bcc($adresses_array);
				//$this->email->to('s.plotto@free.fr','Stéphane Plotto');
				$this->email->subject($subject);
				$this->email->message($text_html);
				$state = $this->email->send();
				$msg = "Votre message a bien été envoyé.";
			}
			
			else {
				$state = false;
				$msg = "Impossible d'envoyer d'email sans connexion internet.";
			}
		
			$return_data = array(
				'state' => $state,
				'data' => $msg
			);
			$output = json_encode($return_data);
			echo $output;
		}
		
	}
	
	
	
	
	//***********************************************************************//
	//******************************    NEWS      ***************************//
	//***********************************************************************//
	
	
	
	//****************** CREATE NEWS  ********************//
	public function create_news() {
		
		if($this->session->superAdmin == "1") {
			// On lance la vue pour une modal box (pop-up)
			echo view('group/create_news');
		}
	}
	
	
	
	//****************** AJAX CREATE NEWS  ********************//
	public function ajax_create_news() {
		
		if($this->session->superAdmin == "1") {

			// On récupère les infos de la répétition
			$title = trim($_POST['title']);
			$text_html = trim($_POST['text_html']);
			$top = trim($_POST['top']);
		
			$news_model = new News_model();
			
			// On récupère la date de la répét
			$date_iso = date("Y-m-d H:i:s");
			//$date_iso = $tmp[2]."-".$tmp[1]."-".$tmp[0];

			$data = array(
				'title' => $title,
				'date' => $date_iso,
				'text' => $text_html,
				'top' => $top == 'true' ? '1' : '0'
			);
			
			$state = $news_model->set_news($data);
			
			
			$return_data = array(
				'state' => isset($state) ? true : false,
				'data' => ""
			);
			$output = json_encode($return_data);
			echo $output;
		}
		
	}
	
	
	
	//****************** UPDATE NEWS  ********************//
	public function update_news($news_id) {

		if($this->session->superAdmin == "1") {
	
			$news_model = new News_model();
	
			// On récupère la news
			$data['news_item'] = $news_model->get_news_by_id($news_id);

			//log_message("debug",json_encode($data['news_item']));
			
			// DATE
			$date = date_create_from_format("Y-m-d H:i:s",$data['news_item']['date']);
			$data['news_item']['date_label'] = date_format($date,"d/m/Y");

			// On lance la vue pour une modal box (pop-up)
			echo view('group/update_news', $data);
		}
	}
	
	
	
	//****************** AJAX UPDATE NEWS  ********************//
	public function ajax_update_news($news_id) {
	
		if($this->session->superAdmin == "1") {
			
			// On récupère les infos de la news
			$title = trim($_POST['title']);
			$text_html = trim($_POST['text_html']);
			$top = trim($_POST['top']);
		
			$news_model = new News_model();
			
			// On créé la nouvelle news
			$data = array(
				'title' => $title,
				'top' => $top == 'true' ? '1' : '0',
				'text' => $text_html
			);
			
			$state = $news_model->update_news($news_id, $data);
			
			$return_data = array(
				'state' => $state,
				'data' => ""
			);
			$output = json_encode($return_data);
			echo $output;
		}
		
	}
	
	
	//****************** DELETE NEWS  ********************//
	public function delete_news() {

		if($this->session->superAdmin == "1") {
	
			// On récupère les infos de la news
			$newsId = trim($_POST['newsId']);
	
			$news_model = new News_model();
	
			// On delete la news
			$state = $news_model->delete_news($newsId);
			
			$return_data = array(
				'state' => $state == false ? false : true,
				'data' => ""
			);
			$output = json_encode($return_data);
			echo $output;
		}
	}

}