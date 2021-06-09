<?php namespace App\Models;

use CodeIgniter\Model;

class Lieux_model extends Model {

	
	public function get_lieux($nom = FALSE) {
		$builder = $this->db->table('lieux');
		
		if ($nom === FALSE) {
			$builder->orderBy("nom", "desc"); 
			$query = $builder->getWhere([ 'id >' => '0' ]);
			return $query->getResultArray();
		}
		
		$query = $builder->getWhere([ 'nom' => $nom ]);
		if (!empty($query->getRow())) {
			return $query->getRowArray();
		}
		else return false;
	}
	
	
	public function get_lieux_by_id($id = 0) {
		$builder = $this->db->table('lieux');
		$builder->where('id', $id);
		$query = $builder->get();
		return $query->getRowArray();
	}
	
	
	public function set_lieux($lieux_item){
		$builder = $this->db->table('lieux');
		return $builder->insert('lieux', $lieux_item);
	}
	
	
	public function update_lieux($old_slug,$lieux_item) {
		$builder = $this->db->table('lieux');
		$builder->where('slug', $old_slug);
		return $builder->update('lieux', $lieux_item);
	}	

	
	public function delete_lieux($slug) {
		if (!empty($slug)) {
			$builder = $this->db->table('lieux');
			return $builder->delete('lieux', array('slug' => $slug)); 
		}
	}
	
}