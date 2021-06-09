<?php namespace App\Models;

use CodeIgniter\Model;

class Artists_model extends Model {
	
	public function get_artists($all = true) {
		$builder = $this->db->table('artistes');
		$builder->orderBy("label");
		// On peut exclure l'artiste -1 : aucun
		if (!$all) $query = $builder->getWhere([ 'id >' => 0 ]);
		else $query = $builder->get();
		if (!empty($query->getRow())) {
			return $query->getResult();
		}
	}
	
	public function get_artist($id) {
		$builder = $this->db->table('artistes');
		$query = $builder->getWhere([ 'id' => $id ]);
		if (!empty($query->getRow())) {
			return $query->getRow();
		}
	}
	

	public function get_artist_label($id) {
		$builder = $this->db->table('artistes');
		$query = $builder->getWhere([ 'id' => $id ]);
		if (!empty($query->getRow())) {
			return $query->getRow()->label;
		}
	}

	
	public function get_idArtist($label) {
		$builder = $this->db->table('artistes');
		$query = $builder->getWhere([ 'label' => $label ]);
		if (!empty($query->getRow())) {
			return $query->getRow()->id;
		}
	}
	
	
	public function get_artist_by_label($label) {
		$builder = $this->db->table('artistes');
		$query = $builder->getWhere([ 'label' => $label ]);
		if (!empty($query->getRow())) {
			return $query->getRow();
		}
	}
	
	
	// On insère un nouvel artiste
	public function set_artist($artist){
		$builder = $this->db->table('artistes');
		$builder->insert($artist);
		return $this->db->insertID();
	}
	
}