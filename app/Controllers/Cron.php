<?php namespace App\Controllers;

use App\Models\Members_model;
use App\Models\Invitation_model;
use App\Models\Message_model;

use CodeIgniter\I18n\Time;
use CodeIgniter\I18n\TimeDifference;


class Cron extends BaseController {

	public function index() {
		
		log_message("debug","*** CRON :: index() ");
		
		if ($this->input->is_cli_request() === false) {
			echo "*** CRON :: Controller : ".PHP_SAPI."<br>";
			echo "*** CRON :: Controller : ".php_sapi_name()."<br>";
			//die("Controller : Cli only")."\n";
			// retourne fpm-fcgi avec firefox sur ovh
		}
	}


	// Envoie un mail de notif selon la fréquence sélectionnée dans le profil // 0 : jamais, 1 : tous les jour, 2 toutes les semaines, 3 : tous les mois
	private function sendNotif($freqTag) {
		
		if (env('app.has_net') || env('app.online')) {
			
			log_message("debug","*** has_net || online ");
			
			$members_model = new Members_model();
			$message_model = new Message_model();
			
			// On récupère tous les membres
			$members = $members_model->get_members();
			
			
			// On liste les utilisateurs ayant des notifications (messages non lus ou invitations)
			$memberWithUnreadMessage = array();
			foreach ($members as &$member) {
				// On vérifie qu'on le membre souhaite une vérif tous les jours
				if ($member->freqRecapMail == $freqTag) {
					$nbMessage = $message_model->get_nb_unread_message($member->id);
					if ($nbMessage > 0) {
						$member->{"nbMessage"} = $nbMessage;
						$memberWithUnreadMessage[] = $member;
					}
				}
			}
			log_message("debug","memberWithUnreadMessage : ".json_encode($memberWithUnreadMessage));
			
			
			// On liste les utilisateurs ayant des notifications (messages non lus ou invitations)
			$memberWithInvitation = array();
			foreach ($members as &$member) {
				// On vérifie qu'on le membre souhaite une vérif tous les jours
				if ($member->freqRecapMail == $freqTag) {
					$nbNotif = $members_model->get_notifications($member->id);
					/*log_message("debug","nbNotif : ".json_encode($nbNotif));
					log_message("debug","sizeOf : ".(sizeOf($nbNotif)));*/
					if (sizeOf($nbNotif) > 0) {
						$member->{"nbNotif"} = sizeOf($nbNotif);
						$memberWithInvitation[] = $member;
					}
				}
			}
			log_message("debug","memberWithInvitation : ".json_encode($memberWithInvitation));
			
			
			// On fait un tableau global avec tous les membres qui reçoivent un mail
			$memberEmailed = array();
			foreach ($memberWithUnreadMessage as $member) {
				$memberEmailed[] = (object) [ "id" => $member->id, "pseudo" => $member->pseudo, "email" => $member->email, "nbMessage" => $member->nbMessage ];
			}
			foreach ($memberWithInvitation as $member) {
				// On cherche si le membre n'existe pas déjà
				$i = 0;
				while ($i < sizeOf($memberWithUnreadMessage)) {
					if ($memberWithUnreadMessage[$i]->id == $member->id) break;
					$i++;
				}
				
				// Le membre n'a pas de message unread => il faut créer une entrée
				if ($i == sizeOf($memberWithUnreadMessage)) {
					$memberEmailed[] = (object) [ "id" => $member->id, "pseudo" => $member->pseudo, "email" => $member->email, "nbNotif" => $member->nbNotif ];
				}
				// Le membre existe déjà dans le tableau global
				else {
					$memberEmailed[$i]->nbNotif = $member->nbNotif;
				}
			}
			log_message("debug","memberEmailed : ".json_encode($memberEmailed));
			
			
			
			// On envoie un mail de notif aux utilisateurs concernés
			foreach ($memberEmailed as $member) {
				
				// On créé le message
				$message = "Bonjour ".$member->pseudo." !\n\n";
				
				$message .= "Vous avez ";
				
				// Message unread
				if (isset($member->nbMessage) && $member->nbMessage > 0) {
					$message .= "reçu ".$member->nbMessage." message".($member->nbMessage > 1 ? "s" : "");
					// connecteur
					if (isset($member->nbNotif) && $member->nbNotif > 0) $message .= " et ";
				}
				
				// Notif unread
				if (isset($member->nbNotif) && $member->nbNotif > 0) {
					$message .= $member->nbNotif." notification".($member->nbNotif > 1 ? "s" : "");
				}
				
				$message .= " sur <a href='https://www.le-gro.com' target='_blanck'>le-gro.com</a>.\n";
				
				$message .= "\nA bientôt !\n<i>L'équipe du Grenoble Reggae Orchestra</i>";
				
				// On envoie le mail
				$email = \Config\Services::email();
				
				$config['mailType'] = 'html';
				$email->initialize($config);
				
				$email->setFrom("manage@le-gro.com", "le-gro.com");
				$email->setReplyTo("manage@le-gro.com", "le-gro.com");
				$email->setTo($member->email);
				$email->setSubject("Grenoble Reggae Orchestra ~ Notification");
				$email->setMessage(nl2br($message));
				$state = $email->send();
			}



			// log message pour l'ADMINISTRATEUR
			$recapMessage = "\n\nListe des membres ayant un ou plusieurs messages et/ou invitations en attente à qui un mail a été envoyé\n";
			$recapMessage .= "=====================================================================\n\n";
			foreach ($memberEmailed as $member) {
				$messageVal = isset($member->nbMessage) && $member->nbMessage > 0 ? $member->nbMessage." messages non lus" : "";
				$notifVal = isset($member->nbNotif) && $member->nbNotif > 0 ? $member->nbNotif." notifications non lues" : "";
				$recapMessage .= $member->pseudo."  (".$member->email.") \t\t\t".$messageVal."\t\t".$notifVal."\n";
			}
			
			// On envoie un email de récap à l'administrateur
			$email = \Config\Services::email();
			$email->setFrom("manage@le-gro.com", "Serveur GRO");
			$email->setReplyTo("manage@le-gro.com", "Serveur GRO");
			$email->setTo("s.plotto@free.fr");
			
			$subject = "";
			if ($freqTag == 1) $subject = "everyDay";
			else if ($freqTag == 2) $subject = "everyWeek";
			else if ($freqTag == 3) $subject = "everyMonth";
			
			$email->setSubject("CRON :: ".$subject);
			$email->setMessage(nl2br($recapMessage));
			$state = $email->send();

		}

		else {
			log_message("debug","*** CRON : Impossible d'envoyer d'email sans connexion internet.");
		}
		
	}
	
	
	public function everyDay() {
		log_message("info","********** CRON :: everyDay() ");
		$this->sendNotif(1);
		
	}
	
	public function everyWeek() {
		log_message("info","********** CRON :: everyDay() ");
		$this->sendNotif(2);
		
	}
	
	public function everyMonth() {
		log_message("info","********** CRON :: everyDay() ");
		$this->sendNotif(3);
	}
	
	
	
	
	
	/*public function job() {

		//////     On envoie un email de relance pour les stagiaires ////////
		
		$day_limit = 7;
		$today = new DateTime();
		//echo $today->format('d-m-Y')."<br>";
		
		// On récupère la liste des stagiaires à relancer
		$trainees_list = $this->stage_model->get_all_waiting_trainees();
	
		foreach ($trainees_list as $member) {
		
			// Si la jam correspondante n'est pas archivée
			if ( ! $this->jam_model->is_archived($member->jamId)) {
			
				//echo '<li>'.$member->pseudo.'</li>';

				// On normalise les dates
				$date = date_create_from_format("Y-m-d",$member->date_relance);
				$date_inscr = date_create_from_format("Y-m-d",$member->stage_membres_date_inscr);
				
				// On calcule le nombre de jour écoulé après l'inscription sans réception du chèque
				$diff = $today->diff($date);
				//echo "Format  : ".date_format($date,"d/m/Y")."  ".$diff->format('%R %d jours et %m mois')."<br>";
				
				if ($diff->format('%d') > $day_limit || ( $diff->format('%m') > 0 && $day_limit < 30)) {
					// On envoie un mail de relance
					if ($this->config->item('net')) {

						$this->load->library('email');
					
						// Le message
						$message = '
									<p>
										Nous avons bien enregistré votre pré-inscription le '.date_format($date_inscr,"d/m/Y").'.<br>
										Afin de finaliser la procédure d\'inscription au stage, merci d\'envoyer dès que possible un chèque de <b>'.$member->cotisation.'&euro;</b> à l\'ordre de <b>'.$member->ordre.'</b> à l\'adresse suivante:						
									</p>
									<div class="small_block_info">
										<p>'.$member->ordre.'<br>'.$member->adresse.'</p>
									</div><br>
									<p>Vous serez notifié par email de la bonne réception de votre réglement.</p>
									<p>A bientôt !<br><i class="note">L\'équipe du Grenoble Reggae Orchestra</i></p>';
					
						$this->email->from('manage@le-gro.com','manage@le-gro.com');
						$this->email->reply_to("contact@le-gro.com");
						$this->email->to( array ($member->email_tuteur, $member->email) );
						$this->email->subject("Grenoble Reggae Orchestra ~ Stage // Relance");
						$this->email->message($message);
						$this->email->send();
						//echo "SEND !!";
					}
					
					// On actualise la date de relance		
					$this->stage_model->update_relance($member->stage_membres_id, $today->format('Y-m-d'));
				}
			}
			
		}

		
	}*/
	
}

?>