<!-- bootstrap datepicker !-->
<script type="text/javascript" src="<?php echo base_url();?>ressources/bootstrap-datepicker-1.6.4/js/bootstrap-datepicker.js"></script>
<script type="text/javascript" src="<?php echo base_url();?>ressources/bootstrap-datepicker-1.6.4/locales/bootstrap-datepicker.fr.min.js"></script>
<link rel="stylesheet" href="<?php echo base_url();?>ressources/bootstrap-datepicker-1.6.4/css/bootstrap-datepicker3.css" />

<!-- autoresize texarea !-->
<script type="text/javascript" src="<?php echo base_url();?>ressources/script/autosize.js"></script>

<!-- bootstrapValidator !-->
<script type="text/javascript" src="<?php echo base_url();?>ressources/script/validator.js"></script>

<script type="text/javascript">

	$(function() {
		
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
		
		
		// On initialise le datepicker
		$('#jam_update_form #date_jam').datepicker({
			format: "dd/mm/yyyy",
			todayBtn: "linked",
			language: "fr",
			todayHighlight: true
		});
		
		
		// On initialise le autoresize
		$('.autosize').autosize({append: "\n"});
		
		
		// Permet d'ouvrir les sections d'affichage dynamique si besoin
		$("input:checkbox:checked").each(function() {
			$("#"+$(this).prop("id")+"_infos").css("display","block");
		});
		
		// En cas de repopulate
		//lieu_change();
		
		// On définit les Hover Btn
		$(".clickableBtn").each(function() {
			$(this).hover(function(){
				$(this).css("filter", "grayscale(0%)");
				}, function(){
				$(this).css("filter", "grayscale(100%)");
			});
		});
		

		// On remplit la liste d'admin
		$.post("<?php echo site_url(); ?>/ajax/get_event_admin",
		
			{'jamId':<?php echo $jam_item['id']; ?>},
			
			function (msg) {
				
				// On affiche la liste selectionnée
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
	
	
	/************** LIEU ************/
	
	function lieu_change($action) {

		if ($("#jam_update_form #lieu").val() == "" && $action == "blur") return;
	
		// En cas de reset
		else if ($action == "reset") {
			$("#jam_update_form #lieu").val("");
			$("#jam_update_form #lieu_details").css("display","none");
			return;
		}
	
		// Requète ajax au serveur
		$.post("<?php echo site_url(); ?>/ajax/get_location",
		
			{
			'lieu_name':$("#jam_update_form #lieu").val()
			},
	
			function (msg) {
			
				// Le lieu spécifié n'est pas présent dans la base
				if (msg == "lieu_not_found" && $action == "input") {
					if ($("#jam_update_form #lieu_details").css("display") == "block") {
						$("#jam_update_form #lieu_details").css("display","none");
						$("#jam_update_form #lieu_adresse").empty();
						$("#jam_update_form #lieu_web").empty();
					}
				}
				// Le lieu spécifié n'est pas présent dans la base et on propose de le créer
				else if (msg == "lieu_not_found" && $action == "blur") {
					$txt = "<p>Le lieu spécifié n'est pas présent dans la base de données.<br> Voulez-vous le créer ?</p>"
					$txt += "<p style='text-align:center'><input type='button' value='valider' onclick='javascript:create_location_box(\""+encodeURI($("#lieu").val())+"\")'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='button' value='annuler' onclick='javascript:TINY.box.hide(); lieu_change(\"reset\");' ></p>";
					TINY.box.show({html:$txt,boxid:'confirm',animate:true,width:650, closejs:function(){lieu_change("reset");}});
				}
				
				// Si on a trouvé le lieu, on affiche les détails
				else {
					$lieu = JSON.parse(msg);
					
					$("#jam_update_form #lieu_details").css("display","block");
					if ($lieu.adresse.length) {
						$("#jam_update_form #lieu_adresse").empty();
						$("#jam_update_form #lieu_adresse").append($lieu.adresse);
						$("#jam_update_form #lieu_adresse").css("display","block");
					}
					if ($lieu.web.length) {
						$("#jam_update_form #lieu_web").empty();
						$("#jam_update_form #lieu_web").append($lieu.web);
						$("#jam_update_form #lieu_web").prop("href","http://"+$lieu.web);
						$("#jam_update_form #lieu_web").css("display","block");
					}
				}
			}
		);
		
	}
	
	
	// Formulaire de création de lieu
	function create_location_box($lieu_name) {
	
		$html = "<p><b><u>Ajouter un lieu</u></b></p>";
		$html += "<div class='formLayout'>";
		$html += "<label>Nom</label><input id='lieu_pop_name' size='32' value='"+decodeURI($lieu_name)+"'><br>";
		$html += "<label>Adresse</label><textarea id='lieu_pop_adresse' cols='32' rows='2' style='resize:none'></textarea><br>";
		$html += "<label>Web</label><input id='lieu_pop_web' size='32' ><br>";
		$html += "</div>";
		$html += "<p style='text-align:center'><input type='button' value='ajouter' onclick='javascript:create_location()'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='button' value='annuler' onclick='javascript:TINY.box.hide()' ></p>";
		TINY.box.show({html:$html,boxid:'confirm',animate:false,width:650});
	
	}
	
	// Création du lieu
	function create_location() {
	
		//alert($("#lieu_adresse").val());
		$lieu_name = $("#jam_update_form #lieu_name").val()
		
		
		// Requète ajax au serveur
		$.post("<?php echo site_url(); ?>/ajax/add_location",
		
			{
			'name': $("#jam_update_form #lieu_pop_name").val(),
			'adresse': $("#jam_update_form #lieu_pop_adresse").val(),
			'web': $("#jam_update_form #lieu_pop_web").val()
			},
	
			function (msg) {
				if (msg == "success") TINY.box.show({html:"Le lieu a été ajouté à la base de donnée.",boxid:'success',animate:false,width:650, closejs:function(){ $("#lieux").append("<option value='"+$lieu_name+"'>"+$lieu_name+"</option>"); lieu_change("blur"); } });
				else TINY.box.show({html:"Le lieu est déjà présent dans la base de donnée !",boxid:'error',animate:false,width:650});
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
		}
		else {
			$("#playlist_infos").css("display","none");
			$("#acces_inscr_label").css("opacity","0.6");
			$("#acces_inscr_label").addClass("soften");
			$("#acces_inscr").prop("checked", 0);
			$("#acces_inscr").prop("disabled",1);
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
	
	
	// Requête ajax d'ajout d'admin
	function add_event_admin_request() {

		// Requète ajax au serveur
		$.post("<?php echo site_url(); ?>/ajax/join_jam",
		
			{	
				'slugJam':'<?php echo $jam_item['slug']; ?>',
				'login': $("#adminInput").val(),
				'event_admin':1,
				'buffet':0,
				'billet':0,
				'balance':0
			},
		
			function (msg) {
				// On affiche le message d'info
				TINY.box.show({html:msg,boxid:'success',animate:false,width:650, closejs:function(){add_event_admin($("#adminInput").val());} });
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
		$.post("<?php echo site_url(); ?>/ajax/remove_event_admin",
		
			{	
				'jamId':'<?php echo $jam_item['id']; ?>',
				'pseudo': $pseudo
			},
		
			function (msg) {
				// On affiche le message d'info
				TINY.box.show({html:msg,boxid:'success',animate:false,width:650, closejs:function(){remove_event_admin($index);} });
			}
		);
	}
	
	// On retire un admin de la liste
	function remove_event_admin($index) {
		$("#admin_list").children(":nth-child("+($index+1)+")").remove();
		
		// On actualise l'affichage de la liste d'admin
		if ($("#admin_list").children().length == 0) $("#admin_list").css("display","none");
	}


	
	/************** BENEVOLE ************/
	function benevole_show_infos() {
		if ($("#benevole_infos").css("display") != "none")
			$("#benevole_infos").css("display","none");
		else $("#benevole_infos").css("display","block");
	}
	
	
	/************** STAGE ************/
	function stage_show_infos() {
		if ($("#stage_infos").css("display") != "none")
			$("#stage_infos").css("display","none");
		else $("#stage_infos").css("display","block");
	}
	
	
	/****** UPDATE JAM  *******/
	function update_jam() {
		
		// On change le curseur
		document.body.style.cursor = 'wait';
	
		// Requète ajax au serveur
		$.post("<?php echo site_url(); ?>/jam/ajax_update/<?php echo $jam_item['slug']; ?>",
		
			{	
				'title':$("#jam_update_form #title").val(),
				'date_jam':$("#jam_update_form #date_jam").val(),
				'date_bal':$("#jam_update_form #date_bal").val(),
				'date_debut':$("#jam_update_form #date_debut").val(),
				'date_fin':$("#jam_update_form #date_fin").val(),
				'lieu':$("#jam_update_form #lieu").val(),
				'text':$("#jam_update_form #jam_textarea").val(),
				'acces_jam':$("#jam_update_form #acces_jam").is(":checked"),
				'max_inscr':$("#jam_update_form #max_inscr").val(),
				'playlistId':$("#jam_update_form #playlist").val(),
				'acces_inscr':$("#jam_update_form #acces_inscr").is(":checked"),
				'benevole':$("#jam_update_form #benevole").is(":checked")
			},
		
			function (return_data) {
				
				console.log(return_data);
				
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
				<input id="lieu" class="form-control" list="lieux" type="text" name="lieu" value="<?php echo $lieu_item["nom"]; ?>"
						autocomplete="off" oninput="lieu_change('input')" onblur="lieu_change('blur')" />
				<datalist id="lieux">
				<?php foreach ($list_lieux as $lieu): ?>
					<option value="<?php echo htmlentities($lieu['nom']) ?>"><?php echo htmlentities($lieu['nom']); ?></option>
				<?php endforeach ?>
				</datalist>
				<!-- On affiche les détails s'il y en a !-->
				<?php if ($lieu_item['adresse'] != "" || $lieu_item['web'] != ""): ?>
					<div id="lieu_details" class="soften small panel panel-default" style="padding: 5px 10px; margin-bottom: 0px">
						<span id="lieu_adresse" style="display:<?php echo $lieu_item['adresse'] == "" ? "none" : "block" ?>"><?php echo $lieu_item['adresse']; ?></span>
						<a id="lieu_web" target="_blanck" style="display:<?php echo $lieu_item['web'] == "" ? "none" : "block" ?>" href="http://<?php echo $lieu_item['web']; ?>"><?php echo $lieu_item['web']; ?></a>
					</div>
				<?php endif; ?>
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
					<textarea id="jam_textarea" class="form-control autosize" name="text" placeholder="Texte de la jam" style="resize:none"><?php echo $jam_item['text']; ?></textarea>
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
		
		
		<!-------- AFFECT ADMIN  --------->
		<div class="form-group">
			<label class="control-label col-sm-4" for="adminInput">Coorganisateurs</label>
			<div class="input-group col-sm-7">
				<input id="adminInput" class="form-control" type="input" name="adminInput" list="membres" placeholder="Membre du gro" autocomplete="off" oninput="event_admin_input()">
				<div class="input-group-btn">
					<button class="btn btn-default" type="" onclick="add_event_admin_request()">
						<i class="glyphicon glyphicon-plus"></i>
					</button>
				</div>
				<!--<img id="adminAddBtn" class="clickableBtn" style='height: 12px; display:none; filter:grayscale(100%)' src='/images/icons/add.png' title='ajouter coorganisateur' onclick='add_event_admin_request()'>!-->
			</div>
			<datalist id="membres">
				<?php foreach ($list_membres as $membre): ?>
					<option value="<?php echo htmlentities($membre->pseudo) ?>"><?php echo htmlentities($membre->pseudo); ?></option>
				<?php endforeach ?>
				<?php echo form_error('membres'); ?>
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
			<label id="acces_inscr_label" class="control-label col-sm-5 col-xs-9 adjust-xs" for="acces_inscr" <?php if ($jam_item['playlistId'] == 0) echo "class='soften' style='opacity:0.6'"; ?>><small>Accès au tableau d'inscription</label></small>
			<div class="checkbox col-sm-1 col-xs-3">
				<label class="pull-right" style="padding-left: 0px;">
					<input id="acces_inscr" name="acces_inscr" type="checkbox" value="" <?php if ($jam_item['acces_inscriptions'] == 1) echo "checked"; ?> />
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
		

		
		<!-- BENEVOLES -->
		<p>
			<label for="benevole">Appel à bénévoles</label>
			<input id="benevole" name="benevole" type="checkbox" value='1' <?php echo set_checkbox('benevole','1',$jam_item['appel_benevole'] == 1); ?> onchange="benevole_show_infos()">
		</p>
		
		<div id="benevole_infos" style="display:none"><p>
		
			<div class="small_block_info">
			
				<p style="margin-bottom:5">
					Moyen de dynamiser la section...
				</p>

			</div>
			
		</p></div>

		<hr>
		
		
		<!-- STAGE -->
		<p>
		<label for="stage">Stage</label>
		<input id="stage" name="stage" type="checkbox" value="1" <?php echo set_checkbox('stage','1',$stage_item['id'] != -1); ?> onchange="stage_show_infos()">
		</p>
		
		<div id="stage_infos" style="display:none"><p>
		
			<textarea id="stage_textarea" name="stage_text" placeholder="Texte du stage"><?php echo set_value('stage_text', $stage_item["text"]); ?></textarea>
			<br><?php echo form_error('stage_text'); ?>
			
			<div class="small_block_info">
			
				<p>
					<label for="duree">Durée du stage en jours</label>
					<input style="float:right" id="duree" name="duree" size="4" value="<?php echo set_value('duree', $stage_item["duree"]); ?>">
					<br><?php echo form_error('duree'); ?>
				</p>
			
				<p>
					<label for="stage_date_debut">Date de début du stage </label>
					<input style="float:right" id="stage_date_debut" name="stage_date_debut" size="8" type="text" value="<?php echo set_value('stage_date_debut', $stage_item["date_debut"]); ?>">
					<br><?php echo form_error('stage_date_debut'); ?>
				</p>
			
				<p>
					<label for="stage_date_limit">Date limite des pré-inscriptions </label>
					<input style="float:right" id="stage_date_limit" name="stage_date_limit" size="8" type="text" value="<?php echo set_value('stage_date_limit', $stage_item["date_limit"]); ?>">
					<br><?php echo form_error('stage_date_limit'); ?>
				</p>

				<p>
					<label for="cotis">Montant de la cotisation en euros</label>
					<input style="float:right" id="cotis" name="cotis" size="4" value="<?php echo set_value('cotis', $stage_item["cotisation"]); ?>">
					<br><?php echo form_error('cotis'); ?>
				</p>
				
				<p>
					<label for="cotis">Ordre</label>
					<input style="float:right" id="ordre" name="ordre" size="35" value="<?php echo set_value('ordre', $stage_item["ordre"]); ?>">
					<br><?php echo form_error('ordre'); ?>
				</p>
				
				<p>
					<label for="cotis">Adresse postale</label>
					<input style="float:right" id="adresse" name="adresse" size="35" value="<?php echo set_value('adresse', $stage_item["adresse"]); ?>">
					<br><?php echo form_error('adresse'); ?>
				</p>
			</div>
			
		</p></div>

		<hr>
		
		<div class="modal-footer">
			<button type="button" class="btn btn-default" data-dismiss="modal">Annuler</button>
			<button type="submit" class="btn btn-primary">Modifier</button>
		</div>

	</form>
</div>