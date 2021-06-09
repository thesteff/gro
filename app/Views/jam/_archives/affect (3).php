<?php

	// Retourne un tableau keys où $array[key]->$param == $id
	function searchForId($id, $array, $param) {
		$keys = array();
		foreach ($array as $key => $val) {
		   if ($val[$param] === $id) {
			   array_push($keys,$key);
		   }
		}
		return $keys;
	}
?>


<!-- Bootstrap select (pour afficher les stagiaires différement dans le select) !-->
<link rel="stylesheet" href="<?php echo base_url();?>ressources/bootstrap-select/bootstrap-select.min.css" />
<script type="text/javascript" src="<?php echo base_url();?>ressources/bootstrap-select/bootstrap-select.min.js"></script>


<!-- Tablesorter: required -->
<link rel="stylesheet" href="<?php echo base_url();?>ressources/tablesorter-master/css/theme.sand.css">
<script src="<?php echo base_url();?>ressources/tablesorter-master/js/jquery.tablesorter.js"></script>
<script src="<?php echo base_url();?>ressources/tablesorter-master/js/widgets/widget-filter.js"></script>
<script src="<?php echo base_url();?>ressources/tablesorter-master/js/widgets/widget-columnSelector.js"></script>


<script type="text/javascript">

	$(function() {
		
		
		/********* TABLE Affectations  **********/
		// On initilise le tablesorter que si une playlist a été choisie
		<?php if ($playlist_item['list'] != 0) : ?>
		
		// initialize column selector using default settings
		// note: no container is defined!
		$("#affectTab").tablesorter({
			theme: 'sand',
			headers: {'th' : {sorter: false}}, 	 // le tableau n'est pas triable
			widgets: ['zebra', 'columnSelector'],
			widgetOptions : {
				
				// remember selected columns (requires $.tablesorter.storage)
				columnSelector_saveColumns: true,
				
				// Responsive Media Query settings //
				// enable/disable mediaquery breakpoints
				columnSelector_mediaquery: true,
				// toggle checkbox name
				columnSelector_mediaqueryName: 'Auto',
				// breakpoints checkbox initial setting
				columnSelector_mediaqueryState: true,
				// hide columnSelector false columns while in auto mode
				columnSelector_mediaqueryHidden: true,
			}
		});
		
		
		// call this function to copy the column selection code into the popover
		$.tablesorter.columnSelector.attachTo( $('#affectTab'), '#popover-target');
		// Button qui gère l'affichage
		$('#popover').popover({
			placement: 'right',
			html: true, // required if content has HTML
			content: $('#popover-target')
		});
		
		
		// Tooltip
		$('[data-toggle="tooltip"]').tooltip({delay: {show: 1000, hide: 100}});
		$("#pdf button").click(function() {
			$("[data-toggle='tooltip']").tooltip('hide');
		});
		
		// On active les handlers pour le player
		song_update("firstTD");
		
		
		
		// On fixe le comportement du CB affectations visibles
		$("#affect_options #show_affect").click(function() {
			
			// On change le curseur
			document.body.style.cursor = 'wait';
			
			// Requète ajax au serveur
			$.post("<?php echo site_url(); ?>/ajax_jam/show_affectations",
			
				{
				'jamId': '<?php echo $jam_item['id'] ?>',
				'show': this.checked?'1':'0'
				},
		
				function (return_data) {

					$obj = JSON.parse(return_data);
					// On change le curseur
					document.body.style.cursor = 'default';
		
					// On actualise l'affichage de l'inscription avec le choicePos et le pseudo
					if ($obj['state'] == 1) {
						//console.log("GOOD : show_affectations");
					}
					
					else console.log("ERROR : show_affectations");
				}
			);
				
		});
		
		
		<?php endif; ?>
		
		
		
		/********* TABLE Affectations RECAP **********/
		$table = $( '#affectations' ).tablesorter({
			theme : 'sand',
			widgets : [ "zebra", "filter" ],
			widgetOptions: {
				// class name applied to filter row and each input
				filter_cssFilter  : 'tablesorter-filter',
				// search from beginning
				filter_startsWith : false,
				// Set this option to false to make the searches case sensitive
				filter_ignoreCase : true
			}
		});
		

		// On complête le tableau de récap (nombre de morceaux joués)
		init_count();
		
		// On gère l'affichage du fichier de récap
		update_file_block();
		
		
		
		/* *************** Gestion des ajouts/suppressions de list_elem   *************/
		
		// On permet de connaître la valeur précédente d'un select (utile pour actualiser le count)
		$("#affectTab select").each(function() {
			$(this).data("prev",$(this).val());
		});

		
		// On définit le change sur un select
		$("#affectTab select").on("change", function() {

		
			// On récupère le morceau et l'instrument avec l'id du select
			$id = $(this).attr("id").split("-");
			$versionId = $id[0];
			$posteId = $id[1];
			
			// On récupère le membre affecté
			$memberId = $(this).find(":selected").val();
			// On récupère le nombre de select
			$nbSelect = $(this).parents("td").find("select").length;
			// On détermine s'il faut suppr le select
			$delete_Select = ($nbSelect > 1) && ($memberId == 0);
			
			// On change le curseur
			document.body.style.cursor = 'wait';
			
			// Requète ajax au serveur
			$.post("<?php echo site_url(); ?>/ajax_jam/set_affectation",
			
				{
				'jamId': '<?php echo $jam_item['id'] ?>',
				'versionId': $versionId,
				'posteId': $posteId,
				'memberId':  $memberId,
				'prevId': $(this).data("prev")
				},
		
				function (return_data) {

					console.log(return_data);
				
					$obj = JSON.parse(return_data);
					// On change le curseur
					document.body.style.cursor = 'default';
		
					// On actualise l'affichage de l'inscription avec le choicePos et le pseudo
					if ($obj['state'] == 1) {
						//console.log("GOOD : set_affectation");
					}
					
					else console.log("ERROR : set_affectation");
				}
			);
		

			// *********** On actualise le count
			
			// On récupère l'id du membre (val de l'option selected)
			$idMember = $(this).val();	// 0 si on ne selectionne personne
			
			// On récupère l'id du membre précédemment sélectionné
			$idPrevMember = $(this).data("prev");
			
			// Décompte
			if ($idPrevMember != 0) {
				// On récupère la td count du membre déselectionné
				$tempTd = $("#affectations tbody").find("td[idMember='"+$idPrevMember+"']").parent().children(".count");
				$val = parseInt($tempTd.text()) - 1;
				$tempTd.empty();
				$tempTd.append($val);
			}
			// Ajout
			if ($idMember != 0) {
				// On récupère la td count du membre selectionné
				$tempTd = $("#affectations tbody").find("td[idMember='"+$idMember+"']").parent().children(".count");
				$val = parseInt($tempTd.text()) + 1;
				$tempTd.empty();
				$tempTd.append($val);

			}
			// On actualise la valeur prev
			$(this).data("prev",$(this).val());
			
			// On supprime la select si besoin
			if ($delete_Select) {
				$(this).remove();
			}
			
			// On actualise le cache du tableau de recap
			$("#affectations").trigger("update");
		});

	});
	
	
	// Tableau de recap remplit (nb morceaux)
	function init_count() {
		// On parcours le tableau principal
		$("#affectTab tbody tr :selected").each(function() {
			if ($(this).val() > 0) {
				$pseudo = $(this).text();
				// On ajoute un morceau dès qu'un participant est selectioné
				$tdCount = $("#affectations tbody").find("td[idMember='"+$(this).val()+"']").parent().children(".count");
				$val = parseInt($tdCount.html());
				$tdCount.empty();
				$tdCount.append($val+1);
			}
		});
		// On actualise le cache du tableau de recap
		$("#affectations").trigger("update");
	}
	
	
	
	/* ************************	 FONCTION DU TABLEAU 	************************/
	/* **************************************************************************/
	
	// Permet de retrouver un titre de morceau à partir de l'versionId
	function get_songTitle(versionId) {
		$songTitle = "";
		$songList = $("#affectTab tr");
		$songList.each(function(index) {
			if (index > 1 && versionId == $(this).children(":first-child").attr("versionId"))
				$songTitle = $(this).children(":first-child").html();
		});
		return $songTitle;
	}
	
	// Permet de retrouver un nom d'instrument à partir de l'idInstru
	function get_instruName(idInstru) {
		$instruName = "";
		$thInstru = $("#affectTab tr:nth-child(2)");
		$thInstru.children().each(function(index) {
			if (index > 1 && $(this).attr("idInstru") == idInstru)
				$instruName = $(this).html();
		});
		return $instruName;
	}
	
	
	// On lance le listener de clic droit pour le contextmenu
	$(function() {
		$("#affectTab td").on("contextmenu", function(event) {
			$clicked_td = $(this);
			// Impossible d'ajouter un select si le premier et unique champ n'a pas de valeur
			if ($(this).find("select:last").val() == 0 && $(this).find("select").length == 1)	{
				$("#contextmenu #add_affect").prop("disabled","true");
				$("#contextmenu #add_affect").attr("icon","<?php echo base_url();?>images/icons/add_disabled.png");
			}
			else {
				$("#contextmenu #add_affect").prop("disabled","");
				$("#contextmenu #add_affect").attr("icon","<?php echo base_url();?>images/icons/add.png");
			}
			
			// Impossible de supprimer le dernier select
			/*if ($(this).find("select").length == 1)	$("#contextmenu #suppr_affect").prop("disabled","true");
			else $("#contextmenu #suppr_affect").prop("disabled","");*/
		});
	});
	
	
	// Permet d'ajouter un select (multi affectation)
	function add_select() {
		
		//console.log("add_select");
		
		// On récupère le dernier select de la td et on le clone
		$select = $clicked_td.find("select:last").clone(true);
		$select.data("prev","0");
		$select.val("0");
		//$clicked_td.find("select:last").after("<br>");
		//$clicked_td.find("select:last").next().after($select);
		$clicked_td.find("select:last").after($select);
		
		// On récupère l'id de la song cliqué
		$versionId = $select.closest("tr").attr("versionId");
		// On récupère la position de la td où se trouve le select
		$tdIndex = $select.closest("td").index()+1;
		// On récupère l'id d'instrument cliqué
		$idInstru = $select.closest("table").find("#instru_head th:nth-child("+$tdIndex+")").attr("idInstru");
	}
	
	// Permet de supprimer un select (multi affectation)
	function suppr_select() {
		
		//console.log("suppr_select");
		
		// On récupère le dernier select de la td
		$select = $clicked_td.find("select:last");
		// On récupère l'id de la song cliqué
		$versionId = $select.closest("tr").attr("versionId");
		// On récupère la position de la td où se trouve le select
		$tdIndex = $select.closest("td").index()+1;
		// On récupère l'id d'instrument cliqué
		$idInstru = $select.closest("table").find("#instru_head th:nth-child("+$tdIndex+")").attr("idInstru");

		if ($clicked_td.find("select").length > 1) {
			$clicked_td.find("select:last").remove();
			$clicked_td.find("br:last").remove();
		}
	}
	
	
	//******************************* FICHIER D'AFFECTATION
	
	
	// Rafraichit l'affichage du block de gestion du fichier d'affectation
	function update_file_block() {
		
		// Requète ajax au serveur permettant de savoir si le fichier existe ou pas
		$.post("<?php echo site_url(); ?>/Ajax/get_affect_file",
			{'jamId': '<?php echo $jam_item['id']; ?>'},
			function (msg) {

				// Le fichier n'existe pas
				if (msg.startsWith("ERROR")) {
					$("#pdf div.alert span:first-child").empty();
					$("#pdf div.alert span:first-child").removeClass('numbers');
					$("#pdf div.alert span:first-child").css('font-weight','normal');
					$("#pdf div.alert span:first-child").append("Le fichier <b>pdf</b> d'affectation n'existe pas ou a été effacé.");
					
					// On affiche le gen btn et on masque le suppr Btn
					$("#pdf #gen_pdf_Btn").removeClass('hidden');
					$("#pdf #suppr_pdf_Btn").addClass("hidden");
					$("#pdf #file_size").empty();
					$("#pdf #file_size").addClass("hidden");

					// On affiche le block
					$("#pdf div.alert").removeClass('hidden');
				}
				else {

					// On récupère les infos du fichiers créé
					$file_infos = JSON.parse(msg);
					
					// On actualise le nom de fichier
					$("#pdf div.alert span:first-child").empty();
					$("#pdf div.alert span:first-child").addClass('numbers');
					$("#pdf div.alert span").css('font-weight','bold');
					
					$new_div = "<a fileId='"+$file_infos.id+"' href='<?php echo base_url().$dirPath; ?>/"+$file_infos.fileName+"' target='_blanck'>"+$file_infos.fileName+"</a>";
					$("#pdf div.alert span:first-child").append($new_div);
					
					// On actualise le file_size
					$("#pdf #file_size").empty();
					$("#pdf #file_size").append($file_infos.sizeMo);
					$("#pdf #file_size").removeClass('hidden');
					
					// On masque le gen btn et on affiche le suppr Btn
					$("#pdf #gen_pdf_Btn").addClass("hidden");
					$("#pdf #suppr_pdf_Btn").removeClass("hidden");
					
					// On affiche le block
					$("#pdf div.alert").removeClass('hidden');
				}
			}
		);

	}
	
	
	function generate_affect_file() {

		// On actualise l'affichage avec l'icone d'attente et curseur d'attente
		$("#pdf #gen_pdf_Btn").addClass("hidden");
		$("#pdf #wait_block").removeClass("hidden");
		$("body").addClass("wait");
		
		
		// Requète ajax au serveur
		$.post("<?php echo site_url(); ?>/Ajax/generate_affect_file",
		
			{
			'jamId':'<?php echo $jam_item["id"] ?>',
			'memberId':'<?php echo $member->id ?>'
			},
		
			function (msg) {

				// On masque le wait_block et on rétablit le pointeur
				$("#pdf #wait_block").addClass("hidden");
				$("body").removeClass("wait");

				if (msg == "error") TINY.box.show({html:"Le fichier n'a pas pu être généré !",boxid:'error',animate:false,width:650});
				else {
					update_file_block();
				}
				
			}
		);
	}
	
	
	function suppr_affect_file() {

		// On change le curseur
		document.body.style.cursor = 'progress';

		// Requète ajax au serveur
		$.post("<?php echo site_url(); ?>/Ajax/remove_file",
		
			{
			'fileId':$("#pdf [fileId]").attr("fileId")
			},
		
			function (msg) {

			// On rétablit le pointeur
			document.body.style.cursor = 'default';

				if (msg == false) TINY.box.show({html:"Le fichier n'a pas pu être effacé !",boxid:'error',animate:false,width:650});
				else {
					update_file_block();
				}
			}
		);
	}

</script>


<!-- Menu contextuel pour les affectations multiples !-->
<menu type="context" id="contextmenu">
	<menuitem id="add_affect" label="Ajouter une affectation" onclick="add_select();" icon="<?php echo base_url();?>images/icons/add_disabled.png"></menuitem>
	<!--<menuitem id="suppr_affect" label="Supprimer une affectation" onclick="suppr_select();" icon="<?php echo base_url();?>images/icons/suppr.png"></menuitem>!-->
</menu>


<!-- CONTAINER GLOBAL !-->
<?php if ($playlist_item['list']) : ?>
<div class="row">

	<!-- PANEL !-->
	<div class="panel-default panel">
	
		<!-- Header !-->
		<div class="row panel-heading panel-bright title_box">
			<h4><a href="<?php echo site_url().'/jam/'.$jam_item['slug']; ?>"><?php echo $jam_item['title']; ?></a> <small>:</small> tableau d'affectations</h4>
		</div>

		
		<!-- Options !-->
		<div class="row">
		<div class="panel-body col-lg-12">

			<!-- ADMIN OPTIONS PANEL !-->
			<div class="panel panel-default">
		
				<form id="affect_options" class="form-horizontal well-sm">
				
					<!----- CB Affectations visibles ----->
					<div class="form-group">
						<label id="show_affect_label" class="control-label col-sm-3" for="show_affect">Affectations visibles</label>
						<div class="checkbox col-sm-1">
							<label>
								<input id="show_affect" name="show_affect" type="checkbox" value="" <?php if ($jam_item['affectations_visibles'] == 1) echo "checked"; ?> />
								<span class="cr"><i class="cr-icon glyphicon glyphicon-ok"></i></span>
							</label>
						</div>
					</div>
					
			
					<!----- FILE BLOCK ----->
					<div id="file_block" class="form-group">
						<label id="file_label" class="control-label col-sm-3" for="show_file">Fichier récapitulatif</label>
						<div id="pdf" class="col-sm-9">
							<!-- alert box !-->
							<div class="alert alert-warning hidden" style="display:flex; flex-wrap: nowrap; justify-content: space-between; margin-bottom:0px">
								<!-- Nom de fichier ou texte -->
								<div>
									<span></span>
								</div>
								<div class="" style="display:inline; white-space: nowrap;">
									<!-- Generate button -->
									<button id="gen_pdf_Btn" class="btn btn-xs btn-default hidden"
											data-toggle="tooltip" data-placement="right" title="Générer PDF"
											onclick="javascript:generate_affect_file()" type="button">
										<i class='glyphicon glyphicon-cog'></i>
									</button>
									<!-- Taille fichier -->
									<span id="file_size" class="numbers soften file_size small hidden-xs hidden"></span>
									<!-- Delete button -->
									<button id="suppr_pdf_Btn" class="btn btn-xs btn-default hidden"
											data-toggle="tooltip" data-placement="right" title="Supprimer PDF"
											onclick="javascript:suppr_affect_file()" type="button">
										<i class='glyphicon glyphicon-trash'></i>
									</button>
									<!-- Wait block -->
									<div id="wait_block" class="hidden"><img style="height: 14px; vertical-align:middle; margin-right:5px;" src="/images/icons/wait.gif"><small class="soften">création du pdf...</small></div>
								</div>
							</div>
						</div>
					</div>
				
				</form>
				
			
			
			</div> <!-- ADMIN OPTION PANEL !-->
			
			
			<!-- AFFICHAGE !-->
			<div class="row">

				<!-- Colomn selector !-->
				<button id="popover" type="button" class="btn btn-default">
					Affichage
				</button>
				<div class="hidden">
					<div id="popover-target"></div>
				</div>
				
			</div>
		
		</div>  <!-- PANEL BODY !-->
		</div>
		
			
		<!-- **************** TABLEAU DYNAMIQUE *************** -->
		
		<?php if ($jam_item["formationId"] > 0) : ?>
		
		<div class="row">
		<div class="col-lg-12" style="overflow:auto">
			

			<table id="affectTab" class="tablesorter bootstrap-popup is_playable firstTD"
							data-playlistId="<?php echo $playlist_item['infos']['id'] ?>">

				<!-- Headers de colonne pupitre !-->
				<thead>
					<tr class="tablesorter-ignoreRow"> <!-- Ignore all cell content; disable sorting & form interaction  -->
						<th>&nbsp;</th> <!-- col du titre de morceau !-->
						<!-- On parcourt le header des pupitres (et on compte le nombre de colonne totale pour le colspan des pauses) !-->
						<?php 
							$nbcol = 1;
							foreach ($instrumentation_header as $header_item) {
								//log_message("debug","header_item : ".json_encode($header_item));
								$nbcol += $header_item["nbInstru"];
								echo "<th class='centerTD' colspan='".$header_item["nbInstru"]."'>".$header_item["pupitreLabel"]."</th>";
							}
						?>
					</tr>
				
					<!-- Headers de colonne instruments !-->
					<tr id="colPoste">
						<!-- On priorise l'affichage du titre de morceau !-->
						<th data-priority="critical">&nbsp;</th>
						<!-- On parcourt le header des pupitres !-->
						<?php
							foreach ($instrumentation_list as $instrumentation_item) {
								
								for ($i = 0; $i < sizeof($instrumentation_header); $i++) {
									if ($instrumentation_header[$i]["pupitreLabel"] == $instrumentation_item["pupitreLabel"]) {
										if ($instrumentation_header[$i]["couldPlay"] == 1)
											$instrumentation_item["dataPriority"] = 3;
										else $instrumentation_item["dataPriority"] = 6;
										break;
									}
									
								}
								//log_message("debug","instrumentation_item : ".json_encode($instrumentation_item));
								
								// On regarde si le membre peut jouer sur le poste
								if ( $this->formation_model->could_play($member->id, $instrumentation_item["id"])) $dataPriority = 'critical';
								else $dataPriority = $instrumentation_item["dataPriority"];
								
								$visible = '';
								if ($dataPriority == '6') $visible = "columnSelector-false"; // y'a un bug d'affichage des fois...(?)
								
								echo '<th class="centerTD '.$visible.'" data-priority="'.$dataPriority.'" instrumentationId="'.$instrumentation_item["id"].'">';
									if ($instrumentation_item["posteLabel"] !== null) echo $instrumentation_item["posteLabel"];
									else echo $instrumentation_item["name"];
								echo '</th>';
							}
						?>
					</tr>
				</thead>
			
				<!--<tfoot>!-->
					<!-- Footer de colonne instruments !-->
					<!--<tr id="instru_head">
						<th>&nbsp </th>
						<?php 
							$nbcol = 1;
							foreach ($cat_instru_list as $cat) {
								echo "<th class='hidden_".$cat['name']." hidden_cell' style='display:none'>&nbsp;</th>";
								foreach ($cat['list'] as $instru) {
									if($instru) echo '<th class="catelem_'.$cat['name'].' soften" idInstru="'.$instru.'">'.$this->instruments_model->get_instrument_name($instru).'</th>';
									// On enregistre le nombre de colonne du tableau pour le colspan des pauses
									$nbcol++;
								}
							}?>
					</tr>
				</tfoot>!-->
			
			
				<tbody>
				<!-- Ligne des morceaux !-->
				<?php foreach ($playlist_item['list'] as $ref): ?>
					<tr class="<?php if ($ref->reserve_stage) echo "stage_elem";?>"
						versionId="<?php echo str_replace("'", "\'",$ref->versionId); ?>"
					>
					
						<!-- On gère les pauses !-->
						<?php if (str_replace("'", "\'",$ref->versionId) == -1) :?>
							<td colspan="<?php echo $nbcol; ?>"></td>
						<?php else : ?>
						
							<!----- Titre du morceau !----->
							<td class="song">
								<?php echo $ref->titre;
									$titreSong = $ref->titre; 
								?>
							</td>
						
						
							<?php foreach ($instrumentation_list as $instrumentation_item) {
							
								$posteId = $instrumentation_item['id'];
							
								if($posteId) {
									echo '<td contextmenu="contextmenu">';
									
									// On set le pseudo du membre affecté si besoin
									// On recherche l'id des affectés par rapport au titre de la ligne $titresong
									$keys = searchForId($titreSong,$affectations,"titre");
									//log_message("debug",$titreSong." : ".sizeof($keys));
									
									$affected_pseudo = array();
									if (isset($keys)) {
										//$find = false;
										// Pour chaque référence, on affiche le pseudo
										foreach ($keys as $key) {
											if($posteId == $affectations[$key]['posteId']) {
												//$find = true;
												array_push($affected_pseudo,$affectations[$key]['pseudo']);
											}
										}
									}
									log_message("debug",sizeof($affected_pseudo));
									
									// On affiche un select vide si aucun affecté
									if (sizeof($affected_pseudo) == 0) {
										// On remplit le select des noms des inscrits sur ce morceau
										echo "<div class='form-group'>";
										echo "<select class='form-control input-sm' title='' id=\"".$ref->versionId."-".$posteId."\">";
											echo "<option value='0'>&nbsp;</option>";
											echo "<optgroup label='Participants'>";
											/* !!!!!!! Pas du tout optimisé !!!!!!! */
												foreach ($list_members as $member) {
													if ($this->formation_model->could_play($member->memberId,$instrumentation_item["id"])) {
														echo "<option value='".$member->memberId."'>".$member->pseudo."</option>";
													}
												}
											echo "</optgroup>";
											// On affiche les stagiaires
											if (isset ($stage_item['id'])) {
												echo "<optgroup label='Stagiaires'>";
												foreach ($list_stage_members as $member) {
													if ($this->formation_model->could_play($member->memberId,$instrumentation_item["id"])) {
														echo "<option value='".$member->memberId."'>".$member->pseudo."</option>";
													}
												}
												echo "</optgroup>";
											}
										echo "</select>";
										echo "</div>";
									}
									else {
										// Multi affect
										echo "<div class='form-group'>";
										for ($i=0; $i<sizeof($affected_pseudo); $i++) {
											// On remplit le select des noms des inscrits sur ce morceau
											echo "<select class='form-control input-sm' id=\"".$ref->versionId."-".$posteId."-".$i."\">";
												echo "<option value='0'>&nbsp;</option>";
												foreach ($list_members as $member) {
													if ($this->formation_model->could_play($member->memberId,$instrumentation_item["id"])) {
														if ($affected_pseudo[$i] == $member->pseudo) echo "<option value='".$member->memberId."' selected>".$member->pseudo."</option>";
														else echo "<option style='color: black' value='".$member->memberId."'>".$member->pseudo."</option>";
													}
												}
											echo "</select>";
											
										}
										echo "</div>";
									}
									
									////// On affiche la liste des inscrits sur ce morceaux
									// On recherche l'id des inscrits par rapport au titre de la ligne $titresong
									$keys = searchForId($titreSong,$inscriptions,"titre");
									if (isset($keys)) {
										$is_set = false;
										// Pour chaque référence, on affiche le pseudo
										foreach ($keys as $key) {
											if($idInstru == $inscriptions[$key]['instruId']) {
												// On gère l'affichage de l'affectation
												if ($inscriptions[$key]['choicePos'] == 0) {
													echo "<p style='background-color:inherit'><b>".$inscriptions[$key]['pseudo']."</b></p>";
												}
												else echo "<p style='background-color:inherit'>".$inscriptions[$key]['choicePos'].".".$inscriptions[$key]['pseudo']."</p>";
											}
										}
									}
								}
								else echo '<td>&nbsp';
								
								echo '</td>';
							}?>
						<?php endif; ?>
					</tr>
				<?php endforeach; ?>
				</tbody>
		
			</table>
		</div>
		</div>
		<?php else: ?>
		
		
		
		<!-- **************** TABLEAU STATIQUE *************** -->	
		<div class="row">
		<div class="col-lg-12" style="overflow:auto">
			

			<table id="affectTab" class="tablesorter bootstrap-popup is_playable firstTD"
							data-playlistId="<?php echo $playlist_item['infos']['id'] ?>">

				<!-- Headers de colonne catégories d'instruments !-->
				<thead>
					<tr class="tablesorter-ignoreRow"> <!-- Ignore all cell content; disable sorting & form interaction  -->
						<th>&nbsp;</th>
						<?php foreach ($cat_instru_list as $cat): ?>
							<th class="centerTD" colspan="<?php echo sizeof($cat['list']); ?>">
								<?php echo $cat['name'];?>
							</th>
						<?php endforeach; ?>
					</tr>
				
					<!-- Headers de colonne instruments !-->
					<tr>
						<!-- On priorise l'affichage du titre de morceau !-->
						<th data-priority="critical">&nbsp;</th>
						<?php 
							$nbcol = 1;
							// On parcourt les catégories d'instruments
							foreach ($cat_instru_list as $cat) {
								
								// On affiche en priorité les instrument de la même catégorie
								//if ($cat['name'] == $instru_cat1 || $cat['name'] == $instru_cat2) $priorityCat = '3';
								//else $priorityCat = '6';
								$priorityCat = '6';
								
								// On parcours les instruments de la catégorie
								foreach ($cat['list'] as $idInstru) {
									
									if($idInstru) {
										// On fixe la priorité d'affichage
										$dataPriority = $priorityCat;
										$visible = '';
										if ($idInstru == $member->idInstru1 || $idInstru == $member->idInstru2 ) $dataPriority = 'critical';
										else if ($priorityCat == '6') $visible = "columnSelector-false";
										// On insère la th
										echo '<th class="centerTD '.$visible.'" data-priority="'.$dataPriority.'" idInstru="'.$idInstru.'">'.$this->instruments_model->get_instrument_name($idInstru).'</th>';
									}
									
									// On enregistre le nombre de colonne du tableau
									$nbcol++;
								}
							}
						?>
					</tr>
				</thead>
			
				<!--<tfoot>!-->
					<!-- Footer de colonne instruments !-->
					<!--<tr id="instru_head">
						<th>&nbsp </th>
						<?php 
							$nbcol = 1;
							foreach ($cat_instru_list as $cat) {
								echo "<th class='hidden_".$cat['name']." hidden_cell' style='display:none'>&nbsp;</th>";
								foreach ($cat['list'] as $instru) {
									if($instru) echo '<th class="catelem_'.$cat['name'].' soften" idInstru="'.$instru.'">'.$this->instruments_model->get_instrument_name($instru).'</th>';
									// On enregistre le nombre de colonne du tableau pour le colspan des pauses
									$nbcol++;
								}
							}?>
					</tr>
				</tfoot>!-->
			
			
				<tbody>
				<!-- Ligne des morceaux !-->
				<?php foreach ($playlist_item['list'] as $ref): ?>
					<tr class="<?php if ($ref->reserve_stage) echo "stage_elem";?>"
						versionId="<?php echo str_replace("'", "\'",$ref->versionId); ?>"
					>
					
						<!-- On gère les pauses !-->
						<?php if (str_replace("'", "\'",$ref->versionId) == -1) :?>
							<td colspan="<?php echo $nbcol; ?>"></td>
						<?php else : ?>
						
							<!----- Titre du morceau !----->
							<td class="song">
								<?php echo $ref->titre;
									$titreSong = $ref->titre; 
								?>
							</td>
						
						
							<?php foreach ($cat_instru_list as $cat) {
							
								foreach ($cat['list'] as $idInstru) {
									if($idInstru) {
										echo '<td contextmenu="contextmenu">';
										
										// On set le pseudo du membre affecté si besoin
										// On recherche l'id des affectés par rapport au titre de la ligne $titresong
										$keys = searchForId($titreSong,$affectations,"titre");
										//log_message("debug",$titreSong." : ".sizeof($keys));
										
										$affected_pseudo = array();
										if (isset($keys)) {
											//$find = false;
											// Pour chaque référence, on affiche le pseudo
											foreach ($keys as $key) {
												if($idInstru == $affectations[$key]['instruId']) {
													//$find = true;
													array_push($affected_pseudo,$affectations[$key]['pseudo']);
												}
											}
										}
										//log_message("debug",sizeof($affected_pseudo));
										
										// On affiche un select vide si aucun affecté
										if (sizeof($affected_pseudo) == 0) {
											// On remplit le select des noms des inscrits sur ce morceau
											echo "<div class='form-group'>";
											echo "<select class='form-control input-sm' title='' id=\"".$ref->versionId."-".$idInstru."\">";
												echo "<option value='0'>&nbsp;</option>";
												//echo "<optgroup label='Participants'>";
													foreach ($list_members as $member) {
														if ($member->idInstru1 == $idInstru || $member->idInstru2 == $idInstru) {
															echo "<option value='".$member->memberId."'>".$member->pseudo."</option>";
														}
													}
												//echo "</optgroup>";
												// On affiche les stagiaires
												if (isset ($stage_item['id'])) {
													echo "<optgroup label='Stagiaires'>";
													foreach ($list_stage_members as $member) {
														if ($member->idInstru1 == $idInstru || $member->idInstru2 == $idInstru) {
															echo "<option value='".$member->membresId."'>".$member->pseudo."</option>";
														}
													}
													echo "</optgroup>";
												}
											echo "</select>";
											echo "</div>";
										}
										else {
											// Multi affect
											echo "<div class='form-group'>";
											for ($i=0; $i<sizeof($affected_pseudo); $i++) {
												// On remplit le select des noms des inscrits sur ce morceau
												
												echo "<select class='form-control input-sm' id=\"".$ref->versionId."-".$idInstru."-".$i."\">";
													echo "<option value='0'>&nbsp;</option>";
													foreach ($list_members as $member) {
														if ($member->idInstru1 == $idInstru || $member->idInstru2 == $idInstru) {
															if ($affected_pseudo[$i] == $member->pseudo) echo "<option value='".$member->memberId."' selected>".$member->pseudo."</option>";
															else echo "<option style='color: black' value='".$member->memberId."'>".$member->pseudo."</option>";
														}
													}
												echo "</select>";
												
											}
											echo "</div>";
										}
										
										////// On affiche la liste des inscrits sur ce morceaux
										// On recherche l'id des inscrits par rapport au titre de la ligne $titresong
										$keys = searchForId($titreSong,$inscriptions,"titre");
										if (isset($keys)) {
											$is_set = false;
											// Pour chaque référence, on affiche le pseudo
											foreach ($keys as $key) {
												if($idInstru == $inscriptions[$key]['instruId']) {
													// On gère l'affichage de l'affectation
													if ($inscriptions[$key]['choicePos'] == 0) {
														echo "<p style='background-color:inherit'><b>".$inscriptions[$key]['pseudo']."</b></p>";
													}
													else echo "<p style='background-color:inherit'>".$inscriptions[$key]['choicePos'].".".$inscriptions[$key]['pseudo']."</p>";
												}
											}
										}
									}
									else echo '<td>&nbsp';
									
									echo '</td>';
								}
							}?>
						<?php endif; ?>
					</tr>
				<?php endforeach; ?>
				</tbody>
		
			</table>
		</div>
		</div>
		<?php endif; ?>
		
	</div>


	<!-- ******* ROW 2 ******** !-->
	<div class="row">
	
		<!-- RECAP TAB PANEL !-->
		<div class="panel panel-default">
			
			<!-- Header !-->
			<div class="row">
				<h4 class="panel-heading">Récapitulation des affectations</h4>
			</div>

			
			<!-- **************** TABLEAU DE RECAP *************** -->
			<div class="row">
			<div id="affect_list" class="col-lg-12">
			
				<table id="affectations" class="tablesorter" cellspacing="0">
					<thead>
						<tr>
							<th class="centerTD">Cat1</th>
							<th class="centerTD">Instrument</th>
							<th class="centerTD">Pseudo</th>
							<th class="centerTD">Nb morceaux</th>
						</tr>
					</thead>
					<tfoot>
						<tr>
							<th>Cat1</th>
							<th>Instrument</th>
							<th>Pseudo</th>
							<th>Nb morceaux</th>
						</tr>
					</tfoot>
					<tbody>
						<?php 
							foreach ($list_members as $tmember) {
								echo '<tr>';
									// Catégorie
									echo '<td style="text-align: center">';
										//echo '<span style="display:none">'.$instru_cat[$instru_list[$tmember->idInstru1 - 1]['categorie']-1]['view_order'].'</span>';
										echo '<span style="display:none"></span>';
										//echo '<img style="height: 16px;" src="'.base_url().'images/icons/'.$instru_cat[$instru_list[$tmember->idInstru1 - 1]['categorie']-1]['iconURL'].'" title="'.$instru_cat[$instru_list[$tmember->idInstru1 - 1]['categorie']-1]['name'].'">';
										echo '<img style="height: 16px;">';
									echo '</td>';
									//echo '<td>'.$instru_list[$tmember->idInstru1 - 1]['name'].'</td>';
									echo '<td></td>';
									echo '<td class="pseudo" idMember="'.$tmember->memberId.'"><b>'.$tmember->pseudo.'</b></td>';
									echo '<td class="count number" style="font-size:120%; font_weight:bold; text-align:center">0</td>';
								echo '</tr>';
							}
						?>
					</tbody>
				</table>
				
			</div>
			</div>
			
		</div> <!-- RECAP TAB PANEL !-->
		
	</div> <!-- ROW 2 !-->
	
</div>  <!-- GLOBAL CONTENT !-->


<?php else:?>
<div class="panel-default">
	<div class="panel-body">
		<i class='glyphicon glyphicon-warning-sign'></i>&nbsp; Pas de playlist sélectionnée.
	</div>
</div>
<?php endif; ?>