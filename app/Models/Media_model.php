<?php namespace App\Models;

use CodeIgniter\Model;

class Media_model extends Model {
	
	
	public function set_media($media) {
		$builder = $this->db->table('media');
		$builder->insert($media);
		$insertID = $this->db->insertID();
		return $insertID;
	}
	
	// Retourne un media
	public function get_media($mediaId) {
		$builder = $this->db->table('media');
		$query = $builder->getWhere([ 'id' => $mediaId ]);
		return $query->getRow();
	}
	
	// Retourne un media à partir d'un filename + version
	public function get_media_by_filename($filename, $versionId) {
		$builder = $this->db->table('media');
		$query = $builder->getWhere([ 'URL' => $filename, 'versionId' => $versionId ]);
		return $query->getRow();
	}
	
	// Retourne les media en fonction d'un id de version
	public function get_medias($versionId) {
		//log_message("debug","get_medias : $versionId");
		$query = $this->db->query('
					SELECT morceau.id as morceauId,
						morceau.titre as titre,
						version.id as versionId,
						version.collection as collection,
						media.id as mediaId,
						media.URL as URL,
						media.transpo as transpo,
						media.catId as catId,
						media.instruId as instruId,
						instru_categories.id as catId,
						instru_categories.name as catName,
						instruments.id as instruId,
						instruments.name as instruName
					FROM media
					LEFT JOIN version
					ON version.id = media.versionId
					LEFT JOIN morceau
					ON morceau.id = version.morceauId
					LEFT JOIN instru_categories
					ON instru_categories.id = media.catId
					LEFT JOIN instruments
					ON instruments.id = media.instruId
					WHERE version.id = '.$versionId.'
					');
					
		return $query->getResult();
	}
	
	// Retourne les media en fonction d'un id de version
	public function get_selected_medias($versionId, $pdfType = 'all', $pdfId = 0) {
		//log_message("debug","get_medias : $versionId");
		
		$catSelect = $pdfType == 'cat' ? "AND instru_categories.id = ".$pdfId : '';
		$instruSelect = $pdfType == 'instru' ? "AND instruments.id = ".$pdfId : '';
		
		$query = $this->db->query('
					SELECT morceau.id as morceauId,
						morceau.titre as titre,
						version.id as versionId,
						version.collection as collection,
						media.id as mediaId,
						media.URL as URL,
						media.transpo as transpo,
						media.catId as catId,
						media.instruId as instruId,
						instru_categories.id as catId,
						instru_categories.name as catName,
						instruments.id as instruId,
						instruments.name as instruName
					FROM media
					LEFT JOIN version
					ON version.id = media.versionId
					LEFT JOIN morceau
					ON morceau.id = version.morceauId
					LEFT JOIN instru_categories
					ON instru_categories.id = media.catId
					LEFT JOIN instruments
					ON instruments.id = media.instruId
					WHERE version.id = '.$versionId.'
					'.$catSelect.'
					'.$instruSelect.'
					');
					
		return $query->getResult();
	}
	
	
	public function update_media($media){
		$builder = $this->db->table('media');
		$builder->where([ 'id' => $media["id"] ]);
		return $builder->update($media);
	}

	
	// Retourne le media et les infos utiles avec (version/pdf)  / Si pas de media on renvoit la version
	public function get_media_infos($mediaId) {

		// On cherche le media
		$builder = $this->db->table('media');
		$temp = $builder->getWhere([ 'id' => $mediaId ]);
		if (!empty($temp->getRow())) {			
			$media = $this->db->query('
					SELECT morceau.id as morceauId,
						morceau.titre as titre,
						version.id as versionId,
						version.collection as collection,
						media.id as mediaId,
						media.URL as URL,
						media.transpo as transpo,
						media.catId as catId,
						media.instruId as instruId
					FROM media
					LEFT JOIN version
					ON version.id = media.versionId
					LEFT JOIN morceau
					ON morceau.id = version.morceauId
					WHERE media.id = '.$mediaId.'
					');
			
			return $media->getRow();
		}
	}

	// !!! C'est le version_model qui s'occupe de delete le fichier physique
	public function delete_media($mediaId){
		if (!empty($mediaId)) {
			$builder = $this->db->table('media');
			$builder->delete([ 'id' => $mediaId ]);
			return "success";
		}
		return "error";
	}
	
	
/********************************************************************************/	
	
	
	public function get_styles() {
		$builder = $this->db->table('tr_genre');
		$builder->orderBy("label");
		$query = $builder->get('tr_genre');
		return $query->getResult();
	}
	
	
	public function get_transpos() {
		$builder = $this->db->table('tr_tona');
		$query = $builder->getWhere([ 'tona_instru' => '1' ]);
		return $query->getResult();
	}
	
}