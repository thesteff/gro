<?php namespace App\Models;

use CodeIgniter\Model;

class Repetition_model extends Model {


	// On insère une nouvelle répétition
	public function set_repetition($repetition) {
	
		// On controle les ref envoyées et on normalise si besoin
		if (!isset($repetition['pupitreId'])) $repetition['pupitreId'] = -1;
		if (!isset($repetition['lieuxId'])) $repetition['lieuxId'] = -1;
	
		$builder = $this->db->table('repetition');
		$builder->insert($repetition);
		return $this->db->insertID();
	}


	// Récupérer les répétitions liées à une jam. ALL renvoie tout si true, les répétitions non passées si false
	public function get_repetitions($jamId, $all = true) {

		$str = $all === true ? '' : 'AND repetition.date_debut >= CURTIME()';
		$choices = $this->db->query('
				SELECT lieux.id as lieuxId, tr_pupitre.name as name, tr_pupitre.id as pupitreId, repetition.id as id, repetition.date_debut as date_debut, repetition.date_fin as date_fin, repetition.text as text, lieux.nom as lieuName, lieux.web as web, lieux.adresse as adresse
				FROM repetition
				INNER JOIN lieux
				ON lieux.id = repetition.lieuxId
				INNER JOIN tr_pupitre
				ON tr_pupitre.id = repetition.pupitreId
				WHERE repetition.jamId = '.$jamId.'
				'.$str.'
				ORDER BY repetition.date_debut
			');
				
		if (!empty($choices->getRow())) return $choices->getResultArray();
		else return false;
	}
	
	
	// Récupère une répétition en fonction de l'id
	public function get_repetition($repetId) {
		$query = $this->db->query('
					SELECT repetition.jamId as jamId, lieux.id as lieuxId, tr_pupitre.name as name, tr_pupitre.id as pupitreId, repetition.id as id, repetition.date_debut as date_debut, repetition.date_fin as date_fin, repetition.text as text, repetition.pupitreId as pupitreId, lieux.nom as lieuName, lieux.web as web, lieux.adresse as adresse
					FROM repetition
					INNER JOIN lieux
					ON lieux.id = repetition.lieuxId
					INNER JOIN tr_pupitre
					ON tr_pupitre.id = repetition.pupitreId
					WHERE repetition.id = '.$repetId.'
				');
					
		if (!empty($query->getRow())) return $query->getRowArray();
		else return false;
	}
	
	
	public function update_repetition($repet_id, $repetition) {
		$builder = $this->db->table('repetition');
		$builder->where([ 'id' => $repet_id ]);
		return $builder->update($repetition);
	}
	
	// On supprime une répétition
	public function delete_repetition($repetId) {
		$builder = $this->db->table('repetition');
		return $builder->delete([ 'id' => $repetId ]); 
	}

}