<?php namespace App\Controllers;

use App\Models\Members_model;
use App\Models\Morceau_model;
use App\Models\Jam_model;
use App\Models\Stage_model;
use App\Models\Instruments_model;
use App\Models\Lieux_model;
use App\Models\Playlist_model;
use App\Models\Formation_model;
use App\Models\Repetition_model;
use App\Models\Invitation_model;


class Jam extends BaseController {
	
	// Fonction de comparaison utilisée pour trier les membres par mainInstruName ascendant
	public function cmp($a, $b) {
		return strcmp($a->mainInstruName, $b->mainInstruName);
	}
	
	/***************** Index (list) **************************/
	public function index() {
		
		$members_model = new Members_model();
		$jam_model = new Jam_model();
		$stage_model = new Stage_model();
		$lieux_model = new Lieux_model();
		$playlist_model = new Playlist_model();
		
		
		$data['title'] = "Jam";
		$data['page_title'] = "Liste des jams";
		$data['page_description'] = "Découvrez la liste des évènements, inscrivez-vous et venez partagez une jam avec le Grenoble Reggae Orchestra.";    // vignette réseaux sociaux
		
		
		// On récupère les infos du membres s'il est connecté (identification des admin pour affichage menu)
		$data['login'] = $this->session->login;
		$data['logged'] = $this->session->logged;
		$data['isSuperAdmin'] = (isset($data['logged']) && $this->session->superAdmin);
		
		// On récupère la liste de jam
		$data['list_jam'] = $jam_model->get_jam();
		
		// On rajoute l'info du stage
		for ($i=0; $i < sizeof($data['list_jam']); $i++) {
			$temp = $stage_model->got_stage($data['list_jam'][$i]['id']);
			$data['list_jam'][$i]['stage'] = $temp;
		}
		
		if ($data['logged']) $member = $members_model->get_member($data['login']);
		else $member = null;
		
		// On récupère les infos liées aux jams
		foreach($data['list_jam'] as $key => $tjam) {
			
			// On détermine si l'utilisateur est admin de la jam
			if (isset($member)) $data['list_jam'][$key]['is_admin'] = $jam_model->is_admin($tjam['id'], $member->id);
			else $data['list_jam'][$key]['is_admin'] = false;
			
			// Le lieu
			$data['list_jam'][$key]['lieu'] = $lieux_model->get_lieux_by_id($tjam['lieuxId']);

			// On récupère le nombre de participants à la jam
			$memberList = $jam_model->get_list_members($tjam['id']);
			if ($memberList != null) $data['list_jam'][$key]['nbMembers'] = count($memberList);
			else $data['list_jam'][$key]['nbMembers'] = 0;
			
			// On récupère la playlist de la jam (pour le nombre de titres joués) et on compte le nombre de titres de la playlist
			$data['list_jam'][$key]['playlist'] = $playlist_model->get_playlist_versions($tjam['playlistId']);
			
			if ($data['list_jam'][$key]['playlist'] == null) {
				$data['list_jam'][$key]['playlist'] = "null";
				$data['list_jam'][$key]['nbSongs'] = -1;
			}
			else $data['list_jam'][$key]['nbSongs'] = count($data['list_jam'][$key]['playlist']['list']);
			
		}
		
		// On lance la vue
		echo view('templates/header', $data);
		echo view('templates/menu', $data);
		echo view('jam/list', $data);
		echo view('templates/footer', $data);	
	}

	
	/********************* VIEW **************************/
	public function view($slug) {
		
		$members_model = new Members_model();
		$jam_model = new Jam_model();
		$invitation_model = new Invitation_model();
		$stage_model = new Stage_model();
		$lieux_model = new Lieux_model();
		$playlist_model = new Playlist_model();
		$formation_model = new Formation_model();
		
		// Menu selected
		$data['title'] = 'Jam';
		
		// On récupère l'info du superAdmin
		$data['isSuperAdmin'] = $this->session->superAdmin;
		
		// On récupère la jam
		$data['jam_item'] = $jam_model->get_jam($slug);
		if (empty($data['jam_item'])) {
			throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
		}
		
		// Si l'utilisateur n'est pas loggé et que la jam n'est pas publique
		if (!isset($this->session->login) && $data['jam_item']['acces_jam'] == 0) {
			$data['page_title'] = 'Erreur';
			$data['title'] = 'Accés refusé !!';
			$data['message'] = 'Votre statut sur le site ne vous permet pas d\'accéder à cette page';
			echo view('templates/header', $data);
			echo view('pages/message', $data);
			echo view('templates/footer');
			return;
		}
		
		// On détermine si la jam est archivée en fonction de la date courante
		$data['jam_item']['is_archived'] = $jam_model->is_archived($data['jam_item']['id']);  //$data['jam_item']['date'] < date("Y-m-d");
		log_message("debug","****** Jam :: view :: jam_item : ".json_encode($data['jam_item']));
		
		// On récupère le lieu de la jam
		$data['lieu_item'] = $lieux_model->get_lieux_by_id($data['jam_item']['lieuxId']);
		//log_message("debug","lieu_item : ".json_encode($data['lieu_item']));
		
		// On récupère la playlist de la jam
		$data['playlist_item'] = $playlist_model->get_playlist_versions($data['jam_item']['playlistId']);
		if (! $data['playlist_item']) $data['playlist_item'] = "null";
		//else if (! $data['playlist_item']['list']) $data['playlist_item']['list'] = 0;
		
		// ***** STAGE
		$data['stage_item'] = $stage_model->get_stage_jamId($data['jam_item']['id']);
		//log_message("debug","stage_item : ".json_encode($data['stage_item']));
		if (isset ($data['stage_item']))  {
			// DATE conversion
			//if ($this->config->item('online')) setlocale(LC_ALL, 'fr_FR');
			//else setlocale(LC_ALL, 'fra');
			
			$tmpDate = strtotime($data['stage_item']['date_debut']);
			//$data['stage_date_debut_norm'] = strftime("%A %d %B %Y à %H:%M",$tmpDate);   // heure rdv à gérer ?
			$data['stage_date_debut_norm'] = utf8_encode(strftime("%A %d %B %Y",$tmpDate));
		}


		// **** LIST_MEMBERS + infos
		$data['list_members'] = $jam_model->get_list_members($data['jam_item']['id']);

		// On récupère l'instrument principal de chaque participant
		foreach ($data['list_members'] as $key => $elem) {
			$members_model->get_instruments_info($data['list_members'][$key], $data['jam_item']['formationId']);
		}
		
		// **** LIST_REFERENT + infos
		$data['list_referent'] = $jam_model->get_jamInfos($data['jam_item']['id'], 1);

		//log_message("debug","list_referent :");
		//log_message("debug",json_encode($data['list_referent']));

		// On récupère les infos de chaque encadrant
		/*foreach ($data['list_referent'] as $key => $elem) {
			$members_model->get_member_by_id($data['list_referent'][$key]->id);
		}*/
		
		// On trie les membres par mainInstruName ascendant
		//usort($data['list_members'], array($this, "cmp"));
		//log_message("debug","list_members : ".json_encode($data['list_members']));
		

		// On récupère le membre s'il est connecté + info de participations ...
		if (isset($this->session->login)) {			
			
			$member = $members_model->get_members($this->session->login);
			$data['member'] = $member;
			
			// On récupère les infos instrumentales du membres + mainPupitre sur une formation
			$members_model->get_instruments_info($data['member'], $data['jam_item']['formationId']);
			//log_message('debug',"get_instruments_info : ".json_encode($data['member']));
			
			// On détermine si l'utilisateur participe à la jam
			$data['attend'] = $jam_model->is_included($data['jam_item']['id'], $member->id);
			
			// On récupère l'invitation à la jam si elle existe (à priori qu'un item d'ivit par couple de targetTag targetId)   $data['jam_item']['id'], $member->id
			$data['invitation'] = $invitation_model->where( [ 'targetTag' => 2, 'targetId' => $data['jam_item']['id'], 'receiverId' => $member->id  ] )->findAll();
			//log_message("debug","invitation : ".json_encode($data['invitation']));
			$data['is_invited'] = sizeof($data['invitation']) == 0 ? false : true;
			
			// On récupère les infos du sender de l'invitation
			if ($data['is_invited']) {
				$sender = $members_model->get_member_by_id($data['invitation'][0]["senderId"]);
				$data['invitation'][0]['sender'] = $sender;
			}
			//log_message("debug","invitation : ".json_encode($data['is_invited']));
			//log_message("debug","invitation : ".json_encode($data['invitation'][0]));
			
			// On détermine si l'utilisateur est admin de la jam
			$data['is_admin'] = $jam_model->is_admin($data['jam_item']['id'], $member->id);

			// On vire si pas admin et que l'acces est réservé aux admin
			if (!$data['is_admin'] && $data['jam_item']['acces_jam'] == 0) {
				redirect('/access_denied');
			}
			
			// On récupère les inscriptions du membres
			$data['member_inscriptions'] = $jam_model->get_member_inscriptions($data['jam_item']['id'], $member->id);
			// On normalise le posteLabel
			foreach($data['member_inscriptions'] as $key => $tinscr) {
				// Si le posteLabel est null on met instruName à la place
				if (is_null($tinscr['posteLabel'])) $data['member_inscriptions'][$key]['posteLabel'] = $tinscr['instruName'];
			}
			//log_message("debug","member_inscriptions : ".json_encode($data['member_inscriptions']));
			
			
			// On récupère les affectations du membres
			$data['member_affectations'] = $jam_model->get_member_affectations($data['jam_item']['id'], $member->id);
			// On normalise le posteLabel
			foreach($data['member_affectations'] as $key => $tinscr) {
				// Si le posteLabel est null on met instruName à la place
				if (is_null($tinscr['posteLabel'])) $data['member_affectations'][$key]['posteLabel'] = $tinscr['instruName'];
			}
			//log_message("debug","member_affectations : ".json_encode($data['member_affectations']));
			
			// **** STAGE MEMBRE INFOS
			if ($data['stage_item'] != "null") {
				// On détermine si l'utilisateur y participe
				$data['stage_member_item'] = $stage_model->is_included($data['jam_item']['id'], $member->id);
				if ($data['stage_member_item']) {
					// DATE_INSCR
					$date = date_create_from_format("Y-m-d",$data['stage_member_item'][0]->date_inscr);
					$data['stage_date_inscr'] = date_format($date,"d/m/Y");
					$data['attend_stage'] = true;
					$data['cheque_stage'] = $data['stage_member_item'][0]->cheque;
				}
				else $data['attend_stage'] = false;
			}
			else $data['attend_stage'] = false;
			
			// On récupère la liste des instru
			//$data['instru_list'] = $this->instruments_model->get_instruments();

			// On récupère la liste d'instru classée par catégorie (et on vire la colonne "aucun")
			//$data['cat_instru_list'] = $this->instruments_model->get_categorized_instruments("aucun");
		
		
			if ($data['jam_item']["formationId"] > 0) {
				
				// On récupère le header des pupitres de la formation (et surlesquels le membre peut jouer)
				$data['instrumentation_header'] = $formation_model->get_instrumentation_header($data['jam_item']['formationId'], $member->id);
				//log_message("debug","instrumentation_header : ".json_encode($data['instrumentation_header']));

				
				// On récupère l'instrumentation de la formation
				$data['instrumentation_list'] = $formation_model->get_instrumentation($data['jam_item']['formationId']);
				//log_message("debug","instrumentationList : ".json_encode($data['instrumentation_list']));
				
				// On récupère le pupitre principal de chaque participant
				foreach ($data['list_members'] as $elem) {
					// On récupère les postes où le membres peut jouer par rapport à l'instrumentation
					foreach ($data['instrumentation_header'] as $header_item) {
					if ( $formation_model->is_main_pupitre($elem->mainInstruId, $data['jam_item']['formationId'], $header_item["pupitreLabel"]) )
						$elem->{$header_item["pupitreLabel"]} = "1";
					else $elem->{"pupitreLabel"} = "0";
					}
				}
			}
		}
		
		
		// Le visiteur n'est pas loggé
		else {
			$data['attend'] = false;
			$data['attend_stage'] = false;
			$data['cheque_stage'] = false;
			$data['is_admin'] = false;
			$data['logged'] = false;
		}
		
	
		// On charge la wishlist
		$data['wishlist'] = $playlist_model->get_wishlist($data['jam_item']['id']);
		//if ( ! isset ($data['wishlist'])) $data['wishlist'] = "null";
		
		
		// DATE_BAL
		$date = date_create_from_format("Y-m-d G:i:s",$data['jam_item']['date_bal']);
		if ($date) $data['jam_item']['date_bal'] = date_format($date,"H:i");
		
		// DATE_DEBUT
		$date = date_create_from_format("Y-m-d G:i:s",$data['jam_item']['date_debut']);
		if ($date) $data['jam_item']['date_debut'] = date_format($date,"H:i");
		
		// DATE_FIN
		$date = date_create_from_format("Y-m-d G:i:s",$data['jam_item']['date_fin']);
		if ($date) $data['jam_item']['date_fin'] = date_format($date,"H:i");
		

		// On finalise $data
		$data['page_title'] = $data['jam_item']['title'];
		$data['page_description'] = "Découvrez la playlist, inscrivez-vous et venez partagez une jam avec le Grenoble Reggae Orchestra.";    // vignette réseaux sociaux
		
		
		// On lance la vue
		echo view('templates/header', $data);
		if ($data['playlist_item'] != 'null' && isset($_SESSION['login'])) {
			echo view('templates/player', $data);
			$data['player'] = true;
		}
		echo view('templates/menu', $data);
		echo view('jam/view', $data);
		echo view('templates/footer', $data);

	}


	
	
	/*************** CREATE ***************/
	public function create() {
	
		if($this->session->superAdmin) {
			// On lance la vue du formulaire
			echo view('jam/create');
		}
		else {
			$data['page_title'] = 'Erreur';
			$data['title'] = 'Accés refusé !!';
			$data['message'] = 'Votre statut sur le site ne vous permet pas d\'accéder à cette page';
			echo view('templates/header', $data);
			echo view('pages/message', $data);
			echo view('templates/footer');
		}
	}

	
	
	//************ UPDATE ************//
	public function update($slug) {
	
		
		if ($this->session->login && $this->session->superAdmin) {
	
			$playlist_model = new Playlist_model();
			$formation_model = new Formation_model();
			$members_model = new Members_model();
			$lieux_model = new Lieux_model();
			$stage_model = new Stage_model();
			$jam_model = new Jam_model();
			$instruments_model = new Instruments_model();
	
			// On récupère la liste de jam
			/*$data['page_title'] = 'Modifier une jam';
			$data['title'] = 'Jam';*/
			
			// On récupère la liste de playlist
			$data['list_playlist'] = $playlist_model->get_playlist();
			
			// On récupère la liste des formations
			$data['list_formations'] = $formation_model->get_formations();
			
			// On récupère la liste des lieux
			$data['list_lieux'] = $lieux_model->get_lieux();
			
			// On récupère les listes d'instru
			$data['famille_instru_list'] = $instruments_model->get_instru_families();
			$data['instru_list'] = $instruments_model->get_instruments("name");
			//log_message("debug",json_encode($data['famille_instru_list']));
			
			// On récupère la jam
			$data['jam_item'] = $jam_model->get_jam($slug);
			
			// On récupère la liste des membres en ordre alpha
			$data['list_membres'] = $members_model->get_members('null','asc');
			// On récupère l'instrument principal de chaque participant
			foreach ($data['list_membres'] as $key => $elem) {
				$members_model->get_mainInstru($data['list_membres'][$key]);
			}			
			
			// DATE
			$date = date_create_from_format("Y-m-d",$data['jam_item']['date']);
			$data['jam_item']['date_label'] = date_format($date,"d/m/Y");
			$old_date = date_format($date,"Y-m-d");   // pour retrouver le répertoire lié à l'event
			
			// PLANNING
			$date = date_create_from_format("Y-m-d H:i:s",$data['jam_item']['date_bal']);
			if ($date) $data['jam_item']['date_bal'] = date_format($date,"H:i");
			$date = date_create_from_format("Y-m-d H:i:s",$data['jam_item']['date_debut']);
			if ($date) $data['jam_item']['date_debut'] = date_format($date,"H:i");
			$date = date_create_from_format("Y-m-d H:i:s",$data['jam_item']['date_fin']);
			if ($date) $data['jam_item']['date_fin'] = date_format($date,"H:i");
			
			// On récupère le lieu de la jam
			$data['lieu_item'] = $lieux_model->get_lieux_by_id($data['jam_item']['lieuxId']);
			
			// On récupère la formation de la jam
			$data['formation_item'] = $formation_model->get_formation_by_id($data['jam_item']['formationId']);
						
			// On récupère la playlist de la jam
			$data['playlist_item'] = $playlist_model->get_playlist_versions($data['jam_item']['playlistId']);
			if (! $data['playlist_item']) $data['playlist_item'] = "null";
			
			// On récupère les admin
			$data['list_admin'] = $jam_model->get_event_admin($data['jam_item']['id']);
		
			// On récupère le stage s'il y en a un associé à la jam sinon on créé un stage vide (pour le populate)
			$data['stage_item'] = $stage_model->get_stage_jamId($data['jam_item']['id']);
			if (! $data['stage_item']) {
				$data['stage_item']['id'] = -1;
				$data['stage_item']['lieuxId'] = -1;
				$data['stage_item']["jamId"] = $data['jam_item']['id'];
				$data['stage_item']["text"] = "";
				$data['stage_item']["duree"] = 0;
				$data['stage_item']["date_debut"] = "00/00/0000";
				$data['stage_item']["date_limit"] = "00/00/0000";
				$data['stage_item']["cotisation"] = 0;
				$data['stage_item']["ordre"] = "";
				$data['stage_item']["adresse_cheque"] = "";
				$data['lieu_stage_item']["nom"] = "";
				$data['lieu_stage_item']["adresse"] = "";
				$data['lieu_stage_item']["web"] = "";
			}
			else {
				// On récupère le lieu du stage
				$data['lieu_stage_item'] = $lieux_model->get_lieux_by_id($data['stage_item']['lieuxId']);
				
				// On reformate les dates récupérées du stage
				$date = date_create_from_format("Y-m-d",$data['stage_item']["date_debut"]);
				$data['stage_item']["date_debut"] = date_format($date,"d/m/Y");
				$date = date_create_from_format("Y-m-d",$data['stage_item']["date_limit"]);
				$data['stage_item']["date_limit"] = date_format($date,"d/m/Y");
			}
			
			
			// On lance la vue pour une modal box (pop-up)
			echo view('jam/update', $data);
		}
		
	}
	
	
	
	//***********************************************************************//
	//**************************     MANAGE    ******************************//
	//***********************************************************************//
	public function manage($slug) {
	
		if($this->session->login) {
				
			$jam_model = new Jam_model();
			$members_model = new Members_model();
			$stage_model = new Stage_model();
			$instruments_model = new Instruments_model();
				
			// On vérifie que l'utilisateur est connecté
			$member = $members_model->get_members($this->session->login);
			if (!isset ($member)) redirect()->to('/access_denied');	
			
			// On récupère le membre
			$data['member'] = $member;
		
			// On récupère la jam
			$data['jam_item'] = $jam_model->get_jam($slug);
			
			// On détermine si l'utilisateur est admin (ou superAdmin) de la jam
			$data['is_admin'] = $jam_model->is_admin($data['jam_item']['id'], $member->id);
			if (!$data['is_admin']) redirect('/access_denied');

			// On fixe les variables de la page
			$data['page_title'] = 'Gérer les participants de la jam : '.$data['jam_item']['title'];
			$data['title'] = 'Jam';
			
			// On récupère la liste des membres qui participent à la jam (mais sans les stagiaires qui sont dans un tab différents)
			$data['list_members'] = $jam_model->get_list_members($data['jam_item']['id'], false);
			//log_message("debug","list_members : ".json_encode($data['list_members']));


			// **** LIST_REFERENT + infos
			$data['list_referent'] = $jam_model->get_jamInfos($data['jam_item']['id'], 1);			
			foreach ($data['list_referent'] as $treferent) {
				// On cherche le référent dans la liste des membres
				$key = array_search($treferent->tag1Val, array_column($data['list_members'], "id"));
				// On complète les infos du membre
				if ($key !== false) {
					$data['list_members'][$key]->{"referent"} = 1;
					$data['list_members'][$key]->{"tag2Title"} = $treferent->tag2Title;
					$data['list_members'][$key]->{"tag2Label"} = $treferent->tag2Label;
				}
			}
			
			// **** LIST_ADMIN
			$data['list_admin'] = $jam_model->get_event_admin($data['jam_item']['id']);
			foreach ($data['list_admin'] as $tadmin) {
				// On cherche le référent dans la liste des membres
				$key = array_search($tadmin->memberId, array_column($data['list_members'], "id"));
				// On complète les infos du membre
				if ($key !== false) {
					$data['list_members'][$key]->{"admin"} = 1;
				}
			}
			
			// On traite chaque membres
			foreach ($data['list_members'] as $tmember) {
								
				// On récupère l'age
				$members_model->calcul_age($tmember);
				
				// On récupère les infos instrumentales du membres + mainPupitre sur une formation
				$members_model->get_instruments_info($tmember, $data['jam_item']['formationId']);
				
				// On reformate les dates d'inscriptions à la jam
				$date = date_create_from_format("Y-m-d G:i:s",$tmember->date_inscr);
				$tmember->date_inscr = date_format($date,"d/m/Y");
			}
			
			// ************* STAGE ********/
			// On récupère le stage s'il y en a un associé à la jam
			$data['stage_item'] = $stage_model->get_stage_jamId($data['jam_item']['id']);
			
			// On récupère la liste des stagiaires qui participent à la jam
			if (isset ($data['stage_item']['id'])) {
				//log_message("debug","stage_item : ".json_encode($data['stage_item']));
				$data['list_stage_members'] = $stage_model->get_list_members($data['stage_item']['id']);
				//log_message("debug","list_stage_members : ".json_encode($data['list_stage_members']));

				// On reformate les dates d'inscriptions à la jam
				if ($data['list_stage_members'] != false) {
					foreach ($data['list_stage_members'] as $tstagiaire) {
						
						// On récupère l'age
						$members_model->calcul_age($tstagiaire);
						
						// On récupère les infos instrumentales du membres + mainPupitre sur une formation
						$members_model->get_instruments_info($tstagiaire, $data['jam_item']['formationId'], true);
						
						$date = date_create_from_format("Y-m-d",$tstagiaire->date_inscr);
						$tstagiaire->date_inscr = date_format($date,"d/m/Y");
						$date = date_create_from_format("Y-m-d",$tstagiaire->date_relance);
						$tstagiaire->date_relance = date_format($date,"d/m/Y");
					}
				}
			}
			else $data['list_stage_members'] = null;
			
			// On récupère la liste d'instru et leur catégories
			$data['instru_list'] = $instruments_model->get_instruments();
			$data['instru_cat'] = $instruments_model->get_instru_categories("id");
			
			
			// On lance la vue
			echo view('templates/header', $data);
			echo view('templates/menu', $data);
			echo view('jam/manage', $data);
			echo view('templates/footer', $data);
		}	
			
		else {
			$data_page['page_title'] = 'Accés refusé !!';
			$data_page['title'] = 'Accés refusé !!';
			$data_page['message'] = 'Votre statut sur le site ne vous permet pas d\'accéder à cette page';
		
			echo view('templates/header', $data_page);
			echo view('pages/message', $data_page);
			echo view('templates/footer');
		}
	}
	
	
	//****************** ADD MEMBER  ********************//
	public function add_member($jamId) {
		
		if($this->session->login) {

			$jam_model = new Jam_model();

			// On récupère la jam
			$data['jam_item'] = $jam_model->get_jam_id($jamId);
		
			// On récupère la liste des membres qui participent à la jam
			$data['list_members'] = $jam_model->get_list_members_not_in_jam($data['jam_item']['id']);
			
			// On récupère la liste des membres qui participent à la jam
			//$data['list_members'] = $this->members_model->get_members('null','asc');
			
			
			// On lance la vue pour une modal box (pop-up)
			echo view('jam/add_member', $data);
		}
	}
	
	
	//***********************************************************************//
	//***********************     INSCRIPTIONS    ***************************//
	//***********************************************************************//
	public function inscriptions($slug) {
	
		if($this->session->login) {
		
			$jam_model = new Jam_model();
			$members_model = new Members_model();
			$playlist_model = new playlist_model();
			$formation_model = new Formation_model();
		
			// Section selectionnée dans le menu
			$data['title'] = 'Jam';
		
			// On vérifie que l'utilisateur est connecté
			$member = $members_model->get_members($this->session->login);
			//if (!isset ($member)) redirect('/access_denied');
			
			// On récupère le membre
			$data['member'] = $member;
		
			// On récupère la jam
			$data['jam_item'] = $jam_model->get_jam($slug);
			
			// On détermine si le jam est archivée
			$data['is_archived'] = $jam_model->is_archived($data['jam_item']['id']);
			
			// On récupère l'event path
			$data['event_path'] = base_url()."/ressources/event/".$jam_model->get_dirPath($data['jam_item']['id']);;
			
			// On détermine si l'utilisateur est admin de la jam
			$data['is_admin'] = $jam_model->is_admin($data['jam_item']['id'], $member->id);

			// On récupère le tableau d'inscription
			//$data['inscrTab'] = $this->jam_model->get_inscrTab($slug);
			
			// On récupère le header des pupitres de la formation et surlesquels le membre peut jouer
			$data['instrumentation_header'] = $formation_model->get_instrumentation_header($data['jam_item']['formationId'], $member->id);
			//log_message("debug","instrumentation_header : ".json_encode($data['instrumentation_header']));
			
			// On récupère l'instrumentation de la formation
			$data['instrumentation_list'] = $formation_model->get_instrumentation($data['jam_item']['formationId']);
			//log_message("debug","instrumentationList : ".json_encode($data['instrumentation_list']));
			
			
			// On récupère les postes où le membres peut jouer par rapport à l'instrumentation
			if ($data['jam_item']["formationId"] > 0) {
				foreach ($data['instrumentation_list'] as $index => $instrumentation_item) {
					if ($formation_model->could_play($member->id,$instrumentation_item["id"])) $data['instrumentation_list'][$index]['couldPlay'] = "1";
					else $data['instrumentation_list'][$index]['couldPlay'] = "0";
				}
			}

					
			// On récupère la playlist de la jam
			$data['playlist_item'] = $playlist_model->get_playlist_versions($data['jam_item']['playlistId']);
			
			// On récupère le membre et on détermine si l'utilisateur participe à la jam
			$member = $members_model->get_members($this->session->login);
			$data['member'] = $member;
			if (isset ($member)) $data['attend'] = $jam_model->is_included($data['jam_item']['id'], $member->id);
			//else redirect('/home/');
			
			// On récupère les catégories d'instrument
			//$data['instru_cat'] = $this->instruments_model->get_instru_categories();
			
			// On récupère les catégories d'instrument à afficher par défaut
			//$data['instru_cat1'] = $this->instruments_model->get_catInstrument($member->idInstru1);
			//$data['instru_cat2'] = $this->instruments_model->get_catInstrument($member->idInstru2);
			
			// On récupère la liste des membres qui participent à la jam
			$data['list_members'] = $jam_model->get_list_members($data['jam_item']['id']);
			
			// On récupère la liste d'instru classée par catégorie (et on vire la colonne "aucun")
			//$data['cat_instru_list'] = $this->instruments_model->get_categorized_instruments("aucun");
			
			// On récupère les inscriptions
			$data['inscriptions'] = $jam_model->get_inscriptions($slug);
			
			// On récupère les affectations
			$data['affectations'] = $jam_model->get_affectations($slug);

			// On lance la vue
			echo view('jam/inscriptions', $data);			
		}
		
		// Utilisateur non loggé
		else {
			$data['page_title'] = 'Erreur';
			$data['title'] = 'Accés refusé !!';
			$data['message'] = 'Votre statut sur le site ne vous permet pas d\'accéder à cette page';
			echo view('templates/header', $data);
			echo view('pages/message', $data);
			echo view('templates/footer');
			return;
		}
	}
	
	
	//****************** UPDATE TEXT TAB  ********************//
	public function update_text_tab($jamId) {

		if ($this->session->login && $this->session->superAdmin) {

			$jam_model = new Jam_model();

			// On récupère la jam
			$data['jam_item'] = $jam_model->get_jam_id($jamId);
			
			// On enlève les <br /> contenu du texte
			$data['jam_item']['text_tab'] = str_replace("<br />","",$data['jam_item']['text_tab']);

			// On lance la vue pour une modal box (pop-up)
			echo view('jam/update_text_tab', $data);
		}
	}


	//***********************************************************************//
	//****************************** INVITATIONS  ***************************//
	//***********************************************************************//
	public function invitations($slug) {

		
		if ($this->session->login) {
		
			$jam_model = new Jam_model();
			$members_model = new Members_model();
			$instruments_model = new Instruments_model();
			
			// On récupère la jam
			$data['jam_item'] = $jam_model->get_jam($slug);
			
			// On récupère le membre et on détermine si l'utilisateur participe à la jam
			$member = $members_model->get_members($this->session->login);
			
			if (isset ($member)) {
				
				$data['member'] = $member;
				
				// On détermine si l'utilisateur est admin de la jam
				$data['is_admin'] = $jam_model->is_admin($data['jam_item']['id'], $member->id);
				
				// On détermine si la jam est archivée (possibilité d'inviter ou pas)
				$data['is_archived'] = $jam_model->is_archived($data['jam_item']['id']);
				
				
				// Invitations possibles que si connecté et admin
				if ($data['is_admin']) {
					
					// On regarde si superAdmin
					$data['isSuperAdmin'] = ($this->session->superAdmin);
					
					// On récupère la liste des membres en ordre alpha
					$data['list_membres'] = $members_model->get_members('null','asc');
					// On récupère l'instrument principal de chaque participant
					foreach ($data['list_membres'] as $key => $elem) {
						$members_model->get_mainInstru($data['list_membres'][$key]);
					}	
					
					// On récupère les invitations
					//$data['invitations'] = "coucou";
					
					// On lance la vue
					echo view('jam/invitations', $data);
					
				}
			}
		}
	}


	
	//***********************************************************************//
	//****************************** REPETITIONS  ***************************//
	//***********************************************************************//
	public function repetitions($slug) {
		
		// Section selectionnée dans le menu
		$data['title'] = 'Jam';
		
		$jam_model = new Jam_model();
		$members_model = new Members_model();
		$instruments_model = new Instruments_model();
		$repetition_model = new Repetition_model();
		$formation_model = new Formation_model();
		$lieux_model = new Lieux_model();
		
		// On récupère la jam
		$data['jam_item'] = $jam_model->get_jam($slug);
		
		// Si l'utilisateur est connecté...
		if ($this->session->login) {
			
			// On récupère le membre et on détermine si l'utilisateur participe à la jam
			$member = $members_model->get_members($this->session->login);
			
			if (isset ($member)) {
				$data['member'] = $member;
				$data['attend'] = $jam_model->is_included($data['jam_item']['id'], $member->id);
				
				// On récupère les catégories d'instrument à afficher par défaut
				//$data['instru_cat1'] = $instruments_model->get_catInstrument($member->idInstru1);
				//$data['instru_cat2'] = $instruments_model->get_catInstrument($member->idInstru2);
				
				// On détermine si l'utilisateur est admin de la jam
				$data['is_admin'] = $jam_model->is_admin($data['jam_item']['id'], $member->id);
				
				// On détermine si la jam est archivée (accès à la création de répét ou pas)
				$data['is_archived'] = $jam_model->is_archived($data['jam_item']['id']);
				
			}
		}
		
		// On récupère l'event path
		$data['event_path'] = base_url()."/ressources/event/".$jam_model->get_dirPath($data['jam_item']['id']);;

		// On récupère les catégories d'instrument
		//$data['instru_cat'] = $this->instruments_model->get_instru_categories();

		// On récupère les répétitions qui ne sont pas encore passée
		$data['repetitions'] = $repetition_model->get_repetitions($data['jam_item']['id'], true);
		
	
		if ($data['repetitions'] != false) {
			
			// On récupère les pupitres et les postes
			if ($data['jam_item']["formationId"] > 0) {
				// On récupère le header des pupitres de la formation (et surlesquels le membre peut jouer si logged)
				if (isset ($member)) $data['pupitre_list'] = $formation_model->get_instrumentation_header($data['jam_item']['formationId'], $member->id);
				else $data['pupitre_list'] = $formation_model->get_instrumentation_header($data['jam_item']['formationId']);
				//log_message("debug","pupitre_list : ".json_encode($data['pupitre_list']));
				// On récupère l'instrumentation de la formation
				$data['poste_list'] = $formation_model->get_instrumentation($data['jam_item']['formationId']);
				//log_message("debug","instrumentationList : ".json_encode($data['instrumentation_list']));
			}

			// On récupère la liste des lieux
			$data['list_lieux'] = $lieux_model->get_lieux();
			//log_message("debug","list_lieux : ".json_encode($data['list_lieux']));
			

			///// DATE conversion
			if (env('app.online')) setlocale(LC_ALL, 'fr_FR');
			else setlocale(LC_ALL, 'fra');
			
			foreach ($data['repetitions'] as $key => $elem) {
				
				// Pupitre Label
				if ($data['repetitions'][$key]['name'] == "aucun") $data['repetitions'][$key]['name'] = "générale";
				
				// DATE
				$tmpDate = strtotime($elem['date_debut']);
				$data['repetitions'][$key]['date_debut_norm'] = utf8_encode(strftime("%A %d %B %Y",$tmpDate));
				$data['repetitions'][$key]['date_debut_fr'] = utf8_encode(strftime("%d/%m/%Y",$tmpDate));
				$data['repetitions'][$key]['heure_debut'] = utf8_encode(strftime("%H:%M",$tmpDate));
				$tmpDate = strtotime($elem['date_fin']);
				$data['repetitions'][$key]['heure_fin'] = utf8_encode(strftime("%H:%M",$tmpDate));
				
				$month_name = strftime("%b", strtotime($elem['date_debut']));
				$day_name = strftime("%a", strtotime($elem['date_debut']));
				if (!env('app.online')) {
					$month_name = utf8_encode($month_name);
					$day_name = utf8_encode($day_name);
				}
				$month_name = substr(strtoupper(no_accent($month_name)),0,3);
				$data['repetitions'][$key]['month_name'] =  $month_name;
				$day_name = substr(strtoupper(no_accent($day_name)),0,3);
				$data['repetitions'][$key]['day_name'] =  $day_name.".";
				
				
				$data['repetitions'][$key]['day'] = strftime("%d", strtotime($elem['date_debut']));
				$data['repetitions'][$key]['month'] = strftime("%m", strtotime($elem['date_debut']));
				$data['repetitions'][$key]['year'] = strftime("%Y", strtotime($elem['date_debut']));
				
			}
		}
		
		
		// On lance la vue du formulaire
		echo view('jam/repetitions', $data);
		
	}
	
	
	
	//****************** VIEW REPETITION  ********************//
	public function view_repetition($repet_id) {
		
		if($this->session->login) {
			
			$repetition_model = new Repetition_model();
			$lieux_model = new Lieux_model();
			
			// On récupère la répétition
			$data['repet_item'] = $repetition_model->get_repetition($repet_id);

			// On récupère le lieu de la jam
			$data['lieu_item'] = $lieux_model->get_lieux_by_id($data['repet_item']['lieuxId']);

			// Pupitre Label
			if ($data['repet_item']['name'] == "aucun") $data['repet_item']['name'] = "générale";

			// On enlève les <br /> contenu du texte
			$data['repet_item']['text_html'] = $data['repet_item']['text'];
			$data['repet_item']['text'] = str_replace("<br />","",$data['repet_item']['text']);
			
			// DATE
			$date = date_create_from_format("Y-m-d H:i:s",$data['repet_item']['date_debut']);
			$data['repet_item']['date_label'] = date_format($date,"d/m/Y");
			
			// PLANNING
			$date = date_create_from_format("Y-m-d H:i:s",$data['repet_item']['date_debut']);
			if ($date) $data['repet_item']['date_debut'] = date_format($date,"H:i");
			$date = date_create_from_format("Y-m-d H:i:s",$data['repet_item']['date_fin']);
			if ($date) $data['repet_item']['date_fin'] = date_format($date,"H:i");

			
			// On lance la vue pour une modal box (pop-up)
			echo view('jam/view_repetition', $data);
		}
		
	}
	
	
	
	//****************** CREATE REPETITION  ********************//
	public function create_repetition($slug) {
			
		if($this->session->login) {
	
			$jam_model = new Jam_model();
			$lieux_model = new Lieux_model();
			$formation_model = new Formation_model();
			
			// On récupère la jam
			$data['jam_item'] = $jam_model->get_jam($slug);
			
			// On récupère les admin
			$data['list_admin'] = $jam_model->get_event_admin($data['jam_item']['id']);
			
			// On récupère la liste des lieux
			$data['list_lieux'] = $lieux_model->get_lieux();
			
			// On récupère la liste des catégories d'instrument
			//$data['instru_cat'] = $this->instruments_model->get_instru_categories();
			
			// On récupère les pupitres
			if ($data['jam_item']["formationId"] > 0) {
				$data['pupitre_list'] = $formation_model->get_instrumentation_header($data['jam_item']['formationId']);
			}
			
			// On récupère la liste des membres en ordre alpha
			//$data['list_membres'] = $this->members_model->get_members('null','asc');
			

			// DATE
			$date = date_create_from_format("Y-m-d",$data['jam_item']['date']);
			$data['jam_item']['date_label'] = date_format($date,"d/m/Y");
			$old_date = date_format($date,"Y-m-d");   // pour retrouver le répertoire lié à l'event
			
			// PLANNING
			$date = date_create_from_format("Y-m-d H:i:s",$data['jam_item']['date_bal']);
			if ($date) $data['jam_item']['date_bal'] = date_format($date,"H:i");
			$date = date_create_from_format("Y-m-d H:i:s",$data['jam_item']['date_debut']);
			if ($date) $data['jam_item']['date_debut'] = date_format($date,"H:i");
			$date = date_create_from_format("Y-m-d H:i:s",$data['jam_item']['date_fin']);
			if ($date) $data['jam_item']['date_fin'] = date_format($date,"H:i");

			
			// On lance la vue pour une modal box (pop-up)
			echo view('jam/create_repetition', $data);
		}
	}
	

	
	//****************** UPDATE REPETITION  ********************//
	public function update_repetition($repet_id) {

		if($this->session->login) {
	
			$repetition_model = new Repetition_model();
			$lieux_model = new Lieux_model();
			$jam_model = new Jam_model();
			$formation_model = new Formation_model();
	
			// On récupère la liste de jam
			/*$data['page_title'] = 'Modifier une répétition';
			$data['title'] = 'Jam';*/
			
			// On récupère la répétition
			$data['repet_item'] = $repetition_model->get_repetition($repet_id);
			
			// On récupère la liste des lieux
			$data['list_lieux'] = $lieux_model->get_lieux();
			
			// On récupère le lieu de la répétition
			$data['lieu_item'] = $lieux_model->get_lieux_by_id($data['repet_item']["lieuxId"]);
			
			// On récupère la liste des catégories d'instrument
			//$data['instru_cat'] = $this->instruments_model->get_instru_categories();
			
			// On récupère la jam
			$data['jam_item'] = $jam_model->get_jam_id($data['repet_item']["jamId"]);
			
			// On récupère les pupitres
			if ($data['jam_item']["formationId"] > 0) {
				$data['pupitre_list'] = $formation_model->get_instrumentation_header($data['jam_item']['formationId']);
			}
			
			// On enlève les <br /> contenu du texte
			$data['repet_item']['text'] = str_replace("<br />","",$data['repet_item']['text']);
			
			// On récupère la liste des membres en ordre alpha
			//$data['list_membres'] = $this->members_model->get_members('null','asc');

			//log_message("error",json_encode($data['repet_item']));
			
			// DATE
			$date = date_create_from_format("Y-m-d H:i:s",$data['repet_item']['date_debut']);
			$data['repet_item']['date_label'] = date_format($date,"d/m/Y");
			
			// PLANNING
			$date = date_create_from_format("Y-m-d H:i:s",$data['repet_item']['date_debut']);
			if ($date) $data['repet_item']['date_debut'] = date_format($date,"H:i");
			$date = date_create_from_format("Y-m-d H:i:s",$data['repet_item']['date_fin']);
			if ($date) $data['repet_item']['date_fin'] = date_format($date,"H:i");

			
			// On lance la vue pour une modal box (pop-up)
			echo view('jam/update_repetition', $data);
		}
	}
	
	
	
	
	//***********************************************************************//
	//*************************      AFFECTATIONS     ***********************//
	//***********************************************************************//
	public function affect($slug) {
	
		if($this->session->login) {
				
			$playlist_model = new Playlist_model();
			$formation_model = new Formation_model();
			$stage_model = new Stage_model();
			$jam_model = new Jam_model();
			$members_model = new Members_model();
			
				
			// On vérifie que l'utilisateur est connecté
			$member = $members_model->get_members($this->session->login);
			if (!isset ($member)) redirect()->to('/access_denied');	
			
			// On récupère le membre
			$data['member'] = $member;
		
			// On récupère la jam
			$data['jam_item'] = $jam_model->get_jam($slug);
			
			// On détermine si l'utilisateur est admin (ou superAdmin) de la jam
			$data['is_admin'] = $jam_model->is_admin($data['jam_item']['id'], $member->id);
			if (!$data['is_admin']) redirect()->to('/access_denied');
			
			
			// On récupère la liste des membres qui participent à la jam sans les stagiaires
			$data['list_members'] = $jam_model->get_list_members($data['jam_item']['id'],false);
			log_message("debug","***** Jam :: affect :: list_members : ".json_encode($data['list_members']));
			
			// On récupère les infos instrumentales du membres + mainPupitre sur une formation
			foreach ($data['list_members'] as $tmember) {
				$members_model->get_instruments_info($tmember, $data['jam_item']['formationId']);
			}
			
			
			// **************** STAGE ***************/
			// On récupère le stage s'il y en a un associé à la jam
			$data['stage_item'] = $stage_model->get_stage_jamId($data['jam_item']['id']);
			
			// On récupère la liste des stagiaires qui participent à la jam
			if (isset ($data['stage_item']['id'])) {
				$data['list_stage_members'] = $stage_model->get_list_members($data['stage_item']['id']);
				if ($data['list_stage_members'] != false) {
					log_message("debug","***** Jam :: affect :: list_stage_members : ".json_encode($data['list_stage_members'])); 
					// On récupère les infos instrumentales des stagiaires + mainPupitre sur une formation
					foreach ($data['list_stage_members'] as $tstagiaire) {
						$members_model->get_instruments_info($tstagiaire, $data['jam_item']['formationId'], true);
					}
				}
				else $data['list_stage_members'] = null;
			}
			else $data['list_stage_members'] = null;
			
			
			
			// On récupère le header des pupitres de la formation et surlesquels le membre peut jouer
			$data['instrumentation_header'] = $formation_model->get_instrumentation_header($data['jam_item']['formationId'], $member->id);
			//log_message("debug","instrumentation_header : ".json_encode($data['instrumentation_header']));
			
			// On récupère l'instrumentation de la formation
			$data['instrumentation_list'] = $formation_model->get_instrumentation($data['jam_item']['formationId']);
			

			// On récupère les membres qui peuvent jouer à un poste
			if ($data['jam_item']["formationId"] > 0) {
				// On parcourt les postes
				foreach ($data['instrumentation_list'] as $index => $poste_item) {
					$data['instrumentation_list'][$index]['members'] = array();
					// On parcourt les membres qui participent à la jam
					foreach ($data['list_members'] as $tmember) {
						if ($formation_model->could_play($tmember->id,$poste_item["id"])) array_push($data['instrumentation_list'][$index]['members'], [ 'memberId' => $tmember->id, 'pseudo' => $tmember->pseudo ]);
					}
					// On parcourt les stagiaires qui participent à la jam
					if (isset ($data['stage_item']['id']) && $data['list_stage_members'] != null) {
						$data['instrumentation_list'][$index]['stagiaires'] = array();
						// On parcourt les membres qui participent à la jam
						foreach ($data['list_stage_members'] as $tstagaire) {
							if ($formation_model->could_play($tstagaire->memberId,$poste_item["id"])) array_push($data['instrumentation_list'][$index]['stagiaires'], [ 'memberId' => $tstagaire->memberId, 'pseudo' => $tstagaire->pseudo ]);
						}
					}
					//log_message("debug","******** ".json_encode($data['instrumentation_list'][$index]));
				}
			}
			
			//log_message("debug","instrumentationList : ".json_encode($data['instrumentation_list']));
			
		
			// Section selectionnée dans le menu
			$data['title'] = 'Jam';
							
			// On récupère la playlist de la jam
			$data['playlist_item'] = $playlist_model->get_playlist_versions($data['jam_item']['playlistId']);
					

			// On récupère le chemin relatif de la jam
			$data['dirPath'] = "/ressources/event/".$jam_model->get_dirPath($data['jam_item']['id']);
			
			// On récupère les inscriptions
			$inscriptions = $jam_model->get_inscriptions($slug);		
			$data['inscriptions'] = $inscriptions;
			
			// On récupère les affectations
			$affectations = $jam_model->get_affectations($slug);		
			$data['affectations'] = $affectations;
			


			if (empty($data['jam_item'])) {
				throw CodeIgniterxceptionsPageNotFoundException::forPageNotFound;
			}

			// On finalise $data
			$data['page_title'] = "Tableau d'affectation aux morceaux de la jam : ".$data['jam_item']['title'];
					
			// On lance la vue
			echo view('templates/header', $data);
			if ($data['playlist_item'] != 'null' && isset($_SESSION['login'])) echo view('templates/player', $data);
			echo view('templates/menu', $data);
			echo view('jam/affect', $data);
			echo view('templates/footer', $data);

		}
		
		else {
			$data_page['page_title'] = 'Accés refusé !!';
			$data_page['title'] = 'Accés refusé !!';
			$data_page['message'] = 'Votre statut sur le site ne vous permet pas d\'accéder à cette page';
		
			echo view('templates/header', $data_page);
			echo view('pages/message', $data_page);
			echo view('templates/footer');
		}
	}
	
	
	
	
	//****************** JAM WISHLIST ********************//
	public function ajax_add_wish() {
	
		$slugJam = trim($_POST['slugJam']);
		$id = trim($_POST['id']);
		//$title = trim($_POST['titre']);
		$url = trim($_POST['url']);
		
		$jam_model = new Jam_model();
		$members_model = new Members_model();
		$playlist_model = new playlist_model();
		
		// On récupère le titre de l'URL
		$str = file_get_contents($url);
		if ( strlen($str) > 0 ) {
			$str = trim(preg_replace('/\s+/', ' ', $str)); // supports line breaks inside <title>
			preg_match("/\<title\>(.*)\<\/title\>/i",$str,$tmp_title); // ignore case
			$title =  $tmp_title[1];
		}
		
		// Pour récupérer l'id du membre et de la jam
		$membre = $members_model->get_members_by_id($id);
		if ($slugJam != 'null') $jam = $jam_model->get_jam($slugJam);
		else $jam['id'] = null;
		
		$data_wish = array(
			'jamId' => $jam['id'],
			'membresId' => $membre->id,
			'titre' => $title,
			'url' => $url
		);
		$playlist_model->add_wish($data_wish);
		
		$return_data = array(
			'state' => 1,
			'data' => $title
		);
		$output = json_encode($return_data);
		echo $output;
		
	}
	
	
	
	
	//***********************************************************************//
	//***********************     PRESENTATION    ***************************//
	//***********************************************************************//
	public function presentation($slug) {
	
		if($this->session->login) {
				
			$playlist_model = new playlist_model();
			$formation_model = new Formation_model();
			$jam_model = new Jam_model();
			$members_model = new Members_model();
			
			// On vérifie que l'utilisateur est connecté
			$member = $members_model->get_members($this->session->login);
			if (!isset ($member)) redirect()->to('/access_denied');	
			
			// On récupère le membre
			$data['member'] = $member;
		
			// On récupère la jam
			$data['jam_item'] = $jam_model->get_jam($slug);
			
			// On détermine si l'utilisateur est admin de la jam
			$data['is_admin'] = $jam_model->is_admin($data['jam_item']['id'], $member->id);
			
			// On détermine si l'utilisateur est admin (ou superAdmin) de la jam
			$data['is_admin'] = $jam_model->is_admin($data['jam_item']['id'], $member->id);
			if (!$data['is_admin']) redirect()->to('/access_denied');
			
			// On fixe les variables de la page
			$data['page_title'] = 'Présentation : '.$data['jam_item']['title'];
			$data['title'] = 'Jam';
			
			// On récupère la liste des membres qui participent à la jam
			$data['list_members'] = $jam_model->get_list_members($data['jam_item']['id']);
			// On récupère les infos instrumentales de chaque membres
			foreach ($data['list_members'] as $tmember) {
				$members_model->get_instruments_info($tmember, $data['jam_item']['formationId']);
			}
			// On trie les membres par mainPupitre / mainInstruName ascendant (pseudo ascendant)
			usort($data['list_members'], function ($a, $b) {
											// On trie sur le pseudo pour ceux qui n'ont pas de mainPupitre
											if ($a->mainPupitre == false && $b->mainPupitre == false)
												return strcasecmp($a->pseudo, $b->pseudo);
											else if ($a->mainPupitre == false) return 1;
											else if ($b->mainPupitre == false) return -1;
											
											// Trie sur pupitre
											if ($a->mainPupitre["pupitreLabel"] != $b->mainPupitre["pupitreLabel"])
												return strcasecmp($a->mainPupitre["pupitreLabel"], $b->mainPupitre["pupitreLabel"]);
											
											// Même pupitre...
											// ... trie sur pseudo pour lead et choeur
											if ($a->mainPupitre["pupitreLabel"] == "choeur" || $a->mainPupitre["pupitreLabel"] == "lead")
												return strcasecmp($a->pseudo, $b->pseudo);
											
											// ... sinon sur mainInstruName
											else if ($a->mainInstruName != $b->mainInstruName)
												return strcasecmp($a->mainInstruName, $b->mainInstruName);
											
											// Même pupitre et instruName, trie sur pseudo
											return strcasecmp($a->pseudo, $b->pseudo);
										});
			
			// Trace pour DEBUG
			/*foreach ($data['list_members'] as $tmember) {
				log_message("debug","member : ".$tmember->pseudo."   ".json_encode($tmember->mainPupitre)."   ".$tmember->mainInstruName."   ".$tmember->mainFamily);
			}*/
			
			// On récupère le header des pupitres de la formation (et surlesquels le membre peut jouer)
			$data['instrumentation_header'] = $formation_model->get_instrumentation_header($data['jam_item']['formationId']);
			log_message("debug","******* Jam :: presentation :: instrumentation_header : ".json_encode($data['instrumentation_header']));

			// On récupère la playlist de la jam
			$data['playlist_item'] = $playlist_model->get_playlist_versions($data['jam_item']['playlistId']);
			if (! $data['playlist_item']) $data['playlist_item'] = "null";
			
			// On récupère les affectations
			$affectations = $jam_model->get_affectations($slug);		
			$data['affectations'] = $affectations;
			
			
			// On lance la vue
			echo view('templates/header', $data);
			echo view('templates/menu', $data);
			echo view('jam/presentation', $data);
			echo view('templates/footer', $data);
		}	
			
		else {
			$data_page['page_title'] = 'Accés refusé !!';
			$data_page['title'] = 'Accés refusé !!';
			$data_page['message'] = 'Votre statut sur le site ne vous permet pas d\'accéder à cette page';
		
			echo view('templates/header', $data_page);
			echo view('pages/message', $data_page);
			echo view('templates/footer');
		}
	}
	
	
	
	//****************** UPDATE TEXT TAB  ********************//
	public function update_credits($jamId) {

		if($this->session->login) {

			$jam_model = new Jam_model();

			// On récupère la jam
			$data['jam_item'] = $jam_model->get_jam_id($jamId);
			
			// On enlève les <br /> contenu du texte
			//$data['jam_item']['credits_html'] = str_replace("<br />","",$data['jam_item']['credits_html']);


			// On lance la vue pour une modal box (pop-up)
			echo view('jam/update_credits', $data);
		}
	}
	
	
	
	//***********************************************************************//
	//******************************   FICHIERS   ***************************//
	//***********************************************************************//
	public function generate_file($jamId) {

		$jam_model = new Jam_model();
		$playlist_model = new Playlist_model();
		$members_model = new Members_model();

		// On vérifie que l'utilisateur est connecté
		$data['member'] = $members_model->get_members($this->session->login);
		if (!isset ($data['member'])) redirect()->to('/access_denied');	

		// On récupère la jam
		$data['jam_item'] = $jam_model->get_jam_id($jamId);
		
		// On récupère la playlist de la jam
		$data['playlist_item'] = $playlist_model->get_playlist_versions($data['jam_item']['playlistId']);
		if (! $data['playlist_item']) $data['playlist_item'] = "null";
		
		// On récupère l'event path
		//$data['event_path'] = base_url()."/ressources/event/".$this->jam_model->get_dirPath($data['jam_item']['id']);

		// On récupère les catégories d'instrument
		//$data['instru_cat'] = $this->instruments_model->get_instru_categories();
		

		// On lance la vue du formulaire
		echo view('jam/generate_file', $data);
		
	}
	
	
	// Fonctions de callback ////////////////////////////////////////////////

	public function check_new_jam() {
	
		/*if($this->input->post('title')) {
			$slug = url_title($this->input->post('title'), 'dash', TRUE);
			$this->db->select('id');
			$this->db->from('jam');
			$this->db->where('slug',$slug);
			if ($this->db->count_all_results()>0) {
				$this->form_validation->set_message('check_new_jam','Ce titre de jam est déjà pris');
				return false;
			}
			else {
				return true;
			}
		}*/
	}
	
	
	public function check_update_jam() {
	
		/*$actual_slug = $this->uri->segment(3);
		$form_slug = url_title($this->input->post('title'), 'dash', TRUE);
		
		if ($actual_slug) {
			// On récupère le jam de l'url dans la base
			$jam_item = $this->jam_model->get_jam($actual_slug);
	
			if($this->input->post('title')) {
				$slug = url_title($this->input->post('title'), 'dash', TRUE);
				$this->db->select('id');
				$this->db->from('jam');
				$this->db->where('slug',$slug);
				if ($this->db->count_all_results()>0 && $form_slug != $jam_item['slug']) {
					$this->form_validation->set_message('check_update_jam','Ce titre de jam est déjà pris');
					return false;
				}
				else {
					return true;
				}
			}
		}*/
	}
	
	
}