<?php namespace App\Models;

use CodeIgniter\Model;
use App\Models\Instruments_model;
use App\Models\Jam_model;

class Fichier_model extends Model {
	
	
	public function set_fichier($fichier){
		$builder = $this->db->table('fichier');
		$builder->insert($fichier);
		return $this->db->insertID();
	}
	
	// Retourne une fichier
	public function get_fichier($fichierId) {
		$builder = $this->db->table('fichier');
		$query = $builder->getWhere([ 'id' => $fichierId ]);
		return $query->getRow();
	}
	
	// Retourne la liste de fichier d'un objet parent
	public function get_fichiers_parent($parentType, $parentId, $accessType = "undefined") {
		
		$instruments_model = new Instruments_model();
		
		$builder = $this->db->table('fichier');
		if ($accessType == "undefined") $query = $builder->orderBy('id', 'asc')->getWhere([ 'parentType' => $parentType, 'parentId' => $parentId ]);
		else $query = $builder->orderBy('id', 'asc')->getWhere([ 'parentType' => $parentType, 'parentId' => $parentId, 'accessType' => $accessType ]);
		
		// On retourne un seul fichier si on veut l'affect (discutable)
		if ($accessType == 'affect') return $query->getRow();
			
		else $fichier_list = $query->getResult();
		
		// On rajoute un label à chaque fichier (cat => nom de la cat, instru => nom de l'instru, etc...)
		if ($fichier_list) {
				foreach($fichier_list as $fichier) {
					switch ($fichier->accessType) {
						case 'cat' :
							$fichier->label = $instruments_model->get_catName($fichier->accessId);
							break;
						case 'instru' :
							$fichier->label = $instruments_model->get_instrument($fichier->accessId);
							break;
						case 'tache' :
							$fichier->label = "tache";
							break;
						case 'admin' :
							$fichier->label = "admin";
							break;
						default : $fichier->label = "divers";
					}
				}
			}	
			
		return $fichier_list;
	}

	
	
	// Retourne le REALPATH du fichier en fonction de ses données
	public function get_realPath($parentType, $parentId) {

		$jam_model = new Jam_model();
		
		$realPath = "";

		switch ($parentType) {
			case 'event' :
				$realPath = $jam_model->get_file_path($parentId);
				break;
			case 'groupe' :
				break;
			case 'membre' :
				break;
			case 'lieu' :
				break;
		}	
			
		return $realPath."/";
		
	}
	
	// Efface le fichier physique et remove le fichier dans la BD
	public function remove_fichier($fichier, $filePath = 'undefined') {
		
		if (!empty($fichier)) {
			
			log_message("debug","REMOVE_fichier : ".json_encode($filePath));
			//log_message("debug","filename : ".$filename);
			
			// On regarde si le fichier existe si oui on le supprime
			$state = false;
			if (file_exists($filePath))
				$state = unlink($filePath);
			
			// On supprime la référence du fichier dans la version
			if ($state || $filePath == 'undefined') {
				$builder = $this->db->table('fichier');
				$builder->delete([ 'id' => $fichier->id ]);
				return "success";
			}
			else return "file_not_found";
		}
	}
	
	
	/*
	public function update_fichier($fichier){
		$this->db->where('id', $fichier->id);
		$this->db->update('fichier', $fichier);
	}
	
	
	
	// Retourne la fichier et les infos utiles avec (morceau)  / Si pas de fichier on renvoit le morceau seul
	public function get_fichier_infos($morceauId, $fichierId) {

		// On cherche la fichier
		$temp = $this->db->get_where('fichier', array('id' => $fichierId));
		if ($temp->num_rows() > 0) {			
			$fichier = $this->db->query('
					SELECT morceau.id as morceauId,
							morceau.titre as titre,
							morceau.artisteId as artisteId,
							morceau.annee as annee,
							fichier.id as fichierId,
							fichier.groupe as groupe,
							fichier.mp3URL as mp3URL,
							fichier.genre as genre,
							fichier.tona as tona,
							fichier.mode as mode,
							fichier.tempo as tempo,
							fichier.langue as langue,
							fichier.soufflants as soufflants,
							fichier.choeurs as choeurs,
							artistes.id as artisteId,
							artistes.label as artisteLabel
					FROM fichier
					LEFT JOIN morceau
					ON morceau.id = fichier.morceauId
					LEFT JOIN artistes
					ON morceau.artisteId = artistes.id
					WHERE fichier.id = '.$fichierId.'
					');
			
			return $fichier->first_row();
		}
		
		// On cherche le morceau
		$temp = $this->db->get('morceau');
		if ($temp->num_rows() > 0) {			
			$fichier = $this->db->query('
					SELECT morceau.id as morceauId, morceau.titre as titre, morceau.artisteId as artisteId, morceau.annee as annee, artistes.id as artisteId, artistes.label as artisteLabel
					FROM morceau
					LEFT JOIN artistes
					ON morceau.artisteId = artistes.id
					WHERE morceau.id = '.$morceauId.'
					');
			
			return $fichier->first_row();
		}
	}

	// Supprime un fichier attaché à une fichier
	public function delete_fichier_fichier($morceauId, $fichierId, $fichiername) {

		$morceau = $this->morceau_model->get_morceau($morceauId);
		$fichier = $this->get_fichier($fichierId);

		log_message("debug","DELETE_fichier_fichier : ".json_encode($fichier));
		log_message("debug","fichiername : ".$fichiername);
		
		// On regarde si le fichier existe si oui on le supprime
		$fichierPath = realpath(FCPATH."/ressources/morceaux/".dir_path($morceau->titre)."/".$fichier->groupe."/".$fichiername);
		$state = false;
		if (fichier_exists($fichierPath))
			$state = unlink($fichierPath);
		
		// On supprime la référence du fichier dans la fichier
		if ($state) {
			if ($fichiername == $fichier->mp3URL) {
				$fichier->mp3URL = '';
				$this->update_fichier($fichier);
			}
			// Si c'est un pdf, il faut le retrouver
			else {
				log_message("debug","ELSE ===");
				$media = $this->media_model->get_media_by_fichiername($fichiername, $fichierId);
				$this->media_model->delete_media($media->id);
			}
			return "success";
		}
		else return "fichier_not_found";
	}
	*/
	
}