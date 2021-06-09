<?php namespace App\Models;

use CodeIgniter\Model;

class Group_model extends Model {

	
	// Retourne un object infos sur le group
	public function get_infos() {

		// On récupère le nombre de ref
		$query = $this->db->query('
					SELECT COUNT(*)
					FROM morceau
					LEFT JOIN version
					ON version.morceauId = morceau.id
					WHERE morceau.id > 0
					');
		$nbRef = $query->getRowArray(0)['COUNT(*)'];		
					
		// On récupère le nombre de membres
		$builder = $this->db->table('membres');
		$nbMembers = $builder->countAll();
		
		$infos = (object) [
				'nbRef' => $nbRef,
				'nbMembers' => $nbMembers
			];
			
		return $infos;
	}
	
}