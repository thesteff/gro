<?php namespace App\Models;

use CodeIgniter\Model;

use App\Models\Jam_model;
use App\Models\Instruments_model;
use App\Models\Formation_model;
use CodeIgniter\I18n\Time;

class Members_model extends Model {
	
	
	public function get_members($pseudo = "null", $sort = "null", $keyword = null) {
		
		$builder = $this->db->table('membres');
		
		// Si on fait une recherche
		if ($keyword != null) {
			$query = $this->db->query('
				SELECT id, pseudo, nom, prenom
				FROM membres
				WHERE (lower(pseudo) LIKE "'.$keyword.'%")
				OR (lower(nom) LIKE "'.$keyword.'%")
				OR (lower(prenom) LIKE "'.$keyword.'%")
				OR (lower(pseudo) LIKE "% '.$keyword.'%")
				OR (lower(nom) LIKE "% '.$keyword.'%")
				OR (lower(prenom) LIKE "% '.$keyword.'%")
				OR (lower(pseudo) LIKE "%-'.$keyword.'%")
				OR (lower(nom) LIKE "%-'.$keyword.'%")
				OR (lower(prenom) LIKE "%-'.$keyword.'%")
				OR (lower(pseudo) LIKE "%.'.$keyword.'%")
				OR (lower(nom) LIKE "%.'.$keyword.'%")
				OR (lower(prenom) LIKE "%.'.$keyword.'%")
			');
				
			return $query->getResult();
		}
		
		// Récupération d'un membre
		else if ($pseudo != "null") $builder->where('pseudo',urldecode($pseudo));
		// Récupération de tous les membres
		else if ($sort != "null") $builder->orderBy('pseudo', $sort);
		$query = $builder->get();
		
		if (!empty($query->getRow())) {
			if ($pseudo != "null") {
				$builder->where('pseudo',$pseudo);
				$builder->update(['slug' => url_title($pseudo)]);
				// on retourne le membre
				return $query->getRow();
			}
			else return $query->getResult();
		}
		else return false;
	}
	
	
	
	public function get_member($pseudo) {
		$builder = $this->db->table('membres');	
		$builder->where('pseudo',urldecode($pseudo));
		$query = $builder->get();
		return $query->getRow();
	}
	
	
	// Renvoie le pseudo à partir d'un email
	public function get_member_by_email($email) {
		$builder = $this->db->table('membres');	
		$builder->where('email',$email);
		$query = $builder->get();
		return $query->getRow();
	}
	
	public function get_member_by_id($id) {
		$builder = $this->db->table('membres');	
		$builder->where('id',$id);
		$query = $builder->get();
		return $query->getRow();
	}
	
	public function get_member_by_slug($slug) {
		$builder = $this->db->table('membres');	
		$builder->where('slug',$slug);
		$query = $builder->get();
		return $query->getRow();
	}
	
	public function get_pseudo($id) {
		$builder = $this->db->table('membres');	
		$builder->where('id',$id);
		$query = $builder->get();
		return $query->getRow()->pseudo;
	}
	
	public function calcul_age($tmember) {
		// On calcule l'age
		$dob = new Time($tmember->naissance);
		$now = new Time();
		$difference = $now->diff($dob);
		//Get the difference in years
		$age = $difference->y;
		if ($age > 0) $tmember->age = $age;
		else $tmember->age = '';
	}
	
	
	// On insère un membre et on return l'id
	public function set_member($data) {
		
		// On formate le nom/prénom
		$data['nom'] = mb_strtoupper( mb_substr( $data['nom'], 0, 1 )) . mb_strtolower( mb_substr( $data['nom'], 1 ));
		$data['prenom'] = mb_strtoupper( mb_substr( $data['prenom'], 0, 1 )) . mb_strtolower( mb_substr( $data['prenom'], 1 ));
		
		log_message("debug","************ Members_model :: set_member : ".json_encode($data));
		
		$builder = $this->db->table('membres');	
		$builder->insert($data);
		$id = $this->db->insertID();
		return $id;
	}
	
	
	public function update_member($id, $data) {
		
		// On formate le nom/prénom
		if (isset($data['nom'])) $data['nom'] = mb_strtoupper( mb_substr( $data['nom'], 0, 1 )) . mb_strtolower( mb_substr( $data['nom'], 1 ));
		if (isset($data['prenom'])) $data['prenom'] = mb_strtoupper( mb_substr( $data['prenom'], 0, 1 )) . mb_strtolower( mb_substr( $data['prenom'], 1 ));
		
		$builder = $this->db->table('membres');	
		$builder->where(['id' => $id]);
		return $builder->update($data);
	}
	
	
	// Retourne les jams auxquelles le membre participe
	public function get_jams($memberId, $archived = false) {

		$modifier = '';
		if (!$archived) $modifier = 'AND date > NOW()';
		
		
		$list_jams = $this->db->query('
			SELECT jamId, slug, title, date
			FROM jam_membres_relation
			INNER JOIN jam
			ON jam.id = jam_membres_relation.jamId
			WHERE jam_membres_relation.membresId = '.$memberId.'
			'.$modifier.'
			GROUP BY jamId
		');
			
		return $list_jams->getResultObject();
	}
	
	
	// Retourne la liste des notifications non lues (pour l'instant que les invitations)
	public function get_notifications($memberId) {

		// On récupère les invit de jam pas encore passée qui n'ont pas de réponse (state = -1)
		$list_invit = $this->db->query('
			SELECT invitation.id as id, senderId, state, targetId as jamId, created_at, title, jam.slug as jamSlug, jam.date as jamDate, pseudo
			FROM invitation
			INNER JOIN jam
			ON jam.id = invitation.targetId
			INNER JOIN membres
			ON invitation.senderId = membres.id
			WHERE invitation.receiverId = '.$memberId.'
			AND invitation.targetTag = 2
			AND state = -1
			AND jam.date > NOW()
		');
			
		return $list_invit->getResultObject();
	}
	
	
	
	public function update_date_access($id) {
		$date_iso = date('c');
		$builder = $this->db->table('membres');	
		$builder->where('id',$id);
		$builder->update([ 'date_access' => $date_iso ]);
	}
	
	
	// Update le cookie lors d'une session
	public function update_cookie($id, $rdmStr) {
		$builder = $this->db->table('membres');	
		$builder->where('id',$id);
		$builder->update([ 'cookie_str' => $rdmStr ]);
	}
	
	
	// Check l'existence d'un cookie et renvoit le membre s'il existe
	public function check_cookie($str) {
		$builder = $this->db->table('membres');
		$query = $builder->getWhere([ 'cookie_str' => $str ]);
		if (!empty($query->getRow())) {
			return $query->getRow();
		}
		return false;
	}
	
	
	// Check la présence de l'id dans la base ainsi que son adéquation avec le pass
	public function check_id($id, $pass) {
		$builder = $this->db->table('membres');	
		$builder->where('id',$id);
		$builder->where('pass',sha1($pass));
		return $builder->countAllResults(false) == 1;
	}
	
	// Check la présence du pseudo dans la base ainsi que son adéquation avec le pass
	public function check_pseudo($pseudo, $pass) {
		$builder = $this->db->table('membres');	
		$builder->where('pseudo',$pseudo);
		$builder->where('pass',sha1($pass));
		return $builder->countAllResults(false) == 1;
	}
	
	// Check la présence de l'email dans la base ainsi que son adéquation avec le pass
	public function check_email($email, $pass) {
		$builder = $this->db->table('membres');	
		$builder->where('email',$email);
		$builder->where('pass',sha1($pass));
		return $builder->countAllResults(false) == 1;
	}
	
	// Check la présence de l'email dans la base
	public function email_exist($email) {
		$builder = $this->db->table('membres');	
		$builder->where('email',$email);
		return $builder->countAllResults(false) == 1;
	}
	
	
	/***********************   ADMIN   *************************/
	
	public function isSuperAdmin($id) {
		$builder = $this->db->table('membres');	
		$query = $builder->getWhere([ 'id' => $id ]);
		if (!empty($query->getRow())) {
			return $query->getRow()->admin == 1;
		}
		return false;
	}
	
	
	// Retourne les jams dont le membre est admin
	public function get_jams_admin($memberId, $jamArchived = true) {
		
		$modifier = '';
		if (!$jamArchived) $modifier = 'AND date > NOW()';
		
		$list_jams = $this->db->query('
			SELECT jamId, title, date, lieuxId
			FROM jam_membres_relation
			INNER JOIN jam
			ON jam.id = jam_membres_relation.jamId
			WHERE jam_membres_relation.event_admin = 1
			'.$modifier.'
			AND jam_membres_relation.membresId = '.$memberId.'
		');
			
		return $list_jams->getResultArray();
	}
	
	
	/***********************   INSTRUMENTS   *************************/

	public function get_instruments($memberId) {

		$query = $this->db->query('
				SELECT 
					instruments.id as instruId,
					instruments.name as instruName
				FROM instruments
				INNER JOIN membre_instruments
				ON instruments.id = membre_instruments.instrumentsId
				INNER JOIN membres
				ON membres.id = membre_instruments.membresId
				WHERE membres.id = '.$memberId.'
				ORDER BY membre_instruments.id
				');
				
		if (!empty($query->getResultArray())) {
			return $query->getResultArray();
		}
		else return false;
	}
	
	
	public function add_instrument($memberId, $instruId) {
		$builder = $this->db->table('membre_instruments');
		
		$builder->where(['membresId' => $memberId, 'instrumentsId' => $instruId]);
		$query = $builder->get();
		// L'instrument existe déjà dans les instruments joués par le membre
		if (!empty($query->getRow())) {
			return false;
		}
		else {
			return ! $builder->insert(['membresId' => $memberId, 'instrumentsId' => $instruId]) == false ;
		}
	}
	
	
	public function update_instrument($memberId, $instruId, $oldInstruId) {

		// On récupère l'object initial
		$builder = $this->db->table('membre_instruments');
		$builder->where(array('membresId' => $memberId, 'instrumentsId' => $oldInstruId));
		$query = $builder->get();
		$object = $query->getRowArray();
		
		// On fait la modif
		$object['instrumentsId'] = $instruId;
		
		// On update
		$builder->where(array('membresId' => $memberId, 'instrumentsId' => $oldInstruId));
		return $builder->update($object);
	}
	
	
	public function delete_instrument($memberId, $instruId) {
		$builder = $this->db->table('membre_instruments');
		return $builder->delete(array('membresId' => $memberId, 'instrumentsId' => $instruId));
	}
	
	
	// Rajoute les infos de l'instrument principal
	public function get_mainInstru($member) {
		
		$instruments_model = new Instruments_model();
		
		// On récupère la liste des intruments joués
		$listInstruArray = $this->get_instruments($member->id);
		//log_message('debug','************ listInstruArray : '.json_encode($listInstruArray));
		
		// Si le membre joue au moins d'un instrument...
		if ($listInstruArray != false) {
			// On récupère les infos du premier instrument joué
			$member->mainInstruName = $listInstruArray[0]['instruName'];
			$member->mainInstruId = $listInstruArray[0]['instruId'];
		}
		else {
			$member->mainInstruName = '';
			$member->mainInstruId = '';
		}
	}
	
	
	// Génère une string avec la liste des instru ($member->instruList)
	// Ajoute les infos instrumentales 'mainInstruName','mainInstruId','mainFamily','mainPupitre'->['id,'pupitreLabel','iconURL'] au membre $membre
	// Dépend donc de la formationId !
	public function get_instruments_info($member, $formationId, $isStagiaire = false) {
		
		$instruments_model = new Instruments_model();
		$formation_model = new Formation_model();
		
		// On récupère la liste des intruments joués et on concatène leur label dans une string
		if (!$isStagiaire) $listInstruArray = $this->get_instruments($member->id);
		else $listInstruArray = $this->get_instruments($member->memberId);
		//log_message('debug','************ listInstruArray : '.json_encode($listInstruArray));
		$listInstru = "";
		
		// Si le membre joue au moins d'un instrument...
		if ($listInstruArray != false) {
			for ($i=0; $i < sizeof($listInstruArray); $i++) {
				if ($i > 1) $listInstru .= ", ";
				if (strlen($listInstruArray[$i]['instruName']) == 0) $listInstru = "Aucun";
				else if ($i == 0)  $listInstru .= "<b>".$listInstruArray[$i]['instruName']."</b><br>";
				else $listInstru .= $listInstruArray[$i]['instruName'];
			}
			$member->instruList = $listInstru;
			
			// On récupère les infos du premier instrument joué
			$member->mainInstruName = $listInstruArray[0]['instruName'];
			$member->mainInstruId = $listInstruArray[0]['instruId'];
			
			// ...On récupère la catégorie principale (famille du premier instrument joué)
			$member->mainFamily = $instruments_model->get_instru_family($listInstruArray[0]['instruId'])->label;
			// ...On récupère le pupitre principal
			$member->mainPupitre = $formation_model->get_main_pupitre($listInstruArray[0]['instruId'], $formationId);
		}
		else {
			$member->instruList = $listInstru;
			$member->mainInstruName = '';
			$member->mainInstruId = '';
			$member->mainFamily = '';
			$member->mainPupitre = '';
		}
	}
	
}