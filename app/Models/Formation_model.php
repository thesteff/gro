<?php namespace App\Models;

use CodeIgniter\Model;
use App\Models\Instruments_model;

class Formation_model extends Model {


	// Récupérer la liste des formations
	public function get_formations() {
		$builder = $this->db->table('tr_formation');
		$builder->where('id >', 0);
		$builder->orderBy("name");
		$query = $builder->get();
		if (!empty($query->getRow())) {
			return $query->getResultArray();
		}
	}
	
	
	// Récupère une formation en fonction de l'id
	public function get_formation_by_id($formationId) {
		$builder = $this->db->table('tr_formation');
		$builder->where('id', $formationId);
		$query = $builder->get();
		if (!empty($query->getRow())) {
			return $query->getRowArray();
		}
	}
	
	
	// Récupérer l'instrumentation d'une formation
	public function get_instrumentation($formationId) {
	
		$formation_model = new Formation_Model();
	
		// On récupère la formation et on vérifie qu'elle existe
		$formation = $formation_model->get_formation_by_id($formationId);
		
		if (isset($formation["id"]) && $formation["id"] > 0) {			
				
			$postes = $this->db->query('
					SELECT instrumentations.id as id, formationId, posteLabel, instrumentsId,
							instrumentations.famille_instruId as poste_famille_instruId, label,
							instruments.id as instruId, instruments.name as name, instruments.famille_instruId as instru_famille_instruId,
							tr_pupitre.name as pupitreLabel, instrumentations.pupitreId as pupitreId
					FROM instrumentations
					LEFT JOIN tr_pupitre
					ON instrumentations.pupitreId = tr_pupitre.id
					LEFT JOIN instruments
					ON instrumentations.instrumentsId = instruments.id
					LEFT JOIN tr_famille_instru
					ON instrumentations.famille_instruId = tr_famille_instru.id
					WHERE instrumentations.formationId = '.$formationId.'
					ORDER BY tr_pupitre.view_order, instruments.name, posteLabel
				');
			
			if (!empty($postes->getRow())) return $postes->getResultArray();
			else return false;
		}
		
		else return false;
	}
	
	
	// Récupérer le header de l'instrumentation (combien de no pupitre, combien / pupitre)
	public function get_instrumentation_header($formationId, $memberId = 0) {
	
		$formation_model = new Formation_Model();
	
		// On récupère la formation et on vérifie qu'elle existe
		$formation = $formation_model->get_formation_by_id($formationId);
		
		if (isset($formation["id"]) && $formation["id"] > 0) {
	
			// On récupère les postes définit par un instrument
			$header = $this->db->query('
					SELECT tr_pupitre.id as pupitreId, tr_pupitre.name as pupitreLabel, COUNT(*) as nbInstru, tr_pupitre.iconURL as iconURL
					FROM instrumentations
					LEFT JOIN tr_pupitre
					ON instrumentations.pupitreId = tr_pupitre.id
					WHERE instrumentations.formationId = '.$formationId.'
					GROUP BY instrumentations.pupitreId
					ORDER BY tr_pupitre.view_order
				');

			if (!empty($header->getRow())) {
				$array = $header->getResultArray();
				
				if ($memberId != 0) {
					// On définit sur quels pupitres le membre peut jouer
					foreach ($array as $index => $pupitre) {
						if ($formation_model->could_play_on_pupitre($memberId, $formationId, $pupitre["pupitreLabel"])) $array[$index]['couldPlay'] = "1";
						else $array[$index]['couldPlay'] = "0";
					}
				}
				
				return $array;
			}
			else return false;
		}
		else return false;
	}
	
	
	// Récupérer les postes d'instrumentation sur lesquels un membres peut jouer
	// Renvoie true si un membre peut jouer sur l'instrumentation donnée
	public function could_play($memberId, $instrumentationsId) {

		// On récupère les postes définit par un instrument
		$query1 = $this->db->query('
				SELECT instrumentations.id as id, posteLabel, instrumentations.instrumentsId as instrumentsId, instrumentations.famille_instruId as poste_famille_instruId, tr_pupitre.name as pupitreLabel
				FROM instrumentations
				LEFT JOIN tr_pupitre
				ON instrumentations.pupitreId = tr_pupitre.id
				INNER JOIN membre_instruments
				ON membre_instruments.instrumentsId = instrumentations.instrumentsId
				WHERE instrumentations.id = '.$instrumentationsId.'
				AND membre_instruments.membresId = '.$memberId.'
			');
			
		$query2 = $this->db->query('
				SELECT instrumentations.id as id, posteLabel, instrumentations.instrumentsId as instrumentsId, instrumentations.famille_instruId as poste_famille_instruId, tr_pupitre.name as pupitreLabel
				FROM instrumentations
				LEFT JOIN tr_pupitre
				ON instrumentations.pupitreId = tr_pupitre.id
				INNER JOIN instruments
				ON instruments.famille_instruId = instrumentations.famille_instruId
				INNER JOIN membre_instruments
				ON membre_instruments.instrumentsId = instruments.id
				WHERE instrumentations.id = '.$instrumentationsId.'
				AND membre_instruments.membresId = '.$memberId.'
			');
		
		// On fusionne les tableaux
		$query = array_merge_recursive($query1->getResultArray(),$query2->getResultArray());
			
		if (sizeof($query) > 0) {
			return $query;
		}
		else return false;
	}
	
	
	// Renvoie true si un membre joue d'un instrument faisant partie du meme pupitre
	public function could_play_on_pupitre($memberId, $formationId, $pupitre) {
		
		if ($pupitre == '') $pupitre = "0";
		
		// On récupère les postes définit par un instrument
		$query1 = $this->db->query('
				SELECT instrumentations.id as id, posteLabel, instrumentations.instrumentsId as instrumentsId, instrumentations.famille_instruId as poste_famille_instruId, tr_pupitre.name as pupitreLabel
				FROM instrumentations
				LEFT JOIN tr_pupitre
				ON instrumentations.pupitreId = tr_pupitre.id
				INNER JOIN membre_instruments
				ON membre_instruments.instrumentsId = instrumentations.instrumentsId
				WHERE instrumentations.formationId = '.$formationId.'
				AND tr_pupitre.name = "'.$pupitre.'"
				AND membre_instruments.membresId = '.$memberId.'
			');
			
		$query2 = $this->db->query('
				SELECT instrumentations.id as id, posteLabel, instrumentations.instrumentsId as instrumentsId, instrumentations.famille_instruId as poste_famille_instruId, tr_pupitre.name as pupitreLabel
				FROM instrumentations
				LEFT JOIN tr_pupitre
				ON instrumentations.pupitreId = tr_pupitre.id
				INNER JOIN instruments
				ON instruments.famille_instruId = instrumentations.famille_instruId
				INNER JOIN membre_instruments
				ON membre_instruments.instrumentsId = instruments.id
				WHERE instrumentations.formationId = '.$formationId.'
				AND tr_pupitre.name = "'.$pupitre.'"
				AND membre_instruments.membresId = '.$memberId.'
			');
		
		// On fusionne les tableaux
		$query = array_merge_recursive($query1->getResultArray(),$query2->getResultArray());
		//log_message("debug"," **** could_play_on_pupitre :: ".json_encode($query));
			
		if (sizeof($query) > 0) {
			return $query;
		}
		else return false;
	}
	
	
	// Renvoie true si l'instrument principal du membre correspond au pupitre
	public function is_main_pupitre($mainInstruId, $formationId, $pupitre) {

		$instruments_model = new Instruments_model();

		if ($mainInstruId > 0) {

			$instru_family = $instruments_model->get_instru_family($mainInstruId);

			// On récupère les postes définit par un instrument
			$query1 = $this->db->query('
					SELECT instrumentations.id as id, posteLabel, instrumentations.instrumentsId as instrumentsId, instrumentations.famille_instruId as poste_famille_instruId, tr_pupitre.name as pupitreLabel
					FROM instrumentations
					LEFT JOIN tr_pupitre
					ON instrumentations.pupitreId = tr_pupitre.id
					WHERE instrumentations.formationId = '.$formationId.'
					AND instrumentations.instrumentsId = '.$mainInstruId.'
				');
			
			$instru_array = $query1->getResultArray();
			
			// Si on trouve un poste défini par l'instrumentId on break avec ce main pupitre
			if (sizeof($instru_array) > 0) {
				//log_message("debug"," ** BREAK !! ".json_encode($instru_array));
				return $instru_array[0]["pupitreLabel"] == $pupitre;
			}
			
			// Sinon, on cherche des postes dont la famille correspond	
			$query2 = $this->db->query('
					SELECT instrumentations.id as id, posteLabel, instrumentations.instrumentsId as instrumentsId, instrumentations.famille_instruId as poste_famille_instruId, tr_pupitre.name as pupitreLabel
					FROM instrumentations
					LEFT JOIN tr_pupitre
					ON instrumentations.pupitreId = tr_pupitre.id
					WHERE instrumentations.formationId = '.$formationId.'
					AND instrumentations.famille_instruId = '.$instru_family->id.'
					AND instrumentations.instrumentsId IS NULL
				');
			
			$instru_family_array = $query2->getResultArray();
			

			if (sizeof($instru_family_array) > 0) {
				//log_message("debug"," ** FAMILY !! ".json_encode($instru_family_array));
				return $instru_family_array[0]["pupitreLabel"] == $pupitre;
			}
		}
		
		return false;
	}
	
	
	// Retourne le pupitre principal d'un instrument ID dans une formation
	public function get_main_pupitre($mainInstruId, $formationId) {

		//log_message('debug','**********  '.$mainInstruId);
		
		$instruments_model = new Instruments_model();
		
		if (!isset($formationId)) $formationId = 0;
		
		$instru_family = $instruments_model->get_instru_family($mainInstruId);

		// On récupère les postes définit par un instrument
		$query1 = $this->db->query('
				SELECT tr_pupitre.id as id, tr_pupitre.name as pupitreLabel, tr_pupitre.iconURL as iconURL
				FROM instrumentations
				LEFT JOIN tr_pupitre
				ON instrumentations.pupitreId = tr_pupitre.id
				WHERE instrumentations.formationId = '.$formationId.'
				AND instrumentations.instrumentsId = '.$mainInstruId.'
			');
		
		$instru_array = $query1->getResultArray();
		
		// Si on trouve un poste défini par l'instrumentId on break avec ce main pupitre
		if (sizeof($instru_array) > 0) {
			//log_message("debug"," ** BREAK !! ".json_encode($instru_array));
			return $instru_array[0];
		}
		
		// Sinon, on cherche des postes dont la famille correspond	
		$query2 = $this->db->query('
				SELECT tr_pupitre.id as id, tr_pupitre.name as pupitreLabel, tr_pupitre.iconURL as iconURL
				FROM instrumentations
				LEFT JOIN tr_pupitre
				ON instrumentations.pupitreId = tr_pupitre.id
				WHERE instrumentations.formationId = '.$formationId.'
				AND instrumentations.famille_instruId = '.$instru_family->id.'
				AND instrumentations.instrumentsId IS NULL
			');
		
		$instru_family_array = $query2->getResultArray();
		

		if (sizeof($instru_family_array) > 0) {
			//log_message("debug"," ** FAMILY !! ".json_encode($instru_family_array));
			return $instru_family_array[0];
		}
		
		return false;
	}

}