<!-- bootstrap datepicker !-->
<script type="text/javascript" src="<?php echo base_url();?>/ressources/bootstrap-datepicker-1.6.4/js/bootstrap-datepicker.js"></script>
<script type="text/javascript" src="<?php echo base_url();?>/ressources/bootstrap-datepicker-1.6.4/locales/bootstrap-datepicker.fr.min.js"></script>
<link rel="stylesheet" href="<?php echo base_url();?>/ressources/bootstrap-datepicker-1.6.4/css/bootstrap-datepicker3.css" />

<!-- autoresize texarea !-->
<script type="text/javascript" src="<?php echo base_url();?>/ressources/script/autosize.js"></script>

<!-- Editeur html -->
<script src="<?php echo base_url();?>/ressources/script/ckeditor/ckeditor.js"></script>

<!-- bootstrapValidator !-->
<script type="text/javascript" src="<?php echo base_url();?>/ressources/script/validator.js"></script>

<!-- flexdatalist pour les input de lieux !-->
<script type="text/javascript" src="<?php echo base_url();?>/ressources/script/jquery-flexdatalist-2.2.4/jquery.flexdatalist.min.js"></script>
<link href="<?php echo base_url();?>/ressources/script/jquery-flexdatalist-2.2.4/jquery.flexdatalist.min.css" rel="stylesheet" type="text/css" />



<script type="text/javascript">

	$(function() {
		
		// On récupère l'instrumentation
		get_instrumentation();
		
		// On récupère la/les playlist
		get_playlist();
		
		
		/******** Bootstrap validator ********/
		$('#jam_update_form form').validator();
		$('#jam_update_form form').validator().on('submit', function (e) {
			
			if (e.isDefaultPrevented()) {
				// handle the invalid form...
			}
			else {
				// On bloque le comportement par défault du submit
				e.preventDefault();
				// Pas de problem avec le validator
				update_jam();
			}
		})
		
		
		// On initialise les datepicker
		$('#jam_update_form #date_jam').datepicker({
			format: "dd/mm/yyyy",
			todayBtn: "linked",
			language: "fr",
			todayHighlight: true
		});
		$('#jam_update_form #stage_date_debut').datepicker({
			format: "dd/mm/yyyy",
			todayBtn: "linked",
			language: "fr",
			todayHighlight: true
		});
		$('#jam_update_form #stage_date_limit').datepicker({
			format: "dd/mm/yyyy",
			todayBtn: "linked",
			language: "fr",
			todayHighlight: true
		});


		// On initialise les textarea
		CKEDITOR.replace( 'jam_textarea', {
			customConfig: '/ressources/script/ckeditor/config_light2.js'
		});
		
		CKEDITOR.replace( 'stage_textarea', {
			customConfig: '/ressources/script/ckeditor/config_light2.js'
		});
		
		
		
		// On rempli les flexdatalist
		$('.flexdatalist').flexdatalist({
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
		$('.flexdatalist').on('change:flexdatalist', function(event, set, options) {
			
			$target = $(this).attr("name");
			
			// Requète ajax au serveur
			$.post("<?php echo site_url(); ?>/ajax/get_location",
			
				{
				'lieuId':$.isNumeric($("#jam_update_form #"+$target+"").val()) ? $("#jam_update_form #"+$target+"").val() : "-1"
				},
		
				function (msg) {
					//console.log("msg : "+msg);
				
					// Le lieu spécifié n'est pas présent dans la base
					if (msg == "lieu_not_found") {
						if (!$("#jam_update_form #"+$target+"_details").hasClass("hidden") ) {
							$("#jam_update_form #"+$target+"_details").addClass("hidden");
							$("#jam_update_form #"+$target+"_adresse").empty();
							$("#jam_update_form #"+$target+"_web").empty();
						}
					}
					
					// Si on a trouvé le lieu, on affiche les détails
					else {
						
						$lieu = JSON.parse(msg);
						
						$("#jam_update_form #"+$target+"_details").removeClass("hidden");
						
						$("#jam_update_form #"+$target+"_web").empty();
						$("#jam_update_form #"+$target+"_adresse").empty();
						if ($lieu.adresse.length) {
							$("#jam_update_form #"+$target+"_adresse").append($lieu.adresse);
							$("#jam_update_form #"+$target+"_adresse").css("display","block");
						}
						if ($lieu.web.length) {
							$("#jam_update_form #"+$target+"_web").append($lieu.web);
							$("#jam_update_form #"+$target+"_web").prop("href","http://"+$lieu.web);
							$("#jam_update_form #"+$target+"_web").css("display","block");
						}
					}
				}
			);
		});
		
		
		// Permet d'ouvrir les sections d'affichage dynamique si besoin
		$("input:checkbox:checked").each(function() {
			$("#"+$(this).prop("id")+"_infos").css("display","block");
		});
		
		
		// On définit les Hover Btn
		$(".clickableBtn").each(function() {
			$(this).hover(function(){
				$(this).css("filter", "grayscale(0%)");
				}, function(){
				$(this).css("filter", "grayscale(100%)");
			});
		});
		

		
		// Gestion du bouton d'ajout d'admin en fonction de l'input
		$("#adminInput").on('input', function() {
			
			// On regarde si l'input est vide ou pas
			$btn = $("#add_adminBtn");
			if ($(this).val().length == 0) {
				$btn.addClass("disabled");
				return;
			}
			
			// On regarde si l'input existe dans les membres saisis  !!!! pas top optimisé
			$find = false;
			$("datalist#membres option").each(function() {
				if ($(this).val() == $("#adminInput").val()) {
					$find = true;
					return;
				}
			});

			if ($find) $btn.removeClass("disabled");
			else $btn.addClass("disabled");
			
		});
		
		
		// On remplit la liste d'admin
		$.post("<?php echo site_url(); ?>/ajax_jam/get_event_admin",
		
			{'jamId':<?php echo $jam_item['id']; ?>},
			
			function (msg) {
				
				// On affiche la liste des admin
				$list_admin = JSON.parse(msg);

				if ($list_admin != null) {
					$.each($list_admin,function(index) {
						add_event_admin($list_admin[index].pseudo);
					});
				}
			}
		);
		
		
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
		
		
	});
	
	
	/************** FORMATION ************/
	function get_instrumentation() {
	
		// On vide la liste actuellement affichée
		$("#formation_infos").empty();

		// On change le curseur
		document.body.style.cursor = 'wait';
	
		// Requète ajax au serveur
		$.post("<?php echo site_url(); ?>/ajax_instruments/get_instrumentation",
		
			{'formationId':$("#formation").val()},
		
			function (return_data) {
				
				//console.log(return_data);
				$obj = JSON.parse(return_data);
				// On change le curseur
				document.body.style.cursor = 'default';
				
				// La playlist a été chargée
				if ($obj['state'] == 1) {
					
					// On affiche la liste selectionnée
					$postes = $obj['data'];

					$li = false;
					$pupitre = '';
					
					$.each($postes,function(index) {
						
						if (!$li) $line = '';
						//console.log("LINE !!  "+$line);
						
						// Nouveau pupitre
						if ($postes[index].pupitreLabel !== null && $postes[index].pupitreLabel != $pupitre) {
	
							// On ferme la liste à puce précédente si besoin
							if ($pupitre != '' && $postes[index].pupitreLabel != $pupitre) {
								//console.log("fin pupitre");
								$line += '</ul>';
								$("#formation_infos").append($line);
								$line = '';
							}
							else if (index > 0) $line += "<br>";
							
							//console.log("nouveau pupitre");
							$pupitre = $postes[index].pupitreLabel;
							
							// On affiche le nouveau pupitre
							$line += '<h5 style="text-transform: capitalize;">'+$pupitre+'</h5>';
							
							// On ouvre la liste à puce
							$line += '<ul>';
							$li = true;
						}
						
						if ($li) $line += '<li>';
						
						if ($postes[index].posteLabel !== null) $line += '<b>'+$postes[index].posteLabel+'</b>';
						else if ( $postes[index].label !== null ) $line += '<b>'+$postes[index].label+'</b>';
						else $line += '<b>'+$postes[index].name+'</b>';
						
						if ($li) $line += '</li>';
						else $line += '</br>';
						
						if ($li == false || index+1 == $postes.length) $("#formation_infos").append($line);
						
						//console.log("*********"+index+"  "+$("#formation_infos").html());
					});
					
					//console.log($("#formation_infos").html());
					$("#formation_infos").removeClass("hidden");
				}
				
				// Pas d'instrumentation trouvé, on masque la zone d'info
				else {
					$("#formation_infos").addClass("hidden");
				}
			}
		);
	}
	
	
	
	/************** PLAYLIST ************/
	function get_playlist() {
	
		// On vide la liste actuellement affichée
		$("#playlist_infos tbody > *").remove();
		
		// Si on a selectionné une playlist non nulle
		if ($("#playlist").val() > 0) {
		
			// On change le curseur
			document.body.style.cursor = 'wait';
		
			// Requète ajax au serveur
			$.post("<?php echo site_url(); ?>/ajax/get_playlist",
			
				{'idPlaylist':$("#playlist").val()},
			
				function (return_data) {
					
					$obj = JSON.parse(return_data);
					// On change le curseur
					document.body.style.cursor = 'default';
					
					// La playlist a été chargée
					if ($obj['state'] == 1) {
						
						// On affiche la liste selectionnée
						$playlist = $obj['data'];
		
						$.each($playlist["list"],function(index) {
							//$("#playlist_infos").append("<p class='list_elem'>"+$(this)[0].titre+"</p>");
							if ($playlist.list[index].versionId != -1) {
								mark = "<span style='display:none'>1</span><img style='height: 12px;' src='/images/icons/ok.png'>";
								empty = "<span style='display:none'>0</span>";
								if ($playlist.list[index].choeurs == 1) choeurs = mark; else choeurs="";
								if ($playlist.list[index].soufflants == 1) soufflants = mark; else soufflants="";
								// Si stage
								classe = "";
								if ($("#playlist").val() >= 0 && $playlist.list[index].reserve_stage == 1)  {
									stage = mark;
									classe = " class='stage'";
								}
								else stage="";
								
								//$("#playlist_infos tbody").append("<tr onclick='update_player("+$playlist.list[index].versionId+");' versionId='"+$playlist.list[index].versionId+"'><td"+classe+">"+$playlist.list[index].titre+" - <small class='soften'>"+$playlist.list[index].artisteLabel+"</small></td></td><td style='text-align: center'"+classe+">"+choeurs+"</td><td style='text-align: center'"+classe+">"+soufflants+"</td><td style='text-align: center'"+classe+">"+stage+"</td></tr>");
								$("#playlist_infos tbody").append("<tr versionId='"+$playlist.list[index].versionId+"'><td"+classe+">"+$playlist.list[index].titre+" - <small class='soften'>"+$playlist.list[index].artisteLabel+"</small></td></td><td style='text-align: center'"+classe+">"+choeurs+"</td><td style='text-align: center'"+classe+">"+soufflants+"</td><td style='text-align: center'"+classe+">"+stage+"</td></tr>");
							}
							// On gère les pauses
							else $("#playlist_infos tbody").append("<tr versionId='"+$playlist.list[index].versionId+"'><td colspan='"+$("#playlist_infos th").children().length+"'>-= <i>pause</i> =-</td></tr>");
						});
						$("#playlist_infos").css("display","block");

					}
					
					else {
						// Erreur
						/*$("#update_modal_msg .modal-dialog").removeClass("success");
						$("#update_modal_msg .modal-dialog").addClass("error");
						$("#update_modal_msg .modal-dialog").addClass("backdrop","static");
						$("#update_modal_msg .modal-header").html("Erreur !");
						$("#update_modal_msg .modal-body").html($obj['data']);
						$("#update_modal_msg .modal-footer").html('<a id="modal_close" href="#" data-dismiss="modal">Fermer</a>');
						$("#update_modal_msg").modal('show');*/
					}
				}
			);
		}
		
		// On actualise l'input acces_inscr
		if ($("#playlist").val() > 0) {
			$("#acces_inscr_label").css("opacity","1.0");
			$("#acces_inscr_label").removeClass("soften");
			$("#acces_inscr").prop("disabled",0);
			$("#show_affect_label").css("opacity","1.0");
			$("#show_affect_label").removeClass("soften");
			$("#show_affect").prop("disabled",0);
		}
		else {
			$("#playlist_infos").css("display","none");
			$("#acces_inscr_label").css("opacity","0.6");
			$("#acces_inscr_label").addClass("soften");
			$("#acces_inscr").prop("checked", 0);
			$("#acces_inscr").prop("disabled",1);
			$("#show_affect_label").css("opacity","0.6");
			$("#show_affect_label").addClass("soften");
			$("#show_affect").prop("checked", 0);
			$("#show_affect").prop("disabled",1);
		}

    }
	
	
	/************** COORGANISATEURS ************/
	function event_admin_input() {
		
		// Requète ajax au serveur
		$.post("<?php echo site_url(); ?>/ajax/get_member",
		
			{'pseudo':$("#adminInput").val()},
		
			function (msg) {
				
				$member = JSON.parse(msg);
				
				// On affiche l'addBtn que si le membre existe dans la BD mais pas déjà dans la liste d'amin
				if ($member != null && $("#admin_list").children(":contains('"+$("#adminInput").val()+"')").length == 0) $("#adminAddBtn").css("display","inline");
				else $("#adminAddBtn").css("display","none");
			}
		);
	}
	
	
	// Requête ajax d'ajout d'admin   !! le boutton d'ajout garanti l'existence de la saisie dans la liste des membres
	function add_event_admin_request() {

		// On change le curseur
		document.body.style.cursor = 'wait';
	
		// On récupère l'id de la personne sélectionnée
		// (expression régulière de recherche pas possible car on autorise les special char dans le pseudo)
		// On regarde si l'input existe dans les membres saisis  !!!! pas top optimisé
		$find = false;
		$findIndex = -1;
		$("datalist#membres option").each(function(index) {
			if ($(this).val() == $("#adminInput").val()) {
				$find = true;
				$findIndex = index+1;
				return;
			}
		});
		
		// Test inutile mais sait-on jamais...
		if (!$find) {
			console.log("Membre inexistant");
			return;
		}
		$memberId = $("datalist#membres option:nth-child("+$findIndex+")").attr("memberId");
		
	
		// Requète ajax au serveur
		$.post("<?php echo site_url(); ?>/ajax_jam/join_jam",
		
			{	
				'slugJam':'<?php echo $jam_item['slug']; ?>',
				'id': $memberId,
				'event_admin':1
			},
		
			function (return_data) {
				
				$obj = JSON.parse(return_data);
				
				// On change le curseur
				document.body.style.cursor = 'default';
				
				if ($obj['state'] == 1) {
					add_event_admin($("#adminInput").val());
					$("#add_adminBtn").addClass("disabled");
				}
				else {
					console.log($obj['data']);
				}
			}
		);
	}
	
	
	// Ajout d'admin dans la liste
	function add_event_admin($pseudo) {
	
		// On ajoute l'admin à la liste
		$("#admin_list").append("<div class='list_elem'><span class='pseudo'>"+$pseudo+'</span>&nbsp;<img id="adminRemoveBtn" class="clickableBtn" style=\'height: 8px; display:inline; filter:opacity(50%)\' src=\'/images/icons/x.png\' title=\'retirer coorganisateur\'></div>');

		$new_elem = $("#admin_list").children(":contains('"+$pseudo+"')").first();
	
		// On définit les Hover Btn
		$new_elem.children(".clickableBtn").on({
			click: function() {
				remove_event_admin_request($(this).parent().index());
			},
			mouseover: function() {
				$(this).css("filter", "opacity(100%)");
			},
			mouseout: function() {
				$(this).css("filter", "opacity(50%)");
			}
		});

		// On vide l'input
		$("#adminAddBtn").css("display","none");
		$("#adminInput").val('');
		
		// On actualise l'affichage de la liste d'admin
		$("#admin_list").css("display","block");
	}
	
	
	// Requête ajax pour retirer un admin de l'event
	function remove_event_admin_request($index) {
		
		$pseudo = $("#admin_list").children(":nth-child("+($index+1)+")").children(".pseudo").html();
		
		// Requète ajax au serveur
		$.post("<?php echo site_url(); ?>/ajax_jam/remove_event_admin",
		
			{	
				'jamId':'<?php echo $jam_item['id']; ?>',
				'pseudo': $pseudo
			},
			
			function (return_data) {
				
				$obj = JSON.parse(return_data);
				
				// On change le curseur
				document.body.style.cursor = 'default';
				
				if ($obj['state'] == 1) {
					remove_event_admin($index);
				}
			}
		);
	}
	
	// On retire un admin de la liste
	function remove_event_admin($index) {
		$("#admin_list").children(":nth-child("+($index+1)+")").remove();
		
		// On actualise l'affichage de la liste d'admin
		if ($("#admin_list").children().length == 0) $("#admin_list").css("display","none");
	}
	
	
	
	/************** STAGE ************/
	$(":checkbox[name=stage]").change(function () { 
		if($(this).is(":checked")) {
			$("#stage_infos").css('display','block');
		}
		else {
			$("#stage_infos").css('display','none');
		}
	});
		
	
	
	/*************** UPDATE JAM  *************/
	function update_jam() {
		
		// On change le curseur
		document.body.style.cursor = 'wait';
	
		// Requète ajax au serveur
		$.post("<?php echo site_url(); ?>/ajax_jam/update_jam/<?php echo $jam_item['slug']; ?>",
		
			{	
				'title':$("#jam_update_form #title").val(),
				'date_jam':$("#jam_update_form #date_jam").val(),
				'date_bal':$("#jam_update_form #date_bal").val(),
				'date_debut':$("#jam_update_form #date_debut").val(),
				'date_fin':$("#jam_update_form #date_fin").val(),
				'formationId':$("#jam_update_form #formation").val(),
				'lieuId':$("#jam_update_form #lieu").val() == "" ? -1 : $("#jam_update_form #lieu").val(),
				'text_html':CKEDITOR.instances.jam_textarea.getData(),
				'acces_jam':$("#jam_update_form #acces_jam").is(":checked"),
				'max_inscr':$("#jam_update_form #max_inscr").val(),
				'playlistId':$("#jam_update_form #playlist").val(),
				'acces_inscr':$("#jam_update_form #acces_inscr").is(":checked"),
				'show_affect':$("#jam_update_form #show_affect").is(":checked"),
				'benevole':$("#jam_update_form #benevole").is(":checked"),
				
				'stage':$("#jam_update_form #stage").is(":checked"),
				'stage_lieuId':$("#jam_update_form #lieu_stage").val() == "" ? -1 : $("#jam_update_form #lieu_stage").val(),
				'stage_text_html':CKEDITOR.instances.stage_textarea.getData(),
				'duree':$("#jam_update_form #duree").val(),
				'stage_date_debut':$("#jam_update_form #stage_date_debut").val(),
				'stage_date_limit':$("#jam_update_form #stage_date_limit").val(),
				'cotis':$("#jam_update_form #cotis").val(),
				'ordre':$("#jam_update_form #ordre").val(),
				'adresse_cheque':$("#jam_update_form #adresse_cheque").val(),
				
			},
		
			function (return_data) {
				
				//console.log(return_data);
				
				$obj = JSON.parse(return_data);
				// On change le curseur
				document.body.style.cursor = 'default';
				
				// Modal
				if ($obj['state'] == 1) {
					window.location.replace($obj['data']);
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
<div id="jam_update_form" class="container-fluid">
	<form class="form-horizontal">

		<!-------- TITRE --------->
		<div class="form-group required">
			<label for="title" class="control-label col-sm-2">Titre</label>
			<div class="col-sm-9">
				<input id="title" class="form-control" required type="text" name="title" value="<?php echo $jam_item['title']; ?>" placeholder="Titre de la jam" />
			</div>
		</div>
		
		<!-------- DATE --------->
		<div class="form-group required">
			<label for="date_jam" class="control-label col-sm-2 col-xs-3 adjust-xs">Date</label>
			<div class="col-sm-3 col-xs-6">
				<input id="date_jam" class="form-control text-center" required="true" type="text" name="date_jam" value="<?php echo $jam_item['date_label']; ?>" autocomplete="off" />
			</div>
		</div>
		
		<hr>
		
		
		
		<!-------- LIEU --------->
		<div class="form-group">
			<label for="lieu" class="control-label col-sm-2">Lieu</label>
			<div class="col-sm-9">
				<input id="lieu" class="form-control flexdatalist" type="text" name="lieu" value="<?php echo $lieu_item["id"]; ?>" />
				<!-- Détails (adresse + web) !-->
				<div id="lieu_details" class="soften small panel panel-default <?php if ($lieu_item['adresse'] == "" && $lieu_item['web'] == "") echo 'hidden' ?>" style="padding: 5px 10px; margin-bottom: 0px">
					<span id="lieu_adresse" style="display:<?php echo $lieu_item['adresse'] == "" ? "none" : "block" ?>"><?php echo $lieu_item['adresse']; ?></span>
					<a id="lieu_web" target="_blanck" style="display:<?php echo $lieu_item['web'] == "" ? "none" : "block" ?>" href="http://<?php echo $lieu_item['web']; ?>"><?php echo $lieu_item['web']; ?></a>
				</div>
			</div>
		</div>
		
		<hr>
		
		
		<!-------- FORMATION --------->
		<div class="form-group">
			<label for="formation" class="control-label col-sm-2">Formation</label>
			<div class="col-sm-9">
				<select id="formation" class="form-control" value="<?php echo isset($formation_item["id"]) ? $formation_item["id"] : '-1'; ?>" onchange="get_instrumentation()">
					<option value="-1">aucune</option>
					<?php foreach ($list_formations as $formation): ?>
						<option value="<?php echo $formation['id'] ?>" <?php if ( isset($formation_item["id"]) && $formation['id'] == $formation_item["id"]) echo "selected"; ?>><?php echo $formation['name']; ?></option>
					<?php endforeach ?>
				</select>
				<div id="formation_infos" class="soften small panel panel-default hidden" style="padding: 5px 10px; margin-bottom: 0px">
					Test
				</div>

			</div>
		</div>
		
		<hr>
		
		
		<!----------------- PLANNING --------------------->
		<!-- **** BALANCES **** !-->
		<div class="form-group">
			<label for="date_bal" class="control-label col-sm-2 col-xs-4 adjust-xs">Balances</label>
			<div class="col-sm-2 col-xs-4">
				<input id="date_bal" class="form-control text-center" type="input" name="date_bal" list="horaires" class="numbers" autocomplete="off" value="<?php echo $jam_item['date_bal']; ?>" />
			</div>
		</div>
		
		<!-- **** DEBUT **** !-->
		<div class="form-group">
			<label for="date_debut" class="control-label col-sm-2 col-xs-4 adjust-xs">Début</label>
			<div class="col-sm-2 col-xs-4">
				<input id="date_debut" class="form-control text-center" type="input" name="date_debut" list="horaires" class="numbers" autocomplete="off" value="<?php echo $jam_item['date_debut']; ?>" />
			</div>
		</div>
		
		<!-- **** FIN **** !-->
		<div class="form-group">
			<label for="date_fin" class="control-label col-sm-2 col-xs-4 adjust-xs">Fin</label>
			<div class="col-sm-2 col-xs-4">
				<input id="date_fin" class="form-control text-center" type="input" name="date_fin" list="horaires" class="numbers" autocomplete="off" value="<?php echo $jam_item['date_fin']; ?>" />
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
					<textarea id="jam_textarea" class="form-control" name="text" placeholder="Texte de la jam" style="resize:none"><?php echo $jam_item['text_html']; ?></textarea>
				</div>
			</div>
		</div>

		
		<hr>
		
		
		<!---------- ACCES à la JAM  -------->
		<div class="form-group">
			<label for="acces_jam" class="control-label col-sm-4 col-xs-6 adjust-xs">Accès public</label>
			<div class="checkbox col-sm-2 col-xs-2">
				<label style="padding-left: 0px">
					<input id="acces_jam" class="form-control" name="acces_jam" type="checkbox" value="" <?php if ($jam_item['acces_jam'] > 0) echo "checked"; ?> />
					<span class="cr"><i class="cr-icon glyphicon glyphicon-ok"></i></span>
				</label>
			</div>
		</div>
		
		
		<!---------- MAXIMUM d'inscrits  -------->
		<div class="form-group">
			<label for="max_inscr_cb" class="control-label col-sm-4 col-xs-6 adjust-xs">Nb max d'inscrits</label>
			<div class="checkbox col-sm-2 col-xs-2">
				<label style="padding-left: 0px">
					<input class="form-control" name="max_inscr_cb" type="checkbox" value="" <?php if ($jam_item['max_inscr'] > 0) echo "checked"; ?> />
					<span class="cr"><i class="cr-icon glyphicon glyphicon-ok"></i></span>
				</label>
			</div>
			
			<label for="max_inscr" class="control-label sr-only"></label>
			<input id='max_inscr' class="form-control text-center" type="input" style="width:55px; display:<?php echo $jam_item['max_inscr'] <= 0 ? "none" : "inline"; ?>" name="max_inscr" list="nb_inscr" value="<?php echo $jam_item['max_inscr']; ?>" />
			<datalist id="nb_inscr">
			<?php
				$nb = 10;
				while ($nb <= 150) {
					echo '<option value="'.$nb.'">'.$nb.'</option>';
					$nb += 10;
				}
			?>
			</datalist>
		</div>

		
		<hr>
		
		<!--===================  GESTION DES ADMINS ============== !-->
		
		<!--------------  COORGANISATEURS  ----------------->
		<div class="form-group">
			<label class="control-label col-sm-4" for="adminInput">Coorganisateurs</label>
			<div class="input-group col-sm-7">
				<input id="adminInput" class="form-control" type="input" name="adminInput" list="membres" placeholder="Membre du gro" autocomplete="off" oninput="event_admin_input()">
				<div class="input-group-btn">
					<button id="add_adminBtn" class="btn btn-default disabled" type="button" onclick="add_event_admin_request()">
						<i class="glyphicon glyphicon-plus"></i>
					</button>
				</div>
				<!--<img id="adminAddBtn" class="clickableBtn" style='height: 12px; display:none; filter:grayscale(100%)' src='/images/icons/add.png' title='ajouter coorganisateur' onclick='add_event_admin_request()'>!-->
			</div>
			<datalist id="membres">
				<?php foreach ($list_membres as $membre): ?>
					<option value="<?php echo htmlentities($membre->pseudo) ?>" memberId="<?php echo htmlentities($membre->id) ?>"><?php echo htmlentities($membre->pseudo); ?></option>
				<?php endforeach ?>
			</datalist>
			
			<!-- Liste des admin_event !-->
			<div id="admin_list" class="panel panel-default col-sm-7 col-sm-offset-4" style="display:none; padding: 5px 10px;">
			</div>
			
		</div>
		
		
		<!--------------  REFERENTS  ----------------->
		<div class="form-group">
			<label class="control-label col-sm-4" for="adminInput">Référents</label>
			<div class="input-group col-sm-7">
				<input id="referentInput" class="form-control" type="input" name="referentInput" list="membres" placeholder="Membre du gro" autocomplete="off" oninput="event_admin_input()">
				<div class="input-group-btn">
					<button id="add_adminBtn" class="btn btn-default disabled" type="button" onclick="add_event_admin_request()">
						<i class="glyphicon glyphicon-plus"></i>
					</button>
				</div>
				<!--<img id="adminAddBtn" class="clickableBtn" style='height: 12px; display:none; filter:grayscale(100%)' src='/images/icons/add.png' title='ajouter coorganisateur' onclick='add_event_admin_request()'>!-->
			</div>
			<datalist id="membres">
				<?php foreach ($list_membres as $membre): ?>
					<option value="<?php echo htmlentities($membre->pseudo) ?>" memberId="<?php echo htmlentities($membre->id) ?>"><?php echo htmlentities($membre->pseudo); ?></option>
				<?php endforeach ?>
			</datalist>
			
			<!-- Liste des admin_event !-->
			<div id="admin_list" class="panel panel-default col-sm-7 col-sm-offset-4" style="display:none; padding: 5px 10px;">
			</div>
			
		</div>
		
	
		
		<hr>
		
		
		<!----------- PLAYLIST   value="default"  ---------------->
		<div class="form-group">
			<label for="playlist" class="control-label col-sm-2 col-xs-4 adjust-xs">Playlist</label>
			<div class="col-sm-4 col-xs-8">
				<select id="playlist" class="form-control" name="playlist" onchange="get_playlist()">
					<option value="0">aucune</option>
					<?php
						foreach ($list_playlist as $playlist): ?>
							<option value="<?php echo $playlist['id']; ?>" <?php if ($playlist['id'] == $jam_item['playlistId']) echo "selected";?> <?php echo set_select('playlist',$playlist['id']); ?> ><?php echo ucfirst($playlist['title']); ?></option>
						<?php endforeach
					?>
				</select>
			</div>
			
			<!-- CB Acces tableau d'inscription !-->
			<label id="acces_inscr_label" class="control-label col-sm-5 col-xs-9 adjust-xs" for="acces_inscr" <?php if ($jam_item['playlistId'] == 0) echo "class='soften' style='opacity:0.6'"; ?>><small>Accès au tableau d'inscription</label></small>
			<div class="checkbox col-sm-1 col-xs-3">
				<label class="pull-right" style="padding-left: 0px;">
					<input id="acces_inscr" name="acces_inscr" type="checkbox" value="" <?php if ($jam_item['acces_inscriptions'] == 1) echo "checked"; ?> />
					<span class="cr"><i class="cr-icon glyphicon glyphicon-ok"></i></span>
				</label>
			</div>
			
			<!-- CB Afficher les affectation !-->
			<label id="show_affect_label" class="control-label col-sm-5 col-xs-9 adjust-xs" for="show_affect" <?php if ($jam_item['playlistId'] == 0) echo "class='soften' style='opacity:0.6'"; ?>><small>Affectations visibles</label></small>
			<div class="checkbox col-sm-1 col-xs-3">
				<label class="pull-right" style="padding-left: 0px;">
					<input id="show_affect" name="show_affect" type="checkbox" value="" <?php if ($jam_item['affectations_visibles'] == 1) echo "checked"; ?> />
					<span class="cr"><i class="cr-icon glyphicon glyphicon-ok"></i></span>
				</label>
			</div>
		</div>
		
		
		<div id="playlist_infos" style="display:none">
			<table class="listTab unactive">
				<thead>
					<tr>
						<th></th>
						<th style="text-align:center"><span class="choeurs"><img style='height: 12px;' src='/images/icons/heart.png' title='choeurs'></span></th>
						<th style="text-align:center"><span class="soufflants"><img style='height: 16px; margin:0px 2px' src='/images/icons/tp.png' title='soufflants'></span></th>
						<th style="text-align:center"><span class="stage"><img style='height: 16px;' src='/images/icons/metro.png' title='réservé aux stagiaires'></span></th>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<th></th>
						<th style="text-align:center"><img style='height: 10px;' src='/images/icons/heart.png'></th>
						<th style="text-align:center"><img style='height: 14px; margin:0px 2px' src='/images/icons/tp.png'></th>
						<th style="text-align:center"><img style='height: 14px; margin:0px 2px' src='/images/icons/metro.png'><span class="stage"></span></th>
					</tr>
				</tfoot>
				<tbody></tbody>
			</table>
		</div>
		<hr>

		
		
		<!-- STAGE CB -->
		<div class="form-group">
			<label for="stage" class="control-label col-sm-4 col-xs-6 adjust-xs">Stage</label>
			<div class="checkbox col-sm-2 col-xs-2">
				<label style="padding-left: 0px">
					<input id="stage" class="form-control" name="stage" type="checkbox" value="" <?php if ($stage_item['id'] > 0) echo "checked"; ?> />
					<span class="cr"><i class="cr-icon glyphicon glyphicon-ok"></i></span>
				</label>
			</div>
		</div>
		
		
		<!-- STAGE INFOS  -->
		<div id="stage_infos" style="display:<?php if ($stage_item['id'] > 0) echo "block"; else echo "none"; ?>">
		
			<!-------- TEXTE STAGE --------->
			<div class="form-group">
				<div class="row">
					<div class="col-sm-12">
						<textarea id="stage_textarea" class="form-control autosize" name="text" placeholder="Texte du stage" style="resize:none"><?php echo isset($stage_item['text_html']) ? $stage_item['text_html'] : '' ?></textarea>
					</div>
				</div>
			</div>

			
			<div class="form-group">
			
			
				<!-------- LIEU STAGE --------->
				<div class="row">
					<label for="lieu_stage" class="control-label col-sm-4">Lieu</label>
					<div class="col-sm-8">
						<input id="lieu_stage" class="form-control flexdatalist" type="text" name="lieu_stage" value="<?php echo isset($lieu_stage_item["id"]) ? $lieu_stage_item["id"] : "-1"; ?>" />
						<!-- Détails (adresse + web) !-->
						<div id="lieu_stage_details" class="soften small panel panel-default <?php if ($lieu_stage_item['adresse'] == "" && $lieu_stage_item['web'] == "") echo 'hidden' ?>" style="padding: 5px 10px; margin-bottom: 0px" data-value-property='id' data-selection-required='true'>
							<span id="lieu_stage_adresse" style="display:<?php echo $lieu_stage_item['adresse'] == "" ? "none" : "block" ?>"><?php echo $lieu_stage_item['adresse']; ?></span>
							<a id="lieu_stage_web" target="_blanck" style="display:<?php echo $lieu_stage_item['web'] == "" ? "none" : "block" ?>" href="http://<?php echo $lieu_stage_item['web']; ?>"><?php echo $lieu_stage_item['web']; ?></a>
						</div>
					</div>
				</div>	
						
				<br>
			
				<div class="row">
					<label for="duree"class="control-label col-sm-4 adjust-xs">Durée du stage en jours</label>
					<div class="col-sm-2 col-xs-4">
						<input class="form-control pull-right" id="duree" name="duree" value="<?php echo set_value('duree', $stage_item["duree"]); ?>">
					</div>
				</div>	
				
				<div class="row">
					<label for="stage_date_debut" class="control-label col-sm-4 adjust-xs">Date de début du stage</label>
					<div class="col-sm-3 col-xs-6">
						<!--<input class="form-control pull-right" id="stage_date_debut" name="stage_date_debut" type="text" value="<?php echo set_value('stage_date_debut', $stage_item["date_debut"]); ?>">!-->
						<input id="stage_date_debut" class="form-control text-center pull-right" type="text" name="stage_date_debut" value="<?php echo $stage_item['date_debut']; ?>" autocomplete="off" />
					</div>
				</div>
			
				<div class="row">
					<label for="stage_date_limit" class="control-label col-sm-4 adjust-xs">Date limite des pré-inscriptions </label>
					<div class="col-sm-3 col-xs-6">
						<!--<input class="form-control" id="stage_date_limit" name="stage_date_limit" type="text" value="<?php echo set_value('stage_date_limit', $stage_item["date_limit"]); ?>">!-->
						<input id="stage_date_limit" class="form-control text-center pull-right" type="text" name="stage_date_limit" value="<?php echo $stage_item['date_limit']; ?>" autocomplete="off" />
					</div>
				</div>

				<div class="row">
					<label for="cotis" class="control-label col-sm-4 adjust-xs">Cotisation en euros</label>
					<div class="col-sm-3 col-xs-4">
						<input class="form-control pull-right" id="cotis" name="cotis" value="<?php echo set_value('cotis', $stage_item["cotisation"]); ?>">
					</div>
				</div>
				
				<div class="row">
					<label for="ordre" class="control-label col-sm-4 adjust-xs">Ordre</label>
					<div class="col-sm-8 col-xs-4">
						<input class="form-control pull-right" id="ordre" name="ordre" value="<?php echo set_value('ordre', $stage_item["ordre"]); ?>">
					</div>
				</div>
				
				<div class="row">
					<label for="adresse_cheque" class="control-label col-sm-4 adjust-xs">Adresse postale</label>
					<div class="col-sm-8 col-xs-4">
						<input class="form-control pull-right" id="adresse_cheque" name="adresse_cheque" value="<?php echo set_value('adresse_cheque', $stage_item["adresse_cheque"]); ?>">
					</div>
				</div>
			</div>
			
		</div>

		<hr>
		
		<div class="modal-footer">
			<button type="button" class="btn btn-default" data-dismiss="modal">Annuler</button>
			<button id="updateBtn" type="submit" class="btn btn-primary">Modifier</button>
		</div>

	</form>
</div>