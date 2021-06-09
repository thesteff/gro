<?php namespace App\Controllers;

use App\Models\Fichier_model;
use App\Models\Members_model;

class Ajax_file extends BaseController {


	public function index() {
	}

	// On envoie un fichier au serveur
	public function upload_file() {
	
		$fichier_model = new Fichier_model();
	
		// On charge la librairie pour manipuler les fichiers
		helper('filesystem');
				
		log_message('debug',"_FILES : ".json_encode($_FILES));
		log_message('debug',"_POST : ".json_encode($_POST));
	
		$fileName = trim($_POST['fileName']);
		$parentType = trim($_POST['parentType']);
		$parentId = trim($_POST['parentId']);
		$accessType = trim($_POST['accessType']);
		$accessId = trim($_POST['accessId']);
		$memberId = trim($_POST['memberId']);
		$text = trim($_POST['text']);
		
		// On récupère le realpath
		$filePath = $fichier_model->get_realPath($parentType, $parentId).$fileName;
	
		if ($filePath) {

			// Si le fichier existe déjà à l'emplacement $filePath
			if (file_exists($filePath)) {
				echo "Le fichier <b>".$fileName."</b> existe déjà sur le serveur !";
			}
		
			// On écrit le fichier...
			else if (sizeof($_FILES) > 0) {
		
				// S'il y a eu un problème d'upload, on quitte
				if ($_FILES["file"]["error"] != 0) {
					if ($_FILES["file"]["error"] == 1) {
						echo "Upload_file :: Error : Taille de fichier supérieur au maximum autorisé !!";
						return;
					}
					echo "Error : ".$_FILES["file"]["error"];
					return;
				}
				// Sinon on écrit le fichier
				$state = move_uploaded_file($_FILES["file"]["tmp_name"], $filePath);

				// ... et on insère dans la BD
				if ($state) {
					$data_fichier = array(
						'fileName' => $fileName,
						'memberId' => $memberId,
						'parentType' => $parentType,
						'parentId' => $parentId,
						'accessType' => $accessType,
						'accessId' => $accessId,
						'text' => $text,
						'admin' => 0
					);
					$fichier_model->set_fichier($data_fichier);
					
					echo "success";
				}
				else echo "Upload_file :: Error avec la fonction move_uploaded_file";
			}
			else echo "Upload_file :: Error : sizeof(_FILES) = 0";
		}
		else echo "Upload_file :: Error filePath : ".$filePath;
		
	}
	
	
	public function remove_file() {
				
		$fileId = trim($_POST['fileId']);
		
		$fichier_model = new Fichier_model();
		
		// On récupère le fichier
		$file = $fichier_model->get_fichier($fileId);
		
		// On récupère le realpath
		$filePath = $fichier_model->get_realPath($file->parentType, $file->parentId).$file->fileName;
	
		// On supprime le fichier
		$state_msg = $fichier_model->remove_fichier($file, $filePath);
		
		$return_data = array(
			'state' => $state_msg == "success" ? "1" : "0",
			'data' => $state_msg
		);
		$output = json_encode($return_data);
		echo $output;
		
	}
	
	// Retourne les infos d'un fichiers (taille...)
	public function get_file_infos() {
		
		// On charge la librairie pour avoir les infos de fichiers
		helper('filesystem');
	
		// Conversion de bytes à megabytes
		function formatBytes($bytes, $precision = 2) { 
			$units = array('B', 'KB', 'MB', 'GB', 'TB'); 

			$bytes = max($bytes, 0); 
			$pow = floor(($bytes ? log($bytes) : 0) / log(1024)); 
			$pow = min($pow, count($units) - 1); 

			// Uncomment one of the following alternatives
			$bytes /= pow(1024, $pow);
			// $bytes /= (1 << (10 * $pow)); 

			return round($bytes, $precision).' '.$units[$pow]; 
		}
	
		$path = trim($_POST['path']);
		
		// On regarde si le fichier existe
		$filePath = realpath(FCPATH.$path);
		if (file_exists($filePath)) {
		
			$file_infos = get_file_info($filePath);
			if(!$file_infos['name']) $file_infos['name'] = basename($path);   // bug de codeigniter
			$file_infos["sizeMo"] = formatBytes($file_infos["size"]);
			
			$output = json_encode($file_infos);
			echo $output;
		}
		else echo "ERROR : Le fichier spécifié \"$path\" n'existe pas ou a été effacé !";
	}
	
	
	// Retourne la liste de fichier d'un objet parent
	public function get_parent_files() {
		
		$fichier_model = new Fichier_model();
		$members_model = new Members_model();
		
		$parentType = trim($_POST['parentType']);
		$parentId = trim($_POST['parentId']);
		
		$file_list = $fichier_model->get_fichiers_parent($parentType, $parentId);
	
		if ($file_list) {
			
			foreach ($file_list as $file) {
				$file->memberName = $members_model->get_pseudo($file->memberId);
			}
			
			$output = json_encode($file_list);
			echo $output;
			return;
		}
	
		else echo "empty";
	}

}
?>