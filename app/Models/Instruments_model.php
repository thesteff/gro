<?php namespace App\Models;

use CodeIgniter\Model;

class Instruments_model extends Model {


	// Retourne les instruments triés croissant sur la colonne $sort
	public function get_instruments($sort = "id", $removeOld = true) {
		$builder = $this->db->table('instruments');
		if ($removeOld) $builder->where(['categorie =' => NULL]);
		$builder->orderBy($sort);
		$query = $builder->get();
		if (!empty($query->getRow())) {
			return $query->getResultArray();
		}
	}

	
	public function get_instrument($id) {
		$builder = $this->db->table('instruments');
		$builder->where(['id' => $id]);
		$query = $builder->get();
		if (!empty($query->getRow())) {
			return $query->getRowArray();
		}
	}
	
	
	
	public function get_instrument_name($id) {
		$builder = $this->db->table('instruments');
		$builder->where(['id' => $id]);
		$query = $builder->get();
		if (!empty($query->getRow())) {
			return $query->getRow()->name;
		}
	}
	
	
	// Retourne le name d'une catégorie en fonction de l'id d'un instrument
	public function get_catInstrument($id) {
		$query = $this->db->query('
					SELECT instru_categories.name as catName FROM instru_categories
					INNER JOIN instruments
					ON instruments.categorie = instru_categories.id
					WHERE instruments.id = '.$id.'
					');
		if (!empty($query->getRow())) {
			return $query->getRow()->catName;
		}		
	}
	
	
	// Retourne le name d'une catégorie en fonction de son id
	public function get_catName($id) {		
		$builder = $this->db->table('instru_categories');
		$builder->where(['id' => $id]);
		$query = $builder->get();
		if (!empty($query->getRow())) {
			return $query->getRow()->name;
		}
	}
	
	// Retourne l'id d'un instrument en fonction de son nom
	public function get_idInstrument($instru) {
		$builder = $this->db->table('instruments');
		$builder->where(['name' => $instru]);
		$query = $builder->get();
		if (!empty($query->getRow())) {
			return $query->getRow()->id;
		}
	}
	
	// Retourne les catégories d'instrument en array
	public function get_instru_categories($order = "view_order", $all = false) {
		$builder = $this->db->table('instru_categories');
		$builder->orderBy($order);
		if (!$all) $builder->where('id >', 0);
		$query = $builder->get();
		if (!empty($query->getRow())) {
			return $query->getResultArray();
		}
	}
	
	
	// Retourne les catégories d'instrument en objet
	public function get_instru_categories2($order = "view_order", $all = false) {
		$builder = $this->db->table('instru_categories');
		$builder->orderBy($order);
		if (!$all) $builder->where('id >', 0);
		$query = $builder->get();
		if (!empty($query->getRow())) {
			return $query->getResult();
		}
	}
	
	
	// Retourne les familles d'instrument en array
	public function get_instru_families() {
		$builder = $this->db->table('tr_famille_instru');
		$query = $builder->get();
		if (!empty($query->getRow())) {
			return $query->getResultArray();
		}
	}
	
	// Retourne la famille d'un instrument donné
	public function get_instru_family($instruId) {
		$query = $this->db->query('
				SELECT *
				FROM instruments
				INNER JOIN tr_famille_instru
				ON tr_famille_instru.id = instruments.famille_instruId
				WHERE instruments.id = \''.$instruId.'\'
				');
		
		if (!empty($query->getRow())) {
			return $query->getRow();
		}
		else return false;
	}
	
	
	
	// Retourne les instruments classés par catégorie
	public function get_categorized_instruments($ignoredLabel = "null") {
	
		$instr_list = $this->get_instruments("name");
		$cat_list = $this->get_instru_categories();
		
		foreach ($cat_list as $cat) {
			$data[$cat['name']] = array(
										"id" => $cat['id'],
										"name" => $cat['name'],
										"iconURL" => $cat['iconURL'],
										"list" => Array()
										);
			foreach ($instr_list as $instru) {
				if ($instru['categorie'] == $cat['id'] && $instru['name'] != $ignoredLabel)
					$data[$cat['name']]['list'][] = $instru['id'];
			}
		}
		
		return $data;
	}

	
	
	// Retourne les instruments classés par famille
	public function get_familyzed_instruments() {
	
		$instr_list = $this->get_instruments("name");
		$family_list = $this->get_instru_families();
		
		foreach ($family_list as $family) {
			$data[$family['label']] = array(
										"id" => $family['id'],
										"label" => $family['label'],
										//"iconURL" => $family['iconURL'],
										"list" => Array()
										);
			foreach ($instr_list as $instru) {
				if ($instru['famille_instruId'] == $family['id'])
					$data[$family['label']]['list'][] = $instru['id'];
			}
		}
		
		return $data;
	}
	
	
	
	// Retourne les instruments d'une famille (recherch dynamique )
	public function get_family_instruments($value, $attr = "id") {
	
		$family_instruments = $this->db->query('
				SELECT name,
					instruments.id as instruId
				FROM instruments
				INNER JOIN tr_famille_instru
				ON tr_famille_instru.id = instruments.famille_instruId
				WHERE tr_famille_instru.'.$attr.' = \''.$value.'\'
				ORDER BY instruments.name
				');
			
		return $family_instruments->getResultArray();	
		
	}
	
}