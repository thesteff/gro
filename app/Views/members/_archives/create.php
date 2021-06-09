<!-- bootstrapValidator !-->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-validator/0.5.3/js/bootstrapValidator.js"></script>

<?php if ($this->config->item("bootstrap") == true): ?>

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
                        message: 'Votre mot de passe doit contenir au moins 6 caractères'
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

			
            // Requète ajax au serveur
			document.body.style.cursor = 'wait';
			$.post("<?php echo site_url(); ?>/ajax/create_member",
			
				// On récupère les données nécessaires
				{'pseudo':$('#profil_form #pseudo').val(),
				'email':$('#profil_form #email').val(),
				'nom':$('#profil_form #nom').val(),
				'prenom':$('#profil_form #prenom').val(),
				'mobile':$('#profil_form #mobile').val(),
				'instru1':$('#profil_form #instru1').val(),
				'instru2':$('#profil_form #instru2').val(),
				'benevole':$('#profil_form #benevole').is(':checked') ? "1" : "0",
				'pass':$('#profil_form #pass').val()
				},
				
				// On traite la réponse du serveur			
				function (return_data) {
					
					console.log("return_data : "+return_data);
					
					$obj = JSON.parse(return_data);
					// On change le curseur
					document.body.style.cursor = 'default';

					// Profil créé
					if ($obj['state']) {
						// Success
						$("#modal_msg").attr("target","success");
						$("#modal_msg .modal-dialog").removeClass("error");
						$("#modal_msg .modal-dialog").addClass("success");
						$("#modal_msg .modal-header").html("Profil créé");
						$("#modal_msg .modal-body").html($obj['data']);
						$("#modal_msg .modal-footer").html('<a id="modal_close" href="#" data-dismiss="modal">Fermer</a>');
					}
					
					// Erreur
					else {
						$("#modal_msg").attr("target","error");
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
		
		

		
		// Affichage dynamique de la select instru2 en fonction de la selection de instru1
		$("#instru1").change(function() {
			if ($("#instru1").val() == 1) {    // Si aucun instrument selectionné
				$("#divInstru2").fadeOut(500);
				$("#instru2").val("1");
			}
			else $("#divInstru2").fadeIn(500);
		});
		
		
		
		// Validation dynamique du pseudo par Ajax
		$("#pseudo").change(function() {
			
			document.body.style.cursor = 'wait';
			$.post("<?php echo site_url(); ?>/ajax/check_pseudo",
			
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
						$("#modal_msg .modal-footer").html('<a id="modal_close" href="#" data-dismiss="modal">Fermer</a>');
						$("#modal_msg").modal("show");
					}
				}
			);

		});
		
		
		// Validation dynamique de l'email par Ajax
		$("#profil_form #email").change(function() {

			document.body.style.cursor = 'wait';
			$.post("<?php echo site_url(); ?>/ajax/check_email",
			
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
						$("#modal_msg .modal-footer").html('<a id="modal_close" href="#" data-dismiss="modal">Fermer</a>');
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
						
						<!-- Mobile !-->
						<div class="form-group">
							<label for="mobile" class="control-label col-sm-2">Mobile</label>
							<div class="col-sm-10">
								<input id="mobile" class="form-control" type="text" name="mobile" />
							</div>
						</div>

						<!-- Instrument 1 !-->
						<div class="form-group">
							<label for="instru1" class="control-label col-sm-2" style="white-space: nowrap">Instrument 1</label>
							<div class="col-sm-10">
								<select id="instru1" class="form-control"name="instru1">
									<?php foreach ($cat_instru_list as $cat): ?>
											<optgroup label="<?php echo $cat['name']; ?>">
											<?php foreach ($cat['list'] as $instru_id): ?>
												<option value="<?php echo $instru_id; ?>"><?php echo ucfirst($instru_list[$instru_id-1]['name']); ?></option>
											<?php endforeach ?>
											</optgroup>
									<?php endforeach ?>
								</select>
							</div>
						</div>
						
						<!-- Instrument 2 !-->
						<div id="divInstru2" class="form-group" style="display:none">
							<label for="instru2" class="control-label col-sm-2" style="white-space: nowrap">Instrument 2</label>
							<div class="col-sm-10">
								<select id="instru2" class="form-control"name="instru2">
									<?php foreach ($cat_instru_list as $cat): ?>
											<optgroup label="<?php echo $cat['name']; ?>">
											<?php foreach ($cat['list'] as $instru_id): ?>
												<option value="<?php echo $instru_id; ?>"><?php echo ucfirst($instru_list[$instru_id-1]['name']); ?></option>
											<?php endforeach ?>
											</optgroup>
									<?php endforeach ?>
								</select>
							</div>
						</div>
						
						<!-- Bénévole !-->
						<div class="form-group">
							<label for="benevole" class="control-label col-sm-2">Bénévole</label>
							<div class="checkbox">
								<label>
									<input id="benevole" type="checkbox">
									<span class="cr"><i class="cr-icon glyphicon glyphicon-ok"></i></span>
								</label>
							</div>
						</div>

						
						<!-- Password !-->
						<div class="form-group required">
							<label for="pass" class="control-label col-sm-2">Mot de passe</label>
							<div class="col-sm-10">
								<input id="pass" class="form-control" required="true" type="password" name="pass" />
							</div>
						</div>
						
						
						<!-- Envoyer !-->
						<input id="update" class="btn btn-default pull-right" type="submit" value="S'inscrire"/>
						
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



<?php else: ?>

	<div id="members_create" class="content">

		<div class="block_left">
			<h3 class="block_title">Création de profil</h3>
			
			<div class="form_block">
				
				<?php echo form_open('members/create') ?>

				<label for="pseudo">Pseudo</label><span class="required"> *</span>
				<input class="right" type="text" id="pseudo" size="32" name="pseudo" value="<?php echo set_value('pseudo');?>" />
				<?php echo form_error('pseudo'); ?>
				<br>

				<label for="email">Email</label><span class="required"> *</span>
				<input class="right" type="text" id="email" size="32" name="email" value="<?php echo set_value('email');?>" />
				<?php echo form_error('email'); ?>
				<br>
				
				<label for="nom">Nom</label>
				<input class="right" type="text" size="32" id="nom" name="nom" value="<?php echo set_value('nom'); ?>" />
				<?php echo form_error('nom'); ?>
				<br>
				
				<label for="prenom">Prénom</label>
				<input class="right" type="text" size="32" id="prenom" name="prenom" value="<?php echo set_value('nom'); ?>" />
				<?php echo form_error('prenom'); ?>
				<br>
			
				<label for="mobile">Mobile</label>
				<input class="right" type="text" id="mobile"size="32" name="mobile" value="<?php echo set_value('mobile');?>" />
				<?php echo form_error('mobile'); ?>
				<br>
				
				<label for="instru1">Instrument 1</label>
				<select class="right" id="instru1" name="instru1">
					<?php foreach ($cat_instru_list as $cat): ?>
							<optgroup label="<?php echo $cat['name']; ?>">
							<?php foreach ($cat['list'] as $instru_id): ?>
								<option value="<?php echo $instru_id; ?>" <?php echo set_select('instru1',$instru_id); ?> ><?php echo ucfirst($instru_list[$instru_id-1]['name']); ?></option>
							<?php endforeach ?>
							</optgroup>
					<?php endforeach ?>
				</select>
				<?php echo form_error('instru1'); ?>
				<br>		
				
				<label for="instru2">Instrument 2</label>
				<select class="right" id="instru2" name="instru2">
					<?php foreach ($cat_instru_list as $cat): ?>
							<optgroup label="<?php echo $cat['name']; ?>">
							<?php foreach ($cat['list'] as $instru_id): ?>
								<option value="<?php echo $instru_id; ?>" <?php echo set_select('instru2',$instru_id); ?> ><?php echo ucfirst($instru_list[$instru_id-1]['name']); ?></option>
							<?php endforeach ?>
							</optgroup>
					<?php endforeach ?>
				</select>
				<?php echo form_error('instru2'); ?>
				<br>

				<label for="benevole">Bénévole ? <small class="soften">(instrument non obligatoire)</small></label>
				<input class="right" type="checkbox" name="benevole" value="set_benevole" <?php echo set_checkbox('benevole', 'set_benevole'); ?> />
				<?php echo form_error('benevole'); ?>
				<br>
				
				<br>
				<label for="pass">Mot de passe</label><span class="required"> *</span>
				<input class="right" type="password" id="pass" size="32" name="pass" value="<?php echo set_value('pass');?>" autocomplete="off" />
				<?php echo form_error('pass'); ?>
				<br>
				
				<input class="right button" type="submit" name="submit" value="Envoyer" />
				<br>
				
				<?php echo form_close() ?>
		
			</div>	
		</div>
		
		<div class="block_info" style="float:right">
			<p>Une fois votre profil créé, vous pourrez vous inscrire aux scènes ouvertes organisées par le Grenoble Reggae Orchestra et accéder à toutes les infos et ressources d'une jam, contacter les autres participants, etc...
			</p>
		</div>
<?php endif; ?>