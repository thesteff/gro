<?php namespace App\Controllers;

use App\Models\Members_model;
use App\Models\Message_model;
use App\Models\Discussion_model;
use App\Models\Jam_model;
use CodeIgniter\I18n\Time;

class Ajax_members extends BaseController {


	// Récupère les membres dont le pseudo commence par...
	public function get_members() {
		
		$keyword = trim($_REQUEST['keyword']);
		$memberId = trim($_REQUEST['memberId']);

		$members_model = new Members_model();

		// On fait une recherche dans les membres
		$filtered_list_membres = $members_model->get_members('null','asc',$keyword);

		// On récupère l'instrument principal de chaque participant, on récup le thumb et on vire le member qui fait la requète au passage
		foreach ($filtered_list_membres as $key => $elem) {
			
			// On trouve le member qui fait la requète
			if ($filtered_list_membres[$key]->id == $memberId) {
				$memberOffset = $key;
			}
			
			else {
			
				// On récup le mainInstru
				$members_model->get_mainInstru($filtered_list_membres[$key]);
				
				// On récupère le thumb
				if (file_exists("images/avatar/".$filtered_list_membres[$key]->id.".png") == true)
					$filtered_list_membres[$key]->thumb = base_url("images/avatar")."/".$filtered_list_membres[$key]->id.".png";
				else $filtered_list_membres[$key]->thumb = base_url("images/icons/avatar1.png");
			
			}
		}
		
		// On supprime le membre qui fait la requète
		if (isset($memberOffset)) array_splice($filtered_list_membres, $memberOffset, 1);
		
		//log_message("debug","filtered_list_membres : ".json_encode($filtered_list_membres));

		$output = json_encode($filtered_list_membres);
		echo $output;
		
	}

	// Create member
	public function create_member() {

		$pseudo = trim($_POST['pseudo']);	// à priori le BootstrapValidator a vérifié que le pseudo n'était pas déjà pris	
		$emailAdr = trim($_POST['email']);		// à priori le BootstrapValidator a vérifié que l'email n'était pas déjà pris	
		$nom = trim($_POST['nom']);
		$prenom = trim($_POST['prenom']);
		$naissance = trim($_POST['naissance']);
		$mobile = trim($_POST['mobile']);
		$pass = trim($_POST['pass']);
		$instruArray = json_decode(trim($_POST['instruArray']));
		
		$members_model = new Members_model();

		$member = $members_model->get_members($pseudo);

		// On s'assure que le pseudo n'est pas déjà pris (normalement c'est fait avec le BootstrapValidator)
		if (!$member) {
			
			// On récupère la date de naissance
			if (!empty($naissance)) {
				$tmp = explode("/", $naissance);
				$naissance_iso = $tmp[2]."-".$tmp[1]."-".$tmp[0];
			}
			else $naissance_iso = NULL;
			
			
			// On génère un hash code pour pouvoir valider l'email
			$hash = random_string('alnum', 10);
			
			// On créé le membre dans la base
			$data = array(
				'pseudo' => $pseudo,
				'nom' => $nom,
				'prenom' => $prenom,
				'naissance' => $naissance_iso,
				'slug' => url_title($pseudo),
				'email' => $emailAdr,
				'mobile' => $mobile,
				'pass' => sha1($pass),
				'date_inscr' => date('Y-m-d G:i:s'),
				'date_access' => date('Y-m-d G:i:s'),
				'allowMail' => true,
				'hash' => $hash,
				'admin' => false
			);
			
			$memberId = $members_model->set_member($data);

			// On créé les relations avec les instruments joués
			for ($i = 0; $i < count($instruArray); $i++) {
				$members_model->add_instrument($memberId,$instruArray[$i]);
			}
			
			// On fixe les variables de sessions
			$data = array('login' => $pseudo,
							'logged' => true,
							'id' => $memberId,
							'superAdmin' => false,
							'validMail' => false,
							'previousAccess' => null
						);
			$this->session->set($data);


			// Lien pour activer le compte
			$link = site_url('members/validateMail/').url_title($pseudo).'/'.$hash;

			// On envoie un email de bienvenue
			$email = \Config\Services::email();
			$email->setFrom('contact@le-gro.com','GRO');
			$email->setTo($emailAdr);
			$email->setSubject('Inscription au site le-gro.com');
			$email->setMessage('<h4>Bienvenue!</h4>
									<p>Vous êtes maintenant membre du site le-gro.com. <br />
									Pour activer votre compte et profiter de toutes les fonctionnalités du site le-gro.com, merci de cliquer sur le lien ci-dessous :</p>
									<p><a target="_blanck" href="'.$link.'">'.$link.'</a></p>
									<p>En espérant vous croiser bientôt !</p>
									<b><i>Le Grenoble Reggae Orchestra</i></b>');
			$email->send();
			
			$state = true;
			$msg = "Votre inscription a bien été enregistrée. En espérant vous croiser bientôt !";
		}
		else {
			$state = false;
			$msg = "Le pseudo que vous avez choisi est déjà pris par un autre utilisateur.";
		}
		
		$return_data = array(
			'state' => $state,
			'data' => $msg
		);
		$output = json_encode($return_data);
		echo $output;

	}

	
	// Update member
	public function update_member() {

		$memberId = trim($_POST['id']);  // Seul le pseudo est fixe	
		$email = trim($_POST['email']);
		$nom = trim($_POST['nom']);
		$prenom = trim($_POST['prenom']);
		$naissance = trim($_POST['naissance']);
		$genre = trim($_POST['genre']);
		$mobile = trim($_POST['mobile']);
		$allowMail = trim($_POST['allowMail']);
		$freqRecapMail = trim($_POST['freqRecapMail']);
		
		$members_model = new Members_model();

		$tmpPseudo = $members_model->get_member_by_email($email);
		$oldMember = $members_model->get_member_by_id($memberId);
		
		
		// On récupère la date de naissance
		if (!empty($naissance)) {
			$tmp = explode("/", $naissance);
			$naissance_iso = $tmp[2]."-".$tmp[1]."-".$tmp[0];
		}
		else $naissance_iso = NULL;
	
		// On s'assure qu'un profil avec le même email n'existe pas déjà dans la base de donnée
		if (!$tmpPseudo || $oldMember->email == $email) {
			// On update le membre dans la base avec le pass temporaire
			$data = array(
				'nom' => $nom,
				'prenom' => $prenom,
				'naissance' => $naissance_iso,
				'genre' => $genre,
				'email' => $email,
				'mobile' => str_replace(' ','',$mobile),
				'allowMail' => $allowMail,
				'freqRecapMail' => $freqRecapMail,
			);
			$state = $members_model->update_member($memberId,$data);
			$msg = $state ? "Le profil a bien été actualisé" : "Une erreur est survenue lors de l'actualisation du profil.";
		}
		else {
			$state = false;
			$msg = "L'email <b>".$email."</b> est déjà utilisé par un autre utilisateur.";
		}
		
		$return_data = array(
			'state' => $state,
			'data' => $msg
		);
		$output = json_encode($return_data);
		echo $output;

	}
	
	
	
	// Update avatar
	public function update_avatar() {

		log_message('debug',"************   Ajax_member :: update_avatar   ***********");
		log_message('debug',"_FILES : ".json_encode($_FILES));
		log_message('debug',"_POST : ".json_encode($_POST));


		$memberId = trim($_POST['memberId']);
		
		$members_model = new Members_model();
		
		// On récupère le membre
		$member_item = $members_model->get_member_by_id($memberId);
			
		$state = false;
		$data = '';
		// Le membre existe bien, on update son avatar
		if (!empty($member_item)) {
				
			// On récupère le realpath
			$filePath = FCPATH."/images/avatar/".$memberId.".png";
		
			if ($filePath) {
			
				// On écrit le fichier...
				if (sizeof($_FILES) > 0) {
					
					// S'il y a eu un problème d'upload, on quitte
					if ($_FILES["file"]["error"] == 1) {
						$data = "Error 1 : La taille de fichier maximale a été atteinte (php.ini)";
					}
					else if ($_FILES["file"]["error"] != 0) {
						$data = "Error : ".$_FILES["file"]["error"];
					}
					else {
						// Sinon on écrit le fichier
						$state = move_uploaded_file($_FILES["file"]["tmp_name"], $filePath);
						$data = "Fichier ".$memberId.".png créé avec succés !";
						
						// On update le member
						$members_model->update_member($memberId, [ "hasAvatar" => 1 ]);
					}
				}
				else $data = "Upload_file :: Error : sizeof(_FILES) = 0";
			}
			else $data = "Upload_file :: Error filePath : ".$filePath;
		}
		// Le membre n'existe pas
		else $data = "Erreur : Le membre n'existe pas dans la base de donnée.";
		
		$return_data = array(
			'state' => $state,
			'data' => $data
		);
		$output = json_encode($return_data);
		echo $output;

	}
	
	
	// Update member pass
	public function update_pass_member() {

		$memberId = trim($_POST['memberId']);
		$pass = trim($_POST['pass']);
		$pass2 = trim($_POST['pass2']);
		
		$members_model = new Members_model();

		// On s'assure que l'ancien mot de passe a bien été saisi
		if ($members_model->check_id($memberId,$pass)) {
			// On update le membre et son nouveau pass
			$data['pass'] = sha1($pass2);
			$state = $members_model->update_member($memberId, $data);
			$msg = $state ? "Le mot de passe a bien été modifié" : "Une erreur est survenue lors de l'actualisation du profil.";
		}
		else {
			$state = false;
			$msg = "Votre mot de passe actuel est erroné";
		}
		
		$return_data = array(
			'state' => $state,
			'data' => $msg
		);
		$output = json_encode($return_data);
		echo $output;
	}
	
	
	
	// Renvoie le membre avec l'instru
	public function get_member_and_listInstru() {
	
		$pseudo = trim($_POST['pseudo']);
		
		$members_model = new Members_model();
		
		$membre = $members_model->get_members($pseudo);
		$listInstru = $members_model->get_instruments($membre->id);

		$data = array(
			"member" => (array)$membre,
			"listInstru" => (array)$listInstru
		);
		
		$output = json_encode($data);
		echo $output;
	}
	
	
	// Renvoie le nombre de messages non lus
	public function get_nb_unread_message() {
	
		$memberId = trim($_POST['memberId']);
		
		$message_model = new Message_model();
		$count = $message_model->get_nb_unread_message($memberId);
		
		// On complète la variable de session
		$this->session->set(['nbUnreadMessage' => $count]);
		$now = new Time('now');
		$this->session->set(['lastCheckUnreadMessage' => $now->toDateTimeString()]);
		
		echo $count;
	}
	
	
	// Renvoie le nombre de messages non lus
	public function update_nb_unread_message() {
	
		$delta = trim($_POST['delta']);
	
		log_message('debug','***************** Ajax_members :: update_nb_unread_message '.$delta);
				
		// On complète la variable de session
		$this->session->set(['nbUnreadMessage' => ($this->session->nbUnreadMessage + $delta )]);
		$now = new Time('now');
		$this->session->set(['lastCheckUnreadMessage' => $now->toDateTimeString()]);
		
		$return_data = array(
			'state' => true,
			'data' => $this->session->nbUnreadMessage
		);
		$output = json_encode($return_data);
		echo $output;
	}
	
	
	// Renvoie les notifications
	public function get_notifications() {
	
		$memberId = trim($_POST['memberId']);
		
		$members_model = new Members_model();
		
		$data = $members_model->get_notifications($memberId);
		
		$return_data = array(
			'state' => true,
			'data' => $data
		);
		$output = json_encode($return_data);
		echo $output;
	}
	
	
	// Renvoie les infos a afficher dans un popover d'un membre en fonction du contexte
	public function get_details() {
		
		log_message('debug','***************** Ajax_members :: get_details');
		
		$memberId = trim($_POST['memberId']);	// Membre pointée
		$context = trim($_POST['context']);		// Membre qui pointe
		
		//log_message("debug","Context : ".$context);
		$context = json_decode($context);
		//log_message("debug","Context : ".$context->userId);
		
		$members_model = new Members_model();
		$jam_model = new Jam_model();
		
		// On récupère le membre
		$member_item = $members_model->get_member_by_id($memberId);
		//log_message("debug","member_item : ".json_encode($member_item));
		$data["pseudo"] = $member_item->pseudo;
		$data["nom"] = $member_item->nom;
		$data["prenom"] = $member_item->prenom;
		$data["slug"] = $member_item->slug;
		
		// On récupère le main instru
		$members_model->get_mainInstru($member_item);
		$data["mainInstruName"] = $member_item->mainInstruName;
		
		// Infos données aux admin
		if ($context->admin) {
			$data["mobile"] = $member_item->mobile;
			$data["email"] = $member_item->email;
		}
		
		// Infos données à tous si référent
		$refObj = $jam_model->is_referent($context->pageId, $memberId);
		//log_message("debug","refObj : ".json_encode($refObj));
		if ($context->page == "jam" && $refObj != null) {
			$data["referent"] = $refObj->tag2Title." ".$refObj->tag2Label;
			$data["mobile"] = $member_item->mobile;
			$data["email"] = $member_item->email;
		}
		
		$return_data = array(
			'state' => true,
			'data' => $data
		);
		$output = json_encode($return_data);
		echo $output;
		
	}
	
	
	// Retourne true si le pseudo existe dans la BD
	public function check_pseudo() {

		$pseudo = trim($_POST['pseudo']);
		
		$members_model = new Members_model();
		
		log_message('debug','***************** Ajax_members :: check_pseudo');
		
		//On essaie de récupérer un memebre avec le pseudo
		$member = $members_model->get_members($pseudo);
		
		// On teste également avec la slug (pas forcément remplie)
		$member2 = $members_model->get_member_by_slug(url_title($pseudo));

		// Le pseudo est déjà pris
		if ($member) {
			$state = true;
			$msg = "Le pseudo <b>".$pseudo."</b> est déjà utilisé.";
		}
		else if ($member2) {
			$state = true;
			$msg = "Le pseudo <b>".$pseudo."</b> ne peut être utilisé.";
		}
		else {
			$state = false;
			$msg = "";
		}
		
		$return_data = array(
			'state' => $state,
			'data' => $msg
		);
		$output = json_encode($return_data);
		echo $output;
	}
	
	
	// Retourne true si l'email existe dans la BD
	public function check_email() {

		$email = trim($_POST['email']);
		
		$members_model = new Members_model();
		
		$state = $members_model->email_exist($email);

		// L'email est déjà pris
		if ($state) $msg = "L'email <b>".$email."</b> est déjà utilisé.";
		else $msg = "";
		
		$return_data = array(
			'state' => $state,
			'data' => $msg
		);
		$output = json_encode($return_data);
		echo $output;
	}
	
	
	// Envoie du mot de passe par email
	public function forgotten() {
		
		$emailAdr = trim($_POST['email']);
		
		$members_model = new Members_model();
		
		// On récupère le membre à partir de l'email
		$member = $members_model->get_member_by_email($emailAdr);

		// Si le membre / email n'existe pas dans la base
		if ($member == null) {
			$state = false;
			$msg = "L'adresse email <b>".$emailAdr."</b> n'existe pas dans notre base de donnée.";
		}
		
		else {

			// On génère un nouveau mot de passe temporaire et on l'affecte au profil
			$temp_pass = random_string('alnum', 8);

			// On update le membre dans la base avec le pass temporaire
			$data = array('pass' => sha1($temp_pass));
			$members_model->update_member($member->id, $data);

			if (env('app.has_net')) {
			
				// On envoie un email
				$email = \Config\Services::email();
				$email->setFrom('contact@le-gro.com','GRO');
				$email->setTo($emailAdr);
				$email->setSubject('Votre nouveau mot de passe pour le site le-gro.com');
				$email->setMessage('<h4>Bonjour !</h4>
								<p>Suite à une demande enregistrée de votre part sur le site le-gro.com, nous vous envoyons un nouveau mot de passe.<br>
								N\'hésitez pas à le modifier à votre guise sur la page de votre profil.</p>
								<p>login : '.$member->pseudo.'<br>
								mot de passe : '.$temp_pass.'
								</p>
								<p>En espérant vous croiser bientôt !</p>');
				$state = $email->send();
				
			}

			else {
				$state = false;
				$msg = "Impossible d'envoyer d'email sans connexion internet.";
			}
			
			// On retourne l'info
			$msg = $state ? "Un email a été envoyé à <b>".$emailAdr."</b> avec votre nouveau mot de passe." : "Une erreur est survenue lors de l'envoie de l'email.";
		}
		
		$return_data = array(
			'state' => $state,
			'data' => $msg
		);
		$output = json_encode($return_data);
		echo $output;
	
	}
	
	
	// Envoie un email + validation string + update bd member avec hash code
	public function sendValidationMail() {
		
		$memberId = trim($_POST['memberId']);
		
		$members_model = new Members_model();
		
		// On récupère le membre à partir de l'email
		$member = $members_model->get_member_by_id($memberId);

		// Si le membre / email n'existe pas dans la base
		if ($member == null) {
			$state = false;
			$msg = "Le membre <b>".$memberId."</b> n'existe pas dans notre base de donnée.";
		}
		
		else {

			// On génère un nouveau hash code
			$hash = random_string('alnum', 10);

			// On update le membre dans la base avec le pass temporaire
			$data = array('hash' => $hash);
			$members_model->update_member($member->id, $data);

			if (env('app.has_net')) {
			
				$link = site_url('members/validateMail/').$member->slug.'/'.$hash;
			
				// On envoie un email
				$email = \Config\Services::email();
				$email->setFrom('contact@le-gro.com','GRO');
				$email->setTo($member->email);
				$email->setSubject('Email de validation pour le-gro.com');
				$email->setMessage('<h4>Bonjour !</h4>
								<p>Suite à une demande enregistrée de votre part sur le site le-gro.com, nous vous envoyons un lien de validation pour activer votre compte :<br>
								</p>
								<a target="_blanck" href="'.$link.'">'.$link.'</a>
								<p>En espérant vous croiser bientôt !</p>');
				$state = $email->send();
				
			}

			else {
				$state = false;
				$msg = "Impossible d'envoyer d'email sans connexion internet.";
			}
			
			// On retourne l'info
			$msg = $state ? "Un email de validation de compte a été envoyé à <b>".$member->email."</b>. Merci de cliquer le lien qui s'y trouve pour activer votre compte." : "Une erreur est survenue lors de l'envoie de l'email.";
		}
		
		$return_data = array(
			'state' => $state,
			'data' => $msg
		);
		$output = json_encode($return_data);
		echo $output;
	}
	
	
}
?>