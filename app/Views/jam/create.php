<!-- bootstrap datepicker !-->
<script type="text/javascript" src="<?php echo base_url();?>/ressources/bootstrap-datepicker-1.6.4/js/bootstrap-datepicker.js"></script>
<script type="text/javascript" src="<?php echo base_url();?>/ressources/bootstrap-datepicker-1.6.4/locales/bootstrap-datepicker.fr.min.js"></script>
<link rel="stylesheet" href="<?php echo base_url();?>/ressources/bootstrap-datepicker-1.6.4/css/bootstrap-datepicker.css" />

<!-- bootstrapValidator !-->
<script type="text/javascript" src="<?php echo base_url();?>/ressources/script/validator.js"></script>


<script type="text/javascript">

	$(function() {
		
		/******** Bootstrap validator ********/
		$('#jam_create_form form').validator();
		$('#jam_create_form form').validator().on('submit', function (e) {
			
			if (e.isDefaultPrevented()) {
				// handle the invalid form...
			}
			else {
				// On bloque le comportement par défault du submit
				e.preventDefault();
				// Pas de problem avec le validator
				create_jam();
			}
		})
		
		// On initialise le datepicker
		$('#jam_create_form #date_jam').datepicker({
			format: "dd/mm/yyyy",
			todayBtn: "linked",
			language: "fr",
			todayHighlight: true
		});
		
	});

	
	
	
	/****** CREATE JAM  *******/
	function create_jam() {
		
		// On change le curseur
		document.body.style.cursor = 'wait';
	
		// Requète ajax au serveur
		$.post("<?php echo site_url('ajax_jam/create_jam'); ?>",
		
			{	
				'title':$("#jam_create_form #title").val(),
				'date_jam':$("#jam_create_form #date_jam").val()
			},
		
			function (return_data) {
				
				$obj = JSON.parse(return_data);
				// On change le curseur
				document.body.style.cursor = 'default';
				
				// Modal
				if ($obj['state'] == 1) {
					window.location.replace($obj['data']);
				}
				else {
					$("#createModal").modal('hide');
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
<div id="jam_create_form" class="container-fluid">
	<form class="form-horizontal">

		<!-------- TITRE --------->
		<div class="form-group required">
			<label for="title" class="control-label col-sm-2">Titre</label>
			<div class="col-sm-9">
				<input id="title" class="form-control" required type="text" name="title" value="" placeholder="Titre de la jam" />
			</div>
		</div>
		
		<!-------- DATE --------->
		<div class="form-group required">
			<label for="date_jam" class="control-label col-sm-2 col-xs-3 adjust-xs">Date</label>
			<div class="col-sm-3 col-xs-6">
				<input id="date_jam" class="form-control text-center" required="true" type="text" name="date_jam" value="" autocomplete="off" />
			</div>
		</div>
		
			
		<!-------- BUTTONS --------->
		<div class="modal-footer">
			<button type="button" class="btn btn-default" data-dismiss="modal">Annuler</button>
			<button type="submit" class="btn btn-primary">Créer</button>
		</div>
		
		</form>	
	</div>
	
</div>