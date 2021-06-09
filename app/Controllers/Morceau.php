<?php namespace App\Controllers;

use App\Models\Members_model;
use App\Models\Morceau_model;
use App\Models\Artists_model;
use App\Models\Instruments_model;
use App\Models\Version_model;
use App\Models\Media_model;
use App\Models\Formation_model;


class Morceau extends BaseController {

	public function index() {
		$this->view();
	}
	
	/********************* VIEW **************************/
	public function view() {

		$data['page_title'] = "Gestion de la médiathèque";
		$data['title'] = "Médiathèque";
		
		$members_model = new Members_model();
		$instruments_model = new Instruments_model();
		$artists_model = new Artists_model();
		$morceau_model = new Morceau_model();
		$version_model = new Version_model();
		$media_model = new Media_model();
		
		// La fonction n'est accessible qu'aux admin
		if ($this->session->superAdmin) {
		
			$data['page_title'] = "Gestion de la médiathèque";
			$data['title'] = "Admin";
			$data['sub_title'] = "Médiathèque";
			
			$data['is_admin'] = 1;
			
			// On récupère le répertoire avec et sans version
			$data['list_morceaux'] = $morceau_model->get_morceaux_extended();
			log_message("debug","list_morceaux : ".json_encode($data['list_morceaux']));
			//$data['list_song_ex'] = $morceau_model->get_morceaux_and_versions();
			
			// On récupère les artistes
			$data['list_artist'] = $artists_model->get_artists();
			
			// On récupère les groupes
			$data['list_collection'] = $version_model->get_collections();
			
			// On récupère les genres
			$data['list_style'] = $version_model->get_styles();
			
			// On récupère les tonas
			$data['list_tona'] = $version_model->get_tonas();
			
			// On récupère les modes
			$data['list_mode'] = $version_model->get_modes();
			
			// On récupère les langues
			$data['list_lang'] = $version_model->get_langues();
			
			// On récupère les transpo
			$data['list_transpo'] = $media_model->get_transpos();
			
			// On récupère les catégories d'instru
			$data['list_cat'] = $instruments_model->get_instru_categories("id",true);
			
			// On récupère les catégories d'instru
			$data['list_instru'] = $instruments_model->get_instruments();
			
			// On lance la vue
			echo view('templates/header', $data);
			//echo view('templates/player', $data);
			echo view('templates/menu', $data);
			echo view('morceau/view', $data);
			echo view('templates/footer', $data);
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
	
	
	/********************* CREATE MODAL **************************/
	public function create() {
		
		$artists_model = new Artists_model();
		
		// La fonction n'est accessible qu'aux admin
		if ($this->session->superAdmin) {
						
			// On récupère les artistes
			$data['list_artist'] = $artists_model->get_artists(false);

			// On lance la vue
			echo view('morceau/create', $data);
		}
		else {
			$data_page['page_title'] = 'Erreur';
			$data_page['title'] = 'Accés refusé !!';
			$data_page['message'] = 'Votre statut sur le site ne vous permet pas d\'accéder à cette page';
		
			echo view('pages/message', $data_page);
		}
			
	}
	
	
	/********************* UPDATE MODAL **************************/
	public function update($morceauId) {
		
		$artists_model = new Artists_model();
		$morceau_model = new Morceau_model();
		
		// La fonction n'est accessible qu'aux admin
		if ($this->session->superAdmin) {
			
			// On récupère le morceau
			$data['morceau_item'] = $morceau_model->get_morceau($morceauId);
			
			// On récupère les artistes
			$data['list_artist'] = $artists_model->get_artists(false);

			// On lance la vue
			echo view('morceau/update', $data);
		}
		else {
			$data_page['page_title'] = 'Erreur';
			$data_page['title'] = 'Accés refusé !!';
			$data_page['message'] = 'Votre statut sur le site ne vous permet pas d\'accéder à cette page';
		
			echo view('pages/message', $data_page);
		}
		
	}
	
	
	/********************* CREATE VERSION MODAL **************************/
	public function create_version() {
		
		// La fonction n'est accessible qu'aux admin
		if ($this->session->superAdmin) {
			
			// On récupère les collections
			$version_model = new Version_model();
			$data['collections'] = $version_model->get_collections();
			
			// On lance la vue
			echo view('morceau/create_version', $data);
		}
		else {
			$data_page['page_title'] = 'Erreur';
			$data_page['title'] = 'Accés refusé !!';
			$data_page['message'] = 'Votre statut sur le site ne vous permet pas d\'accéder à cette page';
		
			echo view('pages/message', $data_page);
		}
			
	}
}
