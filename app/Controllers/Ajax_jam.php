<?php namespace App\Controllers;

use App\Models\Jam_model;
use App\Models\Fichier_model;
use App\Models\Stage_model;
use App\Models\Members_model;
use App\Models\Repetition_model;
use App\Models\Playlist_model;
use App\Models\Invitation_model;

class Ajax_jam extends BaseController {

	public function index() {
	}

	
	
	//**********************  JAM  ************************//
	public function create_jam() {
	
		log_message("debug","******** ajax_jam :: create_jam ********");
	
		$msg = "";
		$state = true;
	
		if($this->session->superAdmin) {
	
			$jam_model = new Jam_model();
	
			// On récupère les nouvelles data de la jam
			$title = trim($_POST['title']);
			$date_jam = trim($_POST['date_jam']);		
		
			// On créé la nouvelle slug
			$slug = url_title($title, 'dash', TRUE);
			
			// On vérifie qu'il n'y a pas de conflit avec une autre jam
			$test_jam = $jam_model->get_jam($slug);
			if (!is_null($test_jam)) {
				$return_data = array(
					'state' => false,
					'data' => "Ce nom de jam existe déjà dans la base de donnée !"
				);
				$output = json_encode($return_data);
				echo $output;
				return;
			}
		
			// On récupère la date de la jam
			$tmp = explode("/", $date_jam);
			$date_iso = $tmp[2]."-".$tmp[1]."-".$tmp[0];


			$data_jam = array(
				'title' => $this->request->getVar('title'),
				'slug' => $slug,
				'date' => $date_iso,
				'lieuxId' => -1
			);
			$id_jam = $jam_model->set_jam($data_jam);
			
			
			// On créé le répertoire de la jam
			$destPath = FCPATH."/ressources/event/".dir_path($date_iso.'_'.$slug);
			if (!is_dir($destPath)) $state = mkdir($destPath,0755);
			
			if (!$state) $msg = 'Le système a rencontré un problème au moment de la création du répertoire de la jam';
			else $msg = site_url("jam/").$slug;
		}
		
		else {
			$state = false;
			$msg = 'Votre statut sur le site ne vous permet pas d\'accéder à cette page';
		}
		
		$return_data = array(
			'state' => $state,
			'data' => $msg
		);
		$output = json_encode($return_data);
		echo $output;
	}
	
	
	
	/**************************** UPDATE JAM ***************************/
	public function update_jam($slug) {	
	
		log_message("debug","******** ajax_jam :: update_jam ********");
		
		// On récupère les nouvelles data de la jam
		$new_title = trim($_POST['title']);
		$date_jam = trim($_POST['date_jam']);
		$date_bal = trim($_POST['date_bal']);
		$date_debut = trim($_POST['date_debut']);
		$date_fin = trim($_POST['date_fin']);
		$formationId = trim($_POST['formationId']);
		$lieuId = trim($_POST['lieuId']);
		$text_html = trim($_POST['text_html']);
		$acces_jam = trim($_POST['acces_jam']);
		$max_inscr = trim($_POST['max_inscr']);
		$playlistId = trim($_POST['playlistId']);
		$acces_inscr = trim($_POST['acces_inscr']);
		$show_affect = trim($_POST['show_affect']);
		
		// Stage
		$stage = trim($_POST['stage']);
		$stage_text_html = trim($_POST['stage_text_html']);
		$stage_lieuId = trim($_POST['stage_lieuId']);
		$stage = trim($_POST['stage']);
		$duree = trim($_POST['duree']);
		$stage_date_debut = trim($_POST['stage_date_debut']);
		$stage_date_limit = trim($_POST['stage_date_limit']);
		$cotis = trim($_POST['cotis']);
		$ordre = trim($_POST['ordre']);
		$adresse_cheque = trim($_POST['adresse_cheque']);
		
	
		$jam_model = new Jam_model();
		$stage_model = new Stage_model();
	
		// On créé la nouvelle slug
		$new_slug = url_title($new_title, 'dash', TRUE);
		
		// On vérifie qu'il n'y a pas de conflit avec une autre jam dans le cas où on change de nom de jam
		if ($slug != $new_slug) {
			$test_jam = $jam_model->get_jam($new_slug);
			if (!is_null($test_jam)) {
				$return_data = array(
					'state' => false,
					'data' => "Ce nom de jam existe déjà dans la base de donnée !"
				);
				$output = json_encode($return_data);
				echo $output;
				return;
			}
		}
	
		// On récupère la date de la jam
		$tmp = explode("/", $date_jam);
		$date_iso = $tmp[2]."-".$tmp[1]."-".$tmp[0];
		
		// RENAME DIRECTORY -- On récupère la jam et sa date
		$jam_item = $jam_model->get_jam($slug);
		$date = date_create_from_format("Y-m-d",$jam_item['date']);
		$old_date = date_format($date,"Y-m-d");
		
		// On récupère les horaires du planning
		$date_bal_iso = $date_iso." ".$date_bal.":00.000001";
		$date_debut_iso = $date_iso." ".$date_debut.":00.000001";
		$date_fin_iso = $date_iso." ".$date_fin.":00.000001";

	
		$data_jam = array(
			'title' => $new_title,
			'slug' => $new_slug,
			'date' => $date_iso,
			'text_html' => $text_html,
			'lieuxId' => $lieuId,
			'formationId' => $formationId,
			'date_bal' => $date_bal_iso,
			'date_debut' => $date_debut_iso,
			'date_fin' => $date_fin_iso,
			'acces_jam' => $acces_jam,
			'max_inscr' => $max_inscr,
			'playlistId' => $playlistId,
			'acces_inscriptions' => $acces_inscr == 'true' ? '1' : '0',
			'affectations_visibles' => $show_affect == 'true' ? '1' : '0',
		);
		$update_state = $jam_model->update_jam($slug,$data_jam);
		
		
		// RENAME DIRECTORY
		// On update le répertoire de l'event
		$rename_state = true;
		$state_msg = '';
		if ($update_state && ( $old_date != $date_iso || $slug != $new_slug) ) {
			$oldDestPath = FCPATH."/ressources/event/".dir_path($old_date.'_'.$slug);
			$newDestPath = FCPATH."/ressources/event/".dir_path($date_iso.'_'.$new_slug);
			if (is_dir($oldDestPath)) $rename_state = rename($oldDestPath, $newDestPath);
			else $rename_state = false;
		}
		
		
		// On gère la création du STAGE si besoin
		if ($stage != "false") {
		
			// On récupère la date de début de stage
			$tmp = explode("/", $stage_date_debut);
			$stage_date_debut_iso = $tmp[2]."-".$tmp[1]."-".$tmp[0];
			
			// On récupère la date de limite d'inscription au stage
			$tmp = explode("/", $stage_date_limit);
			$stage_date_limit_iso = $tmp[2]."-".$tmp[1]."-".$tmp[0];
			
			$data_stage = array(
				'jamId' => $jam_item['id'],
				'lieuxId' => $stage_lieuId,
				'text_html' => isset($stage_text_html) ? $stage_text_html : "",
				'duree' => $duree,
				'date_debut' => $stage_date_debut_iso,
				'date_limit' => $stage_date_limit_iso,
				'cotisation' => $cotis,
				'ordre' => $ordre,
				'adresse_cheque' => $adresse_cheque
			);
			
			// On gère la création ou l'update
			$stage_model->update_stage($data_stage,$jam_item['id']);
			
		}
		// On gère la supression de l'ancien stage si besoin
		else if ($jam_item['id'] != -1) {
			$stage_model->delete_stage_jamId($jam_item['id']);
		}
			
		$msg = "";
		if (!$update_state) $msg = "La mise à jour de la base de donnée ne s'est pas effectuée convenablement.";
		if (!$rename_state) $msg .= "\nIl y eu un problème lors du renommage du dossier de la jam.";

		$state = ($update_state && $rename_state);
		if ($state) $msg = $new_slug;
		
		$return_data = array(
			'state' => $state,
			'data' => $msg
		);
		$output = json_encode($return_data);
		echo $output;
	}
	
	
	/**************************** DELETE JAM ***************************/
	public function delete_jam() {
	
		log_message("info","******** ajax_jam :: delete_jam ********");
	
		$slug = trim($_POST['jamSlug']);
		
		$jam_model = new Jam_model();
		$fichier_model = new Fichier_model();
		
		// On récupère la jam pour le delete dir
		$jam = $jam_model->get_jam($slug);

		// On supprime tous les fichiers du répertoire
		$destPath = FCPATH."/ressources/event/".dir_path($jam['date'].'_'.$jam['slug']);
		if (is_dir($destPath)) {
			$dir = $destPath;
			$di = new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS);
			$ri = new \RecursiveIteratorIterator($di, \RecursiveIteratorIterator::CHILD_FIRST);
			foreach ( $ri as $file ) {
				$file->isDir() ?  rmdir($file) : unlink($file);
			}
			// On supprime le répertoire vide
			rmdir($destPath);
		}
		
		// On remove les entrées de fichiers dans la BD
		$file_list = $fichier_model->get_fichiers_parent('event', $jam['id']);
		foreach ($file_list as $file) {
			$fichier_model->remove_fichier($file);
		}
		
		// On delete la jam de la BD
		$jam_model->delete_jam($slug);
		
		
		$return_data = array(
			'state' => true,
			'data' => "La jam a été supprimée de la base de donnée ainsi que tous les fichiers associés."
		);
		$output = json_encode($return_data);
		echo $output;
	}
	
	
	
	
	/***********************   UPDATE TEXT TAB  *************************/
	// Pour actualiser le texte d'info de la jam (écran tableau d'inscription)
	public function update_text_tab() {
	
		$jamId = trim($_POST['jamId']);
		$new_text_tab = trim($_POST['text_tab']);
		
		log_message('debug',"****** ajax_jam : new_text_tab : ".$new_text_tab);
		
		$jam_model = new Jam_model();
		
		if ($jamId != 'null') {
			$jam = $jam_model->get_jam_id($jamId);
			$jam["text_tab"] = $new_text_tab;
			$state = $jam_model->update_jam($jam['slug'],$jam);
		}
		else $state = false;
		
		
		$return_data = array(
			'state' => $state,
			'data' => ""
		);
		$output = json_encode($return_data);
		echo $output;
	}
	
	
	/***********************   PLAYLIST INDEX  *************************/
	// Permet de récupérer l'index de la jam par rapport à la playlist
	public function get_playlistIndex() {
	
		$jamId = trim($_POST['jamId']);
		
		$jam_model = new Jam_model();
				
		if ($jamId != 'null') {
			$state = true;
			$playIndex = $jam_model->get_playlistIndex($jamId);
		}
		else $state = false;
		
		
		$return_data = array(
			'state' => $state,
			'data' => $state ? $playIndex : "0"
		);
		$output = json_encode($return_data);
		echo $output;
	}
	
	// Permet de fixer l'index de la jam par rapport à la playlist
	public function set_playlistIndex() {
	
		$jam_model = new Jam_model();
	
		$jamId = trim($_POST['jamId']);
		$playlistIndex = trim($_POST['playlistIndex']);
				
		if ($jamId != 'null') {
			$state = true;
			$state = $jam_model->set_playlistIndex($jamId, $playlistIndex);
		}
		else $state = false;
		
		
		$return_data = array(
			'state' => $state,
			'data' => ""
		);
		$output = json_encode($return_data);
		echo $output;
	}
	
	
	/***********************   INSCRIPTION A LA JAM VIA BOUTON ou INVITATION *************************/
	public function join_jam() {
	
		$slugJam = trim($_POST['slugJam']);
		$memberId = trim($_POST['id']);
		$event_admin = trim($_POST['event_admin']);
		
		$jam_model = new Jam_model();
		$members_model = new Members_model();
		$invitation_model = new Invitation_model();
		
		// On récupère le membre et la jam
		$member = $members_model->get_member_by_id($memberId);
		$jam = $jam_model->get_jam($slugJam);
		
		// On insère un participant
		if ($event_admin == 0  &&  !$jam_model->is_included($jam['id'],$member->id)) {

			$jam_model->join_member($jam['id'],$member->id, 0);
			
			// On vérifié s'il le participant était invité, si oui, on actualise l'état de son invitation
			$invit = $invitation_model->where( [ 'targetTag' => 2, 'targetId' => $jam['id'], 'receiverId' => $member->id  ] )->first();
			if ($invit != null) $invitation_model->update($invit["id"], [ 'state' => 1 ] );
			
			$msg = "Inscription à la jam validée !";
			$state = true;
		}
		// On insère un admin
		else if ($event_admin == 1 && !$jam_model->is_admin($jam['id'],$member->id)) {
			$jam_model->join_member($jam['id'],$member->id, 1);
			$msg = "Inscription de l'administrateur '<b>".$member->pseudo."</b>' validée !";
			$state = true;
		}
		else {
			$msg = "Inscription déjà enregistrée !";
			$state = false;
		}
		
		$return_data = array(
			'state' => $state,
			'data' => $msg
		);
		$output = json_encode($return_data);
		echo $output;

	}
	
	/***********************   DESINSCRIPTION A LA JAM VIA BOUTON *************************/
	public function quit_jam() {
	
		$slugJam = trim($_POST['slugJam']);
		$memberId = trim($_POST['id']);
		
		$jam_model = new Jam_model();
		$members_model = new Members_model();
		$stage_model = new Stage_model();
		$invitation_model = new Invitation_model();
		
		// Pour récupérer l'id du membre et de la jam
		$member = $members_model->get_member_by_id($memberId);
		$jam = $jam_model->get_jam($slugJam);
		
		
		// Si la jam a un stage, on delete son inscription éventuel au stage
		if ($stage_model->got_stage($jam['id'])) {
			$stage = $stage_model->get_stage_jamId($jam['id']);
			$stage_model->unjoin_stage_member($jam['id'], $stage['id'], $memberId);
		}

		$jam_model->unjoin_member($jam['id'],$member->id);
		
		// On vérifié s'il le participant était invité, si oui, on actualise l'état de son invitation
		$invit = $invitation_model->where( [ 'targetTag' => 2, 'targetId' => $jam['id'], 'receiverId' => $member->id  ] )->first();
		if ($invit != null) $invitation_model->update($invit["id"], [ 'state' => 0 ] );
		
		$return_data = array(
			'state' => true,
			'data' => "Votre désinscription a bien été prise en compte."
		);
		$output = json_encode($return_data);
		echo $output;
	}
	
	
	/***********************   INSCRIPTIONS A LA JAM VIA ADMIN *************************/
	
	// Ajouter un membre à la jam, utilisé par les admin quand la jam est full et qu'on veut ajouter un membre via le manage panel
	public function add_inscription() {
		
		$jamId = trim($_POST['jamId']);
		$memberId = trim($_POST['memberId']);
		
		$jam_model = new Jam_model();
		$members_model = new Members_model();
		
		// Pour récupérer l'id du membre et de la jam
		$member = $members_model->get_member_by_id($memberId);
		$jam = $jam_model->get_jam_id($jamId);
		
		//log_message('debug','****** '.json_encode($member).'      **********   '.$memberId);
		
		// On insère un participant
		if (!$jam_model->is_included($jam['id'],$member->id)) {
			$jam_model->join_member($jam['id'],$member->id);
			$msg = "Inscription à la jam validée !";
			$state = true;
		}
		else {
			$msg = "Inscription déjà enregistrée !";
			$state = false;
		}
		
		$return_data = array(
			'state' => $state,
			'data' => $msg
		);
		$output = json_encode($return_data);
		echo $output;
	}
	
	
	
	/***********************   DESINSCRIPTIONS A LA JAM  *************************/
	// Permet à un admin de suppr l'inscription d'un membre
	public function delete_inscr() {
		
		$jamId = trim($_POST['jamId']);
		$pseudo = trim($_POST['pseudo']);
		
		$jam_model = new Jam_model();
		$members_model = new Members_model();
		
		$member = $members_model->get_member($pseudo);
		
		// Si la jam a un stage, on delete son inscription éventuel au stage
		/*if ($this->stage_model->got_stage($jamId)) {
			$stage = $this->stage_model->get_stage_jamId($jamId);
			$this->stage_model->unjoin_stage_member($jamId, $stage['id'], $member->id);
		}*/
		
		$jam_model->unjoin_member($jamId, $member->id);
		echo "success";
	}
	
	
	/***********************   DESINSCRIPTIONS AU STAGE  *************************/
	// Permet à un admin de suppr l'inscription d'un membre
	public function delete_inscr_stage() {
		
		$jamId = trim($_POST['jamId']);
		$stageId = trim($_POST['stageId']);
		$pseudo = trim($_POST['pseudo']);
		
		$members_model = new Members_model();
		$stage_model = new Stage_model();
		
		$member = $members_model->get_member($pseudo);
		$stage_model->unjoin_stage_member($jamId, $stageId, $member->id);
		echo "success";
	}
	
	/***********************   INSCRIPTIONS A UN MORCEAU  *************************/
	
	public function set_inscription() {

		log_message('debug', "************ ajax_jam :: set_inscription");
	
		// On récupère les infos de la répétition
		$versionId = trim($_POST['versionId']);
		$elemId = trim($_POST['elemId']);
		$memberId = trim($_POST['memberId']);
		$jamSlug = trim($_POST['jamSlug']);
	
		$jam_model = new Jam_model();
		$members_model = new Members_model();
		
		// On récupère la jam
		$jam_item = $jam_model->get_jam($jamSlug);

		// On récupère le membre
		$member_item = $members_model->get_member_by_id($memberId);
		
		$data = array(
			'jamId' => $jam_item["id"],
			'hasFormation' => $jam_item["formationId"] > 0,
			'membresId' => $member_item->id,
			'versionId' => $versionId,
			'elemId' => $elemId
		);
		
		$choicePos = $jam_model->set_inscription($data);
		
		// On retourne la choicePos de l'inscription effectuée
		$return_data = array(
			'state' => $choicePos > 0 ? true : false,
			'data' => $choicePos
		);
		$output = json_encode($return_data);
		echo $output;
		
	}
	
	
	/***********************   DESINSCRIPTIONS A UN MORCEAU  *************************/
	public function delete_inscription() {

		log_message('debug', "************ ajax_jam :: delete_inscription");
	
		// On récupère les infos de la répétition
		$versionId = trim($_POST['versionId']);
		$elemId = trim($_POST['elemId']);
		$memberId = trim($_POST['memberId']);
		$jamSlug = trim($_POST['jamSlug']);
	
		$jam_model = new Jam_model();
		$members_model = new Members_model();
		
		// On récupère la jam
		$jam_item = $jam_model->get_jam($jamSlug);

		// On récupère le membre
		$member_item = $members_model->get_member_by_id($memberId);
		
		$data = array(
			'jamId' => $jam_item["id"],
			'hasFormation' => $jam_item["formationId"] > 0,
			'membresId' => $member_item->id,
			'versionId' => $versionId,
			'elemId' => $elemId
		);
		
		$deletedChoicepos = $jam_model->delete_inscription($data);
		
		// On retourne la choicePos de l'inscription effacée
		$return_data = array(
			'state' => true,
			'data' => $deletedChoicepos
		);
		$output = json_encode($return_data);
		echo $output;
		
	}
	
	
	//****************** UPDATE INSCRIPTION LIST (UPDATE ORDER CHOICE) ********************//
	public function update_inscription() {

		log_message('debug', "************ ajax_jam :: update_inscription");
	
		// On récupère les infos de la répétition
		$memberId = trim($_POST['memberId']);
		$jamSlug = trim($_POST['jamSlug']);
		$choiceList = json_decode($_POST["choiceList"]);
		
		$jam_model = new Jam_model();
		$members_model = new Members_model();
		
		// On récupère la jam
		$jam_item = $jam_model->get_jam($jamSlug);

		// On récupère le membre
		$member_item = $members_model->get_member_by_id($memberId);
		
		$data = array(
			'jamId' => $jam_item["id"],
			'membresId' => $member_item->id,
		);
		
		$state = $jam_model->update_inscription_list($data, $choiceList);
		
		// On retourne la choicePos de l'inscription effectuée
		$return_data = array(
			'state' => $state,
			'data' => ""
		);
		$output = json_encode($return_data);
		echo $output;
		
	}
	
	
	
	/*********************************   AFFECTATION   *****************************************/
	public function set_affectation() {
	
		log_message('debug', "************ ajax_jam :: set_affectation");
	
		// On récupère les infos de la jam
		$jamId = trim($_POST['jamId']);
		$versionId = trim($_POST['versionId']);
		$posteId = trim($_POST['posteId']);
		$memberId = trim($_POST['memberId']);
		$prevId = trim($_POST['prevId']);
		//$delete_affect = trim($_POST['delete_affect']);

		//log_message('debug', "memberId :: $memberId");
		$jam_model = new Jam_model();
		
		$data = array(
			'jamId' => $jamId,
			'membresId' => $memberId,
			'versionId' => $versionId,
			'posteId' => $posteId
		);
		
		$old_data = array(
			'jamId' => $jamId,
			'membresId' => $prevId,
			'versionId' => $versionId,
			'posteId' => $posteId
		);
		
		
		// On gère le delete de l'ancienne affectation
		if ($prevId > 0 || $memberId == 0) $state = $jam_model->delete_affectation($old_data);
		
		// On insère la nouvelle affectation
		if ($memberId > 0) $state = $jam_model->set_affectation($data);
		
		// On retourne la choicePos de l'inscription effectuée
		$return_data = array(
			'state' => $state,
			'data' => ''
		);
		$output = json_encode($return_data);
		echo $output;
	}
	
	
	
	public function show_affectations() {
	
		log_message('debug', "************ ajax_jam :: show_affectations");
	
		// On récupère les infos de la jam
		$jamId = trim($_POST['jamId']);
		$show = trim($_POST['show']);

		$jam_model = new Jam_model();
		
		// On récupère la jam
		$jam = $jam_model->get_jam_id($jamId);
		$jam["affectations_visibles"] = $show;
		
		// On update la jam
		$state = $jam_model->update_jam($jam["slug"], $jam);
		
		// On retourne l'état de la transaction
		$return_data = array(
			'state' => $state,
			'data' => ''
		);
		$output = json_encode($return_data);
		echo $output;
	}
	
	
	
	/*****************************************   REPETITIONS   *************************************/
	
	/////////////// Créer une répétition
	public function create_repetition($slug) {

		// On récupère les infos de la répétition
		$date_repet = trim($_POST['date_repet']);
		$date_debut = trim($_POST['date_debut']);
		$date_fin = trim($_POST['date_fin']);
		$lieuId = trim($_POST['lieuId']);
		$text = trim($_POST['text']);
		$pupitreId = trim($_POST['pupitreId']);
	
		$repetition_model = new Repetition_model();
		$jam_model = new Jam_model();
		
		// On récupère la jam
		$jam_item = $jam_model->get_jam($slug);

		// On récupère la date de la répét
		$tmp = explode("/", $date_repet);
		$date_iso = $tmp[2]."-".$tmp[1]."-".$tmp[0];
		
		// On récupère les horaires du planning
		$date_debut_iso = $date_iso." ".$date_debut.":00.000001";
		$date_fin_iso = $date_iso." ".$date_fin.":00.000001";
		
		$data = array(
			'jamId' => $jam_item["id"],
			'lieuxId' => $lieuId,
			'pupitreId' => $pupitreId,
			'date_debut' => $date_debut_iso,
			'date_fin' => $date_fin_iso,
			'text' => nl2br($text),
		);
		
		$state = $repetition_model->set_repetition($data);
		
		
		$return_data = array(
			'state' => $state > 0 ? true : false,
			'data' => ""
		);
		$output = json_encode($return_data);
		echo $output;
		
	}
	
	
	/////////////// Update une répétition
	public function update_repetition($repet_id) {
	
		// On récupère les infos de la répétition
		$date_repet = trim($_POST['date_repet']);
		$date_debut = trim($_POST['date_debut']);
		$date_fin = trim($_POST['date_fin']);
		$lieuId = trim($_POST['lieuId']);
		$text = trim($_POST['text']);
		$pupitreId = trim($_POST['pupitreId']);
	
		$repetition_model = new Repetition_model();
		
		log_message('debug',"*** update_repetition : ".$lieuId);
		
		// On récupère la date de la répét
		$tmp = explode("/", $date_repet);
		$date_iso = $tmp[2]."-".$tmp[1]."-".$tmp[0];
		
		// On récupère les horaires du planning
		$date_debut_iso = $date_iso." ".$date_debut.":00.000001";
		$date_fin_iso = $date_iso." ".$date_fin.":00.000001";
		
		$data = array(
			//'jamId' => $jam_item["id"],
			'lieuxId' => $lieuId,
			'pupitreId' => $pupitreId,
			'date_debut' => $date_debut_iso,
			'date_fin' => $date_fin_iso,
			'text' => nl2br($text),
		);
		
		$state = $repetition_model->update_repetition($repet_id, $data);
		
		
		$return_data = array(
			'state' => $state > 0 ? true : false,
			'data' => ""
		);
		$output = json_encode($return_data);
		echo $output;
		
	}
	
	/////////////// Delete une répétition
	public function delete_repet() {
	
		$repetId = trim($_POST['repetId']);
		$login = trim($_POST['login']);
		
		$repetition_model = new Repetition_model();
		
		// Pour récupérer l'id du membre et de la jam
		//$membre = $this->members_model->get_members($login);

		// On supprime la répétition
		$repetition_model->delete_repetition($repetId);

		$return_data = array(
			'state' => true,
			'data' => ""
		);
		$output = json_encode($return_data);
		echo $output;
		
	}
	
	
	/*****************************************   ADMINISTRATEURS   *************************************/
	
	// Permet de récupérer la list d'admin d'un event
	public function get_event_admin() {
		
		$jamId = trim($_POST['jamId']);
		//$tache = trim($_POST['tache']);
		
		$jam_model = new Jam_model();
		
		$list_admin = $jam_model->get_event_admin($jamId);
		
		$output = json_encode($list_admin);
		echo $output;
	}
	
	
	// Permet de retirer un admin d'un event
	public function remove_event_admin() {
		
		$jamId = trim($_POST['jamId']);
		$memberId = trim($_POST['memberId']);
		
		$jam_model = new Jam_model();
		$members_model = new Members_model();
		
		// On récupère le membre admin à retirer
		$member = $members_model->get_member_by_id($memberId);
		
		$jam_model->remove_event_admin($jamId,$memberId);
		
		$return_data = array(
			'state' => true,
			'data' => "L'administrateur '<b>".$member->pseudo."</b>' a bien été retiré de la liste."
		);
		$output = json_encode($return_data);
		echo $output;
	}
	
	
	/********************************   AJOUTE UNE JAMINFO  *********************************/
	public function add_jamInfo() {
	
		$jamId = trim($_POST['jamId']);
		$tag1 = trim($_POST['tag1']);
		$tag1Val = trim($_POST['tag1Val']);
		$tag2 = trim($_POST['tag2']);
		$tag2Id = trim($_POST['tag2Id']);

		
		$jam_model = new Jam_model();
		
		// On créé la jamInfo
		$jamInfo_item = array (
			'jamId' => $jamId,
			'tag1' => $tag1,
			'tag1Val' => $tag1Val,
			'tag2' => $tag2,
			'tag2Id' => $tag2Id
		);

		// On insère la jamInfo dans la BD
		$insertId = $jam_model->add_jamInfo($jamInfo_item);
		
		// On récupère le tag2Label (pour affichage ajax)
		$tag2Label = $jam_model->get_tag2Label($tag2,$tag2Id);
		
		$return_data = array (
			'state' => true,
			'data' => array ( "insertId" => $insertId, "tag2Label" => $tag2Label )
		);
		$output = json_encode($return_data);
		echo $output;

	}
	
	/*************************   RECUPERE UNE JAMINFO DEFINI PAR jamID et le tag1  *********************************/
	public function get_jamInfos() {
	
		$jamId = trim($_POST['jamId']);
		$tag1 = trim($_POST['tag1']);

		$jam_model = new Jam_model();
		
		// On récupère les infos correspondantes
		$jamInfos = $jam_model->get_jamInfos($jamId, $tag1);
		
		$return_data = array (
			'state' => true,
			'data' => $jamInfos
		);
		$output = json_encode($return_data);
		echo $output;
	}
	
	
	// Permet de retirer un admin d'un event
	public function delete_jamInfo() {
		
		$jamInfoId = trim($_POST['jamInfoId']);

		$jam_model = new Jam_model();
		
		$jam_model->delete_jamInfo($jamInfoId);
		
		$return_data = array(
			'state' => true,
			'data' => "La jamInfo '<b>".$jamInfoId."</b>' a bien été retiré de la bd."
		);
		$output = json_encode($return_data);
		echo $output;
	}
	
	
	/*****************************************   FICHIERS   *************************************/
	
	// On récupère le fichier d'affectation
	public function get_files() {
		
		$jam_model = new Jam_model();
		$fichier_model = new Fichier_model();
		
		helper('filesystem');
		
		// Conversion de bytes à megabytes
		function formatBytes($bytes, $precision = 2) { 
			$units = array('B', 'KB', 'MB', 'GB', 'TB'); 

			$bytes = max($bytes, 0); 
			$pow = floor(($bytes ? log($bytes) : 0) / log(1024)); 
			$pow = min($pow, count($units) - 1); 

			// Uncomment one of the following alternatives
			$bytes /= pow(1024, $pow);
			// $bytes /= (1 << (10 * $pow)); 

			return round($bytes, $precision).' '.$units[$pow]; 
		}
		
		$jamId = trim($_POST['jamId']);
		
		// On récupère les fichiers liés à la jam
		$fichier_list = $fichier_model->get_fichiers_parent('event',$jamId);
		
		// On récupère le path physique
		$basePath = $jam_model->get_file_path($jamId);
		
		// On vérifie l'existence des fichiers et on complète les infos (file_size)
		foreach ($fichier_list as $fichier) {
			
			// On regarde si le fichier existe
			$filePath = $basePath."/".$fichier->fileName;
			if (file_exists($filePath)) {
				$file_infos = get_file_info($filePath);
				$fichier->sizeMo = formatBytes($file_infos["size"]);
			}
		
			// Si le fichier n'existe pas, on met sa taille à -1
			else $fichier->sizeMo = "-1";
		}
		
		// On retourne la liste + jamURL
		$return_data = array(
			'state' => true,
			'data' => $fichier_list,
			'jamURL' => base_url("ressources/event")."/".$jam_model->get_dirPath($jamId)
		);
		$output = json_encode($return_data);
		echo $output;
	}
	
	
	
	// On génére un zip de la playlist et on renvoit le file_info
	public function generate_playlist_file() {
	
		$playlistId = trim($_POST['playlistId']);
		$file_type = trim($_POST['file_type']);
		$sort = trim($_POST['sort']);
		
		$fileName = trim($_POST['fileName']);
		$parentId = trim($_POST['parentId']);   // le parentType est forcément 'event'
		$accessType = trim($_POST['accessType']);
		$accessId = trim($_POST['accessId']);
		$pdfType = trim($_POST['pdfType']);    // pour les selection de pdf dans les medias
		$pdfId = trim($_POST['pdfId']);
		$memberId = trim($_POST['memberId']);
		$text = trim($_POST['text']);
		$admin = trim($_POST['admin']);
		
		$jam_model = new Jam_model();
		$playlist_model = new Playlist_model();
		$fichier_model = new Fichier_model();
		
		// On récupert la playlist
		$playlist = $playlist_model->get_playlist_versions($playlistId);
		
		// On récupère le filePath
		$filePath = $jam_model->get_file_path($parentId)."/".$fileName.".".$file_type;
	
		// Si le fichier existe déjà à l'emplacement $filePath
		if (file_exists($filePath)) {
			$file_infos = "error";
			$msg_error = "Error : Le fichier <b>".$fileName.".".$file_type."</b> existe déjà sur le serveur !";
		}
		
		else {
			// On traite les zip de mp3 et on génère le fichier correspondant
			if ($file_type == "zip") $file_infos = $playlist_model->generate_zipmp3($playlist, $filePath);
			else if ($file_type == "pdf") $file_infos = $playlist_model->generate_pdf($playlist, $filePath, $sort, $pdfType, $pdfId);
			else $file_infos = "Error : le type de fichier à créer '$file_type' n'est pas reconnu.";
			
			// ... et on insère dans la BD
			if ( ! (is_string($file_infos) && strtolower(substr($file_infos, 0, 5)) === 'error')) {
				$data_fichier = array(
					'fileName' => $fileName.".".$file_type,
					'memberId' => $memberId,
					'parentType' => 'event',
					'parentId' => $parentId,
					'accessType' => $accessType,
					'accessId' => $accessId,
					'text' => $text,
					'admin' => $admin
				);
				$fichier_model->set_fichier($data_fichier);
			}
			else $msg_error = $file_infos;
		}
		
		// On retourne les infos du fichiers créé			
		$return_data = array(
			'state' => !is_string($file_infos),
			'data' => is_string($file_infos) ? $msg_error : $file_infos
		);
		$output = json_encode($return_data);
		echo $output;
		
	}

	
	// On récupère le fichier d'affectation
	public function get_affect_file() {
		
		$jam_model = new Jam_model();
		$fichier_model = new Fichier_model();
		
		helper('filesystem');

		// Conversion de bytes à megabytes
		function formatBytes($bytes, $precision = 2) { 
			$units = array('B', 'KB', 'MB', 'GB', 'TB'); 

			$bytes = max($bytes, 0); 
			$pow = floor(($bytes ? log($bytes) : 0) / log(1024)); 
			$pow = min($pow, count($units) - 1); 

			// Uncomment one of the following alternatives
			$bytes /= pow(1024, $pow);
			// $bytes /= (1 << (10 * $pow)); 

			return round($bytes, $precision).' '.$units[$pow]; 
		}
		
		$jamId = trim($_POST['jamId']);
				
		$affect_file = $fichier_model->get_fichiers_parent('event',$jamId,'affect');
		
		if ($affect_file) {
			// On regarde si le fichier existe
			$filePath = $jam_model->get_file_path($jamId)."/".$affect_file->fileName;

			if (file_exists($filePath)) {
				$file_infos = get_file_info($filePath);
				$affect_file->sizeMo = formatBytes($file_infos["size"]);
			}
		
			echo json_encode($affect_file);
		}
		else echo "ERROR";
	}


	
	// On génére un pdf de recap des affectations
	public function generate_affect_file() {
	
		$jamId = trim($_POST['jamId']);
		$memberId = trim($_POST['memberId']);
		
		$fileName = "Affectations.pdf";
		
		$jam_model = new Jam_model();
		$fichier_model = new Fichier_model();
		
		// On récupère le filePath
		$filePath = $jam_model->get_file_path($jamId)."/".$fileName;

		// Si le fichier existe déjà à l'emplacement $filePath
		if (file_exists($filePath)) {
			echo "Error : Le fichier <b>".$fileName."</b> existe déjà sur le serveur !";
			return;
		}
		
		// On créé le fichier d'affectation
		$file_infos = $jam_model->generate_affect_pdf($jamId, $filePath);
		
		log_message('debug', '********* ajax_jam : generate_affect_file : file_infos : '.json_encode($file_infos));
		
		if ($file_infos != false) {
			$data_fichier = array(
				'fileName' => $fileName,
				'memberId' => $memberId,
				'parentType' => 'event',
				'parentId' => $jamId,
				'accessType' => 'affect',
				'accessId' => '',
				'text' => 'Fichier faisant la récapitulation des affectations',
				'admin' => 0
			);
			$fileId = $fichier_model->set_fichier($data_fichier);
			$file_infos['fileId'] = $fileId;
		}

		
		$return_data = array(
			'state' => $file_infos != false,
			'data' => $file_infos != false ? $file_infos : "Une erreur a eu lieu à l'écriture du fichier ! \"jam_model->generate_affect_pdf\""
		);
		$output = json_encode($return_data);
		echo $output;
	}
	
	
	
	
	/*****************************************   PRESENTATION   *************************************/
	
	/***********************   UPDATE CREDITS  *************************/
	// Pour actualiser le texte d'info de la jam (écran tableau d'inscription)
	public function update_credits() {
	
		$jamId = trim($_POST['jamId']);
		$new_credits = trim($_POST['credits']);
		
		//log_message('debug',"********* ajax_jam :: new_credits : ".$new_credits);
		
		$jam_model = new Jam_model();
				
		if ($jamId != 'null') {
			$jam = $jam_model->get_jam_id($jamId);
			$jam["credits_html"] = $new_credits;
			$state = $jam_model->update_jam($jam['slug'],$jam);
		}
		else $state = false;
		
		
		$return_data = array(
			'state' => $state,
			'data' => ""
		);
		$output = json_encode($return_data);
		echo $output;
	}
	
}
?>