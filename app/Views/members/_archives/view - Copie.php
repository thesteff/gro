
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
				// le widget du datepicker assure la saisie d'une date   :-)
				/*naissance: {
					validators: {
						date: {
							format: 'DD/MM/YYYY',
							message: 'La date doit être valide'
						}
					}
				},*/
				mobile: {
					validators: {
						phone: {
							country: 'FR',
							message: 'Le numéro doit être valide'
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

			console.log($('#profil_form #genre').val());
			
            // Requète ajax au serveur
			document.body.style.cursor = 'wait';
			$.post("<?php echo site_url(); ?>/ajax_members/update_member",
			
				// On récupère les données nécessaires
				{
					'id':'<?php echo $member_item->id ?>',
					'email':$('#profil_form #email').val(),
					'nom':$('#profil_form #nom').val(),
					'prenom':$('#profil_form #prenom').val(),
					'genre':$('#profil_form #genre').val(),
					'naissance':$('#profil_form #naissance').val(),
					'mobile':$('#profil_form #mobile').val().replace(/\s/g, ''),
					'allowMail':$('#profil_form #allowMail').is(':checked') ? "1" : "0"
				},
				
				// On traite la réponse du serveur			
				function (return_data) {
					
					$obj = JSON.parse(return_data);
					// On change le curseur
					document.body.style.cursor = 'default';

					// Profil mis à jour
					if ($obj['state']) {
						// Success
						$("#modal_msg .modal-dialog").removeClass("error");
						$("#modal_msg .modal-dialog").addClass("success");
						$("#modal_msg .modal-header").html("Profil mis à jour !");
						$("#modal_msg .modal-body").html($obj['data']);
						$("#modal_msg .modal-footer").html('<button class="btn btn-default" id="modal_close" href="#" data-dismiss="modal">Fermer</a>');
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
		
		
		
		$('#pass_form').bootstrapValidator({
        // To use feedback icons, ensure that you use Bootstrap v3.1.0 or later
        feedbackIcons: {
            valid: 'glyphicon glyphicon-ok',
            invalid: 'glyphicon glyphicon-remove',
            validating: 'glyphicon glyphicon-refresh'
        },
        fields: {
            pass: {
                validators: {
                    notEmpty: {
                        message: 'Vous devez saisir votre mot de passe actuel'
                    },
					stringLength: {
                        min: 6,
                        message: 'Votre mot de passe contient au moins 6 caractères'
                    }
				}
            },
            pass2: {
                validators: {
                    notEmpty: {
                        message: 'Vous devez saisir votre nouveau mot de passe'
                    },
                    stringLength: {
                        min: 6,
                        message: 'Votre nouveau mot de passe doit contenir au moins 6 caractères'
                    }
                }
            }
         }
        })
        .on('success.form.bv', function(e) {
            //$('#success_message').slideDown({ opacity: "show" }, "slow") // Do something ...
            $('#pass_form').data('bootstrapValidator').resetForm();

            // Prevent form submission
            e.preventDefault();
            // Get the form instance
            var $form = $(e.target);
            // Get the BootstrapValidator instance
            var bv = $form.data('bootstrapValidator');

			
            // Requète ajax au serveur
			document.body.style.cursor = 'wait';
			$.post("<?php echo site_url(); ?>/ajax_members/update_pass_member",
			
				// On récupère les données nécessaires
				{
					//'memberLogin':$("#memberLogin"),
					'memberId':'<?php echo $member_item->id ?>',
					'pass':$('#pass_form #pass').val(),
					'pass2':$('#pass_form #pass2').val()
				},
				
				// On traite la réponse du serveur			
				function (return_data) {
					
					$obj = JSON.parse(return_data);
					// On change le curseur
					document.body.style.cursor = 'default';

					// Profil mis à jour
					if ($obj['state']) {
						// Success
						$("#modal_msg .modal-dialog").removeClass("error");
						$("#modal_msg .modal-dialog").addClass("success");
						$("#modal_msg .modal-header").html("Mot de passe mis à jour");
						$("#modal_msg .modal-body").html($obj['data']);
						$("#modal_msg .modal-footer").html('<a id="modal_close" href="#" data-dismiss="modal">Fermer</a>');
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
		
		
		// Ono initialise le selectpicker du genre
		$('#profil_form #genre').selectpicker('val', '<?php echo $member_item->genre ?>'),

		
		// Validation dynamique de l'email par Ajax
		$("#profil_form #email").change(function() {
		
			document.body.style.cursor = 'wait';
			$.post("<?php echo base_url(); ?>/ajax_members/check_email",
			
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
		
	
		// Dialog box pour la validation dynamique
		$('#modal_msg').on('hidden.bs.modal', function () {
			$target = $("#modal_msg").attr("target");
			if ($target) {
				$('#profil_form').data('bootstrapValidator').resetForm();
				$('#profil_form #'+$target).val('<?php echo $member_item->email; ?>'); 	// à priori on check que l'email dynamiquement
				$('#profil_form #'+$target).focus();
				$("#modal_msg").attr("target","");
			}
		});
		

		
		/**************************** Instrument IHM ***************************/
		// On affiche la liste d'instruments joués par le membre
		show_instruList();
		
		// On affiche la liste d'instrument quand la famille d'instrument a été select
		$("#instruments select[id$='Family']").change(function() {
			
			// On récupère le mode c'est à dire "new" ou "update" pour factoriser la fonction
			$div = $(this).parents("[id$='InstruDiv']");
			$divId = $div.attr("id");
			$mode = $divId.substr(0,$divId.indexOf('InstruDiv'));
			
			// On masque la liste des instruments
			$("#"+$mode+"ListInstru").fadeOut("fast");
			
			// On change le curseur
			document.body.style.cursor = 'wait';

			// Requète ajax au serveur
			$.post("<?php echo base_url(); ?>/ajax_instruments/get_family_instru_list",
			
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
			
		});
		
		
		// On disabled le boutton d'update si pas d'instru select
		$("#instruments select[id$='Instru']").change(function() {

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
			
		});
		

	});
	
	
	/************************ Validation Mail ***********************/
	//////////////////////////////////////////////////////////////////
	
	function sendValidationMail() {
		// On change le curseur
		document.body.style.cursor = 'wait';

		// Requète ajax au serveur
		$.post("<?php echo base_url(); ?>/ajax_members/sendValidationMail",
		
			{
				'memberId' : <?php echo $member_item->id; ?>
			},
	
			function (return_data) {
				
				console.log(return_data);
				
				$obj = JSON.parse(return_data);
				
				// On change le curseur
				document.body.style.cursor = 'default';
				
				if ($obj['state'] == 1) {
					$("#modal_msg .modal-dialog").addClass("success");
					$("#modal_msg .modal-dialog").removeClass("error");
					$("#modal_msg .modal-header").html("Email envoyé");
					$("#modal_msg .modal-body").html($obj['data']);
					$("#modal_msg .modal-footer").html('<button class="btn btn-default" id="modal_close" href="#" data-dismiss="modal">Fermer</button>');
					$("#modal_msg").modal("show");		
				}
				
				// Erreur
				else {
					$("#modal_msg .modal-dialog").addClass("error");
					$("#modal_msg .modal-dialog").removeClass("success");
					$("#modal_msg .modal-header").html("Erreur");
					$("#modal_msg .modal-body").html($obj['data']);
					$("#modal_msg .modal-footer").html('<button class="btn btn-default" id="modal_close" href="#" data-dismiss="modal">Fermer</button>');
					$("#modal_msg").modal("show");
				}
				
				
		});
	}
	
	
	/************************ INSTRUMENT ***********************/
	//////////////////////////////////////////////////////////////
	
	// On affiche la liste d'instruments joués par le membre
	function show_instruList() {
		
		// On change le curseur
		document.body.style.cursor = 'wait';

		// On récupère le memberId
		$memberId = <?php echo $member_item->id ?>;

		// Requète ajax au serveur
		$.post("<?php echo base_url(); ?>/ajax_instruments/get_member_instruments",
		
			{
				'memberId' : $memberId
			},
	
			function (return_data) {
							
				$obj = JSON.parse(return_data);
				
				// On change le curseur
				document.body.style.cursor = 'default';
				
				if ($obj['state'] == 1) {
					
					// On vide la liste
					$("#listInstruDiv").empty();
					
					// On remplit la liste
					$div = "";
					for (i = 0; i < $obj['data'].length; i++) {
						$div += "<div class='form-group instruDiv'>";
							$div += "<label class='control-label col-sm-2' style='white-space: nowrap'>Instrument "+(i+1)+"</label>";
							$div += "<div class='btn-group col-sm-5'>";
								<!-- Label !-->
								$div += "<div class='btn btn-static instruItem coloredItem' instruId='"+$obj['data'][i]["instruId"]+"' instruName='"+$obj['data'][i]["instruName"]+"'>&nbsp;&nbsp;&nbsp;&nbsp;"+$obj['data'][i]["instruName"]+"&nbsp;&nbsp;&nbsp;&nbsp;</div>";
								<!-- Modifier -->
								$div += '<button class="btn btn-default update_btn" href="" title="Modifier instrument"><i class="glyphicon glyphicon-pencil"></i></button>';
								<!-- Supprimer -->
								$div += '<button class="btn btn-default delete_btn" title="Supprimer instrument"><i class="glyphicon glyphicon-trash"></i></button>';
							$div += "</div>";
						$div += "</div>";
						//$div += "<option value='"+$obj['data'][i]["instruId"]+"'>"+$obj['data'][i]["name"]+"</option>";
					}
					$("#listInstruDiv").append($div);
					
					// ADMIN IHM
					<?php if (true): ?>
						// On fixe le comportement des bouttons d'admin de delete
						$('#listInstruDiv .delete_btn').each(function(index) {
							$(this).on("click", function() {
								$instruId = $(this).siblings(".instruItem").attr("instruId");
								$instruName = $(this).siblings(".instruItem").attr("instruName");
								popup_delete_instru($instruId, $instruName);
							});
						});
						// On fixe le comportement des bouttons d'admin de update
						$('#listInstruDiv .update_btn').each(function(index) {
							$(this).on("click", function() {
								escape_new();
								$instruDiv = $(this).parent().parent();
								$pos = $("#listInstruDiv").children().index($instruDiv);
								show_updateInstruDiv($pos);
							});
						});
					<?php endif; ?>
					
					// On affiche la liste
					$("#listInstruDiv").fadeIn("fast");
				}
				else console.log("error");
			}
		);

		
	}

	
	/*********** ADD INSTRUMENT ***********/
	function show_newInstruDiv() {
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
		
		// On récupère l'instruId à ajouter
		$instruId = $("#newInstru").val();
		
		// On récupère le memberId
		$memberId = <?php echo $member_item->id ?>;

		
		// Requète ajax au serveur
		$.post("<?php echo site_url(); ?>/ajax_instruments/add_member_instrument",
		
			{
				'instruId' : $instruId,
				'memberId' : $memberId
			},
	
			function (return_data) {
							
				$obj = JSON.parse(return_data);
				
				// On change le curseur
				document.body.style.cursor = 'default';
				
				if ($obj['state'] == 1) {
					
					// L'instrument à été ajouté dans la bd => on actualise l'affichage
					show_instruList();
					$("#newInstruDiv").fadeOut("fast", function() {
						$("#add_instru").fadeIn("fast");
						$("#newListInstru").css("display","none");
						$("#newInstruBtn").addClass("disabled");
						$("#newFamily").selectpicker('val', 'Famille d\'instrument');
						$("#newInstru").selectpicker('val', 'Instrument');
					});
				}
				else console.log("error : " + $obj['data']);
			}
		);
    }
	
	
	
	/*********** UPDATE INSTRUMENT ***********/
	function show_updateInstruDiv($pos) {
		
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
    }
	
	
	function escape_update()  {
		// On masque l'update Div
		$("#updateInstruDiv").css("display","none");
		// On réaffiche le div de l'instrument
		$("#updateInstruDiv").parents(".instruDiv").children(".btn-group").fadeIn("fast");
		// On replace l'update div à un niveau où elle ne sera pas delete
		$("#updateInstruDiv").detach().appendTo($("#instruments"));
	}		
		
	
	function update_instrument()  {

		// On change le curseur
		document.body.style.cursor = 'wait';
		
		// On récupère l'instruId à ajouter
		$instruId = $("#updateInstru").val();
		
		// On récupère l'instruId initiale (pour trouver l'item exact et conserver l'id et donc l'ordre des instruments)
		$oldInstruId = $("#updateInstruDiv").attr("initVal");
		
		// On récupère le memberId
		$memberId = <?php echo $member_item->id ?>;

		// Requète ajax au serveur
		$.post("<?php echo site_url(); ?>/ajax_instruments/update_member_instrument",
		
			{
				'instruId' : $instruId,
				'oldInstruId' : $oldInstruId,
				'memberId' : $memberId
			},
	
			function (return_data) {
								
				$obj = JSON.parse(return_data);
				
				// On change le curseur
				document.body.style.cursor = 'default';
				
				if ($obj['state'] == 1) {
					
					// On masque la div d'update
					$("#updateInstruDiv").css("display","none");
					
					// On replace l'update div à un niveau où elle ne sera pas delete
					$("#updateInstruDiv").detach().appendTo($("#instruments"));
					
					// L'instrument à été updaté dans la bd => on actualise l'affichage
					$hidden = $("#listInstruDiv").find(".btn-group:hidden");
					$hidden.find(".instruItem").attr("instruId",$obj['data']['id']);
					$hidden.find(".instruItem").attr("instruName",$obj['data']['name']);
					$hidden.find(".instruItem").html("&nbsp;&nbsp;&nbsp;&nbsp;"+$obj['data']['name']+"&nbsp;&nbsp;&nbsp;&nbsp;");
					$hidden.fadeIn("fast");
				}
				else console.log("error");
			}
		);
    }
	
	
	
	// ******** DELETE INSTRUMENT *********/
	function popup_delete_instru($instruId, $instruName) {
		$text = "Etes-vous sûr de vouloir supprimer <b>"+$instruName+"</b> de votre liste d'instrument ?";
		$confirm = "<div class='modal-footer'>";
			$confirm += "<button type='button' class='btn btn-default' data-dismiss='modal'>Annuler</button>";
			$confirm += "<button type='submit' class='btn btn-primary' onclick='javascript:delete_instru(\""+$instruId+"\")'>Supprimer</button>";
		$confirm += "</div>";
		
		$("#modal_msg .modal-dialog").removeClass("error success");
		$("#modal_msg .modal-dialog").addClass("default");
		$("#modal_msg .modal-dialog").addClass("backdrop","static");
		$("#modal_msg .modal-header").html("Supprimer l'instrument");
		$("#modal_msg .modal-body").html($text);
		$("#modal_msg .modal-footer").html($confirm);
		$("#modal_msg").modal('show');
	}
	
	
	function delete_instru($instruId) {
	
		// On change le curseur
		document.body.style.cursor = 'progress';
		
		// On récupère l'id du membre
		$memberId = <?php echo $member_item->id ?>;

		$.post("<?php echo site_url(); ?>/ajax_instruments/delete_member_instrument",
		
			{
				'instruId' : $instruId,
				'memberId' : $memberId
			},
	
			function (return_data) {
				
				$obj = JSON.parse(return_data);
				
				// On change le curseur
				document.body.style.cursor = 'default';
				
				if ($obj['state'] == 1) {
					
					// On ferme la modal
					$("#modal_msg").modal('toggle');
					
					// On sort d'un eventuel update
					escape_update();
					
					// L'instrument à été supprimé de la bd => on actualise l'affichage
					show_instruList();
					
					// On sort d'un éventuel add new
					escape_new();
					
				}
				else console.log("error");
			}
		);
	}
	
	
</script>



<div class="row">

	<!-- Block principal !-->
	<div class="col-md-9 col-lg-9 panel panel-default">
		
	
			<!-- Pseudo !-->
			<div class="col-sm-10 col-sm-offset-2" style="padding-top:10px; padding-bottom:10px">
				<h3><strong><?php echo $member_item->pseudo; ?></strong></h3>
				<!--<input id="pseudo" class="form-control" disabled="true" required="true" type="text" name="pseudo" value="<?php echo $member_item->pseudo; ?>" />!-->
			</div>
	
			<!-- Formulaire !-->
			<div class="container-fluid">
				<form id="profil_form" class="form-horizontal">

					<!-- Email !-->
					<div class="form-group required">
						<label for="email" class="control-label col-sm-2">Email</label>
						<div class="col-sm-10">
							<input id="email" class="form-control" required="true" type="email" name="email" value="<?php echo $member_item->email; ?>" />
						</div>
					</div>
					
					<!-- Nom !-->
					<div class="form-group">
						<label for="nom" class="control-label col-sm-2">Nom</label>
						<div class="col-sm-10">
							<input id="nom" class="form-control" type="text" name="nom" value="<?php echo $member_item->nom; ?>" />
						</div>
					</div>
					
					<!-- Prénom !-->
					<div class="form-group">
						<label for="prenom" class="control-label col-sm-2">Prénom</label>
						<div class="col-sm-10">
							<input id="prenom" class="form-control" type="text" name="prenom" value="<?php echo $member_item->prenom ?>" />
						</div>
					</div>
					
					<!-- Date de naissance !-->
					<div class="form-group">
						<label for="naissance" class="control-label col-sm-2">Date de naissance</label>
						<div class="col-sm-10">
							<input id="naissance" class="form-control" type="text" name="naissance" value="<?php echo empty($member_item->naissance) ? "" : $member_item->naissance ?>" />
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
							<input id="mobile" class="form-control" type="text" name="mobile" value="<?php echo substr($member_item->mobile,0,2).' '.substr($member_item->mobile,2,2).' '.substr($member_item->mobile,4,2).' '.substr($member_item->mobile,6,2).' '.substr($member_item->mobile,8,2) ?>" />
						</div>
					</div>


					<!-- Allow Mail !-->
					<div class="form-group">
						<label for="allowMail" class="control-label col-sm-2">Autoriser les emails</label>
						<div class="checkbox col-sm-10">
							<label>
								<input id="allowMail" type="checkbox" value="" <?php if ($member_item->allowMail == "1") echo "checked"; ?>>
								<span class="cr"><i class="cr-icon glyphicon glyphicon-ok"></i></span>
							</label>
						</div>
					</div>
	
					<!-- Envoyer !-->
					<input id="update" class="btn btn-primary pull-right" type="submit" value="Modifier"/>
					
					<!--Valid Mail !-->
					<?php if ($member_item->validMail == 0) : ?>
						<div class="form-group">
							<div class="col-sm-offset-2 col-sm-10">
								<div class="alert alert-danger small">
									<p><span class="alert-danger glyphicon glyphicon-warning-sign"></span>&nbsp;<strong>Profil non validé !</strong></p>
									<p>Votre adresse email n'a pas encore été validée. Pour activer votre compte et profiter des fonctionnalités du site, merci de cliquer sur le lien présent dans le mail de validation.</p>
									<br>
									<div class="btn btn-xs btn-danger pull-right small" onclick="javascript:sendValidationMail()">Envoyer mail de validation</div>
								</div>
							</div>
						</div>
					<?php endif ?>
					
				</form>
			</div>
			
			
			<!-- INSTRUMENTS !-->
			<h4 class="col-sm-offset-2 col-sm-10" style="padding-bottom:10px"><strong>Instrument</strong></h4>
			
			<div class="container-fluid" style="padding-bottom:15px">
				<form id="instruments" class="form-horizontal" action="javascript:void(0)">
		
					
					<!-- Section de la liste d'instruments joués par le membre !-->
					<div id="listInstruDiv" style="display:none; margin-right: 15px">
					</div>
		
		
					<!-- UPDATE INSTRUMENT !-->
					<!-- Section de modification d'instrument !-->
					<div id="updateInstruDiv" style="display:none">

						<!-- Famille instru !-->
						<div class="col-sm-4">
							<select id="updateFamily" class="selectpicker show-tick" name="updateFamily" title="Famille d'instrument">
								<?php foreach ($famille_instru_list as $famille): ?>
									<option value="<?php echo $famille['id']; ?>"><?php echo $famille['label']; ?></option>
								<?php endforeach ?>
							</select>
						</div>
						
						<!-- Liste instru !-->
						<div id="updateListInstru" class="col-sm-4">
							<select id="updateInstru" class="selectpicker show-tick" name="updateInstru" title="Instrument">
							</select>
						</div>
						
						<!-- Btn Modifier un instrument !-->
						<div class='btn-group col-sm-2 pull-right' style="display:flex">
							<button id="updateInstruBtn" class="btn btn-default" type="button" value="Modifier" onclick="javascript:update_instrument()" />Modifier</button>
							<button class="btn btn-default" type="button" value="Abort" onclick="javascript:escape_update()" /><i class="glyphicon glyphicon-remove"></i></button>
						</div>
					
					</div>
					
					
					<!-- ADD INSTRUMENT !-->
					<!-- Ajouter un instrument btn !-->
					<input id="add_instru" class="btn btn-default col-sm-offset-2 col-sm-10" type="button" value="Ajouter un instrument" onclick="javascript:show_newInstruDiv()" style="margin-bottom:15px"/>
					
					<!-- Section d'ajout d'instrument !-->
					<div id="newInstruDiv" class="form-group" style="display:none; margin-right: 0px">
					
						<!-- Famille instru !-->
						<div class="col-sm-offset-2 col-sm-4">
							<select id="newFamily" class="selectpicker show-tick" name="newFamily" title="Famille d'instrument">
								<?php foreach ($famille_instru_list as $famille): ?>
									<option value="<?php echo $famille['id']; ?>"><?php echo $famille['label']; ?></option>
								<?php endforeach ?>
							</select>
						</div>
						
						<!-- Liste instru !-->
						<div id="newListInstru" class="col-sm-4" style="display:none">
							<select id="newInstru" class="selectpicker show-tick" name="newInstru" title="Instrument">
							</select>
						</div>
						
						
						<!-- Btn Ajouter un instrument !-->
						<div class='btn-group col-sm-2 pull-right' style="display:flex">
							<button id="newInstruBtn" class="btn btn-default disabled" type="button" value="Ajouter" onclick="javascript:add_instrument()" />Ajouter</button>
							<button class="btn btn-default" type="button" value="Abort" onclick="javascript:escape_new()" /><i class="glyphicon glyphicon-remove"></i></button>
						</div>
						
		
					</div>
					

				</form>

			</div>			
			
			
			
			<!-- PASSWORD !-->
			<div class="container-fluid">
				<form id="pass_form" class="form-horizontal" action="javascript:update_password()">
		
					
					<h4 class="col-sm-offset-2 col-sm-10" style="padding-bottom:10px"><strong>Sécurité</strong></h4>
					
					<!-- Pass !-->
					<div class="form-group">
						<label for="pass" class="control-label col-sm-2">Mot de passe</label>
						<div class="col-sm-10">
							<input id="pass" class="form-control" type="password" name="pass" value="" autocomplete="off" placeholder="Mot de passe actuel" />
						</div>
					</div>
					
					<!-- New Pass !-->
					<div class="form-group">
						<label for="pass2" class="control-label col-sm-2">Nouveau mot de passe</label>
						<div class="col-sm-10">
							<input id="pass2" class="form-control" type="password" name="pass2" value="" autocomplete="off"  placeholder="Nouveau mot de passe" />
						</div>
					</div>
					
					
					<!-- Infos admin !-->
					<?php if ($member_item->admin == 1) : ?>
						<div class="col-sm-offset-2 col-sm-10 alert alert-success">
							<strong>Vous êtes administrateur du site</strong>
						</div>
					<?php endif; ?>
					
					<!-- Envoyer !-->
					<input id="update_pass" class="btn btn-primary pull-right" type="submit" value="Modifier"/>
					
					
				</form>

			</div>	
			
	</div>
		
	<!-- Block de droite !-->
	<div class="col-md-3 col-lg-3">		<!-- On sépare col et panel pour avoir un pad !-->
		<div class="panel panel-default">
			<div class="panel-body" style="padding-top:15px">	<!-- On rajoute le padding top !-->
				Attention lorsque vous modifiez votre profil, cela peut annuler vos données d'inscription à une Jam (avec un changement d'instrument par exemple).
			</div>
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