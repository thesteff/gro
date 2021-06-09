<?php namespace App\Controllers;

use App\Models\Members_model;
use App\Models\Message_model;

class Message extends BaseController {
	
	
	// dest doit être un slug (url_title)
	public function view($login, $dest = null) {

		if(url_title($this->session->login) == url_title($login) || $this->session->superAdmin) {
			
			$data['page_title'] = "Messages";
			$data['title'] = "Messages";
		
			$members_model = new Members_model();
		
			// On récupère les infos du membre loggé dans la BD
			$log = url_title($login);
			$data['member_item'] = $members_model->get_member_by_slug($log);
			$data['isSuperAdmin'] = $this->session->superAdmin;
			
			
			// On vérifie que le login de l'url existe
			if (isset($data['member_item'])) {
				
				// Si on a un destinataire qui a déjà été défini
				if ($dest != null) {
					$data['dest_item'] = $members_model->get_member_by_slug($dest);
				}
				
				// On lance la vue
				echo view('templates/header', $data);
				echo view('templates/menu', $data);
				echo view('message/view', $data);
				echo view('templates/footer');
			}
			else {
				$data_page['page_title'] = 'Erreur';
				$data_page['title'] = 'Membre inexistant !';
				$data_page['message'] = 'Le membre <b>'.$login.'</b> n\'existe pas !';
			
				echo view('templates/header',$data_page);
				echo view('pages/message', $data_page);
				echo view('templates/footer');
			}
		}
		
		else {
			$data_page['page_title'] = 'Erreur';
			$data_page['title'] = 'Accés refusé !!';
			$data_page['message'] = 'Votre statut sur le site ne vous permet pas d\'accéder à cette page';
		
			echo view('templates/header', $data_page);
			echo view('pages/message', $data_page);
			echo view('templates/footer');
		}
	}
	
	
	//****************** CREATE ********************//
	public function create() {

		/*if($this->session->login || $this->session->logged) {
			return redirect('/');
		}

		$data['title'] = 'Inscription';
		$data['is_admin'] = $this->session->admin;
		
		$instruments_model = new Instruments_model();
		
		//$data['instru_list'] = $this->instruments_model->get_instruments();
		//$data['cat_instru_list'] = $this->instruments_model->get_categorized_instruments();
		
		$data['famille_instru_list'] = $instruments_model->get_familyzed_instruments();
		
		echo view('templates/header', $data);
		echo view('templates/menu', $data);
		echo view('members/create', $data);
		echo view('templates/footer');*/
	
	}
	
	
}
