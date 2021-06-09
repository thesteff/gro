
<!-- bootstrapValidator !-->
<script type="text/javascript" src="<?php echo base_url();?>/ressources/script/validator.js"></script>

<!-- Bootstrap-select !-->
<link rel="stylesheet" href="<?php echo base_url();?>/ressources/bootstrap-select/bootstrap-select.min.css" />
<script type="text/javascript" src="<?php echo base_url();?>/ressources/bootstrap-select/bootstrap-select.min.js"></script>



<script type="text/javascript">

	$(function() {
		
		/******** Bootstrap validator ********/
		$('#create_version_form form').validator();
		$('#create_version_form form').validator().on('submit', function (e) {
			
			if (e.isDefaultPrevented()) {
				// handle the invalid form...
			}
			else {
				// On bloque le comportement par défault du submit
				e.preventDefault();
				// Pas de problem avec le validator
				create_version();
			}
		})
		
		
		/************* Selectpicker ***************/
		// On réinit car sinon pas visible à cause du scroll de modal
		$('.selectpicker').selectpicker();
		
		// On désactive le bouton de création par défaut
		$(".modal-footer #submit").prop('disabled', true);
		
		// Un change = une selection de collection = on active le bouton submit
		$("#collectionInput").on("change", function() {
			$(".modal-footer #submit").prop('disabled', false);
		});

	});

	
	
	/****** CREATE MORCEAU  *******/
	function create_version() {

		// On change le curseur
		document.body.style.cursor = 'wait';
	
		// Requète ajax au serveur
		$.post("<?php echo site_url('ajax_version/create_version'); ?>",
		
			{	
				'morceauId': JSON.parse($("#titreInput").val())['id'],
				'label': $("#create_version_form #collectionInput").val()
			},
		
			function (return_data) {
				
				console.log(return_data);
				$obj = JSON.parse(return_data);
				
				// On change le curseur
				document.body.style.cursor = 'default';
				
				// Modal
				if ($obj['state'] == 1) {
					
					// On hide la modal
					$("#createVersionModal").modal('hide');
					
					// On actualise l'affichage
					get_versions();		// un peu sale car on a les données dans return_data
					
				}
				else {
					$("#createVersionModal").modal('hide');
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
<div id="create_version_form" class="container-fluid">
	<form class="form-horizontal">
		
		
		<!-------- COLLECTION --------->
		<div class="form-group required">
			<label for="collectionInput" class="control-label col-sm-2 col-xs-2 adjust-xs">Collection</label>
			<div class="col-sm-8 col-xs-8">
				<select id="collectionInput" class="form-control selectpicker" name="collectionInput" required="true" data-style="btn-default" title="Collections existantes">
					<?php foreach ($collections as $col) {
						echo '<option value="'.$col->label.'">'.$col->label.'</option>';
					} ?>
				</select>
			</div>
		</div>

		<hr>
		
		<div class="modal-footer">
			<button type="button" class="btn btn-default" data-dismiss="modal">Annuler</button>
			<button id="submit" type="submit" class="btn btn-primary">Créer</button>
		</div>

	</form>
</div>