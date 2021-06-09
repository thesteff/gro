<?php
	// On récupère les variables de sessions
	$session = \Config\Services::session();
?>

<!-- bootstrap datepicker !-->
<script type="text/javascript" src="<?php echo base_url("ressources/bootstrap-datepicker-1.6.4/js/bootstrap-datepicker.js"); ?>"></script>
<script type="text/javascript" src="<?php echo base_url("ressources/bootstrap-datepicker-1.6.4/locales/bootstrap-datepicker.fr.min.js"); ?>"></script>
<link rel="stylesheet" href="<?php echo base_url("ressources/bootstrap-datepicker-1.6.4/css/bootstrap-datepicker3.css"); ?>"/>

<!-- bootstrapValidator !-->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-validator/0.5.3/js/bootstrapValidator.js"></script>

<!-- Bootstrap-select !-->
<link rel="stylesheet" href="<?php echo base_url("ressources/bootstrap-select/bootstrap-select.min.css" ); ?>"/>
<script type="text/javascript" src="<?php echo base_url("ressources/bootstrap-select/bootstrap-select.min.js"); ?>"></script>


<script type="text/javascript">

	$(function() {
		

		$('#profil_form').bootstrapValidator({
			// To use feedback icons, ensure that you use Bootstrap v3.1.0 or later
			feedbackIcons: {
				valid: 'glyphicon glyphicon-ok',
				invalid: 'glyphicon glyphicon-remove',
				validating: 'glyphicon glyphicon-refresh'
			},
			fields: {
				pseudo: {
					validators: {
							notEmpty: {
							message: 'Vous devez saisir un pseudo'
						},
						stringLength: {
							min: 3,
							max: 50,
							message: 'Le pseudo est invalide (trop petit ou trop long)'
						}
					}
				},
				email: {
					validators: {
						notEmpty: {
							message: 'Vous devez saisir une adresse email'
						},
						emailAddress: {
							message: 'L\'adresse email doit être valide'
						}
					}
				},
				mobile: {
					validators: {
						phone: {
							country: 'FR',
							message: 'Le numéro doit être valide'
						}
					}
				},
				pass: {
					validators: {
						notEmpty: {
							message: 'Vous devez saisir un mot de passe'
						},
						stringLength: {
							min: 6,
							message: 'Le mote de passe doit comporter au moins 6 caractères'
						}
					}
				},
				pass2: {
					validators: {
						notEmpty: {
							message: 'Vous devez confirmer le mot de passe'
						},
						identical: {
							field: 'pass',
							message: 'Vous devez saisir deux fois le même mot de passe'
						}
					}
				}
			 }
        })
        .on('success.form.bv', function(e) {
            //$('#success_message').slideDown({ opacity: "show" }, "slow") // Do something ...
            $('#profil_form').data('bootstrapValidator').resetForm();

            // Prevent form submission
            e.preventDefault();
            // Get the form instance
            var $form = $(e.target);
            // Get the BootstrapValidator instance
            var bv = $form.data('bootstrapValidator');

			// On créé un array avec les instruId des instruments joués
			var instruArray = [];
			$("#listInstruDiv div[instruid]").each(function() {
				instruArray.push($(this).attr("instruId"));
			});
			
			
            // Requète ajax au serveur
			document.body.style.cursor = 'wait';
			$.post("<?php echo site_url('ajax_members/create_member'); ?>",
			
				// On récupère les données nécessaires
				{
					'pseudo':$('#profil_form #pseudo').val(),
					'email':$('#profil_form #email').val(),
					'nom':$('#profil_form #nom').val(),
					'prenom':$('#profil_form #prenom').val(),
					'genre':$('#profil_form #genre').val(),
					'naissance':$('#profil_form #naissance').val(),
					'mobile':$('#profil_form #mobile').val().replace(/[\. ,:-]+/g, ""),
					'pass':$('#profil_form #pass').val(),
					'instruArray':JSON.stringify(instruArray),
					'allowMail':$('#profil_form #allowMail').is(':checked') ? "1" : "0"
				},
				
				// On traite la réponse du serveur			
				function (return_data) {
					
					console.log("create_member : "+return_data);
					
					$obj = JSON.parse(return_data);
					// On change le curseur
					document.body.style.cursor = 'default';

					// Profil créé
					if ($obj['state']) {
						// Success
						$("#modal_msg .modal-dialog").removeClass("error");
						$("#modal_msg .modal-dialog").addClass("success");
						$("#modal_msg .modal-header").html("Profil créé avec succès !");
						$("#modal_msg .modal-body").html($obj['data']);
						$("#modal_msg .modal-footer").html('<button class="btn btn-default" id="modal_close" onclick="location.href=\'<?php echo site_url(); ?>\'" data-dismiss="modal">Fermer</button>');
					}
					
					// Profil inchangé
					else {
						// Erreur
						$("#modal_msg .modal-dialog").removeClass("success");
						$("#modal_msg .modal-dialog").addClass("error");
						$("#modal_msg .modal-header").html("Erreur !");
						$("#modal_msg .modal-body").html($obj['data']);
						$("#modal_msg .modal-footer").html('<a id="modal_close" href="#" data-dismiss="modal">Fermer</a>');
					}
					$("#modal_msg").modal("show");
				}
			);
        });
		
		
		// On initialise le datepicker
		$('#naissance').datepicker({
			format: "dd/mm/yyyy",
			todayBtn: "linked",
			language: "fr",
			todayHighlight: true,
			startView: 2
		});

		
		
		// Validation dynamique du pseudo par Ajax
		$("#profil_form #pseudo").change(function() {
			
			document.body.style.cursor = 'wait';
			$.post("<?php echo site_url('ajax_members/check_pseudo'); ?>",
			
				// On récupère les données nécessaires
				{'pseudo':$('#profil_form #pseudo').val()
				},
				
				// On traite la réponse du serveur
				function (return_data) {
					
					$obj = JSON.parse(return_data);
					document.body.style.cursor = 'default';

					// Si le pseudo est déjà pris
					if ($obj['state']) {
						$("#modal_msg").attr("target","pseudo");
						$("#modal_msg .modal-dialog").addClass("error");
						$("#modal_msg .modal-dialog").removeClass("success");
						$("#modal_msg .modal-header").html("Erreur");
						$("#modal_msg .modal-body").html($obj['data']);
						$("#modal_msg .modal-footer").html('<button class="btn btn-default" id="modal_close" href="#" data-dismiss="modal">Fermer</button>');
						$("#modal_msg").modal("show");
					}
				}
			);

		});
		
		
		// Validation dynamique de l'email par Ajax
		$("#profil_form #email").change(function() {

			document.body.style.cursor = 'wait';
			$.post("<?php echo site_url('ajax_members/check_email'); ?>",
			
				// On récupère les données nécessaires
				{'email':$('#profil_form #email').val()
				},
				
				// On traite la réponse du serveur
				function (return_data) {
					
					$obj = JSON.parse(return_data);
					document.body.style.cursor = 'default';

					// Si le pseudo est déjà pris
					if ($obj['state']) {
						$("#modal_msg").attr("target","email");
						$("#modal_msg .modal-dialog").addClass("error");
						$("#modal_msg .modal-dialog").removeClass("success");
						$("#modal_msg .modal-header").html("Erreur");
						$("#modal_msg .modal-body").html($obj['data']);
						$("#modal_msg .modal-footer").html('<button class="btn btn-default" id="modal_close" href="#" data-dismiss="modal">Fermer</button>');
						$("#modal_msg").modal("show");
					}
				}
			);

		});
		
		
		
		// Dialog box pour la validation dynamique et le callback de création
		$('#modal_msg').on('hidden.bs.modal', function () {
			$target = $("#modal_msg").attr("target");
			
			// On gère la réponse du create member ajax
			if ($target == "success") window.location.href = "<?php echo site_url(); ?>";
			else if ($target == "error") $target = "pseudo";
			
			// On gère dynamiquement la saisie des inputs
			$('#profil_form').data('bootstrapValidator').resetForm();
			$('#profil_form #'+$target).val('');
			$('#profil_form #'+$target).focus();
			$("#modal_msg").attr("target","");
		});
		
		
		
		/****************************** Instrument IHM ****************************/

		// ****** INSTRUMENTS MODALS ********
		$("[id$='InstruModal'").on("show.bs.modal", function(e) {
			var link = $(e.relatedTarget);
			$(this).find(".modal-content").load(link.attr("href"));
		});
		
		
		$("#addInstru").on("click", function(e) {
			// Prevent form submission
            e.preventDefault();
		});
		
		
	});




</script>


<div class="row">

	<!-- Block principal !-->
	<div class="col-md-9 col-lg-9 panel panel-default">
		
	
			<!-- Header !-->
			<div class="row">
				<h4 class="panel-heading">Inscription</h4>
			</div>
	
			<!-- Formulaire !-->
			<div class="container-fluid">
				<form id="profil_form" class="form-horizontal">

					<!-- Pseudo !-->
					<div class="form-group required">
						<label for="pseudo" class="control-label col-sm-2">Pseudo</label>
						<div class="col-sm-10">
							<input id="pseudo" class="form-control" required="true" type="text" name="pseudo" />
						</div>
					</div>
				
					<!-- Email !-->
					<div class="form-group required">
						<label for="email" class="control-label col-sm-2">Email</label>
						<div class="col-sm-10">
							<input id="email" class="form-control" required="true" type="email" name="email" />
						</div>
					</div>
					
					<!-- Nom !-->
					<div class="form-group">
						<label for="nom" class="control-label col-sm-2">Nom</label>
						<div class="col-sm-10">
							<input id="nom" class="form-control" type="text" name="nom" />
						</div>
					</div>
					
					<!-- Prénom !-->
					<div class="form-group">
						<label for="prenom" class="control-label col-sm-2">Prénom</label>
						<div class="col-sm-10">
							<input id="prenom" class="form-control" type="text" name="prenom" />
						</div>
					</div>
					
					<!-- Date de naissance !-->
					<div class="form-group">
						<label for="naissance" class="control-label col-sm-2">Date de naissance</label>
						<div class="col-sm-10">
							<input id="naissance" class="form-control" type="text" name="naissance" value="<?php echo empty($members_item['naissance']) ? "" : $members_item['naissance'] ?>" />
						</div>
					</div>
					
					<!-- Genre !-->
					<div class="form-group">
						<label for="genre" class="control-label col-sm-2">Genre</label>
						<div class="col-sm-10">
							<select id="genre" class="form-control selectpicker" name="genre" data-style="btn-default">
								<option value="0">Non spécifié</option>
								<option value="1">Homme</option>
								<option value="2">Femme</option>
							</select>
						</div>
					</div>
					
					<!-- Mobile !-->
					<div class="form-group">
						<label for="mobile" class="control-label col-sm-2">Mobile</label>
						<div class="col-sm-10">
							<input id="mobile" class="form-control" type="text" name="mobile" />
						</div>
					</div>
					
					
					<!-- Allow Mail !-->
					<div class="form-group">
						<label for="allowMail" class="control-label col-sm-2">Autoriser les emails</label>
						<div class="checkbox col-sm-10">
							<label>
								<input id="allowMail" type="checkbox" value="">
								<span class="cr"><i class="cr-icon glyphicon glyphicon-ok"></i></span>
							</label>
						</div>
					</div>

					
					
					<!-- INSTRUMENTS !-->
					<h4 class="col-sm-offset-2 col-sm-10" style="padding-bottom:10px"><strong>Instrument</strong></h4>
						
					<div class="container-fluid" style="padding-bottom:15px">

						<!-- LISTE des instruments joués par le membre !-->
						<div id="listInstruDiv" style="margin-right: 15px">
						</div>

						<!-- ADD INSTRUMENT !-->
						<button id="addInstru" class="btn btn-default col-sm-offset-2 col-sm-10" href="<?php echo site_url("members/add_instrument/-1") ?>" data-remote="false" data-toggle="modal" data-target="#addInstruModal">Ajouter un instrument</button>
						
					</div>
					
					
					<!-- PASSWORD !-->
					<h4 class="col-sm-offset-2 col-sm-10" style="padding-bottom:10px; padding-top:10px;"><strong>Sécurité</strong></h4>
					
					<!-- Password !-->
					<div class="form-group required">
						<label for="pass" class="control-label col-sm-2">Mot de passe</label>
						<div class="col-sm-10">
							<input id="pass" class="form-control" type="password" name="pass" required="true" />
						</div>
					</div>
					
					
					<!-- Confirm Pass !-->
					<div class="form-group required">
						<label for="pass2" class="control-label col-sm-2">Vérification</label>
						<div class="col-sm-10">
							<input id="pass2" class="form-control" type="password" name="pass2" required="true"  />
						</div>
					</div>
					
					
					
					<!-- Envoyer !-->
					<input id="create" class="btn btn-primary pull-right" type="submit" value="S'inscrire"/>
					
				</form>
			</div>				
	</div>			
		
</div>



<!-- Dialogue box de resultat !-->
<div id="modal_msg" class="modal fade" role="dialog" data-keyboard="true" data-backdrop="static">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header lead"></div>
			<div class="modal-body"></div>
			<div class="modal-footer"></div>
		</div>
	</div>
</div>


<!-- ******** MODAL ADD INSTRUMENT ******* !-->
<div id="addInstruModal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog default">
	<div class="modal-content">
		...
	</div>
	</div>
</div>

<!-- ******** MODAL UPDATE INSTRUMENT ******* !-->
<div id="updateInstruModal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog default">
	<div class="modal-content">
		...
	</div>
	</div>
</div>
