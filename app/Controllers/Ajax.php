<?php namespace App\Controllers;

use App\Models\Members_model;
use App\Models\Version_model;
use App\Models\Media_model;
use App\Models\Jam_model;
use App\Models\Playlist_model;
use App\Models\Lieux_model;
use App\Models\Instruments_model;
use App\Models\Message_model;
use App\Models\Discussion_model;
use App\Models\Invitation_model;

class Ajax extends BaseController {

	
	/***********************   GLOBAL   *************************/
	public function add_wish() {
	
		$slugJam = trim($_POST['slugJam']);
		$login = trim($_POST['login']);
		
		$title = trim($_POST['titre']);
		$url = trim($_POST['url']);
		
		$member_model = new Member_model();
		$jam_model = new Jam_model();
		$playlist_model = new Playlist_model();
		
		// Pour récupérer l'id du membre et de la jam
		$membre = $members_model->get_members($login);
		if ($slugJam != 'null') $jam = $jam_model->get_jam($slugJam);
		else $jam['id'] = null;
		
		$data_wish = array(
			'jamId' => $jam['id'],
			'membresId' => $membre->id,
			'titre' => $title,
			'url' => $url
		);
		$playlist_model->add_wish($data_wish);
	}
	
	
	
	/***********************   ARTISTE   *************************/
	
	public function get_artist() {
	
		$artist_name = trim($_POST['artist_name']);
		
		$artist = $this->artists_model->get_artist_by_label($artist_name);
		
		// Si le lieu n'existe pas
		if (!$artist) echo "artist_not_found";
		else {
			$output = json_encode($artist);
			echo $output;   
		}
	}
	
	
	public function get_artists() {
	
		$maxLength = trim($_POST['maxLength']);
		
		$data = $this->artists_model->get_artists();
		
		$output = json_encode($data);
		echo $output;
	}
	
	
	public function add_artist() {

	$artist_name = trim($_POST['name']);
		
		// On récupère le lieu pour être sûr qu'il n'existe pas à la création
		$artist = $this->artists_model->get_artist_by_label($artist_name);

		if (!isset($artist)) {
		
			$artist_item = array(
				'label' => $artist_name,
			);
		
			$this->artists_model->set_artist($artist_item);
			echo "success";
		
		}
		
		else echo "artist_found";
		
	}
	
	
	
	
	/***********************   PLAYLISTS   *************************/
	public function get_playlist() {
	
		$idPlaylist = trim($_POST['idPlaylist']);
		
		$playlist_model = new Playlist_model();

		// On récupère les titres de la playlist
		$playlist = $playlist_model->get_playlist_versions($idPlaylist);

		if (is_null($playlist)) {
			$state = false;
			$msg = "La playlist demandée n'existe pas dans la base de donnée";
		}
		else {
			$state = true;
			$msg = $playlist;
		}
		
		$return_data = array(
			'state' => $state,
			'data' => $msg
		);
		$output = json_encode($return_data);
		echo $output;

	}
	
	
	public function get_evt_playlists() {
	
		$idEvent = trim($_POST['idEvent']);
		// idGRO = 0  ==> tous les morceaux
		
		$jam_model = new Jam_model();
		$playlist_model = new Playlist_model();
		
		if ($idEvent != 0) {
			$jam = $jam_model->get_jam_id($idEvent);
			$playlist = $playlist_model->get_playlist_id($jam["playlistId"]);
		}
		// GRO
		else {
			$playlist = $playlist_model->get_playlist_id(0);
			$jam = $jam_model->get_jam_id(0);
		}

		$data = array(
			'playlist' => $playlist,
			'jam' => $jam
		);
		
		$output = json_encode($data);
		
		echo $output;
	}


	/***********************   LIEUX   *************************/
	
	public function add_location() {
	
		$lieu_name = trim($_POST['name']);
		$lieu_adresse = nl2br(trim($_POST['adresse']));
		$lieu_web = trim($_POST['web']);
		
		$lieux_model = new Lieux_model();
		
		// On récupère le lieu pour être sûr qu'il n'existe pas à la création
		$lieu = $lieux_model->get_lieux($lieu_name);
		
		if ($lieu == false) {
		
			$lieu_item = array(
				'nom' => $lieu_name,
				'adresse' => $lieu_adresse,
				'web' => $lieu_web
			);
		
			$lieux_model->set_lieux($lieu_item);
			echo "success";
		
		}
		
		else echo "lieu_found";
		
	}
	
	
	public function get_location() {
	
		$lieu_name = isset($_POST['lieu_name']) ? trim($_POST['lieu_name']) : null;
		$lieuId = isset($_POST['lieuId']) ? trim($_POST['lieuId']) : null;
		
		$lieux_model = new Lieux_model();
		
		log_message('debug', "******* ajax :: get_location : ".$lieuId);
		
		// On récupère le lieu
		if (isset($lieu_name)) $lieu = $lieux_model->get_lieux($lieu_name);
		else if (isset($lieuId)) $lieu = $lieux_model->get_lieux_by_id($lieuId);

		// Si le lieu n'existe pas
		if (!isset($lieu) || !$lieu || $lieu["id"] == -1 || $lieu["id"] == 0) echo "lieu_not_found";
		else {
			$output = json_encode($lieu);
			echo $output;   
		}
	}
	
	
	/*********************************   INVITATIONS   *****************************************/
	public function get_invitations() {
	
		//log_message('debug', "************ ajax :: get_invitations");
	
		$targetTag = trim($_POST['targetTag']);		// 1 : group	2 : jam		3 : repetition
		$targetId = trim($_POST['targetId']);

		$invitation_model = new Invitation_model();
		$members_model = new Members_model();

		// On récupère la liste des invitations
		$list_invit = $invitation_model->where( ['targetTag' => $targetTag, 'targetId' => $targetId] )->findAll();
		
		
		// On récupère les infos des membres
		for ($i = 0; $i < sizeof($list_invit); $i++) {
			// Sender
			$sender = $members_model->get_member_by_id($list_invit[$i]['senderId']);
			$list_invit[$i]['senderPseudo'] = $sender->pseudo;
			$list_invit[$i]['senderNom'] = $sender->nom;
			$list_invit[$i]['senderPrenom'] = $sender->prenom;
			// Receiver
			$receiver = $members_model->get_member_by_id($list_invit[$i]['receiverId']);
			$list_invit[$i]['receiverPseudo'] = $receiver->pseudo;
			$list_invit[$i]['receiverNom'] = $receiver->nom;
			$list_invit[$i]['receiverPrenom'] = $receiver->prenom;
			$members_model->get_mainInstru($receiver);
			$list_invit[$i]["receiverMainInstruName"] = $receiver->mainInstruName;
		}
		
		
		// On retourne la liste
		$return_data = array(
			'state' => true,
			'data' => $list_invit
		);
		$output = json_encode($return_data);
		echo $output;
	}
	
	
	
	public function add_invitation() {
	
		//log_message('debug', "************ ajax :: add_invitation");
	
		// On récupère les infos de l'invitation
		$senderId = trim($_POST['senderId']);
		$receiverId = trim($_POST['receiverId']);
		$targetTag = trim($_POST['targetTag']);		// 1 : group	2 : jam		3 : repetition
		$targetId = trim($_POST['targetId']);

		$invitation_model = new Invitation_model();
		$members_model = new Members_model();
		
		$data = array(
			'senderId' => $senderId,
			'receiverId' => $receiverId,
			'targetTag' => $targetTag,
			'targetId' => $targetId
		);
		
		// On insère l'invitation dans la BD
		$insertId = $invitation_model->insert($data);

		// Sender
		$sender = $members_model->get_member_by_id($senderId);
		$data['senderPseudo'] = $sender->pseudo;
		$data['senderNom'] = $sender->nom;
		$data['senderPrenom'] = $sender->prenom;
		// Receiver
		$receiver = $members_model->get_member_by_id($receiverId);
		$data['receiverPseudo'] = $receiver->pseudo;
		$data['receiverNom'] = $receiver->nom;
		$data['receiverPrenom'] = $receiver->prenom;
		$members_model->get_mainInstru($receiver);
		$data["receiverMainInstruName"] = $receiver->mainInstruName;

		// On récupère les infos à afficher (created_at) et on les normalize
		$invitation = $invitation_model->find($insertId);
		$data["id"] = $insertId;
		$data["state"] = -1;
		$data["created_at"] = date_create()->format('Y-m-d H:i:s');
		$data["updated_at"] = date_create()->format('Y-m-d H:i:s');
		$invitation = $data;

		//$invitation = $invitation_model->normalize_dates($message);
		
		// On retourne la choicePos de l'inscription effectuée
		$return_data = array(
			'state' => true,
			'data' => $invitation
		);
		$output = json_encode($return_data);
		echo $output;
	}
	
	
	
	public function update_invitation_state() {
	
		log_message('debug', "************ ajax :: update_invitation");
	
		// On récupère les infos de l'invitation
		$invitId = trim($_POST['invitId']);
		$state = trim($_POST['state']);

		$invitation_model = new Invitation_model();
		
		// On actualise l'invit dans la bd
		$invitation_model->update($invitId, [ 'state' => $state ]);
		
		// Si invit acceptée, on fait un join_member
		if ($state) {
			
			$jam_model = new Jam_model();
			$members_model = new Members_model();
			
			$invit = $invitation_model->find($invitId);
			
			// On récupère le membre et la jam
			$member = $members_model->get_member_by_id($invit["receiverId"]);
			$jam = $jam_model->get_jam_id($invit["targetId"]);
			
			// On insère un participant
			if (!$jam_model->is_included($jam['id'],$member->id)) {
				$jam_model->join_member($jam['id'],$member->id, 0);
			}			
		}
		
		// ****** On actualise la session
		// On récupère les notif
		$temp_list_notif = $this->session->get("list_notif");
		// On cherche la notif pour laquelle le membre a répondu
		foreach($temp_list_notif as $key => $temp_notif) {
			if ($temp_notif->id == $invitId) break;
		}
		// On remove la notif en question dans la session
		unset($temp_list_notif[$key]);
		$this->session->set("list_notif",$temp_list_notif);
		
		//log_message("debug","session_list_noti : ".json_encode($this->session->get("list_notif")));

		$return_data = array(
			'state' => true,
			'data' => null
		);
		$output = json_encode($return_data);
		echo $output;
	}
	
	
	public function delete_invitation() {
	
		//log_message('debug', "************ ajax :: delete_invitation");
	
		$invitId = trim($_POST['invitId']);

		$invitation_model = new Invitation_model();

		$invitation_model->delete($invitId);
		
		// On retourne la liste
		$return_data = array(
			'state' => true,
			'data' => null
		);
		$output = json_encode($return_data);
		echo $output;
	}
	

	/***********************   STAGIAIRES   *************************/
	
	public function create_stage_inscription() {
		
		//log_message('debug',"create_stage_inscription ************");
		
		// ******************  On update le member au cas où
		$stageId = trim($_POST['stage_id']);
		$memberId = trim($_POST['id']);  // Seul le pseudo est fixe	
		$email = trim($_POST['email']);
		$nom = trim($_POST['nom']);
		$prenom = trim($_POST['prenom']);
		$naissance = trim($_POST['naissance']);
		$mobile = trim($_POST['mobile']);
		$nb_prat = trim($_POST['nb_prat']);
		$nb_grp = trim($_POST['nb_grp']);
		$ecole = trim($_POST['ecole']);
		$prof = trim($_POST['prof']);
		$email_tuteur = trim($_POST['email_tuteur']);
		$tel_tuteur = trim($_POST['tel_tuteur']);
		$remarque = trim($_POST['remarque']);
		

		$tmpPseudo = $this->members_model->get_members_by_email($email);
		$oldMember = $this->members_model->get_members_by_id($memberId);
		
		// On récupère le stage
		$stage_item = $this->stage_model->get_stage($stageId);
		
		// On récupère la date de naissance
		$tmp = explode("/", $naissance);
		$naissance_iso = $tmp[2]."-".$tmp[1]."-".$tmp[0];
	
		// On s'assure qu'un profil avec le même email n'existe pas déjà dans la base de donnée
		if (!$tmpPseudo || $oldMember->email == $email) {
			// On update le membre dans la base avec le pass temporaire
			$data = array(
				'nom' => $nom,
				'prenom' => $prenom,
				'naissance' => $naissance_iso,
				'email' => $email,
				'mobile' => $mobile
			);
			$state = $this->members_model->update_members($memberId,$data);
			$msg = $state ? "Le profil a bien été actualisé." : "Une erreur est survenue lors de l'actualisation du profil.";
		}
		else {
			$state = false;
			$msg = "L'email <b>".$email."</b> est déjà utilisé par un autre utilisateur.";
		}
		
		
		// ******************  On créé l'inscription si tout s'est bien passé
		if ($state) {
			
			// Inscription du membre à la jam (si la jam est pleine)
			if (!$this->jam_model->is_included($stage_item->jamId, $memberId)) {
				$this->jam_model->join_member($stage_item->jamId, $memberId);
			}
			
			
			// Inscription du membre en tant que stagiaire			
			$inscr = array(
				'stageId' => $stageId,
				'membresId' => $memberId,
				'nom' => ucfirst(mb_strtolower($nom,'UTF-8')),
				'prenom' => ucfirst(mb_strtolower($prenom,'UTF-8')),
				'age' => (int) ((time() - strtotime($naissance)) / 3600 / 24 / 365),
				'nb_prat' => $nb_prat,
				'nb_grp' => $nb_grp,
				'ecole' => $ecole,
				'prof' => ucfirst(mb_strtolower($prof,'UTF-8')),
				'email_tuteur' => $email_tuteur,
				'tel_tuteur' => $tel_tuteur,
				'remarque' => $remarque,
				'date_inscr' => date("Y-m-d G:i:s"),
				'date_relance' => date("Y-m-d G:i:s")
			);

			// On insère le membre dans la base
			$this->stage_model->join_stage_member($inscr);
			
			$msg .= "<br>La pré-inscription a bien été enregistrée.<br>Votre inscription définitive au stage sera validée sur réception du chèque.";
			
			
			// ************ On envoie un EMAIL
			$this->load->library('email');
			
			$message = '<p>
							Bonjour,<br><br>
							Nous avons bien enregistré votre formulaire de préinscription.<br>
							Votre inscription définitive sera validée dès la réception d\'un chèque d\'un montant de <b>'.$stage_item->cotisation.' euros</b> libellé à l\'ordre "<b>'.$stage_item->ordre.'</b>" à envoyer à l\'adresse suivante :<br>
							<b>'.$stage_item->adresse_cheque.'</b>
						</p>
						<p>A bientôt !<br><i class="note">L\'équipe du Grenoble Reggae Orchestra</i></p>';
		
			$emailArray = [];
			$emailArray[] = $email;
			if (! empty($email_tuteur)) $emailArray[] = $email_tuteur;
			
			log_message('debug',json_encode($emailArray));
					
			if ($this->config->item('net')) {		
				$this->email->from('manage@le-gro.com','GRO');
				$this->email->reply_to('contact@le-gro.com');
				$this->email->to($emailArray);
				$this->email->subject("Grenoble Reggae Orchestra ~ Stage // Pré-inscription");
				$this->email->message($message);
				$this->email->send();
			}
			
			else $msg .= "<br>Impossible d'envoyer d'email de confirmation sans connexion internet.";
			
		}
		else $msg .= "<br>La pré-inscription n'a pas été enregistrée !";
		
		
		
		$return_data = array(
			'state' => $state,
			'data' => $msg
		);
		$output = json_encode($return_data);
		echo $output;
		
		
	}
	
	
	// Permet d'actualiser l'état de réception d'un chèque
	public function update_cheque_state() {
	
		$tmemberId = trim($_POST['tmemberId']);
		$state = trim($_POST['state']);
		$send_email =  trim($_POST['send_email']);
		
		$this->stage_model->update_cheque_state($tmemberId, $state);
		
		//On récupère le membre
		$member = $this->stage_model->get_member_by_stageMemberId($tmemberId);
		
		//On récupère le stage
		$stage = $this->stage_model->get_stage_stmbId($tmemberId);
		
		// On reformate les dates récupérées du stage
		$date = date_create_from_format("Y-m-d",$stage->date_debut);
		$stage_date1 = date_format($date,"d/m/Y");
		date_add($date,date_interval_create_from_date_string("2 days"));
		$stage_date2 = date_format($date,"d/m/Y");

		$jam = $this->jam_model->get_jam_id($stage->jamId);
		$lieu = $this->lieux_model->get_lieux_by_id($stage->lieuxId);
		
		if ($send_email == "true") {
			
			// On envoie un EMAIL
			$this->load->library('email');
			
			$message = '<p>
							Bonjour,<br><br>
							Nous avons bien reçu le chéque concernant le réglement du stage organisé par le Grenoble Reggae Orchestra.<br>
							Le stage se déroulera du <b>'.$stage_date1.'</b> au <b>'.$stage_date2.'</b> à <b>'.$lieu['nom'].'</b>.<br>
							Le stage se déroulera en journée et également en soirée le jour de la jam.
							
						</p>
						<p>A bientôt !<br><i class="note">L\'équipe du Grenoble Reggae Orchestra</i></p>';
		
			if ($this->config->item('net')) {		
				$this->email->from('manage@le-gro.com','GRO');
				$this->email->reply_to('contact@le-gro.com');
				$this->email->to( array ($member->email_tuteur, $member->email) );
				$this->email->subject("Grenoble Reggae Orchestra ~ Stage // Réception chèque");
				$this->email->message($message);
				$this->email->send();
				$msg = "Modification de l'état de réception du chèque validée et mail envoyé !";
			}
			
			else $msg = "Impossible d'envoyer d'email sans connexion internet.";
		}
		
		else $msg = "Modification de l'état de réception du chèque validée !";
		
		
		$return_data = array(
			'state' => $state,
			'data' => $msg
		);
		$output = json_encode($return_data);
		echo $output;
		
	}


	
	/***********************   EMAILS   *************************/
	public function send_email() {
		
		$adresses = trim($_POST['adresses']);
		$subject = trim($_POST['subject']);
		$message = trim($_POST['message']);
		$name = trim($_POST['name']);
		$from = trim($_POST['from']);
		$reply_to = trim($_POST['reply_to']);
	
		$adresses_array = json_decode($adresses,true);

		// Pour débug
		/*$list = "<ul>";
		foreach ($adresses_array as $email) {
			$list .= "<li>".$email."</li>";
		}
		$list .= "</ul>";*/

		if (env('app.has_net')) {
			
			// On envoie un email
			$email = \Config\Services::email();
			$email->setFrom($from,$name);
			$email->setReplyTo($reply_to, $name);
			$email->setTo($adresses_array);
			$email->setSubject($subject);
			$email->setMessage($message);
			$state = $email->send();
			
			// Send mail utilisé par un admin
			if ($from == 'manage@le-gro.com') {
				$msg = "Votre message a bien été envoyé.";
			}
			// Send mail utilisé par le formulaire de contact
			else {
				$msg = $state ? "<p>Merci pour votre message, nous vous répondrons dans les plus brefs délais.</p><p>Bonne visite !<br><i class='note'>L'équipe du Grenoble Reggae Orchestra</i></p>" : "<p>Désolé, mais votre message n'a pas pu être correctement envoyé.</p>";
			}
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

	
	/***********************   ACCESS   *************************/
	
	// Retourne la liste des éléments définits par accessType
	public function get_access_elem() {
		
		$parentType = trim($_POST['accessType']);
		
		$instruments_model = new Instruments_model();
		
		$list_elem = 'empty';
		
		// Marche si les colonnes sont bien 'id' et 'name'
		switch ($parentType) {
			case 'tous' :
				$output = $list_elem;
				break;
			case 'cat' :
				$list_elem = $instruments_model->get_instru_categories2("view_order",true);
				$output = json_encode($list_elem);
				break;
			case 'instru' :
				$list_elem = $instruments_model->get_instruments("name");
				$output = json_encode($list_elem);
				break;
			case 'tache' :
				$output = $list_elem;
				break;
			default : 
				$output = $list_elem;
				break;
		}
		
		echo $output;
	}
	
}
?>