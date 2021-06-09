<!-- Editeur html -->
<!--<script src="<?php echo base_url();?>ressources/ckeditor/ckeditor.js"></script>!-->

<!-- Tablesorter: required -->
<link rel="stylesheet" href="<?php echo base_url();?>ressources/tablesorter-master/css/theme.sand.css">
<script src="<?php echo base_url();?>ressources/tablesorter-master/js/jquery.tablesorter.js"></script>
<script src="<?php echo base_url();?>ressources/tablesorter-master/js/widgets/widget-storage.js"></script>
<script src="<?php echo base_url();?>ressources/tablesorter-master/js/widgets/widget-columnSelector.js"></script>


<script src="<?php echo base_url();?>ressources/script/sortable.min.js"></script>


<script type="text/javascript">

	$(function() {

		// On initilise le tablesorter que si une playlist a été choisie
		<?php if ($playlist_item['list'] != 0) : ?>
		
		// initialize column selector using default settings
		// note: no container is defined!
		$("#inscrTab").tablesorter({
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
		$.tablesorter.columnSelector.attachTo( $('#inscrTab'), '#popover-target');

		$('#popover').popover({
			placement: 'right',
			html: true, // required if content has HTML
			content: $('#popover-target')
		});
		

	
		// On fixe le comportement des checkboxes
		$("#inscrTab input:checkbox").each(function() {	
		
			$(this).click(function() {
				
				// On récupère les infos du checkbox cliqué
				versionId = $(this).closest("tr").attr("versionId");
				nameSong = get_songTitle(versionId);
				idInstru = $(this).attr("idInstru");
				labelInstru = get_instruName(idInstru);
				
				
				// Comportement d'un checkbox
				if (this.checked) set_inscription(versionId, idInstru);
				else delete_inscription(versionId, idInstru);
				
			});		
		});
		
		// On active les handlers pour le player
		song_update();
		
		// Si une song a été sélectionnée dans un autre Block, on la sélectionne aussi dans le Block d'inscription
		$(".is_playable:not(.firstTD) tbody").find(".selected").each(function() {
			$("#inscrBlock .is_playable tbody").find('[versionId="'+$(this).attr("versionId")+'"] td:first-child').addClass("selected");
		});
		
		<?php endif; ?>

		//////////////////   ORDER MODAL   ///////////////////////:
		var el = document.getElementById('items');
		var sortable = new Sortable(el, {
				sort: true,
				chosenClass: "sortable-chosen",  // Class name for the chosen item
				animation: 150,
				// On vérifie s'il y a changement d'ordre pour activer le bouton de modification
				onEnd: function (evt) {
					$active = false;
					$("#modal_order #items li").each(function(index) {
						if ($(this).find(".choicePos").html() != index+1) {
							$active = true;
							return false;
						}
					});
					if ($active) $("#modal_order .modal-footer [type='submit']").removeClass("disabled");
					else $("#modal_order .modal-footer [type='submit']").addClass("disabled");
				},
			});
			
		
		// On disable le boutton si 1 ou pas de choix effectué
		update_orderBtn();
		
		// On update la hidden_list (utile pour la présentation sur un order)
		update_hidden_member_inscriptions_list();
		
		// On initialise la modal sur le click de l'orderBtn
		$("#orderBtn").click(function() {
			// On réinitialise la liste des choix
			$("#modal_order #items").empty();
			
			// On utilise la hidden list
			$("#member_inscriptions li").each(function() {
				$(this).clone().addClass("list-group-item").appendTo($("#modal_order #items"));
			});
			
			$("#modal_order").modal();
		});
		
	});
	
	
	
	// Disable ou non le bouton d'ordre des choix en fonction du nombre d'inscriptions
	function update_orderBtn() {
		// On récupère le nb d'inscription
		nbInscr = $("#inscrTab tbody input:checked").length;
		// On update l'état du bouton
		if (nbInscr < 2) $("#orderBtn").prop('disabled', true);
		else $("#orderBtn").prop('disabled', false);
	}
	
	
	// Met à jour l'ordre de choix des inscriptions
	function update_order() {

		// On change le curseur
		document.body.style.cursor = 'wait';
		
		// On créé un array[choicePos]{versionId;instruId}
		choiceList = new Array();
		// On parcours les items dans le nouvel ordre
		$("#modal_order #items li").each(function() {
			choiceList.push({ versionId : $(this).attr("versionId") ,
								instruId : $(this).attr("instruId") }
			);
		});
		
		// Requète ajax au serveur
		$.post("<?php echo site_url(); ?>/jam/ajax_update_inscription",
		
			{
			'memberId': '<?php echo $this->session->userdata('id')?>',
			'jamSlug': '<?php echo $jam_item['slug'] ?>',
			'choiceList': JSON.stringify(choiceList)
			},
	
			function (return_data) {
		
				$obj = JSON.parse(return_data);
				// On change le curseur
				document.body.style.cursor = 'default';
	
				// On actualise l'affichage de l'inscription avec le choicePos et le pseudo
				if ($obj['state'] == 1) {
					$("#modal_order #items li").each(function(index) {
						// On actualise le inscrTab
						$pos = get_instruPos($(this).attr('instruId'));
						$label = $('#inscrTab tbody tr[versionId="'+$(this).attr('versionId')+'"] td:nth-child('+$pos+') input:checked').parent().find(".choiceLabel");
						$newLabel = (index+1) + "." + $label.html().substr($label.html().indexOf('.')+1);
						$label.empty().append($newLabel);
						$(this).find(".choicePos").fadeOut(200, function() {
							$(this).empty();
							$(this).append(index+1).fadeIn(400);
						});
					});
					// On désactive le bouton de modif
					$("#modal_order .modal-footer [type='submit']").addClass("disabled");
				}
				
				else console.log("ERROR : update_inscription");
				
				// On actualise la hidden_list
				update_hidden_member_inscriptions_list();
			}
		);
	}

	
	function set_inscription(versionId, instruId) {
		
		// On change le curseur
		document.body.style.cursor = 'wait';
		
		
		// Requète ajax au serveur
		$.post("<?php echo site_url(); ?>/jam/ajax_set_inscription",
		
			{
			'memberId': '<?php echo $this->session->userdata('id')?>',
			'jamSlug': '<?php echo $jam_item['slug'] ?>',
			'instruId': instruId,
			'versionId': versionId
			},
	
			function (return_data) {
				
				$obj = JSON.parse(return_data);
				// On change le curseur
				document.body.style.cursor = 'default';
	
				// On actualise l'affichage de l'inscription avec le choicePos et le pseudo
				if ($obj['state'] == 1) {
					$pos = get_instruPos(instruId);
					$span = $obj['data']+"."+$("#memberLogin").html();
					$('#inscrTab tr[versionId="'+versionId+'"] td:nth-child('+$pos+') .choiceLabel').append($span).hide().fadeIn(500);					
				}
				
				else console.log("ERROR : set_inscription");
				
				// On actualise la hidden_list
				update_hidden_member_inscriptions_list();
				
				// On actualise l'état du bouton d'ordre des choix*
				update_orderBtn();
			}
		);
	}	
		
		
		
	function delete_inscription(versionId, instruId) {
		
		// On change le curseur
		document.body.style.cursor = 'wait';
		
		
		// Requète ajax au serveur
		$.post("<?php echo site_url(); ?>/jam/ajax_delete_inscription",
		
			{
			'memberId': '<?php echo $this->session->userdata('id')?>',
			'jamSlug': '<?php echo $jam_item['slug'] ?>',
			'instruId': instruId,
			'versionId': versionId
			},
	
			function (return_data) {
				
				$obj = JSON.parse(return_data);
				// On change le curseur
				document.body.style.cursor = 'default';
				
				//console.log(return_data);
	
				// On actualise l'affichage
				if ($obj['state'] == 1) {
					// On efface le choicePos + pseudo à coté de la checkbox
					$pos = get_instruPos(instruId);
					$('#inscrTab tr[versionId="'+versionId+'"] td:nth-child('+$pos+') .choiceLabel').fadeOut(400, function() { $(this).empty().show(); });
					// On actualise les choicePos des autres choix
					$('#inscrTab tbody .choiceLabel').each(function() {
						if ($(this).html().length > 0) {
							// On récupère le choicePos
							var $CP = $(this).html().substr(0,$(this).html().indexOf('.'));
							// On l'actualise si besoin
							if (parseInt($CP) > $obj['data']) {
								$newPos = $CP-1;
								$newSpan = $newPos+'.'+$("#memberLogin").html();
								$(this).empty().append($newSpan);
							}
						}
					});
					
					// On actualise la hidden_list
					update_hidden_member_inscriptions_list();
				}
				
				else console.log("ERROR : set_inscription");
				
				// On actualise l'état du bouton d'ordre des choix*
				update_orderBtn();
			}
		);
	}

	
	/* ************************	 FONCTION DU TABLEAU 	************************/
	/* **************************************************************************/
	
	// Permet de retrouver un titre de morceau à partir du versionId
	function get_songTitle(versionId) {
		return $("#inscrTab tbody tr[versionId='"+versionId+"'] .song").html();
	}
	
	// Permet de retrouver un nom d'instrument à partir de l'idInstru
	function get_instruName(idInstru) {
		$instruName = "";
		$thInstru = $("#inscrTab tr:nth-child(2)");//.children(":nth-child(2)");
		$thInstru.children().each(function(index) {
			if (index > 1 && $(this).attr("idInstru") == idInstru)
				$instruName = $(this).children().html();
		});
		return $instruName;
	}
	
	// Permet de retrouver un nom d'instrument à partir de l'idInstru
	function get_instruPos(idInstru) {
		$tdPos = -1;
		$thInstru = $("#inscrTab tr:nth-child(2)");//.children(":nth-child(2)");
		$thInstru.children().each(function(index) {
			if (index > 0 && $(this).attr("idInstru") == idInstru)
				$tdPos = index+1;
		});
		return $tdPos;
	}
	
	// Permet d'actualiser la liste des choix sur toutes les sections
	function update_hidden_member_inscriptions_list() {
		$("#member_inscriptions").empty();
		
		// On récupère le nb d'inscription
		nbInscr = $("#inscrTab tbody input:checked").length;
		
		//console.log("nbInscr : "+nbInscr);
		
		for (i = 1; i <= nbInscr; i++) {
			$("#inscrTab tbody input:checked").each(function(index) {
				// On récupère la position du choix
				pos = $(this).parent().children(".choiceLabel").html().split('.',1)[0];
				if (pos == i) {
					// On rempli la liste des choix
					$versionId = $(this).closest("tr").attr("versionId");
					$titre = get_songTitle($versionId);
					$instruId = $(this).attr("idInstru");
					$instru = get_instruName($instruId);
					$("#member_inscriptions").append("<li versionId='"+$versionId+"' instruId='"+$instruId+"'><b><span class='choicePos'>"+pos+"</span></b>. "+$titre+" - <small class='soften'>"+$instru+"</small></li>");
				}
			});
		}
	}
	
	
	/* ******************  Gestion des sections admin    ************************/
	/* **************************************************************************/
	
	// On gère l'ouverture et fermeture de section
	/*function trig_admin_section($target) {
		$("#admin_"+$target+"_item").hasClass("active") ? $("#admin_"+$target+"_item").removeClass("active") : $("#admin_"+$target+"_item").addClass("active");
		$("#admin_"+$target+"_section").css("display") == "block" ?	$("#admin_"+$target+"_section").css("display", "none") : $("#admin_"+$target+"_section").css("display", "block");
	}*/
	
	
	// Update du texte d'intro
	function update_text_tab() {
		//alert(CKEDITOR.instances.editor1.getData());
		
		
		// Requète ajax au serveur
		$.post("<?php echo site_url(); ?>/ajax/update_text_tab",
		
			{
			'slugJam':'<?php echo $jam_item['slug'] ?>',
			'text_tab':CKEDITOR.instances.editor1.getData(),
			},
	
			function (msg) {
				// Le lieu spécifié n'est pas présent dans la base
				if (msg == "success") {
					TINY.box.show({html:"Le texte d'information a été mis à jour.",boxid:'success',animate:false,width:650, closejs:function(){location.reload();}});
				}
			}
		);
		
	}

 </script>

 
<!-- Span caché pour obtenir les infos du membres -->
<span id="instru_cat1" style="display:none"><?php echo $instru_cat1; ?></span>
<span id="instru_cat2" style="display:none"><?php echo $instru_cat2; ?></span>


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


<!----------- INSCRIPTIONS  ------------>
<div class="row">
<div class="panel panel-default">

	<div id="inscrBlock" class="row block">
	
		<!-- ****** INFOS INSCRIPTIONS ****** -->
		<?php if ($jam_item["text_tab"] != "") : ?>
			<div class="panel panel-default no-border">
			
				<!-- Heading !-->
				<div class="panel-heading">
					<span class="soften">Infos inscriptions</span>
				</div>
				
				<!-- Block !-->
				<div class="row">
					<div id="text_tab" class="panel panel-transparent panel-body no-border small">
						<?php echo $jam_item["text_tab"] ?>
					</div>
				</div>
				
			</div>
		<?php endif; ?>
			 
			<!--------- Section d'admin pour le texte d'intro !----------->
			<!--<?php if ($this->session->userdata('admin') == 1) : ?>
			 Barre pour ouvrir le cadre d'admin 
				<div class="block_footer" style="margin-top:-10">
					<hr style="width:100%; margin:0;">
					<div class="soften">
						<a id="admin_info_item" href="javascript:trig_admin_section('info')" class="ui_elem soften"><img width="16" style="vertical-align: middle;" src="/images/icons/edit.png" alt="edit">  modifier</a>
					</div>
					<hr style="width:100%; margin:0; padding: 0 0 2 0;">
				</div>
			<?php endif; ?>!-->


			<!--<div id="admin_info_section" class="block" style="display:none; padding-bottom:0">
				<form action="javascript:update_text_tab()">
					<div>
						<textarea name="editor1" id="editor1">
						</textarea>
						<script>
							CKEDITOR.replace( 'editor1', { customConfig : 'config_medium.js' } );
							CKEDITOR.width="500";
						</script>
						<div style="text-align:right;padding-bottom:0"><small><input id="admin_submit" class="button" type="submit" name="submit" value="Mettre à jour" /></small></div>
					</div>
				</form>
			</div>!-->
			
	 
	<!-- **************** TABLEAU *************** -->
	<?php if ($playlist_item['list'] != 0) : ?>
	
		<div class="panel-default no-border">
			
			<!-- Heading !-->
			<div class="panel-heading">
				<span class="soften">Tableau d'inscription</span>
			</div>
			
			
			<div class="row panel-body">
			<div class="col-lg-12">
			
				<!-- Colomn selector !-->
				<button id="popover" type="button" class="btn btn-default">
					Affichage
				</button>
				<div class="hidden">
					<div id="popover-target"></div>
				</div>
				
				<button id="orderBtn" type="button" class="btn btn-default">
					Ordre
				</button>
				
			</div>
			</div>
			
	
	<!-- ************************ TABLEAU DYNAMIQUE ********************* !-->
	
			<?php if ($jam_item["formationId"] > 0) : ?>
	
			<!-- TABLEAU !-->
			<div class="row">
			<div class="col-lg-12" style="overflow:auto">
				
				<table id="inscrTab" class="tablesorter bootstrap-popup is_playable firstTD"
								data-playlistId="<?php echo $playlist_item['infos']['id'] ?>">

					<!-- Headers de colonne pupitre !-->
					<thead>
						<tr class="tablesorter-ignoreRow"> <!-- Ignore all cell content; disable sorting & form interaction  -->
							<th>&nbsp;</th> <!-- col du titre de morceau !-->
							<!-- On parcourt le header des pupitres !-->
							<?php foreach ($instrumentation_header as $header_item)
									echo "<th class='centerTD' colspan='".$header_item["nbInstru"]."'>".$header_item["pupitreLabel"]."</th>";
							?>
						</tr>
						
						
						<!-- Headers de colonne instruments !-->
						<tr>
							<!-- On priorise l'affichage du titre de morceau !-->
							<th data-priority="critical">&nbsp;</th>
							<!-- On parcourt le header des pupitres !-->
							<?php
								foreach ($instrumentation_list as $instrumentation_item) {
									log_message("debug","instrumentation_item : ".json_encode($instrumentation_item));
									echo '<th class="centerTD" data-priority="'.'$dataPriority'.'" idInstru="0">';
									if ($instrumentation_item["posteLabel"] !== null) echo $instrumentation_item["posteLabel"];
									else echo $instrumentation_item["name"];
									echo '</th>';
								}
							?>
						</tr>
					</thead>
					
					
					<tbody>
					<!-- Ligne des morceaux !-->
					<?php foreach ($playlist_item['list'] as $key=>$ref): ?>
						<tr class="<?php if ($ref->reserve_stage) echo "stage_elem";?>" versionId="<?php echo $ref->versionId; ?>">
						
							<!-- On gère les pauses !-->
							<?php if ($ref->versionId == -1) :?>
								<td colspan="<?php echo $nbcol; ?>"></td>
							<!-- Titre du morceau !-->
							<?php else : ?>
								<td class="song">
									<?php echo $ref->titre;
										$titreSong = $ref->titre; 
									?>
								</td>

								<?php foreach ($cat_instru_list as $cat) {
									
									foreach ($cat['list'] as $idInstru) {
										if($idInstru) {
											echo '<td>';
											
											// On affiche le pseudo du membre affecté si besoin
											if ($jam_item["affectations_visibles"]) {
												// On recherche l'id des affectés par rapport au titre de la ligne $titresong
												$keys = searchForId($titreSong,$affectations,"titre");
												if (isset($keys)) {
													$find = false;
													// Pour chaque référence, on affiche le pseudo
													echo "<div class='affected'>";
													foreach ($keys as $key) {
														if($idInstru == $affectations[$key]['instruId']) {
															$find = true;
															echo $affectations[$key]['pseudo']."<br>";
														}
													}
													echo "</div>";
												}
											}
											else $find = false;
											
											// Si l'internaute est concerné	la checkbox doit être affichée
											$set_checkbox = false;
											if ( ($idInstru == $member->idInstru1 || $idInstru == $member->idInstru2) && $attend )
												$set_checkbox = true;

											////// On affiche la liste des inscrits sur ce morceaux
											// On recherche l'id des inscrits par rapport au titre de la ligne $titresong
											$keys = searchForId($titreSong,$inscriptions,"titre");
											if (isset($keys)) {
												$is_set = false;
												// Pour chaque référence, on affiche le pseudo
												foreach ($keys as $key) {
													if($idInstru == $inscriptions[$key]['instruId']) {
														// On affiche une hr si besoin
														//if ($find) { echo '<hr style="margin:inherit">'; $find = false; }
														echo '<div style="white-space:nowrap; background-color:inherit">';
														// CHECKBOX
														if ($set_checkbox && $inscriptions[$key]['pseudo'] == $member->pseudo) {
															
															echo '
																	<div class="checkbox">
																		<label>
																			<input type="checkbox" value="" idInstru="'.$idInstru.'" checked>
																			<span class="cr"><i class="cr-icon glyphicon glyphicon-ok"></i></span>
																			<span class="choiceLabel">'.$inscriptions[$key]['choicePos'].".".$inscriptions[$key]['pseudo'].'</span>
																		</label>
																	</div>';
															
															
															$is_set = true;
														}
														else echo $inscriptions[$key]['choicePos'].".".$inscriptions[$key]['pseudo'];
														echo "</div>";
													}
												}
												// Si il n'y a pas d'inscription, on affiche la checkbox décochée.
												if ($set_checkbox && !$is_set) {
												
													echo '
															<div class="checkbox">
																<label>
																	<input type="checkbox" value="" idInstru="'.$idInstru.'">
																	<span class="cr"><i class="cr-icon glyphicon glyphicon-ok"></i></span>
																	<span class="choiceLabel"></span>
																</label>
															</div>';
												}
											}

										}
										else echo '&nbsp';
										
										echo '</td>';
									}
								}?>
							<?php endif; ?>				
						</tr>
					<?php endforeach; ?>
					<tbody>
					
					
				</table>
				
			</div>	
			</div> <!-- fin tableau dynamique !-->
			
	
	
	<!-- ************************ TABLEAU STATIQUE ********************* !-->
			<?php else : ?>
	
			<!-- TABLEAU !-->
			<div class="row">
			<div class="col-lg-12" style="overflow:auto">
				
				<table id="inscrTab" class="tablesorter bootstrap-popup is_playable firstTD"
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
								if ($cat['name'] == $instru_cat1 || $cat['name'] == $instru_cat2) $priorityCat = '3';
								else $priorityCat = '6';
								
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
					
					
					<tbody>
					<!-- Ligne des morceaux !-->
					<?php foreach ($playlist_item['list'] as $key=>$ref): ?>
						<tr class="<?php if ($ref->reserve_stage) echo "stage_elem";?>" versionId="<?php echo $ref->versionId; ?>">
						
							<!-- On gère les pauses !-->
							<?php if ($ref->versionId == -1) :?>
								<td colspan="<?php echo $nbcol; ?>"></td>
							<!-- Titre du morceau !-->
							<?php else : ?>
								<td class="song">
									<?php echo $ref->titre;
										$titreSong = $ref->titre; 
									?>
								</td>

								<?php foreach ($cat_instru_list as $cat) {
									
									foreach ($cat['list'] as $idInstru) {
										if($idInstru) {
											echo '<td>';
											
											// On affiche le pseudo du membre affecté si besoin
											if ($jam_item["affectations_visibles"]) {
												// On recherche l'id des affectés par rapport au titre de la ligne $titresong
												$keys = searchForId($titreSong,$affectations,"titre");
												if (isset($keys)) {
													$find = false;
													// Pour chaque référence, on affiche le pseudo
													echo "<div class='affected'>";
													foreach ($keys as $key) {
														if($idInstru == $affectations[$key]['instruId']) {
															$find = true;
															echo $affectations[$key]['pseudo']."<br>";
														}
													}
													echo "</div>";
												}
											}
											else $find = false;
											
											// Si l'internaute est concerné	la checkbox doit être affichée
											$set_checkbox = false;
											if ( ($idInstru == $member->idInstru1 || $idInstru == $member->idInstru2) && $attend )
												$set_checkbox = true;

											////// On affiche la liste des inscrits sur ce morceaux
											// On recherche l'id des inscrits par rapport au titre de la ligne $titresong
											$keys = searchForId($titreSong,$inscriptions,"titre");
											if (isset($keys)) {
												$is_set = false;
												// Pour chaque référence, on affiche le pseudo
												foreach ($keys as $key) {
													if($idInstru == $inscriptions[$key]['instruId']) {
														// On affiche une hr si besoin
														//if ($find) { echo '<hr style="margin:inherit">'; $find = false; }
														echo '<div style="white-space:nowrap; background-color:inherit">';
														// CHECKBOX
														if ($set_checkbox && $inscriptions[$key]['pseudo'] == $member->pseudo) {
															
															echo '
																	<div class="checkbox">
																		<label>
																			<input type="checkbox" value="" idInstru="'.$idInstru.'" checked>
																			<span class="cr"><i class="cr-icon glyphicon glyphicon-ok"></i></span>
																			<span class="choiceLabel">'.$inscriptions[$key]['choicePos'].".".$inscriptions[$key]['pseudo'].'</span>
																		</label>
																	</div>';
															
															
															$is_set = true;
														}
														else echo $inscriptions[$key]['choicePos'].".".$inscriptions[$key]['pseudo'];
														echo "</div>";
													}
												}
												// Si il n'y a pas d'inscription, on affiche la checkbox décochée.
												if ($set_checkbox && !$is_set) {
												
													echo '
															<div class="checkbox">
																<label>
																	<input type="checkbox" value="" idInstru="'.$idInstru.'">
																	<span class="cr"><i class="cr-icon glyphicon glyphicon-ok"></i></span>
																	<span class="choiceLabel"></span>
																</label>
															</div>';
												}
											}

										}
										else echo '&nbsp';
										
										echo '</td>';
									}
								}?>
							<?php endif; ?>				
						</tr>
					<?php endforeach; ?>
					<tbody>
					
					
				</table>
				
			</div>	
			</div> <!-- fin tableau statique !-->
			
			<?php endif; ?>	
			
		</div>	
		

	<?php else:?>
		<div class="panel-default">
			<div class="panel-body">
				<i class='glyphicon glyphicon-warning-sign'></i>&nbsp; Pas de playlist sélectionnée.
			</div>
		</div>
	<?php endif; ?>
			
		
		
		
	</div>
	
	
	
	<!-- MODAL Ordre des choix !-->
	<div id="modal_order" class="modal fade" role="dialog">
		<div class="modal-dialog default modal-md">
			<div class="modal-content">
				<div class="modal-header lead">Modifier l'ordre des choix</div>
				<div class="modal-body">
					
					<ul id="items" class="list-group list-group-hover list-group-sortable">
						<!--<li class="list-group-item" versionId='' instruId=''><b><span class='choicePos'></span></b>First item</li>
						<li class="list-group-item">Second item</li>
						<li class="list-group-item">Third item</li>!-->
					</ul>
					
				</div>
				
				<!-- Mot de passe oublié !-->
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Fermer</button>
					<button type="submit" onclick="update_order()" class="btn btn-primary disabled">Modifier</button>
				</div>
			</div>
		</div>
	</div>
	
	
</div>
</div>



