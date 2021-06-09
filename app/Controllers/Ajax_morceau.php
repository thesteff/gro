<?php namespace App\Controllers;

use App\Models\Morceau_model;
use App\Models\Artists_model;

class Ajax_morceau extends BaseController {


	public function index() {
	}


	public function create_morceau() {
	
		$titre = trim($_POST['titre']);
		$artisteId = trim($_POST['artisteId']);
		$morceau_annee = trim($_POST['annee']);
		
		$morceau_model = new Morceau_model();
		$artists_model = new Artists_model();
		
		// On récupère le morceau pour être sûr qu'il n'existe pas à la création
		$morceau_item = $morceau_model->get_morceau_by_titre($titre);
			
		// On fait un add
		if ($morceau_item == false) {
	
			// On créé le morceau dans la BD
			$morceau_item = array(
				'titre' => $titre,
				'artisteId' => !empty($artisteId) ? $artisteId : "-1",		// si pas d'artiste spécifié, on met -1 : aucun
				'annee' => intval($morceau_annee)
			);
			
			$morceau_id = $morceau_model->set_morceau($morceau_item);
			
			// On créé un répertoire pour le morceau créé en normalisant la string	
			$path = FCPATH."/ressources/morceaux/".dir_path($titre);
			if(!is_dir($path)) mkdir($path,0755);
			
			// On retourne l'objet qui permettra d'actualiser le flexdatalist
			$state = true;
			$data = $morceau_item;
			$data["id"] = $morceau_id;
			$data["artistLabel"] = $artists_model->get_artist_label($artisteId);
		}
			
		else {
			$state = false;
			$data = "Erreur : Le morceau \"<b>$titre\" est déjà présent dans la base de donnée.";
		}
		
		$return_data = array(
			'state' => $state,
			'data' => $data
		);
		$output = json_encode($return_data);
		echo $output;
	}
	

	// Retourne un morceau en fonction de l'id
	public function get_morceau() {
	
		$morceauId = trim($_POST['morceauId']);
		
		$morceau_model = new Morceau_model();
		$artists_model = new Artists_model();
		
		if ($morceauId != 'null') {
			// On récupère le morceau
			$morceau = $morceau_model->get_morceau($morceauId);
			// On récupère l'artiste
			if(!empty($morceau)) {
				$artiste = $artists_model->get_artist($morceau->artisteId);
				$state = true;
				$data = array("morceau" => $morceau, "artiste" => $artiste);
			}
			// Pas de morceau
			else {
				$state = false;
				$data = "Erreur : Le morceau <b>$morceauId</b> n'a pas été trouvé dans la base de donnée !";
			}
		}
		else {
			$state = false;
			$data = "Erreur : morceauId = null";
		}
		
		
		$return_data = array(
			'state' => $state,
			'data' => $data
		);
		$output = json_encode($return_data);
		echo $output;
		
	}
	
	
	// Retourne un morceau en fonction du titre (on ajoute le nom du compositeur)
	public function get_morceau_by_titre() {
	
		$titre = trim($_POST['titre']);
		
		$morceau_model = new Morceau_model();
		$artists_model = new Artists_model();
		
		$morceau = $morceau_model->get_morceau_by_titre($titre);
		
		// Si le lieu n'existe pas
		if (!$morceau) echo "error : morceau_not_found";
		else {
			$data = array("morceau" => $morceau,
						"artiste" => $artists_model->get_artist($morceau->artisteId));
		
			$output = json_encode($data);
		
			echo $output;
		}
	}
	
	
	
	public function update_morceau() {
	
		$morceauId = trim($_POST['morceauId']);
		$titre = trim($_POST['titre']);
		$artisteId = trim($_POST['artisteId']);
		$morceau_annee = trim($_POST['annee']);
		
		$morceau_model = new Morceau_model();
		$artists_model = new Artists_model();
		
		// On récupère le morceau
		$morceau_item = $morceau_model->get_morceau($morceauId);
			
		// Le morceau existe bien, on l'update
		if (!empty($morceau_item)) {
	
			// On update le morceau dans la BD
			$newMorceau = array(
				'id' => $morceauId,
				'titre' => $titre,
				'artisteId' => !empty($artisteId) ? $artisteId : "-1",		// si pas d'artiste spécifié, on met -1 : aucun
				'annee' => intval($morceau_annee)
			);
			
			$morceau_model->update_morceau($newMorceau);
			
			// On rename le répertoire si besoin
			if ($morceau_item->titre != $titre) {
				$oldPath = FCPATH."/ressources/morceaux/".dir_path($morceau_item->titre);
				$destPath = FCPATH."/ressources/morceaux/".dir_path($titre);
				if (is_dir($oldPath)) rename($oldPath,$destPath);
			}
			
			// On retourne l'objet qui permettra d'actualiser le flexdatalist
			$state = true;
			$data = $newMorceau;
			$data["id"] = $morceauId;
			$data["artistLabel"] = $artists_model->get_artist_label($artisteId);
		}
		// Le morceau n'existe pas
		else {
			$state = false;
			$data = "Erreur : Le morceau n'existe pas dans la base de donnée.";
		}
		
		$return_data = array(
			'state' => $state,
			'data' => $data
		);
		$output = json_encode($return_data);
		echo $output;
	}
	
	
	public function delete_morceau() {
	
		$morceau_id = trim($_POST['morceauId']);

		$morceau_model = new Morceau_model();

		// On récupère le morceau
		$morceau_item = $morceau_model->get_morceau($morceau_id);
		if ($morceau_item == false) return;
		
		// On supprime le répertoire du morceau		
		$path = FCPATH."/ressources/morceaux/".dir_path($morceau_item->titre);
		if(is_dir($path)) rmdir($path);

		// On supprime le morceau de la base
		$state = $morceau_model->delete_morceau($morceau_id);
	
		$return_data = array(
			'state' => $state == false ? $state : true,
			'data' => $state == false ? "Erreur : Le morceau n'a pas été supprimé de la base de donnée !" : "Le morceau a été supprimé de la base de donnée."
		);
		$output = json_encode($return_data);
		echo $output;
	}


}
?>