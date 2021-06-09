<?php namespace App\Controllers;

use App\Models\Members_model;
use App\Models\Instruments_model;
use App\Models\Formation_model;

class Ajax_instruments extends BaseController {
	
	// Récupérer la liste des instrument correspondant à une famille d'instrument
	public function get_family_instru_list() {
		
		$familyId = trim($_POST['familyId']);
		
		$instruments_model = new Instruments_model();
		
		// Pour récupérer l'id du membre et de la jam
		$instruList = $instruments_model->get_family_instruments($familyId);
		
		$return_data = array(
			'state' => true,
			'data' => $instruList
		);
		$output = json_encode($return_data);
		echo $output;
	}
	
	
	// Récupérer la liste des instrument correspondant à la famille d'instrument d'un instrument donné
	public function get_family_instru_list_by_instrument() {
		
		$instruId = trim($_POST['instruId']);
		
		$instruments_model = new Instruments_model();
		
		// On récupère l'instrument
		$instrument = $instruments_model->get_instrument($instruId);
		
		// Pour récupérer l'id du membre et de la jam
		$instruList = $instruments_model->get_family_instruments($instrument["famille_instruId"]);
		
		$return_data = array(
			'state' => true,
			'data' => array(
							'instrument' => $instrument,
							'instruList' => $instruList
			)
		);
		$output = json_encode($return_data);
		echo $output;
	}
	
	
	/***********************   MEMBRES   *************************/
	
	// Récupérer la liste des instruments joués
	public function get_member_instruments() {

		$memberId = trim($_POST['memberId']);
		
		$members_model = new Members_model();
		
		// On ajoute l'instrument au membre
		$data = $members_model->get_instruments($memberId);
		
		$return_data = array(
			'state' => true,
			'data' => $data
		);
		$output = json_encode($return_data);
		echo $output;
	}
	
	
	// Ajouter un instrument à un membre
	public function add_member_instrument() {
		
		$instruId = trim($_POST['instruId']);
		$memberId = trim($_POST['memberId']);
		
		$members_model = new Members_model();
		$instruments_model = new Instruments_model();
		
		// On ajoute l'instrument au membre
		$state = $members_model->add_instrument($memberId,$instruId);
		
		if ($state) $data = $instruments_model->get_instrument($instruId);
		else $data = "Instrument joué déjà présent";
		
		$return_data = array(
			'state' => $state,
			'data' => $data
		);
		$output = json_encode($return_data);
		echo $output;
	}
	
	
	// Ajouter un instrument à un membre
	public function update_member_instrument() {
		
		$instruId = trim($_POST['instruId']);
		$oldInstruId = trim($_POST['oldInstruId']);
		$memberId = trim($_POST['memberId']);
		
		$members_model = new Members_model();
		$instruments_model = new Instruments_model();

		// On ajoute l'instrument au membre
		$state = $members_model->update_instrument($memberId,$instruId,$oldInstruId);
		
		if ($state) $data = $instruments_model->get_instrument($instruId);
		else $data = "update_member_instrument";
		
		$return_data = array(
			'state' => $state,
			'data' => $data
		);
		$output = json_encode($return_data);
		echo $output;
	}
	
	
	// Ajouter un instrument à un membre
	public function delete_member_instrument() {
		
		$instruId = trim($_POST['instruId']);
		$memberId = trim($_POST['memberId']);
		
		$members_model = new Members_model();
		
		// On supprime l'instrument du membre
		$state = ! ($members_model->delete_instrument($memberId,$instruId) == false);
		
		$return_data = array(
			'state' => $state,
			'data' => ""
		);
		$output = json_encode($return_data);
		echo $output;
	}
	
	// Transfert les données d'instrument du vieux au nouveau système
	public function transfert_member_instrument() {
		
		$instruments_model = new Instruments_model();
		
		$state = $instruments_model->transfert_member_instrument();
		$return_data = array(
			'state' => $state,
			'data' => ""
		);
		$output = json_encode($return_data);
		echo $output;
	}
	
	
	/***********************   INSTRUMENTATION   *************************/
	
	// Récupérer la liste des instruments joués dans une formation
	public function get_instrumentation() {
		
		$formationId = trim($_POST['formationId']);
		
		$formation_model = new Formation_model();
		
		// On ajoute l'instrument au membre
		$data = $formation_model->get_instrumentation($formationId);
		
		if ($data == false) $state = false;
		else $state = true;
		
		$return_data = array(
			'state' => $state,
			'data' => $data
		);
		$output = json_encode($return_data);
		echo $output;
	}
	
	
}
?>