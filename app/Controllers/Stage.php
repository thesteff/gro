<?php namespace App\Controllers;

class Stage extends BaseController {

	public function suppress($slug) {
		/*$this->jam_model->delete_jam($slug);
		redirect('/jam');*/
	}

	
	/*********** INSCRIPTION STAGE ***************/
	public function inscription($slug) {
	
		// On récupère les infos du membres s'il est connecté (identification des admin pour affichage menu)
		$data['connected'] = isset($_SESSION['login']);
		$data['is_admin'] = ($data['connected'] && $this->session->userdata('admin'));
	
		if ($data['connected'] || $data['is_admin']) {
	
			// Section selectionnée dans le menu
			//$data['title'] = 'Jam';
			
			// On récupère les infos du membre loggé dans la BD
			$data['members_item'] = $this->members_model->get_members($this->session->userdata('login'));
			//log_message('debug', "members_item : ".json_encode($members_item));
		
			// On récupère la jam
			$data['jam_item'] = $this->jam_model->get_jam($slug);
			if (empty($data['jam_item'])) {
				throw CodeIgniterxceptionsPageNotFoundException::forPageNotFound;();
			}
			
			// Si l'utilisateur n'est pas loggé et que la jam n'est pas publique
			if (!isset($_SESSION['login']) && $data['jam_item']['acces_jam'] == 0) {
				$data['page_title'] = 'Erreur';
				$data['title'] = 'Accés refusé !!';
				$data['message'] = 'Votre statut sur le site ne vous permet pas d\'accéder à cette page';
				$this->load->view('templates/header', $data);
				$this->load->view('pages/message', $data);
				$this->load->view('templates/footer');
				return;
			}
							
			// On récupère le stage
			$data['stage_item'] = $this->stage_model->get_stage_jamId($data['jam_item']['id']);
			
			// On récupère le lieu du stage
			$data['lieu_stage_item'] = $this->lieux_model->get_lieux_by_id($data['stage_item']['lieuxId']);
			
			// On récupère le lieu de la jam
			$data['lieu_jam_item'] = $this->lieux_model->get_lieux_by_id($data['jam_item']['lieuxId']);
			
			// On récupère la liste d'instru classée par catégorie
			//$data['cat_instru_list'] = $this->instruments_model->get_categorized_instruments();
			
			// DATE conversion
			if ($this->config->item('online')) setlocale(LC_ALL, 'fr_FR');
			else setlocale(LC_ALL, 'fra');

			$tmpDate = strtotime($data['jam_item']['date_debut']);
			$data['jam_item']['date_debut_norm'] = utf8_encode(strftime("%A %d %B %Y",$tmpDate));
			
			$tmpDate = strtotime($data['stage_item']['date_debut']);
			$data['stage_item']['date_debut_norm'] = utf8_encode(strftime("%A %d %B %Y",$tmpDate));
			
			$tmpDate = strtotime($data['stage_item']['date_debut']."+".($data['stage_item']['duree']-1)." days");
			$data['stage_item']['date_fin'] = date("Y-m-d",$tmpDate);
			$data['stage_item']['date_fin_norm'] = utf8_encode(strftime("%A %d %B %Y",$tmpDate));
		
		

			// On vérifie que le login de l'url existe
			if (isset($data['members_item'])) {
				
				// On normalise la date de naissance
				if (!empty($data['members_item']->naissance)) {
					$date = date_create_from_format("Y-m-d",$data['members_item']->naissance);
					$data['members_item']->naissance = date_format($date,"d/m/Y");
				}
				
				
				// On récupère l'instrument 1 de l'utilisateur
				$data['instru_list'] = $this->members_model->get_instruments($data['members_item']->id);
				$data['members_item']->instrument1 = $data['instru_list'][0]['instruName'];
			
			
				// On lance la vue
				$this->load->view('stage/inscription', $data);
			}
			else {
				$data_page['page_title'] = 'Erreur';
				$data_page['title'] = 'Membre inexistant !';
				$data_page['message'] = 'Le membre <b>'.$login.'</b> n\'existe pas !';
			
				$this->load->view('pages/message', $data_page);
			}
		}
		
		
		else {
			$data_page['page_title'] = 'Erreur';
			$data_page['title'] = 'Accés refusé !!';
			$data_page['message'] = 'Votre statut sur le site ne vous permet pas d\'accéder à cette page';
		
			$this->load->view('pages/message', $data_page);
		}
	}
}