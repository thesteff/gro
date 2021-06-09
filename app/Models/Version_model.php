<?php namespace App\Models;

use CodeIgniter\Model;
use App\Models\Morceau_model;
use App\Models\Media_model;

class Version_model extends Model {
	
	// Retourne une version
	public function get_version($versionId) {
		$builder = $this->db->table('version');
		$query = $builder->getWhere(['id' => $versionId ]);
		return $query->getRow();
	}
	
	public function set_version($version) {
		$builder = $this->db->table('version');
		$builder->insert($version);
		return $this->db->insertID();
	}
	
	// Met à jour une version
	public function update_version($version) {
		$builder = $this->db->table('version');
		$builder->where([ 'id' => $version['id'] ]);
		return $builder->update($version);
	}
	
	// Retourne les version en fonction d'un id de morceau
	public function get_versions($morceauId) {
		$builder = $this->db->table('version');
		$query = $builder->getWhere([ 'id >' => '0', 'morceauId' => $morceauId ]);
		return $query->getResult();
	}
	
	// Retourne la version et les infos utiles avec (morceau)  / Si pas de version on renvoit le morceau seul
	public function get_version_infos($morceauId, $versionId) {

		// On cherche la version
		$builder = $this->db->table('version');
		$temp = $builder->getWhere(['id' => $versionId]);
		
		// Si elle existe
		if (isset($temp)) {			
			$version = $this->db->query('
					SELECT morceau.id as morceauId,
							morceau.titre as titre,
							morceau.artisteId as artisteId,
							morceau.annee as annee,
							version.id as versionId,
							version.collection as collection,
							version.mp3URL as mp3URL,
							version.genre as genre,
							version.tona as tona,
							version.mode as mode,
							version.tempo as tempo,
							version.langue as langue,
							version.soufflants as soufflants,
							version.choeurs as choeurs,
							artistes.id as artisteId,
							artistes.label as artisteLabel
					FROM version
					LEFT JOIN morceau
					ON morceau.id = version.morceauId
					LEFT JOIN artistes
					ON morceau.artisteId = artistes.id
					WHERE version.id = '.$versionId.'
					');
			
			return $version->getRow();
		}
		
		// Si la version n'existe pas, on cherche le morceau seul
		$temp = $this->db->get('morceau');
		if (isset($temp)) {			
			$version = $this->db->query('
					SELECT morceau.id as morceauId, morceau.titre as titre, morceau.artisteId as artisteId, morceau.annee as annee, artistes.id as artisteId, artistes.label as artisteLabel
					FROM morceau
					LEFT JOIN artistes
					ON morceau.artisteId = artistes.id
					WHERE morceau.id = '.$morceauId.'
					');
			
			return $version->getRow();
		}
	}

	
	public function delete_version($versionId){
		if (!empty($versionId)) {
			$builder = $this->db->table('version');
			return $builder->delete([ 'id' => $versionId ]);
		}
	}
	
	
	// Supprime un fichier attaché à une version
	public function delete_version_file($morceauId, $versionId, $filename) {

		$morceau_model = new Morceau_model();
		$media_model = new Media_model();

		$morceau = $morceau_model->get_morceau($morceauId);
		$version = $this->get_version($versionId);

		log_message("debug","DELETE_version_file : ".json_encode($version));
		log_message("debug","filename : ".$filename);
		
		// On regarde si le fichier existe si oui on le supprime
		$filePath = realpath(FCPATH."/ressources/morceaux/".dir_path($morceau->titre)."/".$version->collection."/".$filename);
		$state = false;
		if (file_exists($filePath))
			$state = unlink($filePath);
		
		// On supprime la référence du fichier dans la version
		if ($state) {
			if ($filename == $version->mp3URL) {
				$version->mp3URL = '';
				$this->update_version((array)$version);
			}
			// Si c'est un pdf, il faut le retrouver
			else {
				log_message("debug","ELSE ===");
				$media = $media_model->get_media_by_filename($filename, $versionId);
				$media_model->delete_media($media->id);
			}
			return "success";
		}
		else return "file_not_found";
	}
	
	/********************************************************************************/		
	
	public function nbPlayed($versionId = -1) {
		
		// Un morceau peut ne pas avoir de version
		if ($versionId == null) $versionId = -1;
		
		$query = $this->db->query('
					SELECT COUNT(*)
					FROM playlist_version_relation
					INNER JOIN playlist
					ON playlist.id = playlist_version_relation.playlistId
					INNER JOIN jam
					ON jam.playlistId = playlist.id
					WHERE playlist_version_relation.versionId = '.$versionId.'
					AND jam.date < "'.date("Y-m-d").'"
					');
		return $query->getRowArray(0)['COUNT(*)'];
	}
	
	
	public function lastTimePlayed($versionId) {
		
		// Un morceau peut ne pas avoir de version
		if ($versionId == null) $versionId = -1;
		
		$query = $this->db->query('
					SELECT MAX(jam.date) as lastTimeDate
					FROM playlist_version_relation
					INNER JOIN playlist
					ON playlist.id = playlist_version_relation.playlistId
					INNER JOIN jam
					ON jam.playlistId = playlist.id
					WHERE playlist_version_relation.versionId = '.$versionId.'
					AND jam.date < "'.date("Y-m-d").'"
					');
		
		$lastDate = $query->getRow()->lastTimeDate;
		if ($lastDate) {
			$tempDate = date_create_from_format("Y-m-d",$lastDate);
			return date_format($tempDate,"d/m/Y");
		}
		else return "jamais";
	}
	

	
	
	
	/********************************************************************************/	
	
	public function get_collections() {
		$builder = $this->db->table('tr_collection');
		$query = $builder->get();
		return $query->getResult();
	}
	
	
	public function get_styles() {
		$builder = $this->db->table('tr_genre');
		$builder->orderBy("label");
		$query = $builder->get();
		return $query->getResult();
	}
	
	
	public function get_tonas() {
		$builder = $this->db->table('tr_tona');
		$query = $builder->getWhere([ 'id >' => '0' ]);
		return $query->getResult();
	}
	
	public function get_modes() {
		$builder = $this->db->table('tr_mode');
		$query = $builder->getWhere([ 'id >' => '0' ]);
		return $query->getResult();
	}
	
	public function get_langues() {
		$builder = $this->db->table('tr_langue');
		$query = $builder->get();
		return $query->getResult();
	}
	
}