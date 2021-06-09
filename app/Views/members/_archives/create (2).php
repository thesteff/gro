<?php
	// On récupère les variables de sessions
	$session = \Config\Services::session();
?>

<!-- bootstrap datepicker !-->
<script type="text/javascript" src="<?php echo base_url();?>/ressources/bootstrap-datepicker-1.6.4/js/bootstrap-datepicker.js"></script>
<script type="text/javascript" src="<?php echo base_url();?>/ressources/bootstrap-datepicker-1.6.4/locales/bootstrap-datepicker.fr.min.js"></script>
<link rel="stylesheet" href="<?php echo base_url();?>/ressources/bootstrap-datepicker-1.6.4/css/bootstrap-datepicker3.css" />


<!-- bootstrapValidator !-->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-validator/0.5.3/js/bootstrapValidator.js"></script>


<!-- Bootstrap-select !-->
<link rel="stylesheet" href="<?php echo base_url();?>/ressources/bootstrap-select/bootstrap-select.min.css" />
<script type="text/javascript" src="<?php echo base_url();?>/ressources/bootstrap-select/bootstrap-select.min.js"></script>


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
		// On affiche la liste d'instruments joués par le membre
		//show_instruList();

		
		// On affiche la liste d'instrument quand la famille d'instrument a été select
		//$("#instruments select[id$='Family']").change(function() {
		/*$("select[id$='Family']").change(function() {
			
			// On récupère le mode c'est à dire "new" ou "update" pour factoriser la fonction
			$div = $(this).parents("[id$='InstruDiv']");
			$divId = $div.attr("id");
			$mode = $divId.substr(0,$divId.indexOf('InstruDiv'));
			
			// On masque la liste des instruments
			$("#"+$mode+"ListInstru").fadeOut("fast");
			
			// On change le curseur
			document.body.style.cursor = 'wait';

			
			// Requète ajax au serveur
			$.post("<?php echo site_url('ajax_instruments/get_family_instru_list'); ?>",
			
				{
					'familyId' : $("#"+$mode+"Family").val()
				},
		
				function (return_data) {
									
					$obj = JSON.parse(return_data);
					
					// On change le curseur
					document.body.style.cursor = 'default';
					
					if ($obj['state'] == 1) {
						
						// On vide la liste
						$("#"+$mode+"Instru").empty();
						
						// On remplit la liste
						$optionBlock = "";
						for (i = 0; i < $obj['data'].length; i++) {
							$optionBlock += "<option value='"+$obj['data'][i]["instruId"]+"'>"+$obj['data'][i]["name"]+"</option>";
						}
						$("#"+$mode+"Instru").append($optionBlock);
						$("#"+$mode+"Instru").selectpicker('refresh');				
					}
					
					
					// Cas du update qui n'utilise pas title sur le premier affichage
					if ($mode == "update") {
						$("#updateInstru").attr("title","Instrument");
					}
					
					// On disable le boutton d'action (si on charge la liste d'instrument l'item select est "Instrument" donc un item inexistant)
					$div.find("#"+$mode+"InstruBtn").addClass("disabled");
					
					
					// On disabled les instruments déjà joués					
					$("#listInstruDiv .instruItem").each(function() {
						$div.find("#"+$mode+"Instru option[value="+$(this).attr("instruId")+"]").prop("disabled", true);
						$("#"+$mode+"Instru").selectpicker('refresh');
					});

					
					// On affiche la liste des instruments
					$("#"+$mode+"ListInstru").fadeIn("fast");
					
				});
			
		});*/
		
		
		// On disabled le boutton d'update si pas d'instru select
		/*$("select[id$='Instru']").change(function() {

			// On récupère la div globale d'instru
			$div = $(this).parents("[id$='InstruDiv']");
			// On récupère le mode c'est à dire "new" ou "update" pour factoriser la fonction
			$divId = $div.attr("id");
			$mode = $divId.substr(0,$divId.indexOf('InstruDiv'));
			
			$init = $div.attr("initVal");
			$val = $div.find("#"+$mode+"Instru").val();
			
			// On disabled le Btn si besoin
			if ($init == $val) {
				$div.find("#"+$mode+"InstruBtn").addClass("disabled");
			}
			else $div.find("#"+$mode+"InstruBtn").removeClass("disabled");
			
		});*/
		
		
		// ****** INSTRUMENTS MODALS ********
		$("[id$='InstruModal'").on("show.bs.modal", function(e) {
			var link = $(e.relatedTarget);
			$(this).find(".modal-content").load(link.attr("href"));
		});
		
		
	});


	/******************************* INSTRUMENT ***********************/
	////////////////////////////////////////////////////////////////////

	
	/*********** ADD INSTRUMENT ***********/
	/*function show_newInstruDiv() {
		
		// On sort d'un update si besoin au cas où
		escape_update();
		
		$("#add_instru").fadeOut("fast", function() {
			$("#newInstruDiv").fadeIn("fast");
		});
    }
	
	
	function escape_new()  {
		// On masque l'update Div
		$("#newInstruDiv").css("display","none");
		// On réaffiche la btn d'ajout
		$("#add_instru").fadeIn("fast");
		
		$("#newListInstru").css("display","none");
		$("#newInstruBtn").addClass("disabled");
		$("#newFamily").selectpicker('val', 'Famille d\'instrument');
		$("#newInstru").selectpicker('val', 'Instrument');
	}	
	
	
	function add_instrument()  {
		
		// On change le curseur
		document.body.style.cursor = 'wait';

			
		//On ajoute l'instrument	
		$("#newInstruDiv").fadeOut("fast", function() {
			
			$pos = $("#listInstruDiv .instruDiv").length + 1;
			$newInstruId = $("#newInstru").val();
			$newInstruName = $("#newInstru option:selected").text();
			
			$div = "<div class='form-group instruDiv'>";
				$div += "<label class='control-label col-sm-2' style='white-space: nowrap'>Instrument "+$pos+"</label>";
				$div += "<div class='btn-group col-sm-5'>";
					<!-- Label !-->
					$div += "<div class='btn btn-static instruItem coloredItem' instruId='"+$newInstruId+"' instruName='"+$newInstruName+"'>&nbsp;&nbsp;&nbsp;&nbsp;"+$newInstruName+"&nbsp;&nbsp;&nbsp;&nbsp;</div>";
					<!-- Modifier -->
					$div += '<button class="btn btn-default update_btn" href="" title="Modifier instrument"><i class="glyphicon glyphicon-pencil"></i></button>';
					<!-- Supprimer -->
					$div += '<button class="btn btn-default delete_btn" title="Supprimer instrument"><i class="glyphicon glyphicon-trash"></i></button>';
				$div += "</div>";
			$div += "</div>";
			$("#listInstruDiv").append($div);
			$("#listInstruDiv").css("display","block");
			
			// ADMIN IHM
			// Delete instrument
			//$("#listInstruDiv .delete_btn").on("click", function(event) {
			$("#listInstruDiv .instruDiv:nth-child("+$pos+") .delete_btn").on("click", function(event) {
				event.preventDefault();
				// On supprime la div d'instrument
				$(this).parents(".instruDiv").remove();
				// On actualise les num d'instrument
				$("#listInstruDiv .instruDiv label").each(function(index) {
					$(this).empty();
					$(this).append("Instrument "+(index+1));
				});
			});
			// Update instrument
			$("#listInstruDiv .instruDiv:nth-child("+$pos+") .update_btn").on("click", function(event) {
				event.preventDefault();
				escape_new();
				$instruDiv = $(this).parent().parent();
				pos = $("#listInstruDiv").children().index($instruDiv);
				show_updateInstruDiv(pos);
			});
			
			// On rétablit le bouton d'ajout + ...
			$("#add_instru").fadeIn("fast");
			$("#newListInstru").css("display","none");
			$("#newInstruBtn").addClass("disabled");
			$("#newFamily").selectpicker('val', 'Famille d\'instrument');
			$("#newInstru").selectpicker('val', 'Instrument');
		});
		
		
		// On change le curseur
		document.body.style.cursor = 'default';
    }*/
	
	
	
	/*********** UPDATE INSTRUMENT ***********/
	/*function show_updateInstruDiv($pos) {
		
		// On change le curseur
		document.body.style.cursor = 'wait';
		
		// On récupère la div instrument
		$divInstru = $("#listInstruDiv").children(":nth-child("+($pos+1)+")");
		$instruId = $divInstru.find(".instruItem").attr("instruId");;
		
		// Si un instrument était masqué, on l'affiche de nouveau
		$tempUpdateDiv = $("#updateInstruDiv").detach();
		$("#listInstruDiv").find(".btn-group:hidden").fadeIn("fast");
		
		
		// Requète ajax au serveur
		$.post("<?php echo site_url(); ?>/ajax_instruments/get_family_instru_list_by_instrument",
		
			{
				'instruId' : $instruId
			},
	
			function (return_data) {
				
				$obj = JSON.parse(return_data);
				
				// On change le curseur
				document.body.style.cursor = 'default';
				
				if ($obj['state'] == 1) {
					
					// On masque l'instrument à updater
					$divInstru.find(".btn-group").fadeOut("fast", function() {
						
						// On init le select de la famille instru
						$tempUpdateDiv.find("#updateFamily").val($obj['data']['instrument']['famille_instruId']);
						$tempUpdateDiv.find("#updateFamily").selectpicker('refresh');
						
						// On mémorise l'init val
						$tempUpdateDiv.attr("initVal",$instruId);
						
						// On vide la liste d'instruments
						$updateInstru = $tempUpdateDiv.find("#updateInstru").empty();
						
						
						$updateInstru.removeAttr("title");
						$updateInstru.selectpicker('refresh');
						
						// On remplit la liste
						$div = "";
						for (i = 0; i < $obj['data']['instruList'].length; i++) {
							$div += "<option value='"+$obj['data']['instruList'][i]["instruId"]+"'>"+$obj['data']['instruList'][i]["name"]+"</option>";
						}
						$updateInstru.append($div);
						
						// On disabled les instruments déjà joués sauf celui de l'update
						$("#listInstruDiv .instruItem").each(function() {
							if ($(this).attr("instruId") != $instruId) {
								$updateInstru.find("option[value="+$(this).attr("instruId")+"]").prop("disabled", true);
								$updateInstru.selectpicker('refresh');
							}
						});
						
						// On init le select de l'instru
						$updateInstru.val($obj['data']['instrument']['id']);
						$updateInstru.selectpicker('refresh');
						
						// On disabled le updateBtn
						$updateInstru.parents("#updateInstruDiv").find("#updateInstruBtn").addClass("disabled");

						// On copy la updateDiv
						$tempUpdateDiv.appendTo($divInstru);
						$("#updateInstruDiv").fadeIn("fast");
						
					});
				}
				else console.log("error");
			}
		);
    }*/
	
	
	/*function escape_update()  {
		// On masque l'update Div
		$("#updateInstruDiv").css("display","none");
		// On réaffiche le div de l'instrument
		$("#updateInstruDiv").parents(".instruDiv").children(".btn-group").fadeIn("fast");
		// On replace l'update div à un niveau où elle ne sera pas delete
		$("#updateInstruDiv").detach().appendTo($("#instruTitle"));
	}		
		
	
	function update_instrument()  {
		
		// On récupère l'instruId à ajouter
		$instruId = $("#updateInstru").val();
		
		// On récupère l'instruId initiale (pour trouver l'item exact et conserver l'id et donc l'ordre des instruments)
		$oldInstruId = $("#updateInstruDiv").attr("initVal");
	
		// On masque la div d'update
		$("#updateInstruDiv").css("display","none");
		
		// On replace l'update div à un niveau où elle ne sera pas delete
		$("#updateInstruDiv").detach().appendTo($("#instruTitle"));
		
		// On actualise l'affichage
		$hidden = $("#listInstruDiv").find(".btn-group:hidden");
		$hidden.find(".instruItem").attr("instruId",$instruId);
		$hidden.find(".instruItem").attr("instruName",$("#updateInstruDiv #updateInstru option:selected").text());
		$hidden.find(".instruItem").html("&nbsp;&nbsp;&nbsp;&nbsp;"+$("#updateInstruDiv #updateInstru option:selected").text()+"&nbsp;&nbsp;&nbsp;&nbsp;");
		$hidden.fadeIn("fast");
		
    }*/



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
						<button class="btn btn-default col-sm-offset-2 col-sm-10" href="<?php echo site_url("members/add_instrument/-1") ?>" data-remote="false" data-toggle="modal" data-target="#addInstruModal">Ajouter un instrument</button>
						
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
