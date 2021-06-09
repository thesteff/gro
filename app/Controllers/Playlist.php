<?php namespace App\Controllers;

use App\Models\Morceau_model;
use App\Models\Version_model;
use App\Models\Playlist_model;


class Playlist extends BaseController {

	/********************* VIEW **************************/
	public function view($idPlaylist = -1) {	// -1 => répertoire entier
		
		// La fonction n'est accessible qu'aux admin
		if ($this->session->superAdmin) {
		
			$data['page_title'] = "Gestion des playlist";
			$data['title'] = "Admin";
			$data['sub_title'] = "Playlist";
			
			$morceau_model = new Morceau_model();
			$playlist_model = new Playlist_model();
			
			$data['is_admin'] = $this->session->admin;
			
			// On récupère le répertoire avec version
			$data['list_song'] = $morceau_model->get_morceaux_and_versions();

			// On récupère les playlists
			$data['playlists'] = $playlist_model->get_playlist();
			
			// On fait fonctionner la preselection si idPlaylist
			if ($idPlaylist > 0) {
				$data['selectedPlaylist'] = $playlist_model->get_playlist_versions($idPlaylist);
				$data['idPlaylist'] = $idPlaylist;
			}
			else $data['idPlaylist'] = -1;
			
			// On lance la vue
			echo view('templates/header', $data);
			echo view('templates/player', $data);
			echo view('templates/menu', $data);
			echo view('playlist/view', $data);
			echo view('templates/footer', $data);
		}
		else {
			$data_page['page_title'] = 'Erreur';
			$data_page['title'] = 'Accés refusé !!';
			$data_page['message'] = 'Votre statut sur le site ne vous permet pas d\'accéder à cette page';
		
			echo view('templates/header',$data_page);
			echo view('pages/message', $data_page);
			echo view('templates/footer');
		}

	}
	
	

	
	/********************* CREATE **************************/
	public function create() {

		if($this->session->superAdmin) {
			
			$data['title'] = 'Playlist';
			$data['page_title'] = 'Créer une playlist';
			
			$version_model = new Version_model();
			$morceau_model = new Morceau_model();
			$playlist_model = new Playlist_model();
			
			helper('form');
			$validation =  \Config\Services::validation();
			
			$data['isSuperAdmin'] = $this->session->superAdmin;
			
			// On récupère le répertoire avec version
			$data['list_song'] = $morceau_model->get_morceaux_and_versions();
			
			// On récupère les infos de jeu des morceaux
			foreach ($data['list_song'] as $key => $song) {
				$data['list_song'][$key]->{'nbPlayed'} = $version_model->nbPlayed($song->versionId);
				$data['list_song'][$key]->{'lastDate'} = $version_model->lastTimePlayed($song->versionId);
			}

			$validation->setRule('title', 'Titre', 'required');
			
			// On lance la vue du formulaire
			if (! $this->validate([
							'title' => 'required'
						])) {
			
				$data['validation'] = $validation;
				echo view('templates/header', $data);
				echo view('templates/player', $data);
				echo view('templates/menu', $data);
				echo view('playlist/create', $data);
				echo view('templates/footer', $data);

			}
			
			// On créer la playlist pour l'isérer dans la base
			else {

				// On créé la slug qui sert à la génération des noms de fichiers par la suite
				$slug = url_title($this->request->getVar('title'), 'dash', TRUE);
			
				$data = array(
					'title' => $this->request->getVar('title'),
					'slug' => $slug
				);
				
				$insertID = $playlist_model->set_playlist($data, $this->request->getVar('song_list'));
				return redirect()->to('/playlist/'.$insertID);		
			}
		}
		
		// Redirect si non admin
		else {
			$data_page['page_title'] = 'Erreur';
			$data_page['title'] = 'Accés refusé !!';
			$data_page['message'] = 'Votre statut sur le site ne vous permet pas d\'accéder à cette page';
		
			echo view('templates/header', $data_page);
			echo view('pages/message', $data_page);
			echo view('templates/footer');
		}
	}
	
	
	/********************* UPDATE **************************/
	public function update($idPlaylist) {
	
		if($this->session->superAdmin) {
		
			helper('form');
			$validation =  \Config\Services::validation();
		
			$data['title'] = 'Playlist';
			$data['page_title'] = 'Modifier une playlist';
			
			$version_model = new Version_model();
			$morceau_model = new Morceau_model();
			$playlist_model = new Playlist_model();
			
			$data['isSuperAdmin'] = $this->session->superAdmin;
			
			// On récupère le répertoire avec version
			$data['list_song'] = $morceau_model->get_morceaux_and_versions();

			// On récupère les infos de jeu des morceaux
			foreach ($data['list_song'] as $key => $song) {
				$data['list_song'][$key]->{'nbPlayed'} = $version_model->nbPlayed($song->versionId);
				$data['list_song'][$key]->{'lastDate'} = $version_model->lastTimePlayed($song->versionId);
			}
			
			// On récupère la playlist
			$data['playlist'] = $playlist_model->get_playlist_versions($idPlaylist);

			$validation->setRule('title', 'Titre', 'required');

			// On lance la vue du formulaire
			if (! $this->validate([
							'title' => 'required'
						])) {
			
				$data['validation'] = $validation;				
				echo view('templates/header', $data);
				echo view('templates/player', $data);
				echo view('templates/menu', $data);
				echo view('playlist/update', $data);
				echo view('templates/footer', $data);

			}
			
			// On modifie la playlist dans la base
			else {
				
				// On créé la slug qui sert à la génération des noms de fichiers par la suite
				$slug = url_title($this->request->getVar('title'), 'dash', TRUE);
				
				$data = array(
					'title' => $this->request->getVar('title'),
					'id' => $idPlaylist,
					'slug' => $slug
				);
				
				$playlist_model->update_playlist($data, $this->request->getVar('song_list'));
				return redirect()->to('/playlist/'.$idPlaylist);		
			}
		}
		// Redirect si non admin
		else {
			$data_page['page_title'] = 'Erreur';
			$data_page['title'] = 'Accés refusé !!';
			$data_page['message'] = 'Votre statut sur le site ne vous permet pas d\'accéder à cette page';
		
			echo view('templates/header', $data_page);
			echo view('pages/message', $data_page);
			echo view('templates/footer');
		}
	}
	
	
	/********************* DELETE **************************/
	public function delete() {
		
		if($this->session->superAdmin) {
		
			if (isset($_POST['playlistId'])) {
				
				$playlistId = trim($_POST['playlistId']);
				
				$playlist_model = new Playlist_model();
				
				// On supprime l'instrument du membre
				$state = $playlist_model->delete_playlist($playlistId);
				
				$return_data = array(
					'state' => $state,
					'data' => ""
				);
				$output = json_encode($return_data);
				echo $output;
			}
			
			// Redirect si pas de playlist select
			else {
				$data_page['page_title'] = 'Erreur';
				$data_page['title'] = 'Playlist à supprimer indéfinie';
				$data_page['message'] = 'La playlist à supprimer n\'est pas correctement identifiée !';
			
				echo view('templates/header', $data_page);
				echo view('pages/message', $data_page);
			}
		}
			
		// Redirect si non admin
		else {
			$data_page['page_title'] = 'Erreur';
			$data_page['title'] = 'Accés refusé !!';
			$data_page['message'] = 'Votre statut sur le site ne vous permet pas d\'accéder à cette page';
		
			echo view('templates/header', $data_page);
			echo view('pages/message', $data_page);
			echo view('templates/footer');
		}
	}

}