<!-- Editeur html -->
<!--<script src="<?php echo base_url();?>ressources/ckeditor/ckeditor.js"></script>!-->

<!-- Tablesorter: required -->
<link rel="stylesheet" href="<?php echo base_url();?>ressources/tablesorter-master/css/theme.sand.css">
<script src="<?php echo base_url();?>ressources/tablesorter-master/js/jquery.tablesorter.js"></script>
<script src="<?php echo base_url();?>ressources/tablesorter-master/js/widgets/widget-storage.js"></script>
<script src="<?php echo base_url();?>ressources/tablesorter-master/js/widgets/widget-columnSelector.js"></script>




<script type="text/javascript">

	$(function() {

		// initialize column selector using default settings
		// note: no container is defined!
		$("#inscrTab").tablesorter({
			theme: 'sand',
			headers: {'th' : {sorter: false}}, 	 // le tableau n'est pas triable
			widgets: ['zebra', 'columnSelector'],
			widgetOptions : {
				
				// remember selected columns (requires $.tablesorter.storage)
				columnSelector_saveColumns: false,
				
				/* Responsive Media Query settings */
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
		
		
		
		
		// On stylise les colonnes
		$(".centerTD").each(function() {
			$(this).css("text-align","center");
			$(this).parents("table").find("tbody tr td:nth-child("+($(this).index()+1)+")").css("text-align","center");
		});
		
		
		// On remplit la choice_list en fonction de la hidden_select list (remplit par le php en cas d'update)
		/*$("#hidden_select option").each(function() {
			add_list_elem($(this).attr("versionId"), get_songTitle($(this).attr("versionId")), $(this).attr("idInstru"), get_instruName($(this).attr("idInstru")), false);
		});*/

	
		// On fixe le comportement des checkboxes
		$("input:checkbox").each(function() {	
		
			$(this).click(function() {
				
				// On récupère les infos du checkbox cliqué
				versionId = $(this).closest("tr").attr("versionId");
				nameSong = get_songTitle(versionId);
				idInstru = $(this).attr("idInstru");
				labelInstru = get_instruName(idInstru);
				
				
				// Comportement d'un checkbox
				if (this.checked) set_inscription(versionId, idInstru);
				else delete_inscription(versionId, idInstru);
				
				// On retire la song de la choice_list
				/*else {
					elemHTML = $("#choice_list div");
					//alert($("#choice_list div").html());
					elemHTML.each(function(index) {
						if ($(this).attr('versionId') == versionId && $(this).attr('idInstru') == idInstru) $(this).remove();
					});
					// On retire l'élément du formulaire caché
					$("#hidden_select [versionId='"+versionId+"'][idInstru='"+idInstru+"']").remove();

				}*/
			});		
		});
		
		
		// On définit les Hover Btn
		/*$(".clickableBtn").each(function() {
			$(this).css("filter", "grayscale(100%)");
			$(this).hover(function(){
				$(this).css("filter", "grayscale(0%)");
				}, function(){
				$(this).css("filter", "grayscale(100%)");
			});
		});*/
		
		
		// On fixe le comportement de l'upload
		/*$("#uploadInput").on("change", function() {
			// On récupère le nom de fichier
			$new_val = $(this).val().substring($(this).val().lastIndexOf("\\")+1,$(this).val().length);
			popup_upload($new_val);
		});*/
		
		
		// On fixe le comportement des select créés dynamiquement par le popup_upload/popup_generate
		/*$("body").on("change", "select", function() {
			
			// On gère le accessType et le pdfType
			if ($(this).attr("id") == "accessType" || $(this).attr("id") == "pdfType") {

				// On rend la fonction dynamique
				$pre = $(this).attr("id").substr(0,$(this).attr("id").length-4)

				// On remove la select du accessId
				$(this).parent().find("#"+$pre+"Id").remove();
				
				// On doit récupérer la list des access possibles					
				// On change le curseur
				document.body.style.cursor = 'progress';
				
				// Requète ajax au serveur
				$.post("<?php echo site_url(); ?>/ajax/get_access_elem",
				
					{'accessType': $(this).val()},
			
					function (msg) {
						
						// On rétablit le pointeur
						document.body.style.cursor = 'default';

						if (msg != "empty") {
							// On récupère les éléments "option"
							$list_item = JSON.parse(msg);
							$option = "";
							$.each($list_item, function(index, elem) {
								if (index == 0) $option += "<option value='"+elem.id+"' selected>"+elem.name+"</option>";
								else $option += "<option value='"+elem.id+"'>"+elem.name+"</option>";
							});
							
							// On créé le select
							$new_select = '<select id="'+$pre+'Id">';
							$new_select += $option;
							$new_select += '</select>';
							$("#"+$pre+"Type").parent().append($new_select);
						}
					}
				);
			}
			
			
			// On gère le fileType du popup_generate
			else if ($(this).attr("id") == "fileType") {
				
				// On retire le pdf_form si besoin
				if ($("#pdf_form") && $(this).val() != "pdf") $("#pdf_form").remove();

				// Si rien n'est selectionné, le boutton générer est désactivé
				if ($(this).val() == '') $("#upload_block").find("[type=button]").prop('disabled', true);
				else {
					// On active le boutton générer
					$("#upload_block [type=button]").prop('disabled', false);
										
					// On actualise le textFile
					$("#upload_block #textFile").empty();
					if ($(this).val() == 'zip')
						$("#upload_block #textFile").append("Zip rassemblant tous les morceaux de la jam au format mp3");

					// On traite le cas pdf
					else if ($(this).val() == 'pdf') {
						$("#upload_block #textFile").append("Pdf rassemblant toutes les partitions de la jam au format pdf avec un sommaire");
						
						// On créé le select pour les media
						$new_select = "<p id='pdf_form'><label for='pdfType'><small>Sélection de pdf pour chaque morceau</small></label><br>";
						$new_select += '<select id="pdfType" style="margin-right:18px">';
							$new_select += "<option value='all'>Tous les documents</option>";
							$new_select += "<option value='cat'>Catégorie d'instrument</option>";
							$new_select += "<option value='instru'>Instrument</option>";
						$new_select += '</select>';
						$new_select += '</p>';
						
						// On créé le select pour le classement des titres du pdf
						$new_select += "<p id='alpha'><label for='alphaType'><small>Ordre des pdf</small></label><br>";
						$new_select += '<select id="alphaType" style="margin-right:18px">';
							$new_select += "<option value='none'>Ordre de la playlist</option>";
							$new_select += "<option value='asc'>Ordre alphabétique</option>";
						$new_select += '</select>';
						$new_select += '</p>';

						$("#upload_form").append($new_select);
					}
				}
				
				// On actualise la height de la popup
				$("#confirm").css("height",$("#confirm .tcontent").innerHeight());
				$("#confirm").resize();
			}
			
		});


		// On actualie l'affichage du block d'admin doc
		//update_doc_section();*/
		
		
		////////////////// Aspect du tableau   ///////////////////////:
		
		//$("#inscrTab").children().addClass( "dark_bg" );
		//$("#inscrTab tbody :first-child").addClass( "dark_bg" );

		
		// On ferme les catégories qui ne concernent pas l'utilisateur en parcourant tous les header
		/*$(".cat_header").each(function(index) {
			if ($(this).text().trim() != $('#instru_cat1').text() && $(this).text().trim() != $('#instru_cat2').text()) {
				display_cat($(this).text().trim());
			}
		});*/
		
		// On vide les champs de saisie sur un reload
		//resetForms();
		//$('#select_repet').val("-1");
		
		// On remplit le CKEditor
		//CKEDITOR.instances.editor1.setData($("#text_tab").html());
		
		// On active les handlers pour le player
		song_update();
		
	});
	
	
	// Permet de fixer le comportement des titre de morceaux
	/*function song_update() {
		$(".is_playable tbody tr[versionId!='-1']").on("click", function() {
			// On déselectionne la tr précédente
			$(this).closest("tbody").find(".selected").removeClass("selected");
			// La tr devient selected
			$(this).addClass("selected");
			update_player($(this).attr("morceauId"), $(this).attr("versionId"));
		});
		
		// On surcharge le css pour les pause
		$(".is_playable tbody tr[versionId='-1'] > td").css("background-color","#dddddd");		
	}*/
	
	
	/*function resetForms() {
		for (i = 0; i < document.forms.length; i++) {
			document.forms[i].reset();
		}
	}*/
	
		
	/* *************** Gestion des ajouts/suppressions de list_elem *************/
	/* **************************************************************************/
	/*function add_list_elem(versionId, nameSong, idInstru, labelInstru, to_hidden) {
		// On créé la div insérée dans la choice_list
		elemHTML = "<div versionId='"+versionId+"' idInstru='"+idInstru+"'>";
		//elemHTML += "<span class='song_title'>"+nameSong+"</span>";
		elemHTML += nameSong;
		elemHTML += "<span class='note'> - "+labelInstru+"</span>";
		elemHTML += "<br></div>";
		
		// On insère la div dans la choice list
		$("#choice_list").append(elemHTML);
		
		// On récupère l'objet jQuery pour lui ajouter la gestion des mouseEvents
		obj = $("#choice_list div:last")
		// Gestion des event sur la nouvelle tr
		obj.on("click", function() {
			// On déselectionne la tr précédente
			$("#choice_list").find(".selected").removeClass("selected");
			// La tr devient selected
			$(this).addClass("selected");
		});
		
		// On créé la tr insérée dans la choice_list		
		elemHTML = "<tr><td versionId='"+versionId+"' idInstru='"+idInstru+"'>";
		//elemHTML += "<span class='song_title'>"+nameSong+"</span>";
		elemHTML += nameSong;
		elemHTML += "<span class='note'> - "+labelInstru+"</span>";
		elemHTML += "</td></tr>";
		
		// On insère la div dans la choice list
		$("#choice_list").append(elemHTML);
		
		// On ajoute l'élément au formulaire caché
		if (to_hidden) $("#hidden_select").append("<option versionId='"+versionId+"' idInstru='"+idInstru+"' selected>"+versionId+" - "+idInstru+"</option>");
	}*/
	
	
	
	function set_inscription(versionId, instruId) {
		
		
		// On change le curseur
		document.body.style.cursor = 'wait';
		
		// Requète ajax au serveur
		$.post("<?php echo site_url(); ?>/jam/ajax_set_inscription",
		
			{
			'memberLogin': '<?php echo $this->session->userdata('login')?>',
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
					$span = $obj['data']+".<?php echo $member->pseudo ?>";
					$('#inscrTab tr[versionId="'+versionId+'"] td:nth-child('+$pos+') .choiceLabel').append($span).hide().fadeIn(500);					
				}
				
				else console.log("ERROR : set_inscription");
			}
		);
	}	
		
		
		
	function delete_inscription(versionId, instruId) {
		
		// On change le curseur
		document.body.style.cursor = 'wait';
		
		// Requète ajax au serveur
		$.post("<?php echo site_url(); ?>/jam/ajax_delete_inscription",
		
			{
			'memberLogin': '<?php echo $this->session->userdata('login')?>',
			'jamSlug': '<?php echo $jam_item['slug'] ?>',
			'instruId': instruId,
			'versionId': versionId
			},
	
			function (return_data) {
				
				$obj = JSON.parse(return_data);
				// On change le curseur
				document.body.style.cursor = 'default';
				
				console.log(return_data);
	
				// On actualise l'affichage
				if ($obj['state'] == 1) {
					// On efface le choicePos + pseudo à coté de la checkbox
					$pos = get_instruPos(instruId);
					$('#inscrTab tr[versionId="'+versionId+'"] td:nth-child('+$pos+') .choiceLabel').fadeOut(400).empty();					
					// On actualise les choicePos des autres choix
					$('#inscrTab tbody .choiceLabel').each(function() {
						if ($(this).html().length > 0) {
							// On récupère le choicePos
							var $CP = $(this).html().match(/\d+/g);
							// On l'actualise si besoin
							if ($CP[0] > $obj['data']) {
								$newPos = $CP[0]-1;
								$newSpan = $newPos+'.<?php echo $member->pseudo ?>';
								$(this).empty().append($newSpan);
							}
						}
					});
				}
				
				else console.log("ERROR : set_inscription");
			}
		);
	}

	
	
	/* ****************** Gestion des actions to top et bottom ******************/
	/* **************************************************************************/
	/*function move_elem(action) {
	
		if (action == "to_top") {
			selected_elem = $("#choice_list > .selected");
			var selected_index = 0;
			
			// S'il n'y a pas d'élément selectionné, on sort
			if (! selected_elem.length > 0) return;
			
			// On traite la liste affichée
			$list = $("#choice_list").children();
			$list.each(function(index) {
				if ($(this).hasClass('selected')) {
					// Si l'élément selectionné est le premier, on sort de la boucle
					if (index == 0) return;
					selected_elem.insertBefore($("#choice_list > div:eq("+(index-1)+")"));
					selected_index = index;
					return;
				}
			});
	
			// Si l'élément selectionné est le premier, on sort de la fonction
			if (selected_index == 0) return;
			
			// On traite la liste cachée
			hidden_elem = $("#hidden_select > option:eq("+selected_index+")");
			hidden_elem.insertBefore($("#hidden_select > option:eq("+(selected_index-1)+")"));
		}
		
		
		else if (action == "to_bottom") {
			selected_elem = $("#choice_list > .selected");

			// S'il n'y a pas d'élément selectionné à gauche, on sort
			if (! selected_elem.length > 0) return;
			
			// On traite la liste affichée
			$list = $("#choice_list").children();
			$list.each(function(index) {
				if ($(this).hasClass('selected')) {
					selected_elem.insertAfter($("#choice_list > div:eq("+(index+1)+")"));
					selected_index = index;
					return;
				}
			});
			
			// On traite la liste cachée
			hidden_elem = $("#hidden_select > option:eq("+selected_index+")");
			hidden_elem.insertAfter($("#hidden_select > option:eq("+(selected_index+1)+")"));
		}
		
	}*/	

	
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
			if (index > 1 && $(this).attr("idInstru") == idInstru)
				$tdPos = index+1;
		});
		return $tdPos;
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
	
		<div class="panel panel-default no-border">
			
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
			</div>
			</div>
			
			
			<!-- TABLEAU !-->
			<div class="row">

				<div class="col-lg-12" style="overflow:auto">
				
				<table id="inscrTab" class="tablesorter bootstrap-popup"
								data-playlistId="<?php echo $playlist_item['infos']['id'] ?>">

					<!-- Headers de colonne catégories d'instruments !-->
					<thead>
					<tr class="tablesorter-ignoreRow">
						<th>&nbsp;</th>
						<?php foreach ($cat_instru_list as $cat): ?>
							<th class="cat_header tablesorter-ignoreRow" id="cat_<?php echo $cat['name']?>" colspan="<?php echo sizeof($cat['list']); ?>">
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
										echo '<th class="centerTD '.$visible.'" data-priority="'.$dataPriority.'" idInstru="'.$idInstru.'">'.$this->instruments_model->get_instrument($idInstru).'</th>';
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
									//echo "<td class='hidden_".$cat['name']."></td>";
									foreach ($cat['list'] as $idInstru) {
										if($idInstru) {
											echo '<td class="catelem_'.$cat['name'].'">';
											
											// On affiche le pseudo du membre affecté si besoin
											if ($jam_item["affectations_visibles"]) {
												// On recherche l'id des affectés par rapport au titre de la ligne $titresong
												$keys = searchForId($titreSong,$affectations,"titre");
												if (isset($keys)) {
													$find = false;
													// Pour chaque référence, on affiche le pseudo
													foreach ($keys as $key) {
														if($idInstru == $affectations[$key]['instruId']) {
															$find = true;
															echo "<p class='affected'>".$affectations[$key]['pseudo']."</p>";
														}
													}
												}
											}
											else $find = false;
											
											// Si l'internaute est concerné	la checkbox doit être affichée
											$set_checkbox = false;
											if ($idInstru == $member->idInstru1 || $idInstru == $member->idInstru2 )
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
														if ($find) { echo '<hr style="margin:inherit">'; $find = false; }
														echo '<p style="white-space:nowrap; background-color:inherit">';
														// CHECKBOX
														if ($set_checkbox && $inscriptions[$key]['pseudo'] == $member->pseudo) {
															//echo '<input type="checkbox" name="" value="" idInstru="'.$idInstru.'"'.set_checkbox("top", "set_top",true).' />&nbsp';
															
															echo '
																<div class="form-group">
																	<div class="checkbox">
																		<label>
																			<input type="checkbox" value="" idInstru="'.$idInstru.'" checked>
																			<span class="cr"><i class="cr-icon glyphicon glyphicon-ok"></i></span>
																			<span class="choiceLabel">'.$inscriptions[$key]['choicePos'].".".$inscriptions[$key]['pseudo'].'</span>
																		</label>
																	</div>
																</div>';
															
															
															$is_set = true;
														}
														else echo $inscriptions[$key]['choicePos'].".".$inscriptions[$key]['pseudo'];
														echo "</p>";
													}
												}
												// Si il n'y a pas d'inscription, on affiche la checkbox décochée.
												if ($set_checkbox && !$is_set) {
												
													echo '
														<div class="form-group">
															<div class="checkbox">
																<label>
																	<input type="checkbox" value="" idInstru="'.$idInstru.'">
																	<span class="cr"><i class="cr-icon glyphicon glyphicon-ok"></i></span>
																	<span class="choiceLabel"></span>
																</label>
															</div>
														</div>';
												
													//echo '<p style="background-color:inherit"><input type="checkbox" name="" value="" idInstru="'.$idInstru.'"'.set_checkbox("top", "set_top").' /></p>';
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
				<!--</div>!-->
				
			</div>
		</div>	
		
		
		<!-- **************** ORDRE DES CHOIX *************** -->
	
		<div class="panel panel-default no-border">
			
			<!-- Heading !-->
			<div class="panel-heading">
				<span class="soften">Ordre des choix</span>
			</div>
			
			<!-- Block !-->
			<div class="row" style="display:flex; align-items: flex-start;">

				<?php echo form_open('jam/inscriptions/'.$jam_item['slug']) ?>
					
					<table class="listTab" style="width:550;">
						<tbody id="choice_list">
						</tbody>
					</table>
					
					
					<!-- Obligé pour forcer le push de la multipl select !-->
					<div style="display:none">
						<input id="titre" type="input" name="title" value="bidon"/>
						<?php echo form_error('title'); ?>
					</div>	
					
					<select id="hidden_select" name="choice_list[]" multiple style="display:block">
						<?php
							////// On insère dans la hidden_select list la liste des morceaux sur lequel le membre est inscrit
							$keys = searchForId($member->pseudo,$inscriptions,"pseudo");
							if (isset($keys)) {
								// Pour chaque référence, "versionId - idInstru"
								foreach ($keys as $key) {
									echo "<option versionId='".$inscriptions[$key]['versionId']."'idInstru='".$inscriptions[$key]['instruId']."' selected>".$inscriptions[$key]['versionId']." - ".$inscriptions[$key]['instruId']."</option>";
								}
							}
						?>
					</select>
					<?php echo form_error('hidden_select'); ?>
					<br />
					<input class="button" type="submit" name="submit" value="  Inscription  " />
				</form>	

			</div>
		</div>


	<?php else:?>
		<div class="main_block small_block_alert"><p>Pas de playlist sélectionnée.</p>
		</div>
	<?php endif; ?>
			
		
		
		
	</div>
	
	
</div>
</div>

