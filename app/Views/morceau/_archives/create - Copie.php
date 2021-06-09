
<!-- bootstrapValidator !-->
<script type="text/javascript" src="<?php echo base_url();?>/ressources/script/validator.js"></script>


<!-- flexdatalist !-->
<script type="text/javascript" src="<?php echo base_url();?>/ressources/script/jquery-flexdatalist-2.2.4/jquery.flexdatalist.min.js"></script>
<link href="<?php echo base_url();?>/ressources/script/jquery-flexdatalist-2.2.4/jquery.flexdatalist.min.css" rel="stylesheet" type="text/css">



<script type="text/javascript">

$(function() {
	
	/******** Bootstrap validator ********/
	$('#create_morceau_form form').validator();
	$('#create_morceau_form form').validator().on('submit', function (e) {
		
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
	$('.flexDL_Compositeur').flexdatalist({
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
	

});

	
	
	/****** CREATE MORCEAU  *******/
	function create_morceau() {

		// On change le curseur
		document.body.style.cursor = 'wait';
	
		// Requète ajax au serveur
		/*$.post("<?php echo site_url(); ?>/ajax_jam/create_repetition/<?php echo '' ?>",
		
			{	
				'date_repet':$("#create_repe_form #date_repet").val(),
				'date_debut':$("#create_repe_form #date_debut").val(),
				'date_fin':$("#create_repe_form #date_fin").val(),
				'lieuId':$("#create_repe_form #lieu").val(),
				'text':$("#create_repe_form #repet_textarea").val(),
				'pupitreId':$("#create_repe_form #pupitreId").val()
			},
		
			function (return_data) {
	
				$obj = JSON.parse(return_data);
				// On change le curseur
				document.body.style.cursor = 'default';
				
				// Modal
				if ($obj['state'] == 1) {
					window.location.reload();
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
		);*/
    }
	
	
 </script>

 

<!-- Formulaire !-->
<div id="create_morceau_form" class="container-fluid">
	<form class="form-horizontal">
		
		
		<!-------- TITRE --------->
		<div class="form-group required">
			<label for="titre" class="control-label col-sm-3 col-xs-3 adjust-xs">Titre</label>
			<div class="col-sm-8 col-xs-8">
				<input id="titre" class="form-control" required="true" type="text" name="titre" value="" autocomplete="off" />
			</div>
		</div>
		
		
		<!-------- COMPOSITEUR --------->
		<div class="form-group">
			<label for="compoInput" class="control-label col-sm-3 col-xs-3 adjust-xs">Compositeur</label>
			<div class="col-sm-8 col-xs-8">
				<input id="compoInput" class="form-control flexDL_Compositeur" type="text" name="compoInput" />
			</div>
		</div>
		
		
		<!-------- DATE --------->
		<div class="form-group">
			<label for="anneeInput" class="control-label col-sm-3">Date</label>
			<div class="col-sm-9">
				<input id="anneeInput" size="5" style="text-align:center" list="years" placeholder="Année" autocomplete="off">
				<datalist id="years">
					<?php 
						for ($i=1900; $i <= date("Y"); $i++) {
							echo "<option value='".$i."'>".$i."</option>";
						}
					?>
				</datalist>
			</div>
		</div>

		<hr>
		
		<div class="modal-footer">
			<button type="button" class="btn btn-default" data-dismiss="modal">Annuler</button>
			<button type="submit" class="btn btn-primary">Créer</button>
		</div>

	</form>
</div>