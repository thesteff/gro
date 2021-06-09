<?php namespace App\Models;

use CodeIgniter\I18n\Time;
use CodeIgniter\I18n\TimeDifference;
use App\Models\Discussion_model;
use CodeIgniter\Model;

class Message_model extends Model {

	protected $table = "message";
	protected $primaryKey = "id";
	
	protected $returnType = "array";
	protected $useSoftDeletes = true;
	
	protected $allowedFields = [ 'membresId', 'text', 'targetTag', 'targetId' ];
	
	protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    protected $validationRules    = [];
    protected $validationMessages = [];
    protected $skipValidation     = false;


	// On rend les dates lisibles + ajout de timeAgo
	public function normalize_dates($message) {
			
		if (isset($message)) {
			
			// CREATED AT
			// Date lisible
			$created_at = new Time($message['created_at']);
			$jour = $created_at->toLocalizedString('EEEE d MMMM');
			$heure = $created_at->toLocalizedString('H:mm');
			$message['createdReadable'] = "le ".$jour." à ".$heure;
			
			// UPDATED AT
			// Date lisible
			$updated_at = new Time($message['updated_at']);
			$jour = $updated_at->toLocalizedString('EEEE d MMMM');
			$heure = $updated_at->toLocalizedString('H:mm');
			$message['updatedReadable'] = "le ".$jour." à ".$heure;
			
			// DELETED AT
			// Date lisible
			$deleted_at = new Time($message['deleted_at']);
			$jour = $deleted_at->toLocalizedString('EEEE d MMMM');
			$heure = $deleted_at->toLocalizedString('H:mm');
			$message['deletedReadable'] = "le ".$jour." à ".$heure;
			
			// Différence de date lisible
			$now = new Time('now');
			if ($message['createdReadable'] == $message['updatedReadable']) $diff = $now->difference($created_at);
			else $diff = $now->difference($updated_at);
			$diffHumanized = $diff->humanize();
			$message['timeAgo'] = $diffHumanized;
			
			// Affichage adaptatif selon date courante
			$flexDate = "";
			$flexMiniDate = "";
			// Même jour
			if ($now->getDay() == $created_at->getDay()) {
				$flexDate = $created_at->toLocalizedString('H:mm');
				$flexMiniDate = $created_at->toLocalizedString('H:mm');
			}
			else {
				$diff = $now->difference($created_at);
				// Moins d'une semaine
				if ($diff->getDays() > -7 && $diff->getYears() == 0) {
					$jour = ucfirst($created_at->toLocalizedString('EEEE'));
					$flexDate = $jour." ".$created_at->toLocalizedString('H:mm');
					$miniJour = $created_at->toLocalizedString('EEE');
					$flexMiniDate = $miniJour." ".$created_at->toLocalizedString('H:mm');
				}
				// Plus d'une semaine
				else {
					$jour = $created_at->toLocalizedString('d MMMM yyyy');
					$flexDate = $jour." à ".$created_at->toLocalizedString('H:mm');
					$miniJour = $created_at->toLocalizedString('d MMM yyyy');
					$flexMiniDate = $miniJour." à ".$created_at->toLocalizedString('H:mm');
				}
			}
			$message['flexDate'] = $flexDate;
			$message['flexMiniDate'] = $flexMiniDate;
			
			return $message;
		}
		
		else return false;
		
	}

	
	// On récupère les messages en fonction d'une target
	public function get_messages($targetTag, $targetId, $order = "DESC") {
		
		//log_message("debug","get_messages **************** ");
		
		$query = $this->db->query('
				SELECT message.id as id, text, created_at, updated_at, deleted_at,
					membres.id as memberId, pseudo, targetTag, targetId, hasAvatar
				FROM message
				LEFT JOIN membres
				ON message.membresId = membres.id
				WHERE message.targetTag = '.$targetTag.'
				AND message.targetId = '.$targetId.'
				ORDER BY created_at '.$order.'
				');
				
		$messageArray = $query->getResultArray();

		//log_message("debug","messageArray : ".json_encode($messageArray));

		// On normalize les dates
		foreach($messageArray as $key => $message) {
			$messageArray[$key] = $this->normalize_dates($messageArray[$key]);
		}
		
		// Dans la liste de message, on détermine ceux qui lancent une nouvelle discussion (différence de temps > 1h)
		for ($i=0; $i < sizeOf($messageArray); $i++) {
			/*log_message("debug","key : ".json_encode($i));
			log_message("debug","current : ".json_encode($messageArray[$i]));*/
			//log_message("debug","next : ".json_encode($messageArray[$i+1]));
			if ( ($i+1) == sizeOf($messageArray)) {
				$messageArray[$i]["newThread"] = true;
			}
			else {
				$created_at = new Time($messageArray[$i]['created_at']);
				$next = new Time($messageArray[$i+1]['created_at']);
				$diff = $next->difference($created_at);
				// Moins d'une année
				$messageArray[$i]["newThread"] = $diff->getHours() > 1;				
			}
		}
		
		//log_message("debug","messageArray normalized : ".json_encode($messageArray));
		return $messageArray;
	}
	
	
	// Compte le nombre de message unread dans les discussions
	public function get_nb_unread_message($memberId) {
		
		//log_message("debug"," ****************   Message_model :: get_nb_unread_message()   ****************");
		$discussion_model = new Discussion_model();
		
		$discArray = $discussion_model->get_discussions($memberId);
		//log_message("debug","discArray :".json_encode($discArray));
		
		// On parcourt chaque discussion du membre pour compter le nombre de message non lus
		$count = 0;
		for ($i=0; $i < sizeOf($discArray); $i++) {

			// Si on a jamais regardé la discussion, tous les messages sont comptés
			if ($discArray[$i]['read_at'] == NULL) {
				//log_message("debug","read_at : NULL");
				$query = $this->db->query('
					SELECT COUNT(*)
					FROM message
					WHERE message.targetTag = 6
					AND message.targetId = '.$discArray[$i]['discussionId'].'
				');
				$count += sizeOf($query->getResult());
			}
			
			else {
				//log_message("debug","read_at :".$discArray[$i]['read_at']);
				$query = $this->db->query('
					SELECT *
					FROM message
					WHERE message.targetTag = 6
					AND message.targetId = '.$discArray[$i]['discussionId'].'
					AND DATEDIFF(created_at,"'.$discArray[$i]['read_at'].'") > 0
					');
					
				$messageArray = $query->getResultArray();
				
				//log_message("debug","messageArray :".json_encode($messageArray));
				$count += sizeOf($messageArray);
			}
		}
		
		return $count;
		
	}
	
	
	// Permet de savoir si le message précédent dans la discussion > 1h
	public function is_new_thread($messageId) {
		
		$query = $this->getWhere(['id' => $messageId]);
		$message = $query->getRow();
		
		//log_message("debug","is_new_thread ****************");
		//log_message("debug","message : ".json_encode($message));
		
		$messageArray = $this->get_messages($message->targetTag, $message->targetId);
		//log_message("debug","messageArray : ".json_encode($messageArray));
		
		// On retrouve notre message
		$i = 0;
		while ($i < sizeOf($messageArray)) {
			if ($messageArray[$i]["id"] == $messageId) break;
			$i++;
		}
		
		//log_message("debug","i : ".$i);
		
		// Premier message de la discussion
		if (sizeOf($messageArray) == 1) return true;
		// Sinon on regarde le précédent
		else if (($i + 1) < sizeOf($messageArray)) {
			//log_message("debug","prec message : ".json_encode($messageArray[$i+1]));
			$created_at = new Time($messageArray[$i]['created_at']);
			$next = new Time($messageArray[$i+1]['created_at']);
			$diff = $next->difference($created_at);
			// Moins d'une année
			return $diff->getHours() > 1;
		}
		
		return true;
	}
	
	
}