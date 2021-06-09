
<!-- bootstrapValidator !-->
<script type="text/javascript" src="<?php echo base_url();?>/ressources/script/validator.js"></script>


<!-- flexdatalist !-->
<script type="text/javascript" src="<?php echo base_url();?>/ressources/script/jquery-flexdatalist-2.2.4/jquery.flexdatalist.min.js"></script>
<link href="<?php echo base_url();?>/ressources/script/jquery-flexdatalist-2.2.4/jquery.flexdatalist.min.css" rel="stylesheet" type="text/css">


<!-- bootstrap datepicker !-->
<script type="text/javascript" src="<?php echo base_url();?>/ressources/bootstrap-datepicker-1.6.4/js/bootstrap-datepicker.js"></script>
<script type="text/javascript" src="<?php echo base_url();?>/ressources/bootstrap-datepicker-1.6.4/locales/bootstrap-datepicker.fr.min.js"></script>
<link rel="stylesheet" href="<?php echo base_url();?>/ressources/bootstrap-datepicker-1.6.4/css/bootstrap-datepicker3.css" />



<script type="text/javascript">

$(function() {
	
	/******** Bootstrap validator ********/
	$('#update_morceau_form form').validator();
	$('#update_morceau_form form').validator().on('submit', function (e) {
		
		if (e.isDefaultPrevented()) {
			// handle the invalid form...
		}
		else {
			// On bloque le comportement par défault du submit
			e.preventDefault();
			// Pas de problem avec le validator
			create_morceau();
		}
	})
	
	
	
	// On rempli le flexdatalist des compositeurs
	$('#update_morceau_form #compoInput').flexdatalist({
		 minLength: 0,
		 selectionRequired: true,
		 data: [{ 'id':'-1', 'label':'compositeur non défini'},
				<?php foreach ($list_artist as $artist): ?>
					{ 'id':'<?php echo $artist->id ?>', 'label':'<?php echo addslashes(htmlspecialchars(($artist->label))); ?>'},
				<?php endforeach ?>
				],
		 searchIn: 'label',
		 searchByWord: true,
		 valueProperty: 'id'	// on envoie l'attribut 'id' quand on appelle la méthode val()
	});
	
	// On set l'artiste
	$('#update_morceau_form #compoInput').flexdatalist('value','<?php echo $morceau_item->artisteId ?>')
	
	
	// On initialise le datepicker
	$('#update_morceau_form #anneeInput').datepicker({
		format: "yyyy",
		language: "fr",
		startView: 2,
		viewMode: "years", 
		minViewMode: "years"
	});
	

});

	
	
	/****** CREATE MORCEAU  *******/
	function create_morceau() {

		// On change le curseur
		document.body.style.cursor = 'wait';
	
		// Requète ajax au serveur
		$.post("<?php echo site_url('ajax_morceau/update_morceau'); ?>",
		
			{	
				'morceauId': <?php echo $morceau_item->id ?>,
				'titre': $("#update_morceau_form #titreInput").val(),
				'artisteId': $("#update_morceau_form #compoInput").val(),
				'annee': $("#update_morceau_form #anneeInput").val()
			},
		
			function (return_data) {
	
				$obj = JSON.parse(return_data);
				// On change le curseur
				document.body.style.cursor = 'default';
				
				// Modal
				if ($obj['state'] == 1) {
					
					// On update le flexdatalist
					$data = $("#titreInput").flexdatalist('data');
					i = 0;
					find = false;
					while (i < $data.length && !find && i < 500) {
						if ($data[i].id == $obj['data']['id']) {
							find = true;
							$data[i].titre = $obj['data']['titre'];
							$data[i].compositeur = $obj['data']['artistLabel'];
							$data[i].date = $obj['data']['annee'].toString();
							break;
						}
						i++;
					}
					console.log($data[i]);
					$("#titreInput").flexdatalist('value', $obj['data']['id']);
					morceauSelected();
					
					// On hide la modal
					$("#updateMorceauModal").modal('hide');
				}
				else {
					$("#updateModal").modal('hide');
					// Erreur
					$("#modal_msg .modal-dialog").removeClass("success");
					$("#modal_msg .modal-dialog").addClass("error");
					$("#modal_msg .modal-dialog").addClass("backdrop","static");
					$("#modal_msg .modal-header").html("Erreur !");
					$("#modal_msg .modal-body").html($obj['data']);
					$("#modal_msg .modal-footer").html('<a id="modal_close" href="#" data-dismiss="modal">Fermer</a>');
					$("#modal_msg").modal('show');
				}
			}
		);
    }
	
	
 </script>

 

<!-- Formulaire !-->
<div id="update_morceau_form" class="container-fluid">
	<form class="form-horizontal">
		
		
		<!-------- TITRE --------->
		<div class="form-group required">
			<label for="titreInput" class="control-label col-sm-3 col-xs-3 adjust-xs">Titre</label>
			<div class="col-sm-8 col-xs-8">
				<input id="titreInput" class="form-control" required="true" type="text" name="titreInput" value="<?php echo $morceau_item->titre ?>" autocomplete="off" />
			</div>
		</div>
		
		
		<!-------- COMPOSITEUR --------->
		<div class="form-group">
			<label for="compoInput" class="control-label col-sm-3 col-xs-3 adjust-xs">Compositeur</label>
			<div class="col-sm-8 col-xs-8">
				<input id="compoInput" class="form-control" type="text" name="compoInput" />
			</div>
		</div>
		
		
		<!-------- DATE --------->
		<div class="form-group">
			<label for="anneeInput" class="control-label col-sm-3">Date</label>
			<div class="col-sm-8 col-xs-8">
				<input id="anneeInput" class="form-control" value="<?php echo $morceau_item->annee ?>">
			</div>
		</div>

		<hr>
		
		<div class="modal-footer">
			<button type="button" class="btn btn-default" data-dismiss="modal">Annuler</button>
			<button type="submit" class="btn btn-primary">Modifier</button>
		</div>

	</form>
</div>