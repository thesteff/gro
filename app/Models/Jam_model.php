<?php namespace App\Models;

use CodeIgniter\Model;
use App\Models\Members_model;
use App\Models\Invitation_model;
use App\Models\Instruments_model;
use App\Models\Stage_model;

class Jam_model extends Model {

	
	public function get_jam($slug = FALSE) {
		$builder = $this->db->table('jam');	
		// On retourne la liste des jams
		if ($slug === FALSE) {
			$builder->orderBy("date", "desc");
			$query = $builder->get();
			return $query->getResultArray();
		}
		// On retourne une jam spécifique
		$query = $builder->getWhere([ 'slug' => $slug ]);
		return $query->getRowArray();
	}
	
	
	public function get_jam_id($id = 0) {
		$builder = $this->db->table('jam');	
		$query = $builder->getWhere([ 'id' => $id ]);
		return $query->getRowArray();
	}
	
	
	// On retourne l'id d'insertion de la jam
	public function set_jam($jam_item){
		$builder = $this->db->table('jam');
		$builder->insert($jam_item);
		return $this->db->insertID();
	}
	
	
	// update de la jam
	public function update_jam($slug, $new_jam_item) {
		
		$builder = $this->db->table('jam');
		
		// On récupère la jam qui va être modifiée
		$query = $builder->getWhere([ 'slug' => $slug ]);
		$oldjam = $query->getRow();

		// On supprime les inscriptions effectuées sur l'ancienne playlist si cette dernière a été modifiée
		if (!empty($oldjam) && $oldjam->playlistId != 0) {
			if ($oldjam->playlistId != $new_jam_item["playlistId"]) {
				$this->db->query('
					DELETE i.*
					FROM inscriptions i
					WHERE idInscr IN (SELECT idInscr
										FROM (SELECT idInscr
												FROM inscriptions
												INNER JOIN jam_membres_relation
												ON jam_membres_relation.id = jam_membresId
												WHERE jamId = '.$oldjam->id.') x)
				');
			}
		}
		
		// On actualise la jam
		if (!empty($query->getRow())) {
			$builder = $this->db->table('jam');
			$builder->where([ 'slug' => $slug ]);
			return $builder->update($new_jam_item);
		}
		else return false;
	}	

	
	public function delete_jam($slug) {
		if (!empty($slug)) {
			$builder = $this->db->table('jam');
			$builder->delete([ 'slug' => $slug ]); 
		}
	}
	
	
	
// ***********  PLAYLIST INDEX*************/	

	public function set_playlistIndex($jamId, $playlistIndex) {
		$builder = $this->db->table('jam');
		$builder->where('id', $jamId);
		return $builder->update([ 'playlistIndex' => $playlistIndex ]);
	}
	
	public function get_playlistIndex($jamId) {
		$builder = $this->db->table('jam');
		$query = $builder->getWhere([ 'id' => $jamId ]);
		
		$jam = $query->getRowArray();
		return $jam["playlistIndex"];
	}
	

// ***********  INFOS JAM *************/
	
	// Insertion d'un objet jamInfo dans la base
	public function add_jamInfo($jamInfo_item) {
		$builder = $this->db->table('jaminfo');
		$builder->insert($jamInfo_item);
		return $this->db->insertID();
	}
	
	// Retourne les jamInfo d'une jam donnée et du mode de sélection d'info définit par tag1 (1 : référent  2 : nbMax   3 : matos   4 : balance)
	public function get_jamInfos($jamId, $tag1) {
		
		$builder = $this->db->table('jaminfo');	
		$query = $builder->getWhere([ 'jamId' => $jamId, 'tag1' => $tag1 ]);
		$results = $query->getResult();
		
		// On complète les infos avec les label du tag1
		switch($tag1) {
			
			case "1":		// memberId (référent)
			
				$member_model = new members_model();
				
				// On parcourt les référents
				foreach ($results as $key => $jamInfo) {
					// On récupère le membre et les infos qui nous intéresse
					$member = $member_model->get_member_by_id($jamInfo->tag1Val);
					$jamInfo->{"pseudo"} = $member->pseudo;
					$jamInfo->{"nom"} = $member->nom;
					$jamInfo->{"prenom"} = $member->prenom;
					$jamInfo->{"email"} = $member->email;
					$jamInfo->{"mobile"} = $member->mobile;
					$jamInfo->{"hasAvatar"} = $member->hasAvatar;
					
					// On récupère le label du tag2
					$jamInfo->{"tag2Label"} = $this->get_tag2Label($jamInfo->tag2, $jamInfo->tag2Id);
					// On complète les infos avec le titre du tag2
					switch($jamInfo->tag2) {
						case "1":
							$jamInfo->{"tag2Title"} = "Poste";
							break;
						case "2":
							$jamInfo->{"tag2Title"} = "Instrument";
							break;
						case "3":
							$jamInfo->{"tag2Title"} = "Famille d'instrument";
							break;
						case "4":
							$jamInfo->{"tag2Title"} = "Pupitre";
							break;
						default:
							break;
					}
				}
				return $results;
				break;
				
			case "2":		// nbMax
				break;
			case "3":		// matos
				break;
			case "4":		// balance
				break;
			default:
				return $query->getResult();
				break;
		}
	}
	
	
	// Retourne la label du tag2 en fonction de tag2 et tag2Id
	public function get_tag2Label($tag2, $tag2Id) {
		
		switch($tag2) {
			case "1":		// posteId
				
				$builder = $this->db->table('instrumentations');
				$query = $builder->getWhere([ 'id' => $tag2Id ]);
				
				// Il y a un posteLabel
				if ($query->getRow()->posteLabel != NULL) return strtolower($query->getRow()->posteLabel);
				// Sinon il faut récupéré le nom de l'instrument
				else {
					$instruments_model = new Instruments_Model();
					return strtolower($instruments_model->get_instrument_name($query->getRow()->instrumentsId));
				}
				break;
				
			case "2":		// instrumentId
				$instruments_model = new instruments_model();
				return strtolower($instruments_model->get_instrument_name($tag2Id));
				break;
				
			case "3":		// famille_instruId
				$builder = $this->db->table('tr_famille_instru');
				$query = $builder->getWhere([ 'id' => $tag2Id ]);
				return strtolower($query->getRow()->label);
				break;
				
			case "4":		// pupitreId
				$builder = $this->db->table('tr_pupitre');
				$query = $builder->getWhere([ 'id' => $tag2Id ]);
				return $query->getRow()->name;
				break;
				
			default:
				break;
		}
	}
	
	
	// Insertion d'un obket jamInfo dans la base
	public function delete_jamInfo($jamInfo_id) {
		$builder = $this->db->table('jamInfo');
		return $builder->delete([ 'id' => $jamInfo_id ]);
	}
	
	
	// On détermine si la jam est archivée en fonction de la date courante
	public function is_archived($jamId) {
		$builder = $this->db->table('jam');
		$query = $builder->getWhere([ 'id' => $jamId]);
		
		if (!empty($query)) {
			return $query->getRow()->date < date("Y-m-d");
		}
	}
	
	
	// Retourne le REALPATH de la jam
	public function get_file_path($jamId) {
		$builder = $this->db->table('jam');
		$query = $builder->getWhere([ 'id' => $jamId ]);
		
		if (!empty($query->getRow())) {
			$path = FCPATH."/ressources/event/".dir_path($query->getRow()->date.'_'.$query->getRow()->slug);
			return $path;
		}
	}
	
	// Retourne le dirPath de la jam
	public function get_dirPath($jamId) {
		$builder = $this->db->table('jam');
		$builder->where([ 'id' => $jamId ]);
		$query = $builder->get();
		if (!empty($query->getRow())) {
			$path = $query->getRow()->date.'_'.$query->getRow()->slug;
			return $path;
		}
	}


	// Récupérer la liste des membres d'une jam donnée ainsi que leur donnée de participation (bénévole)
	public function get_list_members($jamId , $withStagiaires = true) {
		
		$stage_model = new Stage_Model();
		
		// On s'adapte à la version utilisée de mysql (>5.7, il faut utiliser ANY_VALUE)
		$mysql_version = $this->db->getVersion();
		$need_any_value = substr($mysql_version, 0, 3) === "5.7";
		$date_inscr = '';
		if ($need_any_value) $date_inscr = "ANY_VALUE(jam_membres_relation.date_inscr)";
		else $date_inscr = "jam_membres_relation.date_inscr";
		
		// On récupère le stageId
		$stage = $stage_model->get_stage_jamId($jamId);
	
		$nostag = ["",""];
		// Requètes pour enlever les stagiaires
		if ($withStagiaires == false && $stage == true) {
			$nostag[0] = "LEFT JOIN stage_membres_relation ON stage_membres_relation.membresId = membres.id";
			$nostag[1] = "AND membres.id NOT IN (
								SELECT membres.id
									FROM membres
									LEFT JOIN stage_membres_relation
									ON stage_membres_relation.membresId = membres.id
									WHERE stage_membres_relation.stageId = ".$stage['id']."
							)";
		}
		
		$list_members = $this->db->query('
				SELECT pseudo,
					membres.id as id,
					membres.nom, membres.prenom, email, mobile, admin, genre, hasAvatar,
					'.$date_inscr.' as date_inscr,
					membres.naissance as naissance,
					jam_membres_relation.jamId as jamId
				FROM membres
				
				INNER JOIN jam_membres_relation
				ON jam_membres_relation.membresId = membres.id
				
				'.$nostag[0].'
				
				WHERE jam_membres_relation.jamId = '.$jamId.'
				AND jam_membres_relation.event_admin = 0
				'.$nostag[1].'
				
				GROUP BY id
				');
				
		
		// On trie par ordre alphabétique
		$array = $list_members->getResult();
		usort($array, function ($a, $b) {
								return strcmp(strtolower($a->pseudo), strtolower($b->pseudo));
							}
		);

		return $array;
	}
	
	
	
	// Récupérer la liste des membres absents d'une jam donnée
	public function get_list_members_not_in_jam($jamId) {
		
		$list_members = $this->db->query('
				SELECT pseudo,
						membres.id as memberId,
						nom, prenom, email, mobile, admin, genre
						FROM membres
						WHERE membres.id NOT IN
						(
							SELECT membres.id
							FROM membres
							LEFT JOIN jam_membres_relation
							ON membres.id = jam_membres_relation.membresId
							WHERE jam_membres_relation.jamId = '.$jamId.'
							AND jam_membres_relation.event_admin = 0
							GROUP BY membres.id
						)
						ORDER BY pseudo
				');
			
		return $list_members->getResult();	
	}
	
	
// ***********  ADMIN *************/
	
	// Récupérer la liste des membres d'une jam donnée ainsi que leur donnée de participation (bénévole)
	public function get_event_admin($jamId) {
	
		$list_admin = $this->db->query('
				SELECT pseudo,
					membres.id as memberId,
					instruments.id as instruId
				FROM membres
				INNER JOIN jam_membres_relation
				ON jam_membres_relation.membresId = membres.id
				INNER JOIN instruments
				ON instruments.id = membres.idInstru1
				WHERE jam_membres_relation.jamId = '.$jamId.'
				AND jam_membres_relation.event_admin = 1
				');
			
		return $list_admin->getResult();

	}
	
	
	// Retirer un admin d'un event
	public function remove_event_admin($jamId, $memberId) {
		$builder = $this->db->table('jam_membres_relation');
		$builder->delete([ 'jamId' => $jamId,
							'membresId' => $memberId,
							'event_admin' => 1
						]); 
	}
	
	
// *****************  INSCRIPTION ********************/
	
	// Récupérer le tableau d'inscription d'une jam
	public function get_inscriptions($slug, $affect_char = '>') {
	
		// On récupère la jam
		$builder = $this->db->table('jam');
		$builder->where([ 'slug' => $slug ]);
		$jam = $builder->get();

		if (!empty($jam->getRow())) {			
			$choices = $this->db->query('
					SELECT pseudo,
						membres.id as memberId,
						morceau.id as morceauId,
						morceau.titre as titre,
						version.id as versionId,
						instruments.id as instruId,
						inscriptions.posteId as posteId,
						choicePos
					FROM membres
					INNER JOIN jam_membres_relation
					ON jam_membres_relation.membresId = membres.id
					INNER JOIN inscriptions
					ON inscriptions.jam_membresId = jam_membres_relation.id
					INNER JOIN version
					ON version.id = inscriptions.versionId
					LEFT JOIN morceau
					ON morceau.id = version.morceauId
					LEFT JOIN instruments
					ON instruments.id = inscriptions.instrumentsId
					WHERE jam_membres_relation.jamId = '.$jam->getRow()->id.'
					AND inscriptions.choicePos '.$affect_char.' 0
					ORDER BY choicePos
					');
			
			return $choices->getResultArray();
		}
	}
	
	
	
	// Récupérer les inscriptions d'un membre pour une jam
	public function get_member_inscriptions($jamId, $memberId, $affect_char = '>') {
	
		$choices = $this->db->query('
				SELECT pseudo,
					membres.id as memberId,
					morceau.id as morceauId,
					morceau.titre as titre,
					version.id as versionId,
					instruments.id as instruId,
					instruments.name as instruName,
					inscriptions.posteId as posteId,
					instrumentations.posteLabel as posteLabel,
					choicePos
				FROM membres
				INNER JOIN jam_membres_relation
				ON jam_membres_relation.membresId = membres.id
				INNER JOIN inscriptions
				ON inscriptions.jam_membresId = jam_membres_relation.id
				INNER JOIN version
				ON version.id = inscriptions.versionId
				LEFT JOIN morceau
				ON morceau.id = version.morceauId
				LEFT JOIN instrumentations
				ON instrumentations.id = inscriptions.posteId
				LEFT JOIN instruments
				ON instruments.id = instrumentations.instrumentsId
				WHERE membres.id = '.$memberId.'
				AND jam_membres_relation.jamId = '.$jamId.'
				AND inscriptions.choicePos '.$affect_char.' 0
				ORDER BY choicePos
				');
				
		/*$choices = $this->db->query('
				SELECT morceau.id as morceauId,
					morceau.titre as titre,
					version.id as versionId,
					instruments.id as instruId,
					instruments.name as instruName,
					choicePos
				FROM jam_membres_relation
				INNER JOIN inscriptions
				ON inscriptions.jam_membresId = jam_membres_relation.id
				INNER JOIN version
				ON version.id = inscriptions.versionId
				LEFT JOIN morceau
				ON morceau.id = version.morceauId
				INNER JOIN instruments
				ON instruments.id = inscriptions.instrumentsId
				WHERE jam_membres_relation.jamId = '.$jamId.'
				AND jam_membres_relation.membresId = '.$memberId.'
				AND inscriptions.choicePos '.$affect_char.' 0
				ORDER BY choicePos
				');*/
		
		return $choices->getResultArray();
	}
	
	
	// Effectuer l'inscription d'une liste (lier jam/membre/liste de morceaux)
	public function set_inscription_list($ref, $choice_list) {
		
		// On récupère l'id correspondant à la ref (jamId+membresId)
		$builder = $this->db->table('jam_membres_relation');
		$query = $builder->getWhere([ 'jamId' => $ref['jamId'],	'membresId' => $ref['membresId'] ]);
		
		if (!empty($query->getRow())) {
			$row = $query->getRow();
			
			// On efface les références de la table relationnelle
			$builder = $this->db->table('inscriptions');
			$builder->delete([ 'jam_membresId' => $row->id, 'choicePos >' => 0 ]); 
			
			$pos = 1;
			foreach ($choice_list as $choice) {
				// On interprête la string correspondant au choix ("versionId - instruId")
				$tmp = explode(" - ", $choice);
				$versionId = $tmp[0];
				$instruId = $tmp[1];
				
				$data = array(
					'jam_membresId' => $row->id,
					'versionId' => $versionId,
					'instrumentsId' => $instruId,
					'choicePos' => $pos++
				);
				$builder->insert($data);
			}
		}
	}
	
	
	// Update une liste d'inscription (lier jam/membre/morceaux)
	// $choiceList => array[choicePos]{ versionId ; instruId }
	public function update_inscription_list($data, $choice_list) {

		$pos = 1;
		foreach ($choice_list as $choice) {
			$this->db->query('
				UPDATE inscriptions
				INNER JOIN jam_membres_relation ON inscriptions.jam_membresId = jam_membres_relation.id
				SET inscriptions.choicePos = '.$pos.'
				WHERE jam_membres_relation.jamId = '.$data['jamId'].'
				AND jam_membres_relation.membresId = '.$data['membresId'].'
				AND inscriptions.versionId = '.$choice->versionId.'
				AND inscriptions.posteId = '.$choice->posteId.'
			');
			$pos++;
		}
		return true;	
	}
	
	
	// Effectuer une inscription (lier jam/membre/morceaux) et renvoie le nombre d'inscription déjà faites sur la jam donnée
	public function set_inscription($ref) {

		// On récupère l'id correspondant à la ref (jamId+membresId)
		$builder = $this->db->table('jam_membres_relation');
		$query = $builder->getWhere([ 'jamId' => $ref['jamId'],	'membresId' => $ref['membresId'] ]);
		
		if (!empty($query->getRow())) {
			$row = $query->getRow();
	
			// On compte le nombre d'inscriptions déjà faites
			$builder = $this->db->table('inscriptions');
			
			//$this->db->where('jam_membresId', $row->id);
			//$this->db->where('choicePos >', 0);				// choicePos = 0 => affect et donc pas un choix à compter
			$builder->where([ 'jam_membresId' => $row->id,
							'choicePos >' => 0 
						]);
			$pos = $builder->countAllResults()+1;

			$data = array(
				'jam_membresId' => $row->id,
				'versionId' => $ref['versionId'],
				'instrumentsId' => $ref['hasFormation'] ? null : $ref['elemId'],
				'posteId' => $ref['hasFormation'] ? $ref['elemId'] : null,
				'choicePos' => $pos
			);
			$builder->insert($data);
			return $pos;
		}
		
	}
	
	
	// Efface une inscription (délier jam/membre/morceaux). On retourne le choicePos de celui à supprimer
	public function delete_inscription($data) {

		// On récupère l'id correspondant à la ref (jamId+membresId)
		$builder = $this->db->table('jam_membres_relation');
		$query = $builder->getWhere([ 'jamId' => $ref['jamId'],	'membresId' => $ref['membresId'] ]);
		
		if (!empty($query->getRow())) {
			$row = $query->getRow();
			
			// On récupère le choicePos de celui à supprimer
			$builder = $this->db->table('inscriptions');
			$toDelete = $builder->getWhere([ 'jam_membresId' => $row->id,
											'versionId' => $data['versionId'],
											'instrumentsId' => $data['hasFormation'] ? null : $data['elemId'],
											'posteId' => $data['hasFormation'] ? $data['elemId'] : null
											]);
			
			// On décrémente le choicePos des choix qui viennent après
			$builder->set('choicePos', 'choicePos-1', FALSE);
			$builder->where([ 'jam_membresId' => $row->id,
									'choicePos >' => $toDelete->getRow()->choicePos
									]);
			$builder->update('inscriptions');
			
			// On efface l'enregistrement
			$builder->delete([ 'jam_membresId' => $row->id,
											'versionId' => $data['versionId'],
											'instrumentsId' => $data['hasFormation'] ? null : $data['elemId'],
											'posteId' => $data['hasFormation'] ? $data['elemId'] : null
											]);
			
			return $toDelete->getRow()->choicePos;
		}
	}
	

	
	// Relier un membre à une jam
	public function join_member($jamId, $membreId, $event_admin = 0) {
		$builder = $this->db->table('jam_membres_relation');	
		$builder->insert([
							'jamId' => $jamId,
							'membresId' => $membreId,
							'event_admin' => $event_admin
						]);
	}
	
	
	// Un membre quitte une jam
	public function unjoin_member($jamId, $membreId) {
		
		// On récupère l'id correspondant à la ref (jamId+membresId)
		$builder = $this->db->table('jam_membres_relation');
		$builder->where([ 'jamId' => $jamId, 'membresId' => $membreId ]);
		$query = $builder->get();
		
		// On efface les inscriptions de morceaux
		if (!empty($query->getRow())) {
			$row = $query->getRow();
			$builder = $this->db->table('inscriptions');			
			$builder->delete([ 'jam_membresId' => $row->id ]);
		}
		
		$builder = $this->db->table('jam_membres_relation');	
		$builder->delete([
							'jamId' => $jamId,
							'membresId' => $membreId,
							'event_admin' => 0
						]);
	}

	
	
	// Savoir si un membre participe à une jam ($tache pour préciser la tache)
	public function is_included($jamId, $membreId, $tache = 1) {
		$builder = $this->db->table('jam_membres_relation');
		$query = $builder->getWhere(['jamId' => $jamId, 'membresId' => $membreId, 'event_admin' => 0, 'tache' => 1]);
		if (!empty($query->getRow())) {
			return true;
		}
		return false;
	}
	
	
	
	// Savoir si un membre est admin à une jam ($tache pour préciser la tache)
	public function is_admin($jamId, $membreId, $tache = 1) {
		
		$members_model = new Members_model();
		
		if ($members_model->isSuperAdmin($membreId)) return true;
		
		$builder = $this->db->table('jam_membres_relation');
		$query = $builder->getWhere([
										'jamId' => $jamId,
										'membresId' => $membreId,
										'event_admin' => 1,
										'tache' => 1 ]
									);
		if (!empty($query->getRow())) return true;
		return false;
	}
	
	
	// Savoir si un membre est référent à une jam
	public function is_referent($jamId, $membreId) {
		
		$members_model = new Members_model();
		
		// On regarde si le membre est référent de la jam
		$builder = $this->db->table('jaminfo');	
		$query = $builder->getWhere([ 'jamId' => $jamId, 'tag1' => 1, 'tag1Val' => $membreId ]);
		$jamInfo = $query->getRowObject(0);
		
		//log_message("debug","****** is_referent :: jamInfo : ".json_encode($jamInfo));
		
		// Si le membre n'est pas référent on break
		if ($jamInfo == null) return false;
		
		// Sinon on renvoie l'intitulé de quoi il est référent (premier résultat seulement)
		// On récupère le label du tag2
		$jamInfo->{"tag2Label"} = $this->get_tag2Label($jamInfo->tag2, $jamInfo->tag2Id);
		// On complète les infos avec le titre du tag2
		switch($jamInfo->tag2) {
			case "1":
				$jamInfo->{"tag2Title"} = "Poste";
				break;
			case "2":
				$jamInfo->{"tag2Title"} = "Instrument";
				break;
			case "3":
				$jamInfo->{"tag2Title"} = "Famille d'instrument";
				break;
			case "4":
				$jamInfo->{"tag2Title"} = "Pupitre";
				break;
			default:
				break;
		}
		
		return $jamInfo;
	}
	
	

// **********************  AFFECTATIONS ************************/	


	// Récupérer le tableau d'affectation d'une jam (choicePos = 0 dans la table relationelle)
	public function get_affectations($slug) {
		$result = $this->get_inscriptions($slug,'=');
		return $result;
	}
	
	
	// Récupérer les affectations d'un membre pour une jam
	public function get_member_affectations($jamId, $memberId) {
		$result = $this->get_member_inscriptions($jamId, $memberId, '=');
		return $result;
	}

	

	// Effectuer les inscriptions d'affectation d'une jam (lier jam et liste membre/instru/morceaux)
	public function set_affectations($jamId, $affect_list, $visible) {
	
		// On efface les références d'affectations de la table relationnelle
		$builder = $this->db->table('jam_membres_relation');
		$query = $builder->getWhere([ 'jamId' => $jamId ]);
		
		foreach ($query->getResult() as $row) {
			$builder = $this->db->table('inscriptions');
			$builder->delete([ 'jam_membresId' => $row->id, 'choicePos' => 0 ]);
		}
	
		foreach ($affect_list as $elem) {
			// On interprête la string correspondant au choix ("versionId - idinstru")
			$tmp = explode(" - ", $elem);
			$versionId = $tmp[0];
			$instruId = $tmp[1];
			$memberId = $tmp[2];
			
			// On récupère l'id correspondant à la ref (jamId+membresId)
			$builder = $this->db->table('jam_membres_relation');
			$builder->where([ 'jamId' => $jamId, 'membresId' => $memberId ]);
			$query = $builder->get();
			
			if (!empty($query->getRow())) {
				$row = $query->getRow(); 
				
				$data = array(
					'jam_membresId' => $row->id,
					'versionId' => $versionId,
					'instrumentsId' => $instruId,
					'choicePos' => 0
				);
				$builder = $this->db->table('inscriptions');
				$builder->insert($data);
			}
			
		}
		
		// On update la jam avec $visible
		$builder = $this->db->table('jam');
		$query = $builder->get>here([ 'id' => $jamId ]);
		$jam = $query->getRowArray();
		$jam['affectations_visibles'] = $visible;
		$this->update_jam($jam['slug'], $jam);		
	}
	
	
	
	// Effectuer une affection (lier jam/version/instru/membre)		**********	CHOICEPOS = 0 !!!!!!
	public function set_affectation($data) {
	
		//log_message('debug', "set_affectation : ".json_encode($data));
	
		// On vérifie qu'il y ait bien une nouvelle affectation (member != 0)
		if ($data['membresId'] > 0) {
			// On récupère l'id correspondant à la ref (jamId+membresId)
			$builder = $this->db->table('jam_membres_relation');
			$builder->where([ 'jamId' => $data['jamId'], 'membresId' => $data['membresId'] ]);
			$query = $builder->get();
			
			if (!empty($query->getRow())) {		
				$row = $query->getRow();
				
				$builder = $this->db->table('inscriptions');
				$builder->where([ 'jam_membresId' => $row->id ]);

				$newData = array(
					'jam_membresId' => $row->id,
					'versionId' => $data['versionId'],
					'posteId' => $data['posteId'],
					'choicePos' => 0
				);
				return $builder->insert($newData);
			}
		}
		return true;		
	}
	
	
	// Supprimer une affection (délier jam/version/poste/membre)
	public function delete_affectation($data) {
		$this->db->query('
			DELETE inscriptions
			FROM inscriptions
			INNER JOIN jam_membres_relation ON inscriptions.jam_membresId = jam_membres_relation.id
			WHERE jam_membres_relation.jamId = '.$data['jamId'].'
			AND inscriptions.versionId = '.$data['versionId'].'
			AND inscriptions.posteId = '.$data['posteId'].'
			AND jam_membres_relation.membresId = '.$data['membresId'].'
			AND inscriptions.choicePos = 0
		');
		return true;		
	}

	
	
	/***********************************/
	/******* GENERATE PDF AFFECT *******/
	/***********************************/
	public function generate_affect_pdf($jamId, $filePath) {

		$playlist_model = new Playlist_model();
		$jam_model = new Jam_model();
		$formation_model = new Formation_model();
	
		// Retourne un tableau keys où $array[key]->$param == $id
		function searchForId($id, $array, $param) {
			$keys = array();
			foreach ($array as $key => $val) {
			   if ($val[$param] === $id) {
				   array_push($keys,$key);
			   }
			}
			return $keys;
		}
	
		// On récupère le nom de fichier
		$tokens = explode('/', $filePath);
		$fileName = $tokens[sizeof($tokens)-1];
	
		// On récupère la jam
		$jam = $this->get_jam_id($jamId);
		
		// On récupère la playlist
		$playlist = $playlist_model->get_playlist_versions($jam["playlistId"]);
	
		// On récupère la liste des membres qui participent à la jam
		$list_members = $jam_model->get_list_members($jam['id']);
		
		// On récupère le header des pupitres de la formation
		$instrumentation_header = $formation_model->get_instrumentation_header($jam['formationId']);
		//log_message("debug","instrumentation_header : ".json_encode($data['instrumentation_header']));
		
		// On récupère l'instrumentation de la formation
		$instrumentation_list = $formation_model->get_instrumentation($jam['formationId']);

		// On récupère les affectations
		$affectations = $jam_model->get_affectations($jam["slug"]);
		
		
		// On charge la librairie mpdf
		require_once APPPATH . '\ThirdParty\autoload.php';
		
		// On charge la librairie pour avoir les infos de fichiers
		helper('filesystem');


		// On créé l'objet pdf
		$mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8', 'format' => 'A4-L']);


		// On importe le css du pdf
		$stylesheet = file_get_contents("ressources/global/pdfStyle.css");
		$mpdf->WriteHTML($stylesheet,1);

		// On écrit le tableau
		$mpdf->WriteHTML("<table id='index'>",2);
		
		// Headers de colonne catégories d'instruments
		/*$header = "<thead>";
		$header .= "<tr class='impair'><td class='tableTitle'>".$jam["title"]."</td>";
		foreach ($cat_instru_list as $cat) {
			$header .= "<td class='cat_header' colspan='".sizeof($cat['list'])."'>";
				$header .= $cat['name']=="hors catégorie" ? $cat['name'] : $cat['name'];
			$header .= "</td>";
		}
		$header .= "</tr>";

		// Headers de colonne instruments
		$header .= "<tr class='pair'>";
		$header .= "<td>&nbsp;</td>";
		$nbcol = 1;
		foreach ($cat_instru_list as $cat) {
			foreach ($cat['list'] as $instru) {
				if($instru) $header .= '<td class="catelem_'.$cat['name'].' cat_elem" idInstru="'.$instru.'">'.$this->instruments_model->get_instrument($instru).'</td>';
				// On enregistre le nombre de colonne du tableau pour le colspan des pauses
				$nbcol++;
			}
		}
		$header .= "</tr>";
		$header .= "</thead>";*/
		
		
		// Headers de colonne pupitre
		$header = "<thead>";
		$header .= "<tr class='pair'><td class='tableTitle'>".$jam["title"]."</td>";
		
		// On parcourt le header des pupitres (et on compte le nombre de colonne totale pour le colspan des pauses) 
		$nbcol = 1;
		foreach ($instrumentation_header as $header_item) {
			//log_message("debug","header_item : ".json_encode($header_item));
			$nbcol += $header_item["nbInstru"];
			$header .= "<th class='pupitreElem' colspan='".$header_item["nbInstru"]."'>".ucfirst($header_item["pupitreLabel"])."</th>";
		}
		
		$header .= "</tr>";

		// Headers de colonne instruments
		$header .= "<tr class='posteRow pair'>";
		$header .= "<td>&nbsp;</td>";
		foreach ($instrumentation_list as $instrumentation_item) {		
			$header .= '<th class="centerTD">';
				if ($instrumentation_item["posteLabel"] !== null) $header .= $instrumentation_item["posteLabel"];
				else $header .= $instrumentation_item["name"];
			$header .= '</th>';
		}
		$header .= "</tr>";
		$header .= "</thead>";
		
		
		
		// On écrit le header
		$mpdf->WriteHTML($header,2);
		
		// On écrit les lignes du tableau
		$index = 1;     // pour colorer les even et odd
		foreach ($playlist['list'] as $key => $song) {
			
			$state = $index%2 ? "impair" : "pair";   // gestion du odd / even
			
			// On saute les pauses
			if ($song->versionId > 0) {

				// On ajoute une ligne à l'index
				$tr = "<tr class='".$state."'>";
				
				// Titre
				$tr .= "<td class='songTitle'><b>&nbsp;".$song->titre."</b></td>";
				
				// On parcours les colonnes
				foreach ($instrumentation_list as $instrumentation_item) {
					
					$posteId = $instrumentation_item['id'];
					if($posteId) {
						$tr .= "<td>";
						//$tr .= '<td idInstru="'.$instru.'">'.$this->instruments_model->get_instrument($instru).'</td>';
						// On recherche l'id des affectés par rapport au titre de la ligne $song->titre
						$keys = searchForId($song->titre,$affectations,"titre");
						if (isset($keys)) {
							//$find = false;
							// Pour chaque référence, on affiche le pseudo
							foreach ($keys as $key) {
								if($posteId == $affectations[$key]['posteId']) {
									//$find = true;
									$tr .= $affectations[$key]['pseudo']."<br>";
								}
							}
						}
						$tr .= "</td>";
					}
				}
				
				$tr .= "</tr>";
				
				// On écrit la tr
				$mpdf->WriteHTML($tr,2);
			}
			// Affichage de la pause
			else {
				//log_message("debug","PAUSE !!!");
				//$mpdf->WriteHTML("<tr class='".$state."><td colspan='".$nbcol."'><i>-= pause =-</i></td></tr>",2);
				$mpdf->WriteHTML("<tr class='".$state."'><td colspan='".$nbcol."'><i>-= pause =-</i></td></tr>",2);
			}
			$index++;
		}

		// On ferme et on écrit l'index
		$mpdf->WriteHTML("</table>",2);

		
		// On écrit le pdf principal
		$mpdf->Output($filePath,'F');
		
		// On récupère les infos du fichiers créé (false si echec)
		$file_infos = get_file_info($filePath);
		
		return $file_infos;

	}
	
}