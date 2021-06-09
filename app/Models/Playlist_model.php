<?php namespace App\Models;

use CodeIgniter\Model;
use App\Models\Morceau_model;
use App\Models\Media_model;


class Playlist_model extends Model {

	
	// Retourne les playlists
	public function get_playlist() {
		$builder = $this->db->table('playlist');
		$builder->orderBy("date", "desc"); 
		$query = $builder->get();
		return $query->getResultArray();
	}
	
	
	// Retourne les infos de la playlist id
	public function get_playlist_infos($id) {
		$builder = $this->db->table('playlist');
		$query = $builder->get_where([ 'id' => $id ]);
		if (!empty($query->getRow())) {
			return $query->getRowArray();
		}
		return "null";
	}
	
	// Met à jour les infos de la playlist id
	public function update_playlist_infos($oldId, $new_playlist_infos) {
		$builder = $this->db->table('playlist');
		$builder->where('id', $oldId);
		return $builder->update($new_playlist_infos);
	}
	
	
	// Retourne les infos de la playlist + versions (et on rajoute les infos de l'artiste)
	public function get_playlist_versions($id) {

		$morceau_model = new Morceau_model();

		// On selectionne tout le répertoire du GRO
		if ($id == -1) {
			$data = array(
				'list' => $morceau_model->get_morceaux_and_versions()
			);
			return $data;
		}
		else if ($id == 0) return;
		else {
			$builder = $this->db->table('playlist');
			$query_playlist = $builder->getWhere(['id' => $id]);
			$row_infos = $query_playlist->getRowArray();
	
	
			if (!empty($row_infos)) {			
				$choices = $this->db->query('
						SELECT  morceau.id as morceauId,
								morceau.titre as titre,
								morceau.annee as annee,
								version.id as versionId,
								version.collection as collection,
								version.mp3URL as mp3URL,
								version.genre as genre,
								version.tempo as tempo,
								version.soufflants as soufflants,
								version.choeurs as choeurs,
								tr_tona.label as tona,
								tr_mode.label as mode,
								tr_langue.label as langue,
								artistes.id as artisteId,
								artistes.label as artisteLabel,
								playlist_version_relation.reserve_stage as reserve_stage
						FROM version
						LEFT JOIN morceau
						ON morceau.id = version.morceauId
						LEFT JOIN artistes
						ON morceau.artisteId = artistes.id
						LEFT JOIN tr_langue
						ON version.langue = tr_langue.id
						LEFT JOIN tr_tona
						ON version.tona = tr_tona.id
						LEFT JOIN tr_mode
						ON version.mode = tr_mode.id
						INNER JOIN playlist_version_relation
						ON playlist_version_relation.versionId = version.id
						WHERE playlist_version_relation.playlistId = '.$row_infos['id'].'
						ORDER BY playlist_version_relation.id
						');
				
				$query_versions = $choices->getResult();
				
			}
			// Si la playlist spécifiée n'existe pas
			else $query_versions = "empty";
				
			$data = array(
				'infos' => $row_infos,
				'list' => $query_versions
			);
			
			return $data;
		}
	}
	
	
	// Retourne les infos de la playlist + playlist (et on rajoute les infos de l'artiste)
	public function get_playlist_id($id) {

		// On selectionne tout le répertoire du GRO
		if ($id == -1) {
			$temp = $this->db->get('morceau');
			
			if ($temp->num_rows() > 0) {			
				$choices = $this->db->query('
						SELECT *
						FROM morceau
						INNER JOIN artistes
						ON artistes.id = morceau.artisteId
						ORDER BY titre
						');
				
				$query = $choices->result();
			}
			
			$data = array(
				//'infos' => $row,
				'list' => $query
			);
			
			return $data;
		}
		else if ($id == 0) return;
		else {
			$playlist = $this->db->get_where('playlist', array('id' => $id));
			$row = $playlist->row_array();
	
			if ($playlist->num_rows() > 0) {
				$choices = $this->db->query('
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
							artistes.label as artisteLabel,
							playlist_version_relation.reserve_stage as reserve_stage
						FROM morceau
						INNER JOIN version
						ON version.morceauId = morceau.id
						INNER JOIN playlist_version_relation
						ON playlist_version_relation.versionId = version.id
						INNER JOIN artistes
						ON artistes.id = morceau.artisteId
						WHERE playlist_version_relation.playlistId = '.$playlist->first_row()->id.'
						ORDER BY playlist_version_relation.id
						');				
				$query = $choices->result();
			}
			// Si la playlist spécifiée n'existe pas
			else $query = 0;
			
			$data = array(
				'infos' => $row,
				'list' => $query
			);
			
			return $data;
		}
	}
	
	
	public function set_playlist($playlist_item, $song_list) {
	
		$builder = $this->db->table('playlist');
		$builder->insert($playlist_item);
		$insertID = $this->db->insertID();
		
		// La playlist peut-être null
		if ($song_list != null) {
			foreach ($song_list as $versionId) {
				// On récupère l'info concernant le stage
				$temp = explode(" ", $versionId);
				$versionId == -1 ? $stage_tag = 0 : $stage_tag = $temp[2];
				
				//log_message('error', $versionId);
				//log_message('error', $stage_tag);
				
				$data = array(
					'playlistId' => $insertID,
					'versionId' => $versionId,
					'reserve_stage' => $stage_tag
				);
				$builder = $this->db->table('playlist_version_relation');
				$builder->insert($data);
			}
		}
		
		return $insertID;
	}
	
	
	// Update de la songlist
	public function update_playlist($playlist_item, $song_list) {
	
		// On met à jour la table playlist
		$builder = $this->db->table('playlist');
		$builder->where('id', $playlist_item['id']);
		$builder->update($playlist_item);
		
		// On créé la nouvelle playlist
		$data = array();
		if (sizeof($song_list)>0) {
			foreach ($song_list as $versionId) {
				// On récupère l'info concernant le stage
				$temp = explode(" ", $versionId);
				$versionId == -1 ? $stage_tag = 0 : $stage_tag = $temp[2];
				
				//log_message('update : ', $versionId);
				//log_message('update : ', $stage_tag);
				
				$data[] = array(
					'playlistId' => $playlist_item['id'],
					'versionId' => $versionId,
					'reserve_stage' => $stage_tag
				);
			}
		}
		
		// On efface les références de la table relationnelle
		$builder = $this->db->table('playlist_version_relation');
		$builder->delete([ 'playlistId' => $playlist_item['id'] ]); 
		
		// On insère la nouvelle playlist
		if (sizeof($song_list)>0) $builder->insertBatch($data);
	}
	
	
	// On efface la playlist
	public function delete_playlist($id) {
				
		if (isset($id)) {
			
			// On maintient la cohérence relationnelle (les clefs étrangères perdues sur des restore de bd)
			$builder1 = $this->db->table('playlist_version_relation');
			$state1 = $builder1->delete([ 'playlistId' => $id ]);
			
			$builder2 = $this->db->table('playlist');
			$state2 = $builder2->delete([ 'id' => $id ]);
			
			return ($state1 && $state2);
		}
		else return false;
	}
	
	
	/**************************************************************/
	/******* GENERATE ZIP MP3                               *******/
	/**************************************************************/
	
	// Génère le zip d'une liste de mp3 et renvoie le nom du fichier généré
	public function generate_zipmp3($playlist, $filePath) {
	
		// On donne une plus grosse mémoire au serveur php
		$oldMemValue = ini_set('memory_limit', '2048M');
		
		// On charge la librairie pour zipper et pour avoir les infos de fichiers
		helper('filesystem');
		$zip = new \ZipArchive();
		
		// On récupère le nom de fichier
		$tokens = explode('/', $filePath);
		$fileName = $tokens[sizeof($tokens)-1];
		
		if ($zip->open($filePath, \ZipArchive::CREATE)!==TRUE) {
			exit("Impossible d'ouvrir le fichier <$fileName>\n");
		}
		
		// On ajoute tous les mp3 de la liste en les numérotant
		$nb_pause = 0;
		$index = 0;
		foreach ($playlist["list"] as $key => $song) {
			$index = $key + 1 - $nb_pause;
			// On saute les pause
			if ($song->morceauId > 0) {
				$tempURL = "ressources/morceaux/".dir_path($song->titre)."/".$song->collection."/".urldecode($song->mp3URL);
				if ($index < 10) $new_path = "0".$index." - ".$song->titre.".mp3";
				else $new_path = $index." - ".$song->titre.".mp3";
				//$this->zip->read_file($tempURL, $new_path);
				$zip->addFile($tempURL, $new_path);
			}
			else $nb_pause++;
		}
				
		// On zippe le tout
		$zip->close($filePath);
		
		//if ($zipstate) {
		if (true) {
		
			// On récupère les infos du fichiers créé
			$file_infos = get_file_info($filePath);

			// On restreint la mémoire du serveur php
			ini_set('memory_limit', $oldMemValue);
			
			return $file_infos;
		}
		
		return "error";
	}
	
	
	
	/**********************************************************/
	/******* GENERATE PDF  (playilist, true = alphanum) *******/
	/**********************************************************/
	public function generate_pdf($playlist, $filePath, $sort = 'asc', $pdfType = 'all', $pdfId, $sommaire = true, $couv = true, $post = true) {
	
		// Fonction de comparaison pour le tri sort
		/*function cmp($a, $b) {
			return strcmp($a->titre, $b->titre);
		}*/
		
		$media_model = new Media_model();
		$instruments_model = new Instruments_model();
	
		// On donne une plus grosse mémoire au serveur php
		$oldMemValue = ini_set('memory_limit', '2048M');
		
		// On charge la librairie pour zipper et pour avoir les infos de fichiers
		helper('filesystem');
		
		// On charge la librairie mpdf
		require_once APPPATH . '\ThirdParty\vendor\autoload.php';
		
		
		// On récupère le nom de fichier
		$tokens = explode('/', $filePath);
		$fileName = $tokens[sizeof($tokens)-1];
		
		// On gère le tri alphabéthique si besoin
		if ($sort == 'asc') {
			// On peut bidouiller l'ordre de la playlist car elle n'est pas update !!!!!
			usort($playlist['list'], function ($a, $b) {
											return strcmp($a->titre, $b->titre);
										}
			);
		}

		
		// On créé les objets pdf
		$mpdf = new \Mpdf\Mpdf();      // pdf principal
		$tempdf = new \Mpdf\Mpdf();
		$sompdf = new \Mpdf\Mpdf();    // couv + sommaire
		$empty = new \Mpdf\Mpdf();
		

		// On importe le css du pdf
		$stylesheet = file_get_contents("ressources/global/pdfStyle.css");
		$mpdf->WriteHTML($stylesheet,1);
		$sompdf->WriteHTML($stylesheet,1);
		
		/************************************** PDF Secondaires ******************************/
		// Page 1 => index sur une page par défaut
		//$tempdf->AddPage();
		$sompdf->WriteHTML("<p class='indexTitle'>Index</p>",2);
		$sompdf->WriteHTML("<table id='index'>",2);
		
		//$tempURL = "ressources/morceaux/".dir_path($version->titre)."/".$version->collection."/".urldecode($version->mp3URL);

		$noerror = true;
		$error_msg = 'error :: ';

		// On est page page 4 (à gauche derrière l'index page 3)
		$page = 4;
		$index = 1;     // pour colorer les even et odd
		foreach ($playlist['list'] as $version) {
			// On saute les pauses
			if ($version->versionId > 0) {
				
				// On récupère les medias associés
				$list_media = $media_model->get_selected_medias($version->versionId, $pdfType, $pdfId);
				
				// On normalise le path
				$version_path = dir_path($version->titre);
				
				log_message("debug", "PDF ********** ".$version_path);
				
				// On parcours les medias d'une version
				foreach ($list_media as $media) {
					
					// On vérifie que le media existe
					if (!file_exists(urldecode("ressources/morceaux/".$version_path."/".$version->collection."/".$media->URL))) {
						$noerror = false;
						$error_msg = 'Error : Le fichier "<b>'.urldecode("ressources/morceaux/".$version_path."/".$version->collection."/".$media->URL).'</b>" est introuvable sur le serveur. L\'opération est interrompue';
						// On break tout
						break 2;
					}
					
					// Le fichier existe
					else {
						// On compte les page du pdf importé
						$pagecount = $tempdf->SetSourceFile(urldecode("ressources/morceaux/".$version_path."/".$version->collection."/".$media->URL));
						
						// On ajoute une page empty si besoin
						if ($pagecount%2 == 0 && $page%2 == 1) {
							$pagecount = $tempdf->SetSourceFile(urldecode("ressources/global/empty.pdf"));
							$tplId = $tempdf->ImportPage($pagecount);
							$tempdf->AddPage();
							$tempdf->UseTemplate($tplId);
							$page++;
							
							// On reload la page courante
							$pagecount = $tempdf->SetSourceFile(urldecode("ressources/morceaux/".$version_path."/".$version->collection."/".$media->URL));
						}
						
						// On ajoute une ligne à l'index
						$state = $index%2 ? "impair" : "pair";
						$media->catId > 0 ? $note = $media->catName : $note = '';
						$sompdf->WriteHTML("<tr class='".$state."'><td class='songTitle'><b>&nbsp;".$version->titre."&nbsp;</b><small><i>&nbsp;&nbsp;".$note."</i></small></td><td class='artist'>&nbsp;&nbsp;&nbsp;".$version->artisteLabel."</td><td class='pageNum'>".$page."</td></tr>",2);
						
						// On importe chaque page du pdf
						for ($i=0; $i<$pagecount; $i++) {
							// Saut de page
							$tempdf->AddPage();
							// On met le pdf en mémoire
							$tplId = $tempdf->ImportPage($i+1);
							// On le fusionne avec le pdf principal
							$tempdf->UseTemplate($tplId);
						}
						
						// On actualise le numéro de page
						$page += $pagecount;
						$index++;
					}
				}
			}
		}
		
		// La postface doit être sur un numéro de page pair
		if ($page%2 == 1) {
			$pagecount = $tempdf->SetSourceFile(urldecode("ressources/global/empty.pdf"));
			$tplId = $tempdf->ImportPage($pagecount);
			$tempdf->AddPage();
			$tempdf->UseTemplate($tplId);
			$page++;
		}
		
		// On ferme et on écrit l'index
		$sompdf->WriteHTML("</table>",2);
		$sompdf->Output("ressources/global/tempIndex.pdf",'F');
		
		// On écrit le pdf temporaire
		$tempdf->Output("ressources/global/tempPdf.pdf",'F');
		
		
		/****************************** PDF principal ********************************/
		
		// COUVERTURE
		if ($couv) {
			// On importe la page de couverture
			$pagecount = $mpdf->SetSourceFile(urldecode("ressources/global/couverture.pdf"));
			$tplId = $mpdf->ImportPage($pagecount);
			$mpdf->UseTemplate($tplId);
			// On écrit le titre
			if ($pdfType == 'cat')
				$titre = ucfirst($instruments_model->get_catName($pdfId))." Book";
			else if ($pdfType == 'instru')
				$titre = ucfirst($instruments_model->get_instrument($pdfId))." Book";
			else if ($pdfType == 'all' || true)
				$titre = "Master Book";
			
			$mpdf->WriteHTML("<br><p class='title'>".$titre."<br>".$playlist["infos"]["title"]."</p><br>",2);
			
			// Saut de page (verso de couverture)
			$mpdf->AddPage();
			// DATE
			$mpdf->SetHTMLFooter('
				<table height="100%" style="vertical-align: bottom; font-family: serif; font-size: 8pt; font-weight: bold;">
					<tr>
						<td style="text-align: right; ">'.date("d-m-Y").'</td>
					</tr>
				</table>');
		}
		
		// SOMMAIRE
		if ($sommaire) {
			// On importe l'index
			$mpdf->AddPage();
			$pagecount = $mpdf->SetSourceFile(urldecode("ressources/global/tempIndex.pdf"));
			$tplId = $mpdf->ImportPage($pagecount);
			$mpdf->UseTemplate($tplId);
		}
		
		// On ajouter les numéro de page
		$mpdf->SetHTMLFooter('
			<table>
				<tr>
					<td id="pageNumber">{PAGENO}</td>
				</tr>
			</table>');

		
		// On écrit le pdf temporaire
		$pagecount = $mpdf->SetSourceFile(urldecode("ressources/global/tempPdf.pdf"));
		// On importe chaque page du pdf
		for ($i=0; $i<$pagecount; $i++) {
			// Saut de page
			$mpdf->AddPage();
			// On met le pdf en mémoire
			$tplId = $mpdf->ImportPage($i+1);
			// On le fusionne avec le pdf principal
			$mpdf->UseTemplate($tplId);
		}
		
		// POSTFACE
		if ($post) {
			// On importe la page de postface
			$mpdf->AddPage();
			// On enlève le numéro de page
			$mpdf->SetHTMLFooter('');
			$pagecount = $mpdf->SetSourceFile(urldecode("ressources/global/postface.pdf"));
			$tplId = $mpdf->ImportPage($pagecount);
			$mpdf->UseTemplate($tplId);
		}
		
		
		// On écrit le pdf principal
		if ($noerror) $mpdf->Output($filePath,'F');
		
		
		/****************************** UPDATE BD ********************************/
		if ($noerror) {
		
			// On récupère les infos du fichiers créé
			$file_infos = get_file_info($filePath);
						
			// On restreint la mémoire du serveur php
			ini_set('memory_limit', $oldMemValue);
			
			return $file_infos;
		}

		return $error_msg;
	}
	
	
	
	/**********************************************************/
	/*******  Gestion de la WISH_LIST    **********************/
	/**********************************************************/
	
	public function add_wish($wish_elem){
		return $this->db->insert('wishlist_jam_relation', $wish_elem);
	}
	
	public function get_wishlist($jamId) {
		$builder = $this->db->table('wishlist_jam_relation');
		if (!$jamId) return;
		// On retourne la wishlist globale (ref n'étant pas liée à une jam précise)
		else if ($jamId == -1) {
			$query = $builder->getWhere(['jamId' => null]);
			$row = $query->getRowArray();
		}
		else {
			$query = $builder->getWhere(['jamId' => $jamId]);
			$row = $query->getRowArray();
		}
		
		if (!empty($query->getRow())) {
			$query2 = $this->db->query('
					SELECT *
					FROM wishlist_jam_relation
					INNER JOIN membres
					ON wishlist_jam_relation.membresId = membres.id
					WHERE jamId '.($row['jamId'] == null ? 'IS NULL' : '= '.$row['jamId']));
			
			return $query2->getResultArray();
		}
		return "null";
	}
		
}