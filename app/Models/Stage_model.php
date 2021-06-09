<?php namespace App\Models;

use CodeIgniter\Model;

class Stage_model extends Model {
	
	
	public function get_stage($stageId){
		$builder = $this->db->table('stage');
		return $builder->getWhere([ 'id' => $stageId ])->getRow();
	}
	
	public function set_stage($stage_item){
		$builder = $this->db->table('stage');
		return $builder->insert('stage', $stage_item);
	}
	
	public function get_stage_membersId($memberId, $jamId) {
		$builder = $this->db->table('stage_membres_relation');
		$query = $builder->getWhere([ 'membresId' => $memberId, "" ]);
		return $query->getRowArray();
	}
	
	public function get_stage_jamId($jamId) {
		$query = $this->db->query('
					SELECT *, stage.id as id
					FROM stage
					LEFT JOIN lieux
					ON lieux.id = stage.lieuxId
					WHERE stage.jamId = '.$jamId.'
				');
			
		return $query->getRowArray();
		
	}
	
	// Retourne le stage en fonction d'un stage_membres_relation ID
	public function get_stage_stmbId($relId) {
		$query = $this->db->query('
					SELECT *
					FROM stage
					LEFT JOIN stage_membres_relation
					ON stage_membres_relation.stageId = stage.id
					WHERE stage_membres_relation.id = '.$relId.'
				');
				
		return $query->getRow();
	}
	
	
	// Créé une relation d'inscription entre un membre et un stage
	public function join_stage_member($data) {
		$builder = $this->db->table('stage_membres_relation');
		return $builder->insert($data);
	}
	
	// Delete une relation d'inscription entre un membre et un stage
	public function unjoin_stage_member($jamId, $stageId, $memberId) {

		// On récupère l'id correspondant à la ref (jamId+membresId)
		$builder = $this->db->table('jam_membres_relation');
		$query = $builder->getWhere([ 'jamId' => $jamId, 'membresId' => $memberId ]);
		
		// On efface les inscriptions de morceaux
		if (!empty($query->getRow())) {
			$row = $query->getRow();
			$builder = $this->db->table('inscriptions');			
			$builder->delete([ 'jam_membresId' => $row->id ]);
		}
		
		// On efface l'inscription au stage (reste l'inscription à la jam)
		$builder = $this->db->table('stage_membres_relation');	
		$builder->delete([
							'stageId' => $stageId,
							'membresId' => $memberId
						]);
	}
	
	
	// Savoir si une jam a un stage
	public function got_stage($jamId) {
		$builder = $this->db->table('stage');
		$query = $builder->getWhere('jamId', $jamId);
		return (!empty($query->getRow()));
	}
	
	// Savoir si un stagiaire appartient à une jam
	public function is_included($idJam, $idMembre) {
		// On vérifie que la jam possède un stage
		$builder = $this->db->table('stage');
		$query = $builder->getWhere(['jamId' => $idJam]);
		if (!empty($query->getRow())) {
			// On récupère l'id du stage
			$stageId = $query->getRow()->id;
			
			//$this->db->select('*');
			//$this->db->from('stage_membres_relation');
			//$this->db->join('stage', 'stage.id = stage_membres_relation.stageId');
			//$this->db->where('membresId', $idMembre);
			//$this->db->where('stage.id', $stageId);
			//$query2 = $this->db->get();
			
			
			$query2 = $this->db->query('
					SELECT *
					FROM stage_membres_relation
					INNER JOIN stage
					ON stage.id = stage_membres_relation.stageId
					WHERE stage.id = '.$stageId);
			
			if (!empty($query2->getRow())) {
				return $query2->getResult();
			}
		}
		return false;
	}
	
	
	// Récupérer la liste des membres d'un stage donné
	public function get_list_members($stageId) {		
		
		$query = $this->db->query('
				SELECT *, membres.id as memberId
				FROM membres
				INNER JOIN stage_membres_relation
				ON stage_membres_relation.membresId = membres.id
				WHERE stage_membres_relation.stageId = '.$stageId.'
				');
				
		if (!empty($query->getRow())) {
			return $query->getResult();
		}
		else return false;
	}
	
	
	// On gère la création ou l'update si l'élément n'existe pas
	public function update_stage($new_stage, $jamId) {
		$builder = $this->db->table('stage');
		$query = $builder->getWhere([ 'jamId' => $jamId ]);
		if (!empty($query->getRow())) {
			$builder->where([ 'jamId' => $jamId ]);
			$builder->update($new_stage);
		}
		else {
			return $builder->insert($new_stage);
		}
	}
	
	
	// Récupérer la liste des stagiaires n'ayant pas encore payés
	public function get_all_waiting_trainees() {	
	
		$trainees = $this->db->query('
				SELECT email_tuteur, email, pseudo, adresse, cotisation, ordre, date_relance, stage_membres_relation.date_inscr as stage_membres_date_inscr, jamId, stage_membres_relation.id as stage_membres_id
				FROM membres
				INNER JOIN stage_membres_relation
				ON stage_membres_relation.membresId = membres.id
				INNER JOIN stage
				ON stage.id = stage_membres_relation.stageId
				INNER JOIN jam
				ON jam.id = stage.jamId
				WHERE stage_membres_relation.cheque = 0
				');
		
		return $trainees->getResult();
	}
	
	
	// On récupère le membre en fonction d'un stage_membersId
	public function get_member_by_stageMemberId($stageMemberId) {
		$query = $this->db->query('
				SELECT email_tuteur, email, pseudo, stage_membres_relation.date_inscr as stage_membres_date_inscr, jamId
				FROM membres
				INNER JOIN stage_membres_relation
				ON stage_membres_relation.membresId = membres.id
				INNER JOIN stage
				ON stage.id = stage_membres_relation.stageId
				INNER JOIN jam
				ON jam.id = stage.jamId
				WHERE stage_membres_relation.id = '.$stageMemberId.'
			');
		
		return $query->getRow();
	}
	
	
	// On gère l'update de l'état de réception du chèque
	public function update_cheque_state($tmemberId, $state) {
		$builder = $this->db->table('stage_membres_relation');		
		$builder->set('cheque', $state);
		$builder->where([ 'id' => $tmemberId ]);
		$builder->update();	
	}
	
	
	// On gère l'update de la date de relance
	public function update_relance($stage_memberId, $relance) {	
		$builder = $this->db->table('stage_membres_relation');		
		$builder->set('date_relance', $relance);
		$builder->where([ 'id' => $stage_memberId ]);
		$builder->update();	
	}
	
	
	
	public function delete_stage_jamId($jamId) {
		if (!empty($jamId)) {
			$builder = $this->db->table('stage');
			$builder->delete([ 'jamId' => $jamId ]); 
		}
	}


}