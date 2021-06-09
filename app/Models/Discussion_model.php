<?php namespace App\Models;

use CodeIgniter\Model;
use CodeIgniter\I18n\Time;
use CodeIgniter\I18n\TimeDifference;

class Discussion_model extends Model {

	protected $table = "discussion";
	protected $primaryKey = "id";
	
	protected $returnType = "array";
	
	protected $allowedFields = [ 'lastMessageId' ];

    protected $validationRules    = [];
    protected $validationMessages = [];
    protected $skipValidation     = false;


	// Permet de récupérer les discussions d'un utilisateur
	public function get_discussions($membresId, $destList = false, $lastMessage = false, $countUnread = false) {
		
		//log_message("debug","******** Discussion_model :: get_discussions **********");
		
		// On récupère les discussions du membre
		$query = $this->db->query('
			SELECT discussion.id as discussionId, membresId, read_at, visible 
			FROM discussion 
			RIGHT JOIN discussion_membres_relation 
			ON discussion_membres_relation.discussionId = discussion.id
			WHERE discussion_membres_relation.membresId = '.$membresId.'
			');
		$discArray = $query->getResultArray();

		if ($destList) {
			// Pour chaque discussion on récupère la liste des destinataires
			foreach($discArray as $key => $value) {
				
				//log_message("debug","value : ".json_encode($value["discussionId"]));
				$query = $this->db->query('
					SELECT membres.id as membresId, pseudo, read_at
					FROM discussion_membres_relation
					LEFT JOIN membres 
					ON discussion_membres_relation.membresId = membres.id
					WHERE discussion_membres_relation.discussionId = '.$value["discussionId"].'
					AND discussion_membres_relation.membresId != '.$membresId.'
				');
				$destArray = $query->getResultArray();
				$discArray[$key]["destList"] = $destArray;
				//log_message("debug",json_encode($discArray));
			}
		}
		
		if ($lastMessage) {
			// Pour chaque discussion on récupère le dernier message en date
			foreach($discArray as $key => $value) {
				
				//log_message("debug","value : ".json_encode($value["discussionId"]));
				$query = $this->db->query('
					SELECT *
					FROM message
					WHERE targetTag = 6
					AND targetId = '.$value["discussionId"].'
					ORDER BY created_at DESC LIMIT 1
				');
				$message = $query->getRowArray();
				
				// CREATED AT
				// Date lisible
				$created_at = new Time($message['created_at']);
				
				// Affichage adaptatif selon date courante
				$flexDate = "";
				$now = new Time('now');
				// Même jour
				if ($now->getDay() == $created_at->getDay()) $flexDate = $created_at->toLocalizedString('H:mm');
				else {
					$diff = $now->difference($created_at);
					// Moins d'une année
					if ($diff->getDays() > -200) $flexDate = $created_at->toLocalizedString('d MMM');
					// Plus d'une année
					else $flexDate = $created_at->toLocalizedString('d')."/".$created_at->toLocalizedString('MM')."/".$created_at->toLocalizedString('YYYY');
				}
				$message['flexDate'] = $flexDate;
				
				$discArray[$key]["lastMessage"] = $message;
				
			}
			
			// On trie par date de dernier message
			usort($discArray, array($this,'cmp'));
		}
		
		if ($countUnread) {
			// On parcourt chaque discussion du membre pour compter le nombre de message non lus par discussion
			
			for ($i=0; $i < sizeOf($discArray); $i++) {
				$count = 0;
				
				// Si on a jamais regardé la discussion, tous les messages sont comptés
				if ($discArray[$i]['read_at'] == NULL) {
					log_message("debug","read_at : NULL");
					$query = $this->db->query('
						SELECT COUNT(*)
						FROM message
						WHERE message.targetTag = 6
						AND message.targetId = '.$discArray[$i]['discussionId'].'
					');
					$count += sizeOf($query->getResult());
				}
				
				else {
					log_message("debug","read_at :".$discArray[$i]['read_at']);
					$query = $this->db->query('
						SELECT *
						FROM message
						WHERE message.targetTag = 6
						AND message.targetId = '.$discArray[$i]['discussionId'].'
						AND DATEDIFF(created_at,"'.$discArray[$i]['read_at'].'") > 0
						');
						
					$messageArray = $query->getResultArray();
					
					log_message("debug","messageArray :".json_encode($messageArray));
					$count += sizeOf($messageArray);
				}
				$discArray[$i]['nb_unread'] = $count;
			}
		}
		
		return $discArray;
	}
	
	
	// Fonction de comparaison entre deux discussion (date dernier message)
	protected function cmp($a, $b) {
		$aDate = new Time($a['lastMessage']['created_at']);
		$bDate = new Time($b['lastMessage']['created_at']);
		if ($aDate->equals($bDate)) {
			return 0;
		}
		return ($aDate->isAfter($bDate)) ? -1 : 1;
	}


	// Créé une nouvelle discussion concernant memberId + le.s membre.s concerné.s
	public function new_discussion($memberId, $targetIdArray) {
		
		// On créé la nouvelle discussion
		$data = [
			"lastMessageId" => -1
		];
		$this->insert($data);
		$id = $this->db->insertID();
		
		// La discussion de l'emetteur
		$data = [
				'discussionId' => $id,
				'membresId' => $memberId,
				'read_at' => date('Y-m-d G:i:s')
			];
		$builder = $this->db->table('discussion_membres_relation');
		$builder->insert($data);
	
		// On créé une discussion par membre concernés
		foreach ($targetIdArray as $target){
			$data = [
				'discussionId' => $id,
				'membresId' => $target->id
			];
			$builder = $this->db->table('discussion_membres_relation');
			$builder->insert($data);
		}
		return $id;
	}
	
	// Actualise le read_at d'une discussion (discussionId, memberId et read_at dans data)
	public function update_read_at($data) {
		
		$builder = $this->db->table('discussion_membres_relation');
		$builder->update($data, ['discussionId' => $data['discussionId'], 'membresId' => $data['membresId']]);
		
	}
		
		
		
	// Permet de récupérer une discussion en fonction des destinataires
	/*public function get_discussion($targetIdArray) {
		
		log_message("debug","******** get_discussion **********");
		
		$query = $this->db->query('
				SELECT message.id as id, text, created_at, updated_at, deleted_at,
					membres.id as memberId, pseudo, targetTag, targetId
				FROM message
				LEFT JOIN membres
				ON message.membresId = membres.id
				WHERE message.targetTag = '.$targetTag.'
				AND message.targetId = '.$targetId.'
				ORDER BY created_at '.$order.'
				');
				
		$messageArray = $query->getResultArray();
		return $messageArray;
		return false;
	}*/
	
}