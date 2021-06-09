<?php namespace App\Controllers;

use App\Models\Morceau_model;
use App\Models\Version_model;
use App\Models\Media_model;

class Ajax_version extends BaseController {


	public function index() {
	}


	// Retourne les versions en fonction d'un id de morceau
	public function get_versions() {
	
		$morceauId = trim($_POST['morceauId']);
		
		$version_model = new Version_model();
		
		$versions = $version_model->get_versions($morceauId);
		// Si aucune version n'existe
		if (!$versions) {
			$state = false;
			$data = "Warning : aucune version n'a été trouvée dans la base de donnée.";
		}
		else {
			$state = true;
			$data = $versions;
		}
		
		$return_data = array(
			'state' => $state,
			'data' => $data
		);
		$output = json_encode($return_data);
		echo $output;
	}
	
	
	// Retourne une version et ses infos (morceau/medias (pdf))
	public function get_version_infos() {
		
		$versionId = trim($_POST['versionId']);
		$morceauId = trim($_POST['morceauId']);
		
		$version_model = new Version_Model();
		$media_model = new Media_Model();
		
		$version = $version_model->get_version_infos($morceauId, $versionId);
		
		// Si la version n'existe pas
		if (!$version) echo "version_not_found";
		else {
			// On récupère les médias
			$medias = $media_model->get_medias($versionId);
			
			// On normalise le path
			$version_path = dir_path($version->titre);
			
			$data = array('version' => $version,
							'version_path' => $version_path,
							'medias' => $medias);
							
			echo json_encode($data);
		}
	}
	
	
	// On ajouter une version (bd + mkdir)
	public function create_version() {
	
		$morceauId = trim($_POST["morceauId"]);
		$versionCollection = trim($_POST["label"]);
		
		$version_model = new Version_model();	
		$morceau_model = new Morceau_model();
		
		// On récupère le morceau
		$morceau = $morceau_model->get_morceau($morceauId);
		
		// On créé le répertoire du morceau si besoin
		$destPath = FCPATH."/ressources/morceaux/".dir_path($morceau->titre);
		if (!is_dir($destPath)) mkdir($destPath,0755);
		
		// On créé le répertoire de la collection si besoin
		$destPath = FCPATH."/ressources/morceaux/".dir_path($morceau->titre)."/".$versionCollection;
		if (!is_dir($destPath)) mkdir($destPath,0755);
	
		// On ajoute la version à la BD
		$version_item = ['morceauId' => $morceauId,
							'collection' => $versionCollection
						];

		
		$insertID = $version_model->set_version($version_item);
		
		// On complète l'objet créé
		$version_item['id'] = $insertID;
		
		$return_data = array(
			'state' => true,
			'data' => $version_item
		);
		$output = json_encode($return_data);
		echo $output;
	}
	
	
	
	public function update_version() {
	
		// On charge la librairie pour manipuler les fichiers
		//$this->load->helper('file');
				
		log_message('debug',"_FILES : ".json_encode($_FILES));
		log_message('debug',"_POST : ".json_encode($_POST));
		
		$versionId = trim($_POST["versionId"]);
		$morceauId = trim($_POST["morceauId"]);
		$genre = trim($_POST["genre"]);
		$tona = trim($_POST["tona"]);
		$mode = trim($_POST["mode"]);
		$tempo = trim($_POST["tempo"]);
		$langue = trim($_POST["lang"]);
		$mp3URL = trim($_POST["mp3URL"]);
		$soufflants = trim($_POST["soufflants"]) == "true" ? '1' : '0';
		$choeurs = trim($_POST["choeurs"]) == "true" ? '1' : '0';
		
		$version_model = new Version_model();
		$morceau_model = new Morceau_model();
	
		// On récupère le morceau
		$morceau = $morceau_model->get_morceau($morceauId);
		
		// On récupère le dirPath
		$morceauPath = dir_path($morceau->titre);
		
		// On récupère la version existante
		$old_version = $version_model->get_version($versionId);
		
		// On récupère le path
		$destPath = FCPATH."/ressources/morceaux/".$morceauPath."/".$old_version->collection;
		
		//if (!is_dir($destPath)) mkdir($destPath,0755);
		
		// Si on a changé de nom de groupe on rename le répertoire
		/*if ($old_version->collection != $groupe) {
			$oldPath = FCPATH."/ressources/morceaux/".$morceauPath."/".trim($old_version->groupe);
			$destPath = FCPATH."/ressources/morceaux/".$morceauPath."/".trim($groupe);
			if (is_dir($oldPath)) rename($oldPath,$destPath);
		}*/
		// Sinon on créé le répertoire du groupe si besoin
		/*else {
			$destPath = FCPATH."/ressources/morceaux/".$morceauPath."/".trim($groupe);
			if (!is_dir($destPath)) mkdir($destPath,0755);
		}*/
	
		// Si on a reçu quelque chose
		if (sizeof($_FILES) > 0) {
		
			// S'il y a eu un problème d'upload, on quitte
			if ($_FILES["file"]["error"] == 1) {
				$state = false;
				$data = "Error 1 : La taille de fichier maximale a été atteinte (php.ini)";
			}
			else if ($_FILES["file"]["error"] != 0) {
				$state = false;
				$data = "Error : ".$_FILES["file"]["error"];
			}
			
			else {
				// S'il y a déjà un fichier présent, on le delete
				if ($mp3URL != '') {
					$version_model->delete_version_file($morceauId, $versionId, $mp3URL);
				}
				
				// On écrit le fichier
				$mp3URL = $_FILES["file"]["name"];
				$state = move_uploaded_file($_FILES["file"]["tmp_name"], $destPath."/".$mp3URL);
			}

		}
		else $state = true;
		
		// Tâche sur les fichier ok
		if ($state) {
			
			// On update le nom du mp3 si besoin (pas de nouveau fichier mais nom différent))
			if (sizeof($_FILES) == 0 && $old_version->mp3URL != $mp3URL) {
				$oldFilePath = FCPATH."/ressources/morceaux/".dir_path($morceau->titre)."/".$old_version->collection."/".$old_version->mp3URL;
				$newFilePath = FCPATH."/ressources/morceaux/".dir_path($morceau->titre)."/".$old_version->collection."/".$mp3URL;
				rename($oldFilePath, $newFilePath);
			}
			
			
			// On ajoute la version à la BD
			$version_item = ['id' => $versionId,
						'morceauId' => $morceauId,
						//'collection' => $collection,
						'genre' => $genre,
						'tona' => $tona,
						'mode' => $mode,
						'tempo' => $tempo,
						'langue' => $langue,
						'mp3URL' => $mp3URL,
						'soufflants' => $soufflants,
						'choeurs' => $choeurs
						];
		
			$state = $version_model->update_version($version_item);

			// On récupère la version update totale pour actualiser correctement l'UI
			$new_version_item = $version_model->get_version($versionId);
			
			// Update ok dans la bd
			if ($state) $data = $new_version_item;
			else $data = "Erreur : L'actualisation de la version $versionId n'a pas été correctement effectuée !";
		}
		
		$return_data = array(
			'state' => $state,
			'data' => $data
		);
		$output = json_encode($return_data);
		echo $output;
	}
	
	
	// Supprime une version et ses infos (mp3/pdf)
	public function delete_version() {
		
		$versionId = trim($_POST['versionId']);
		
		$version_model = new Version_model();
		$morceau_model = new Morceau_model();
		
		// On récupère la version et le morceau
		$version = $version_model->get_version($versionId);
		$morceau = $morceau_model->get_morceau($version->morceauId);
		
		// On supprime tous les fichiers du répertoire
		$destPath = FCPATH."/ressources/morceaux/".dir_path($morceau->titre)."/".$version->collection;
		$dir = $destPath;
		$di = new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS);
		$ri = new \RecursiveIteratorIterator($di, \RecursiveIteratorIterator::CHILD_FIRST);
		foreach ( $ri as $file ) {
			$file->isDir() ?  rmdir($file) : unlink($file);
		}
		// On supprime le répertoire vide
		if (is_dir($destPath)) rmdir($destPath);
		
		// On supprime le groupe de la base
		$state = $version_model->delete_version($versionId);
	
		$return_data = array(
			'state' => $state == false ? $state : true,
			'data' => $state == false ? "Erreur : La version n'a pas été supprimé de la base de donnée !" : "La version a été supprimé de la base de donnée."
		);
		$output = json_encode($return_data);
		echo $output;
	}
	
	
	// Supprime un fichier attaché à une version
	public function delete_version_file() {
		
		$morceauId = trim($_POST['morceauId']);
		$versionId = trim($_POST['versionId']);
		$filename = trim($_POST['filename']);
		
		$version_model = new Version_model();
	
		$state = $version_model->delete_version_file($morceauId, $versionId, $filename);
		
		$return_data = array(
			'state' => $state == false ? $state : true,
			'data' => $state == false ? "Erreur : Le fichier n'a pas été supprimé correctement !" : "Le fichier a bien été supprimé du serveur."
		);
		$output = json_encode($return_data);
		echo $output;
	}
	
	
	
	/***********************   MEDIA   *************************/
	
	// Retourne les versions en fonction d'un id de morceau
	public function get_medias() {
	
		$versionId = trim($_POST['versionId']);
		
		$media_model = new Media_model();
		
		$medias = $media_model->get_medias($versionId);
		// Si le lieu n'existe pas
		if (!$medias) echo "medias_not_found";
		else echo json_encode($medias);
	}
	
	
	public function create_media() {
		
		log_message('debug',"************   MEDIA   ***********");
		log_message('debug',"_FILES : ".json_encode($_FILES));
		log_message('debug',"_POST : ".json_encode($_POST));
		
		
		$versionId = trim($_POST["versionId"]);
		
		$version_model = new Version_model();
		$morceau_model = new Morceau_model();
		$media_model = new Media_model();
	
		// On récupère la version et le morceau
		$version = $version_model->get_version($versionId);
		$morceau = $morceau_model->get_morceau($version->morceauId);
		
		// On récupère le destPath
		$destPath = FCPATH."/ressources/morceaux/".dir_path($morceau->titre)."/".$version->collection;
		
		// Si on a reçu quelque chose
		if (sizeof($_FILES) > 0) {
		
			// S'il y a eu un problème d'upload, on quitte
			if ($_FILES["file"]["error"] == 1) {
				$state = false;
				$data = "Error 1 : La taille de fichier maximale a été atteinte (php.ini)";
			}
			else if ($_FILES["file"]["error"] != 0) {
				$state = false;
				$data = "Error : ".$_FILES["file"]["error"];
			}
			
			else {
				// Sinon on écrit le fichier
				$URL = 	$_FILES["file"]["name"];
				$state = move_uploaded_file($_FILES["file"]["tmp_name"], $destPath."/".$URL);
			}

		}
		else $state = true;
		
		// Tâche sur les fichier ok
		if ($state) {
			
			// On ajoute le media à la BD
			$media_item = ['versionId' => $versionId,
										/*'transpo' => $transpo,
										'catId' => $catId,
										'instruId' => $instruId,*/
										'URL' => $URL
										];
			
			$insertID = $media_model->set_media($media_item);

			// On récupère la version update totale pour actualiser correctement l'UI
			$new_media_item = $media_model->get_media($insertID);
		}
		
		$return_data = array(
			'state' => $state,
			'data' => $new_media_item
		);
		$output = json_encode($return_data);
		echo $output;
	}
	
	
	
	
	
	public function update_media() {
	
		log_message('debug',"************   MEDIA   ***********");
		log_message('debug',"_FILES : ".json_encode($_FILES));
		log_message('debug',"_POST : ".json_encode($_POST));
		
		$versionId = trim($_POST["versionId"]);
		$mediaId = trim($_POST["mediaId"]);
		$URL = trim($_POST["URL"]);
		
		$version_model = new Version_model();
		$morceau_model = new Morceau_model();
		$media_model = new Media_model();
	
		// On récupère la version et le morceau
		$version = $version_model->get_version($versionId);
		$morceau = $morceau_model->get_morceau($version->morceauId);
		
		// On récupère le destPath
		$destPath = FCPATH."/ressources/morceaux/".dir_path($morceau->titre)."/".$version->collection;

		// On récupère le media
		$media = $media_model->get_media($mediaId);
		
		
		// On gère un éventuel changement de nom de fichier
		if ($URL != $media->URL) {
			$oldFilePath = $destPath."/".$media->URL;
			$newFilePath = $destPath."/".$URL;
			$state = rename($oldFilePath, $newFilePath);
		}
		else $state = true;

		
		if ($state) {
			// On modifie la version de la BD
			$new_media_item = ['id' => $mediaId,
								'versionId' => $versionId,
								/*'transpo' => $transpo,
								'catId' => $catId,
								'instruId' => $instruId,*/
								'URL' => $URL
								];
			
			
			$state = $media_model->update_media($new_media_item);
		}
		
		$return_data = array(
			'state' => $state,
			'data' => $new_media_item
		);
		$output = json_encode($return_data);
		echo $output;
	}


}
?>