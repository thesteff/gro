<!-- bootstrap datepicker !-->
<script type="text/javascript" src="<?php echo base_url();?>/ressources/bootstrap-datepicker-1.6.4/js/bootstrap-datepicker.js"></script>
<script type="text/javascript" src="<?php echo base_url();?>/ressources/bootstrap-datepicker-1.6.4/locales/bootstrap-datepicker.fr.min.js"></script>
<link rel="stylesheet" href="<?php echo base_url();?>/ressources/bootstrap-datepicker-1.6.4/css/bootstrap-datepicker3.css" />

<!-- autoresize texarea !-->
<script type="text/javascript" src="<?php echo base_url();?>/ressources/script/autosize.js"></script>

<!-- bootstrapValidator !-->
<script type="text/javascript" src="<?php echo base_url();?>/ressources/script/validator.js"></script>


<!-- flexdatalist pour les input de lieux !-->
<script type="text/javascript" src="<?php echo base_url();?>/ressources/script/jquery-flexdatalist-2.2.4/jquery.flexdatalist.min.js"></script>
<link href="<?php echo base_url();?>/ressources/script/jquery-flexdatalist-2.2.4/jquery.flexdatalist.min.css" rel="stylesheet" type="text/css">



<script type="text/javascript">

$(function() {
	
	/******** Bootstrap validator ********/
	$('#create_repe_form form').validator();
	$('#create_repe_form form').validator().on('submit', function (e) {
		
		if (e.isDefaultPrevented()) {
			// handle the invalid form...
		}
		else {
			// On bloque le comportement par défault du submit
			e.preventDefault();
			// Pas de problem avec le validator
			create_repetition();
		}
	})
	
	
	// On initialise le datepicker
	$('#create_repe_form #date_repet').datepicker({
		format: "dd/mm/yyyy",
		todayBtn: "linked",
		language: "fr",
		todayHighlight: true
	});
	
	
	// On initialise le autoresize
	$('.autosize').autosize({append: "\n"});
	
	
	// En cas de repopulate
	//lieu_change();
	
	
	/************** MAX_INSCR ************/
	$(":checkbox[name=max_inscr_cb]").change(function () { 
		if($(this).is(":checked")) {
			$("#max_inscr").css('display','inline');
			$("#max_inscr").val(0);
		}
		else {
			$("#max_inscr").css('display','none');
			$("#max_inscr").val(-1);
		}
	});
	
	// On définit la class numbersOnly
	$('.numbersOnly').keyup(function () { 
		this.value = this.value.replace(/[^0-9]/g,'');
	});
	

	// ************** LIEUX **************/	
	// On rempli les flexdatalist
	$('#create_repe_form .flexdatalist').flexdatalist({
		 minLength: 0,
		 selectionRequired: true,
		 data: [{ 'id':'-1', 'name':'lieu non défini'},
				<?php foreach ($list_lieux as $lieu): ?>
					{ 'id':'<?php echo $lieu["id"] ?>', 'name':'<?php echo htmlentities($lieu["nom"]) ?>'},
				<?php endforeach ?>
				],
		 searchIn: 'name',
		 searchByWord: true,
		 valueProperty: 'id'	// on envoie l'attribut 'id' quand on appelle la méthode val()
		});
		
	
	// LIEU CHANGE
	$('#create_repe_form .flexdatalist').on('change:flexdatalist', function(event, set, options) {
		
		// Requète ajax au serveur
		$.post("<?php echo site_url('ajax/get_location'); ?>",
		
			{
			'lieuId':$.isNumeric($("#create_repe_form #lieu").val()) ? $("#create_repe_form #lieu").val() : "-1"
			},
	
			function (msg) {
				console.log("msg : "+msg);
			
				// Le lieu spécifié n'est pas présent dans la base
				if (msg == "lieu_not_found") {
					if (!$("#create_repe_form #lieu_details").hasClass("hidden") ) {
						$("#create_repe_form #lieu_details").addClass("hidden");
						$("#create_repe_form #lieu_adresse").empty();
						$("#create_repe_form #lieu_web").empty();
					}
				}
				
				// Si on a trouvé le lieu, on affiche les détails
				else {
					
					$lieu = JSON.parse(msg);
					
					$("#create_repe_form #lieu_details").removeClass("hidden");
					
					$("#create_repe_form #lieu_web").empty();
					$("#create_repe_form #lieu_adresse").empty();
					if ($lieu.adresse.length) {
						$("#create_repe_form #lieu_adresse").append($lieu.adresse);
						$("#create_repe_form #lieu_adresse").css("display","block");
					}
					if ($lieu.web.length) {
						$("#create_repe_form #lieu_web").append($lieu.web);
						$("#create_repe_form #lieu_web").prop("href","http://"+$lieu.web);
						$("#create_repe_form #lieu_web").css("display","block");
					}
				}
			}
		);
	});
});

	
	
	/****** CREATE REPETITION  *******/
	function create_repetition() {

		// On change le curseur
		document.body.style.cursor = 'wait';
	
		// Requète ajax au serveur
		$.post("<?php echo site_url('ajax_jam/create_repetition/').$jam_item['slug']; ?>",
		
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
		);
    }
	
	
 </script>

 

<!-- Formulaire !-->
<div id="create_repe_form" class="container-fluid">
	<form class="form-horizontal">
		
		<!-- Pupitres !-->
		<div class="form-group required">
			<label for="instru_catId" class="control-label col-sm-2 col-xs-3 adjust-xs">Pupitre</label>
			<div class="col-sm-5 col-xs-8">
				<select id="pupitreId" class="form-control" name="pupitreId">
					<option value="-1">Générale</option>
					<?php if (isset($pupitre_list)) : ?>
						<?php foreach ($pupitre_list as $pupitre): ?>
							<option value="<?php echo $pupitre['pupitreId']; ?>"><?php echo ucfirst($pupitre['pupitreLabel']); ?></option>
						<?php endforeach; ?>
					<?php endif; ?>
				</select>
			</div>
		</div>
		
		<!-------- DATE --------->
		<div class="form-group required">
			<label for="date_repet" class="control-label col-sm-2 col-xs-3 adjust-xs">Date</label>
			<div class="col-sm-3 col-xs-8">
				<input id="date_repet" class="form-control text-center" required="true" type="text" name="date_repetition" value="" autocomplete="off" />
			</div>
		</div>
		
		<hr>
		
		<!-------- LIEU --------->
		<div class="form-group">
			<label for="lieu" class="control-label col-sm-2">Lieu</label>
			<div class="col-sm-9">
				<input id="lieu" class="form-control flexdatalist" list="lieux" type="text" name="lieu" value="-1" />
				<!-- On affiche les détails s'il y en a !-->
				<div id="lieu_details" class="soften small panel panel-default hidden" style="padding: 5px 10px; margin-bottom: 0px">
					<span id="lieu_adresse" style="display:none"></span>
					<a id="lieu_web" target="_blanck" style="display:none" href="#"></a>
				</div>
			</div>
		</div>
		
		<hr>
		
		<!----------------- PLANNING --------------------->		
		<!-- **** DEBUT **** !-->
		<div class="form-group">
			<label for="date_debut" class="control-label col-sm-2 col-xs-4 adjust-xs">Début</label>
			<div class="col-sm-2 col-xs-4">
				<input id="date_debut" class="form-control text-center" type="input" name="date_debut" list="horaires" class="numbers" autocomplete="off" value="" />
			</div>
		</div>
		
		<!-- **** FIN **** !-->
		<div class="form-group">
			<label for="date_fin" class="control-label col-sm-2 col-xs-4 adjust-xs">Fin</label>
			<div class="col-sm-2 col-xs-4">
				<input id="date_fin" class="form-control text-center" type="input" name="date_fin" list="horaires" class="numbers" autocomplete="off" value="" />
				<datalist id="horaires">
				<?php
					$h = 0;
					$m = 0;
					while ($h < 24) {
						if ($h < 10) $pref = "0"; else $pref = "";
						while ($m < 60) {
							if ($m < 10) $pref2 = "0"; else $pref2 = "";
							echo '<option value="'.$pref.strval($h).':'.$pref2.strval($m).'">'.$pref.strval($h).':'.$pref2.strval($m).'</option>';
							$m += 30;	// Pas des minutes dans la liste
						}
						$m = 0;
						$h++;
					}
				?>
				</datalist>
			</div>
		</div>
				
		<hr>
		
		<!-------- TEXTE --------->
		<div class="form-group">
			<div class="row">
				<div class="col-sm-12">
					<textarea id="repet_textarea" class="form-control autosize" name="text" placeholder="Texte de la répétition" style="resize:none"></textarea>
				</div>
			</div>
		</div>

		
		<!--<hr>!-->

		
		<!---------- MAXIMUM d'inscrits  -------->
		<!--<div class="form-group">
			<label for="max_inscr_cb" class="control-label col-sm-4 col-xs-6 adjust-xs">Nb max d'inscrits</label>
			<div class="checkbox col-sm-2 col-xs-2">
				<label style="padding-left: 0px">
					<input class="form-control" name="max_inscr_cb" type="checkbox" value="" />
					<span class="cr"><i class="cr-icon glyphicon glyphicon-ok"></i></span>
				</label>
			</div>
			
			<label for="max_inscr" class="control-label sr-only"></label>
			<input id='max_inscr' class="form-control text-center" type="input" style="width:55px; display:none" name="max_inscr" list="nb_inscr" value="-1" />
			<datalist id="nb_inscr">
			<?php
				$nb = 10;
				while ($nb <= 150) {
					echo '<option value="'.$nb.'">'.$nb.'</option>';
					$nb += 10;
				}
			?>
			</datalist>
		</div>!:-->

		<hr>
		
		<div class="modal-footer">
			<button type="button" class="btn btn-default" data-dismiss="modal">Annuler</button>
			<button type="submit" class="btn btn-primary">Créer</button>
		</div>

	</form>
</div>