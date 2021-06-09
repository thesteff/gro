<?php namespace App\Controllers;

use App\Models\News_model;
use App\Models\Members_model;
use App\Models\Morceau_model;
use App\Models\Playlist_model;
use App\Models\Instruments_model;
use App\Models\Version_model;
use App\Models\Group_model;


class Pages extends BaseController {

	public function view($page = 'home') {

		if ( ! is_file(APPPATH.'/Views/pages/'.$page.'.php') && $page != "access_denied") {
			// Whoops, we don't have a page for that!
			throw new \CodeIgniter\Exceptions\PageNotFoundException($page);
		}
		
		//log_message('debug', "SESSION : ".json_encode($this->session->userdata));
		//log_message('debug', "ADMIN : ".$this->session->userdata('admin'));
		
		// On récupère les infos du membres s'il est connecté (identification des admin pour affichage menu)
		$data['logged'] = $this->session->logged;
		$data['memberId'] = $this->session->id;
		$data['isSuperAdmin'] = ($data['logged'] && $this->session->superAdmin);

		
		// On gère les access_denied
		if ($page == "members" && !$data['isSuperAdmin']) $page = "access_denied";
		

		// ************* HOME (news + aperçu + messages) *********** //
		if ($page == "home") {
			
			$data['title'] = "Home";
			$data['page_title'] = "Bienvenue sur le site du Grenoble Reggae Orchestra";
			
			$model = new News_model();
			$group = new Group_model();
			
			// On récupère les news
			$data['list_news'] = $model->get_news();
			
			// On récupère les infos de l'aperçu
			$data['infos'] = $group->get_infos();
			
			// On lance la vue
			echo view('templates/header', $data);
			echo view('templates/menu', $data);
			echo view('pages/'.$page, $data);
			echo view('templates/footer', $data);
		}
		
		
		// ************* REPERTOIRE *********** //
		else if ($page == "repertoire") {
				
			$data['page_title'] = "Répertoire du GRO";
			$data['title'] = "About";
			$data['sub_title'] = "Répertoire";
			
			
			// On récupère les modèles utilisés
			$members_model = new Members_model();
			$morceau_model = new Morceau_model();
			$version_model = new Version_model();
			
			
			// On récupère le répertoire avec version
			$data['list_song_ex'] = $morceau_model->get_morceaux_and_versions();
			
			
			// Section donnant accès aux infos supplémentaires à destination des admin
			if ($this->session->logged) {
				// On récupère le membre s'il est connecté
				$data['member'] = $members_model->get_member($this->session->login);
				
				// On regarde s'il est admin d'une jam qui ne s'est pas encore déroulée
				$data['is_admin'] = count($members_model->get_jams_admin($data['member']->id, false)) > 0;
				
				// Pour le super admin
				if ($this->session->superAdmin) $data['is_admin'] = 1;
				
				// Si le membre est un admin, on calcul les infos supplémentaire concernant les morceaux
				if ($data['is_admin'] == 1) {
					// On récupère les infos de jeu des morceaux
					foreach ($data['list_song_ex'] as $key => $song) {
						$data['list_song_ex'][$key]->{'nbPlayed'} = $version_model->nbPlayed($song->versionId);
						$data['list_song_ex'][$key]->{'lastDate'} = $version_model->lastTimePlayed($song->versionId);
					}
				}				
			}
		
			
			// On lance la vue
			echo view('templates/header', $data);
			if ($data['logged'])
				echo view('templates/player', $data);
			echo view('templates/menu', $data);
			echo view('pages/repertoire', $data);
			echo view('templates/footer', $data);
			
		}
		
		
		// ************* INFOS *********** //
		else if ($page == "infos") {
		
			$data['page_title'] = "Qui sommes-nous ?";
			$data['title'] = "About";
			$data['sub_title'] = "Infos";
			
			// On lance la vue du player
			echo view('templates/header', $data);
			echo view('templates/menu', $data);
			echo view('pages/infos', $data);
			echo view('templates/footer', $data);
		}
		
		
		// ************* WISHLIST *********** //
		else if ($page == "wishlist") {
		
			$data['page_title'] = "Wishlist";
			$data['title'] = "About";
			$data['sub_title'] = "Wishlist";
			
			// On récupère les modèles utilisés
			$playlist_model = new Playlist_model();
			
			// On charge la wishlist globale (jamid = null)
			$data['wishlist'] = $playlist_model->get_wishlist(-1);
			
			// On lance la vue
			//if ($this->form_validation->run() === FALSE) {
			
				echo view('templates/header', $data);
				echo view('templates/menu', $data);
				echo view('pages/wishlist', $data);
				echo view('templates/footer', $data);
			/*}
			else {
				redirect('/wishlist/');	
			}*/
		}
		
		// ************* MEMBERS (super admin only) *********** //
		else if ($page == "members") {
		
			if ($data['isSuperAdmin']) {
				
				$data['page_title'] = "Liste des membres";
				$data['title'] = "Admin";
				$data['sub_title'] = "Membres";
				
				// On récupère les modèles utilisés
				$members_model = new Members_model();
				$instruments_model = new Instruments_model();
				
				// On récupère les membres inscrits au site
				$data['list_members'] = $members_model->get_members();			

				// On traite chaque membres
				foreach ($data['list_members'] as $tmember) {

					// On récupère la liste des intruments joués et on concatène leur label dans une string
					$listInstruArray = $members_model->get_instruments($tmember->id);
					$listInstru = "";
					if ($listInstruArray != false) {
						for ($i=0; $i < sizeof($listInstruArray); $i++) {
							if ($i > 1) $listInstru .= ", ";
							if (strlen($listInstruArray[$i]['instruName']) == 0) $listInstru = "Aucun";
							else if ($i == 0)  $listInstru .= "<b>".$listInstruArray[$i]['instruName']."</b><br>";
							else $listInstru .= $listInstruArray[$i]['instruName'];
						}
						$tmember->instruList = $listInstru;
						
						// On récupère la catégorie principale (famille du premier instrument joué)
						if (sizeof($listInstruArray) > 0) {
							$instru_family = $instruments_model->get_instru_family($listInstruArray[0]['instruId']);
							if ($instru_family !== false) $tmember->mainFamily = $instru_family->label;
							else $tmember->mainFamily = "";
						}
					}
					else {
						$tmember->instruList = "";
						$tmember->mainFamily = "";
					}
					
					// On calcule l'age
					$members_model->calcul_age($tmember);
					
					// On reformate les dates d'inscriptions au site et de dernier accés
					$date_inscr = date_create_from_format("Y-m-d G:i:s",$tmember->date_inscr);
					$tmember->date_inscr = date_format($date_inscr,"d/m/Y");
					$date_access = date_create_from_format("Y-m-d G:i:s",$tmember->date_access);
					$tmember->date_access = date_format($date_access,"d/m/Y");
				}
				
				echo view('templates/header', $data);
				echo view('templates/menu', $data);
				echo view('pages/members', $data);
				echo view('templates/footer', $data);
			}
			
		}
		
		
		
		// ************* Accès Page Refusé *********** //
		//else if ($page == "access_denied" || !$this->session->userdata('admin')) {
		else if ($page == "access_denied") {
		
			$data['page_title'] = 'Accés refusé !!';
			$data['title'] = 'Accés refusé !!';
			$data['message'] = 'Votre statut sur le site ne vous permet pas d\'accéder à cette page';
		
			echo view('templates/header',$data);
			echo view('pages/message',$data);
			echo view('templates/footer');
		}
		
		
		
		// ************* PAGE Standard (contact) *********** //
		else {
		
			$data['title'] = ucfirst($page); // Capitalize the first letter
				
			echo view('templates/header', $data);
			echo view('templates/menu', $data);
			echo view('pages/'.$page, $data);
			echo view('templates/footer', $data);
		}
			
	}
}
