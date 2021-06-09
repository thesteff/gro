<?php namespace App\Controllers;

use App\Models\Members_model;
use App\Models\Instruments_model;
use CodeIgniter\HTTP\IncomingRequest;

class Members extends BaseController {
	
	
	// Fait également office de update
	public function view($login) {

		if(url_title($this->session->login) == url_title($login) || $this->session->superAdmin) {
			
			$data['page_title'] = "Profil";
			$data['title'] = "Profil";
		
			$members_model = new Members_model();
			$instruments_model = new Instruments_model();
		
			// On récupère les infos du membre loggé dans la BD
			$log = url_title($login);
			$data['member_item'] = $members_model->get_member_by_slug($log);
			$data['isSuperAdmin'] = $this->session->superAdmin;
			
			
			// On vérifie que le login de l'url existe
			if (isset($data['member_item'])) {
				
				// On normalise la date de naissance
				if (!empty($data['member_item']->naissance)) {
					$date = date_create_from_format("Y-m-d",$data['member_item']->naissance);
					$data['member_item']->naissance = date_format($date,"d/m/Y");
				}
				
				// On récupère la liste d'instru
				$data['famille_instru_list'] = $instruments_model->get_familyzed_instruments();
				
				// On lance la vue
				echo view('templates/header', $data);
				echo view('templates/menu', $data);
				echo view('members/view', $data);
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

		if($this->session->login || $this->session->logged) {
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
		echo view('templates/footer');
	
	}
	
	
	
	
	//****************** ADD INSTRUMENT ********************//
	public function add_instrument($memberSlug) {		// -1 si le membre n'existe pas encore

		if(url_title($this->session->login) == $memberSlug || $this->session->admin || $memberSlug == -1) {

			$instruments_model = new Instruments_model();
			$members_model = new Members_model();
			
			// Le membre existe
			if ($memberSlug != -1) {
			
				$data['is_admin'] = $this->session->admin;
				
				// On récupère le membre + ses instruments
				$data['member_item'] = $members_model->get_member_by_slug($memberSlug);
				$data['member_instruments'] = $members_model->get_instruments($data['member_item']->id);
			}
			
			// On récupère les familles d'instru
			$data['famille_instru_list'] = $instruments_model->get_familyzed_instruments();

			echo view('members/add_instrument', $data);
		}
		
		else {
			echo "<div class='alert alert-warning'><i class='glyphicon glyphicon-warning-sign'></i>&nbsp;&nbsp;Votre statut sur le site ne vous permet pas d'accéder à cette page.</div>";
		}
	}
	
	
	
	
	//****************** UPDATE INSTRUMENT ********************//
	public function update_instrument($memberSlug, $instruId) {

		if(url_title($this->session->login) == $memberSlug || $this->session->admin || $memberSlug == -1) {

			$instruments_model = new Instruments_model();
			$members_model = new Members_model();
			
			// Le membre existe
			if ($memberSlug != -1) {
				$data['is_admin'] = $this->session->admin;
				
				// On récupère le membre + ses instruments
				$data['member_item'] = $members_model->get_member_by_slug($memberSlug);
				$data['member_instruments'] = $members_model->get_instruments($data['member_item']->id);
			}
			
			// Instru selectionné
			$data['instruId'] = $instruId;
			
			// On récupère la famille de l'instru selectionné dans l'update
			$data['famille_item'] = $instruments_model->get_instru_family($instruId);
			
			// On récupère les familles d'instru
			$data['famille_instru_list'] = $instruments_model->get_familyzed_instruments();

			echo view('members/update_instrument', $data);
		}
		
		else {
			echo "<div class='alert alert-warning'><i class='glyphicon glyphicon-warning-sign'></i>&nbsp;&nbsp;Votre statut sur le site ne vous permet pas d'accéder à cette page.</div>";
		}
	}
	
	
	
	//****************** LOGIN ********************//
	public function login() {
				
		$input = trim($_POST['input']); // email ou pseudo
		$pass = trim($_POST['pass']);
		
		$members_model = new Members_model();
		
		// on check l'id par le pseudo
		$is_member = $members_model->check_pseudo($input,$pass);
		$temp_pseudo = $input;
		
		
		// On check l'id par l'adresse email
		if (!$is_member) {
			$is_member = $members_model->check_email($input,$pass);
			if ($is_member) {
				$temp_member = $members_model->get_member_by_email($input);
				$temp_pseudo = $temp_member->pseudo;
			}
		}
		
		
		if ($is_member) {
			
			// On récupère le pseudo en case_sensitive directement à partir de la base
			$member = $members_model->get_member($temp_pseudo);

			// On liste les événements auxquel le membres participe pour les rendre accessibles directement à partir du menu sans faire de reload
			$arrayEvent = [];
			$arrayEvent = $members_model->get_jams($member->id);
			//log_message("debug",json_encode($arrayEvent));
			
			// On récupère les notifications
			$arrayNotif = [];
			$arrayNotif = $members_model->get_notifications($member->id);
			//log_message("debug","******* Members :: login :: arrayNotif : ".json_encode($arrayNotif));

			// On fixe les variables de sessions
			$data = array('login' => $member->pseudo,
							'logged' => true,
							'id' => $member->id,
							'superAdmin' => $member->admin,
							'validMail' => $member->validMail,
							'list_event' => $arrayEvent,
							'list_notif' => $arrayNotif
						);
			$this->session->set($data);
			
			// On actualise la date_access
			$members_model->update_date_access($member->id);
			
			// Pour le domaine on enlève http:// ou https://
			$domain = substr(base_url(),strpos(base_url(),"//")+2);
			// Pour le domaine on enlève le / en fin de string	
			if (substr($domain,-1) == '/') $domain = substr($domain,0,-1);
			
			//log_message("debug","Members::login : ".$domain);
			
			// On s'occupe de créer le cookie pour le remember_me et on actualise le membre
			$rdmStr = random_string('alnum',64);
			$cookie = array(
				'name'   => 'remember_me',
				'value'  => $rdmStr,
				'expire' => '15778800',            // 6 mois
				'domain' => $domain,
				'path'   => '/'
				// nbUnreadMessage => fixé via menu.php et Ajax_Members::get_nb_unread_message
				// lastCheckUnreadMessage => idem
			);
			set_cookie($cookie);
			log_message('debug', "  ******* Set_Cookie : ".json_encode($cookie)."   ******");
			
			$members_model->update_cookie($member->id, $rdmStr);

			$return_data = array(
				'state' => 1,
				'data' => ""
			);
			$output = json_encode($return_data);
			echo $output;
			
		}
		
		else {
			$return_data = array(
				'state' => 0,
				'data' => "Identifiant/email inconnu ou mot de passe incorrect."
			);
			$output = json_encode($return_data);
			echo $output;
		}
		
	}
	
	
	
	//****************** LOGOUT ********************//
	public function logout() {
		
		$members_model = new Members_model();
		
		// On update le cookie du membre dans la base (sinon, reconnection automatique)
		$members_model->update_cookie($this->session->id, '');
		
		$this->session->destroy();
		
		// Pour le domaine on enlève http:// ou https://
		$domain = substr(base_url(),strpos(base_url(),"//")+2);
		// Pour le domaine on enlève le / en fin de string	
		if (substr($domain,-1) == '/') $domain = substr($domain,0,-1);
		
		delete_cookie("remember_me", $domain);
		
		// On reste sur la même page mais la session sera destroy
		$uri = new \CodeIgniter\HTTP\URI(previous_url());
		header('Location: '.site_url($uri->getPath()));
		exit;
	}
	
	
	
	//****************** VALIDATE ********************//
	public function validateMail($memberSlug, $hash) {

		log_message('debug','******* Members : validate : '.$memberSlug.' / '.$hash);
		
		$members_model = new Members_model();
		
		// On récupère le membre
		$member = $members_model->get_members($memberSlug);
		
		// Problème avec la slug
		if (empty($member)) return;
		
		// On active le compte si le hash correspond
		$data = array('validMail' => 1);
		if ($member->hash == $hash) $state = $members_model->update_member($member->id, $data);
		

		if ($state != false) {
			
			// On actualise la session
			$this->session->set([ 'validMail' => 1 ]);
			
			// On affiche une page de succès
			$data_page['page_title'] = 'Activation du compte';
			$data_page['title'] = 'Email validé !';
			$data_page['message'] = 'Votre email a été validé et votre compte est maintenant actif !';
		
			echo view('templates/header', $data_page);
			echo view('pages/message', $data_page);
			echo view('templates/footer');
		}
		else {
			$data_page['page_title'] = 'Erreur';
			$data_page['title'] = 'Problème d\'activation';
			$data_page['message'] = 'Il y a eu un problème d\'activation de votre compte. Veuillez contactez <contact@le-gro.com> pour plus d\'informations.';
		
			echo view('templates/header', $data_page);
			echo view('pages/message', $data_page);
			echo view('templates/footer');
		}
	}	
}
