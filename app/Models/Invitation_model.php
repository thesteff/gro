<?php namespace App\Models;

use CodeIgniter\Model;
use CodeIgniter\I18n\Time;
use CodeIgniter\I18n\TimeDifference;

class Invitation_model extends Model {

	protected $table = "invitation";
	protected $primaryKey = "id";
	
	protected $returnType = "array";
	
	protected $allowedFields = [ 'senderId', 'receiverId', 'state', 'targetTag', 'targetId' ];

    protected $validationRules    = [];
    protected $validationMessages = [];
    protected $skipValidation     = false;


	// Permet de récupérer les discussions d'un utilisateur
	//public function get_invitations($targetTag, $targetId) {

	
}