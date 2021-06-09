<?php namespace App\Models;

use CodeIgniter\Model;

class Morceau_model extends Model {
	
	
	public function set_morceau($morceau) {
		$builder = $this->db->table('morceau');
		$builder->insert($morceau);
		return $this->db->insertID();
	}
	
	public function update_morceau($morceau) {
		$builder = $this->db->table('morceau');
		$builder->where([ 'id' => $morceau['id'] ]);
		$builder->update($morceau);
	}
	
	
	public function get_morceaux() {
		$builder = $this->db->table('morceau');
		$builder->orderBy('titre', 'DESC');
		//$query = $this->db->get_where('morceau', array('id >' => '0'));
		$query = $builder->get();
		return $query->getResult();
	}
	
	
	public function get_morceaux_extended() {
				
		$query = $this->db->query('
					SELECT morceau.id as morceauId,
							morceau.titre as titre,
							morceau.annee as annee,
							artistes.id as artisteId,
							artistes.label as artisteLabel
					FROM morceau
					LEFT JOIN artistes
					ON morceau.artisteId = artistes.id
					WHERE morceau.id > 0
					ORDER BY titre
					');
			
		return $query->getResult();
	}
	
	
	public function get_morceaux_and_versions() {
				
		$query = $this->db->query('
					SELECT morceau.id as morceauId,
							morceau.titre as titre,
							morceau.annee as annee,
							version.id as versionId,
							version.collection as collection,
							version.mp3URL as mp3URL,
							version.genre as genre,
							version.tempo as tempo,
							tr_tona.label as tona,
							tr_mode.label as mode,
							tr_langue.label as langue,
							version.soufflants as soufflants,
							version.choeurs as choeurs,
							artistes.id as artisteId,
							artistes.label as artisteLabel
					FROM morceau
					LEFT JOIN version
					ON version.morceauId = morceau.id
					LEFT JOIN artistes
					ON morceau.artisteId = artistes.id
					LEFT JOIN tr_langue
					ON version.langue = tr_langue.id
					LEFT JOIN tr_tona
					ON version.tona = tr_tona.id
					LEFT JOIN tr_mode
					ON version.mode = tr_mode.id
					WHERE morceau.id > 0
					ORDER BY titre
					');
			
		return $query->getResult();
	}
	
	
	public function get_morceau($id) {
		$builder = $this->db->table('morceau');
		$query = $builder->getWhere([ 'id' => $id ]);
		return $query->getRow();
	}
	
	
	public function get_morceau_by_titre($titre) {
		$builder = $this->db->table('morceau');
		$query = $builder->getWhere([ 'titre' => $titre ]);
		return $query->getRow();
	}
	
	
	public function delete_morceau($morceauId){
		if (!empty($morceauId)) {
			$builder = $this->db->table('morceau');
			return $builder->delete([ 'id' => $morceauId ]);
		}
	}
	
}