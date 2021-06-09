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


<!-- Tablesorter: required -->
<link rel="stylesheet" href="<?php echo base_url();?>ressources/tablesorter-master/css/theme.sand.css">
<script src="<?php echo base_url();?>ressources/tablesorter-master/js/jquery.tablesorter.js"></script>
<script src="<?php echo base_url();?>ressources/tablesorter-master/js/widgets/widget-filter.js"></script>

<script type="text/javascript">

	$(function() {
		
		/********* TABLE Affectations **********/
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
	
	
		// On fixe le comportement des titre de morceaux
		$("td.song").on("click", function() {
			// On déselectionne la tr précédente
			$("tbody .selected").removeClass("selected");
			// On select le tr et on le met selected
			$(this).parent().addClass("selected");
			update_player($(this).closest("tr").attr("versionId"));
		});
		
		// On définit les Hover Btn
		$(".clickableBtn").each(function() {
			$(this).css("filter", "grayscale(100%)");
			$(this).hover(function(){
				$(this).css("filter", "grayscale(0%)");
				}, function(){
				$(this).css("filter", "grayscale(100%)");
			});
		});
		
		
		////////////////// Aspect du tableau   ///////////////////////:
		
		/*$("#inscrTab").children().addClass( "dark_bg" );
		$("#inscrTab tbody :first-child").addClass( "dark_bg" );

	
		// On ferme les catégories qui ne concernent pas l'utilisateur en parcourant tous les header		
		$(".cat_header").each(function(index) {
			display_cat($(this).text().trim());
		});*/
		

		// On complête le tableau de récap (nombre de morceaux joués)
		init_count();
		
		// On gère l'affichage du fichier de récap
		update_file_block();
		
		
		/* *************** Gestion des ajouts/suppressions de list_elem   *************/
		
		// On permet de connaître la valeur précédente d'un select (utile pour actualiser le count)
		$("#inscrTab select").each(function() {
			$(this).data("prev",$(this).val());
		});

		// On définit le change sur un select
		$("#inscrTab select").on("change", function() {

			// On récupère la position du select (<select><br> ... utiliser flex...!!!)
			$selectPos = ($(this).index()+2)/2;		
			// On récupère l'id du membre (val de l'option selected)
			$idMember = $(this).val();	// 0 si on ne selectionne personne
			// On récupère l'id de la song
			$versionId = $(this).closest("tr").attr("versionId");
			// On récupère la position de la td où se trouve le select
			$tdIndex = $(this).closest("td").index()+1;
			// On récupère l'id d'instrument
			$idInstru = $(this).closest("table").find("#instru_head th:nth-child("+$tdIndex+")").attr("idInstru");
			
			// On supprime l'ancienne selection de la hidden_select
			$("#hidden_select option[versionId*='"+$versionId+"'][idInstru*='"+$idInstru+"']").each(function($index) {
				if (($index+1) == $selectPos ) $(this).remove();
			});
			
			
			
			// On ajoute l'élément au formulaire caché à la bonne position de select
			$("#hidden_select option[versionId*='"+$versionId+"'][idInstru*='"+$idInstru+"']").each(function($index) {
				if (($index+2) == $selectPos) {
					$(this).after("<option versionId='"+$versionId+"' idInstru='"+$idInstru+"' selected>"+$versionId+" - "+$idInstru+" - "+$idMember+"</option>");
					$find = true;
				}
			});
			if ($selectPos == 1) $("#hidden_select").prepend("<option versionId='"+$versionId+"' idInstru='"+$idInstru+"' selected>"+$versionId+" - "+$idInstru+" - "+$idMember+"</option>");

				
			// ******* On actualise le count
			$idPrevMember = $(this).data("prev");
			// Decompte
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
			
			// On actualise le cache du tableau de recap
			$("#affectations").trigger("update");
		});

	});
	
	
	// Tableau de recap remplit (nb morceaux)
	function init_count() {
		// On parcours le tableau principal
		$("#inscrTab tbody tr :selected").each(function() {
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
	
	
	/* ****************** Gestion des actions du tableau ************************/
	/* **************************************************************************/
	
	// Permet d'afficher et de masquer une catégorie d'instrument (et ses colonnes)
	function display_cat(name) {
	
		is_visible = $("#inscrTab .catelem_"+name).css('display');
		if (is_visible == "table-cell") {
			$("#inscrTab #cat_"+name).addClass("hidden_cell");
			$("#inscrTab .catelem_"+name).css('display','none');
			$("#inscrTab .hidden_"+name).css("display",'table-cell');
			$("#inscrTab tr:nth-child(1) > th[id='cat_"+name+"']").attr("colspan",'1');
		}
		else {
			$("#inscrTab #cat_"+name).removeClass("hidden_cell");
			$("#inscrTab .catelem_"+name).css('display',"table-cell");
			$("#inscrTab .hidden_"+name).css("display",'none');
			nb_cat = 0;
			$("#inscrTab tr:nth-child(2) > th").each(function() {
				//alert($(this).hasClass("catelem_"+name));
				if ($(this).hasClass("catelem_"+name)) nb_cat++;
			});
			//alert(nb_cat);
			//alert($("#inscrTab tr:nth-child(2) > th.catelem_"+name).html());
			//var colCount = $("#inscrTab tr:nth-child(1) > th[id='cat_"+name+"']").length();
			$("#inscrTab tr:nth-child(1) > th[id='cat_"+name+"']").attr("colspan",nb_cat);
		}
    }
	
	// Permet de retrouver un titre de morceau à partir de l'versionId
	function get_songTitle(versionId) {
		$songTitle = "";
		$songList = $("#inscrTab tr");
		$songList.each(function(index) {
			if (index > 1 && versionId == $(this).children(":first-child").attr("versionId"))
				$songTitle = $(this).children(":first-child").html();
		});
		return $songTitle;
	}
	
	// Permet de retrouver un nom d'instrument à partir de l'idInstru
	function get_instruName(idInstru) {
		$instruName = "";
		$thInstru = $("#inscrTab tr:nth-child(2)");
		$thInstru.children().each(function(index) {
			if (index > 1 && $(this).attr("idInstru") == idInstru)
				$instruName = $(this).html();
		});
		return $instruName;
	}
	
	
	// On lance le listener de clic droit pour le contextmenu
	$(function() {
		$("#inscrTab td[class^=catelem_]").on("contextmenu", function(event) {
			$clicked_td = $(this);
			// Impossible d'ajouter un select si le premier n'a pas de valeur
			if ($(this).children("select:last").val() == 0)	{
				$("#contextmenu #add_affect").prop("disabled","true");
				$("#contextmenu #add_affect").attr("icon","<?php echo base_url();?>images/icons/add_disabled.png");
			}
			else {
				$("#contextmenu #add_affect").prop("disabled","");
				$("#contextmenu #add_affect").attr("icon","<?php echo base_url();?>images/icons/add.png");
			}
			
			// Impossible de supprimer le dernier select
			if ($(this).children("select").length == 1)	$("#contextmenu #suppr_affect").prop("disabled","true");
			else $("#contextmenu #suppr_affect").prop("disabled","");
		});
	});
	
	
	// Permet d'ajouter un select (multi affectation)
	function add_select() {
		// On récupère le dernier select de la td et on le clone
		$select = $clicked_td.find("select:last").clone(true);
		$select.data("prev","0");
		$select.val("0");
		$clicked_td.find("select:last").after("<br>");
		$clicked_td.find("select:last").next().after($select);
		
		// On récupère l'id de la song cliqué
		$versionId = $select.closest("tr").attr("versionId");
		// On récupère la position de la td où se trouve le select
		$tdIndex = $select.closest("td").index()+1;
		// On récupère l'id d'instrument cliqué
		$idInstru = $select.closest("table").find("#instru_head th:nth-child("+$tdIndex+")").attr("idInstru");
		
		// On traite la hidden_select
		$("#hidden_select").append("<option versionId='"+$versionId+"' idInstru='"+$idInstru+"' selected>"+$versionId+" - "+$idInstru+" - 0</option>");
	}
	
	// Permet de supprimer un select (multi affectation)
	function suppr_select() {
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
			// On traite la hidden_select
			$("#hidden_select option[versionId*='"+$versionId+"'][idInstru*='"+$idInstru+"']:last").remove();
		}
	}
	
	
	//*******************************
	
	
	// Rafraichit l'affichage du block de gestion du fichier d'affectation
	function update_file_block() {
		
		// Requète ajax au serveur permettant de savoir si le fichier existe ou pas
		$.post("<?php echo site_url(); ?>/Ajax/get_affect_file",
			{'jamId': '<?php echo $jam_item['id']; ?>'},
			function (msg) {

				// Le fichier n'existe pas
				if (msg.startsWith("ERROR")) {
					$("#pdf span:first-child").empty();
					$("#pdf span:first-child").removeClass('numbers');
					$("#pdf span:first-child").addClass('line_alert');
					$("#pdf span:first-child").css('font-weight','normal');
					$("#pdf span:first-child").append("Le fichier <b>pdf</b> d'affectation n'existe pas ou a été effacé.");
					$("#pdf .ui_elem").css("display","inline");
					$("#pdf #file_size").css("display","none");
				}
				else {

					// On récupère les infos du fichiers créé
					$file_infos = JSON.parse(msg);
					
					// On actualise le nom de fichier
					$("#pdf span:first-child").empty();
					$("#pdf span:first-child").removeClass('line_alert');
					$("#pdf span:first-child").addClass('numbers');
					$("#pdf span:first-child").css('font-weight','bold');
					
					$new_div = "<div>";
						$new_div += "<a fileId='"+$file_infos.id+"' class='actionable' href='<?php echo base_url().$dirPath; ?>/"+$file_infos.fileName+"' target='_blanck'>"+$file_infos.fileName+"</a>";
						// On ajoute le suppr_icon
						$new_div += '<a class="rollOverLink" href="javascript:suppr_affect_file()"><img class="x" style="padding-left:10; width:14;" src="/images/icons/x.png"></a>';
					$new_div += "</div>";
					$("#pdf span:first-child").append($new_div);
					
					// On actualise le file_size
					$("#pdf #file_size").empty();
					$("#pdf #file_size").append($file_infos.sizeMo);
					$("#pdf #file_size").css("display","inline");
					
					$("#pdf .ui_elem").css("display","none");
				}
			}
		);

	}
	
	
	function generate_affect_file() {

		// On actualise l'affichage avec l'icone d'attente et curseur d'attente
		$("#pdf .ui_elem").css("display","none");
		$("#pdf #wait_block").css("display","block");
		$("body").addClass("wait");
		
		
		// Requète ajax au serveur
		$.post("<?php echo site_url(); ?>/Ajax/generate_affect_file",
		
			{
			'jamId':'<?php echo $jam_item["id"] ?>',
			'memberId':'<?php echo $member->id ?>'
			},
		
			function (msg) {

				// On masque le wait_block et on rétablit le pointeur
				$("#pdf #wait_block").css("display","none");
				$("body").removeClass("wait");

				if (msg == "error") TINY.box.show({html:"Le fichier n'a pas pu être généré !",boxid:'error',animate:false,width:650});
				else {
					update_file_block();
					// Message de succés
					TINY.box.show({html:"Le fichier a été généré avec succès !",boxid:'success',animate:false,width:650, closejs:function(){ }});
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
			'fileId':$("#pdf .actionable").attr("fileId")
			},
		
			function (msg) {

			// On rétablit le pointeur
			document.body.style.cursor = 'default';

				if (msg == false) TINY.box.show({html:"Le fichier n'a pas pu être effacé !",boxid:'error',animate:false,width:650});
				else {
					update_file_block();
				
					// Message de succés
					TINY.box.show({html:"Le fichier d'affectations a été effacé avec succès !",boxid:'success',animate:false,width:650, closejs:function(){ 
					}});
				}
			}
		);
	}

</script>



<div class="panel panel-default row">
	
	<!-- Header !-->
	<div class="row">
		<h4 class="panel-heading"><?php echo $page_title; ?></h4>
	</div>

	
	<div class="row">
	<div class="col-lg-12" style="overflow:auto">


<!-- Menu contextuel pour les affectations multiples !-->
<menu type="context" id="contextmenu">
	<menuitem id="add_affect" label="Ajouter une affectation" onclick="add_select();" icon="<?php echo base_url();?>images/icons/add_disabled.png"></menuitem>
	<menuitem id="suppr_affect" label="Supprimer une affectation" onclick="suppr_select();" icon="<?php echo base_url();?>images/icons/suppr.png"></menuitem>
</menu>

<!-- **************** TABLEAU *************** -->
	<?php if ($playlist_item['list']) : ?>
	<table id="inscrTab" class="tablesorter bootstrap-popup is_playable">

		<!--=========== Ligne des headers de colonne !========================-->
		<!-- Headers de colonne catégories d'instruments !-->
		<thead>
		<tr id="cat_head">
			<th style="width:80px;">&nbsp </th>
			<?php foreach ($cat_instru_list as $cat): ?>
				<th class="cat_header"
					id="cat_<?php echo $cat['name']?>"
					colspan="<?php echo sizeof($cat['list']); ?>"
					onclick="display_cat('<?php echo $cat['name']?>')"				
					>
					
					<?php echo ($cat['name']=="hors catégorie" ? $cat['name'] : $cat['name']);?> <!--<span onclick="display_cat('<?php echo $cat['name']?>')">+</span>-->
				</th>
			<?php endforeach; ?>
		</tr>
		
		<!-- Headers de colonne instruments !-->
		<tr id="instru_head">
			<th>&nbsp </th>
			<?php 
				$nbcol = 1;
				foreach ($cat_instru_list as $cat) {
					echo "<th class='hidden_".$cat['name']." hidden_cell' style='display:none'>&nbsp;</th>";
					foreach ($cat['list'] as $instru) {
						if($instru) echo '<th class="catelem_'.$cat['name'].'" idInstru="'.$instru.'">'.$this->instruments_model->get_instrument($instru).'</th>';
						// On enregistre le nombre de colonne du tableau
						$nbcol++;
					}
				}?>
		</tr>
		</thead>
		
		<tfoot>
		<!-- Footer de colonne instruments !-->
		<tr id="instru_head">
			<th>&nbsp </th>
			<?php 
				$nbcol = 1;
				foreach ($cat_instru_list as $cat) {
					echo "<th class='hidden_".$cat['name']." hidden_cell' style='display:none'>&nbsp;</th>";
					foreach ($cat['list'] as $instru) {
						if($instru) echo '<th class="catelem_'.$cat['name'].' soften" idInstru="'.$instru.'">'.$this->instruments_model->get_instrument($instru).'</th>';
						// On enregistre le nombre de colonne du tableau pour le colspan des pauses
						$nbcol++;
					}
				}?>
		</tr>
		</tfoot>
		
		
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
						<?php 
							echo $ref->titre;
							$titreSong = $ref->titre; 
						?>
					</td>
				
				
					<?php foreach ($cat_instru_list as $cat): ?>
						<?php echo "<td class='hidden_".$cat['name']." hidden_cell' style='display:none'></td>";
						foreach ($cat['list'] as $idInstru): ?>
						

							<?php if($idInstru) {
								echo '<td class="catelem_'.$cat['name'].'" contextmenu="contextmenu">';
								
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
									echo "<select style='color: red' id=\"".$ref->versionId."-".$idInstru."\">";
										echo "<option value='0'>&nbsp;</option>";
										foreach ($list_members as $member) {
											if ($member->idInstru1 == $idInstru || $member->idInstru2 == $idInstru) {
												echo "<option style='color: black' value='".$member->memberId."'>".$member->pseudo."</option>";
											}
										}
									echo "</select>";
								}
								else {
									// Multi affect
									for ($i=0; $i<sizeof($affected_pseudo); $i++) {
										// On remplit le select des noms des inscrits sur ce morceau
										echo "<select style='color: red' id=\"".$ref->versionId."-".$idInstru."-".$i."\">";
											echo "<option value='0'>&nbsp;</option>";
											foreach ($list_members as $member) {
												if ($member->idInstru1 == $idInstru || $member->idInstru2 == $idInstru) {
													if ($affected_pseudo[$i] == $member->pseudo) echo "<option value='".$member->memberId."' selected>".$member->pseudo."</option>";
													else echo "<option style='color: black' value='".$member->memberId."'>".$member->pseudo."</option>";
												}
											}
										echo "</select><br>";
									}
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
							?>
							
							
							</td>
						<?php endforeach; ?>
					<?php endforeach; ?>
				<?php endif; ?>
			</tr>
		<?php endforeach; ?>
	</tbody>
	
</table>
</div>


</div> <!-- On ferme le spécial content !-->


<br>



<br>

<?php else:?>
	<div class="main_block small_block_alert"><p>Pas de playlist sélectionnée.</p>
	</div>
<?php endif; ?>


<!--========================= Tableau de recap par musicien =========================-->
<div class="content">

<div class="main_block">
	<div id="manage_content" class="block_content">	
		<div class="block_head">
			<h3 id="manage_title" class="block_title">Gérer les affectations de la jam : <?php echo $page_title; ?></h3>
			<hr>
		</div>
		
	
		<div style="display: inline-flex">
				
			<div>
				<!------ Tableau de recap par musicien ------>
				<div class="small_block_list_title soften">Liste des affectations <span class="soften"><small>(<?php echo sizeof($list_members); ?>)</small></span></div>
				<div id="affect_list">
					<table id="affectations" class="tablesorter" cellspacing="0">
						<thead>
							<tr>
								<th>Cat1</th>
								<th>Instrument</th>
								<th>Pseudo</th>
								<th>Nb morceaux</th>
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
											echo '<span style="display:none">'.$instru_cat[$instru_list[$tmember->idInstru1 - 1]['categorie']-1]['view_order'].'</span>';
											echo '<img style="height: 16px;" src="'.base_url().'images/icons/'.$instru_cat[$instru_list[$tmember->idInstru1 - 1]['categorie']-1]['iconURL'].'" title="'.$instru_cat[$instru_list[$tmember->idInstru1 - 1]['categorie']-1]['name'].'">';
										echo '</td>';
										echo '<td>'.$instru_list[$tmember->idInstru1 - 1]['name'].'</td>';
										echo '<td class="pseudo" idMember="'.$tmember->memberId.'"><b>'.$tmember->pseudo.'</b></td>';
										echo '<td class="count number" style="font-size:120%; font_weight:bold; text-align:center">0</td>';
									echo '</tr>';
								}
							?>
						</tbody>
					</table>
				</div>
			</div>
			
			
			<div style="padding-left:40px">
				<br><br>
				<!----- HIDDEN_SELECT + INPUT BUTTON ------>
				<?php echo form_open('jam/affect/'.$jam_item['slug']) ?>
				
					<!-- Obligé pour forcer le push de la multipl select !-->
					<div style="display:none">
						<input id="titre" type="input" name="title" value="bidon"/>
						<?php echo form_error('title'); ?>
					</div>	
				
					<select id="hidden_select" name="affect_list[]" multiple style="display:none">
						<?php
							// On remplit la hidden_select avec la liste des affectations
							foreach ($affectations as $affect_elem) {
								echo "<option versionId='".$affect_elem['versionId']."'idInstru='".$affect_elem['instruId']."' selected>".$affect_elem['versionId']." - ".$affect_elem['instruId']." - ".$affect_elem['memberId']."</option>";
							}
						?>
					</select>
					
					<!-- Affectations visibles -->
					<label for="visibleCb">Affectations visibles </label>
					<input id="visibleCb" name="visibleCb" style="vertical-align: bottom;" type="checkbox" <?php if($jam_item['affectations_visibles']) echo "checked"; ?>/>
					<br><br>
					
					<!----- FILE BLOCK ----->
					<div id="file_block" >
						<div>
							<div class="small_block_list_title soften">Fichier récapitulatif</div>
							<div class="small_block_info file_list" style="text-align:left; margin:inherit;">
								<ul>
									<li class="admin_item" id="pdf">
										<!-- Nom de fichier ou texte -->
										<span></span>
										<!-- Taille fichier -->
										<small><span id="file_size" class="numbers soften"></span></small>
										<!-- Generate -->
										<a href="javascript:generate_affect_file()" class="ui_elem action_icon soften clickableBtn" style="width:85px">
											générer pdf <img class="action_icon" style="height: 12px; vertical-align:middle;" src="/images/icons/gear.png" alt="Générer">
										</a>
										<!-- Wait block -->
										<div id="wait_block" style="display:none"><img class="action_icon" style="height: 14px; vertical-align:middle; margin-right:5px;" src="/images/icons/wait.gif"><small>création du pdf...</small></div>
									</li>
								</ul>
							</div>
						</div>
					</div>
					<br>
					
					
					<!-- Boutton !-->
					<input class="button" type="submit" name="submit" value="Modifier affectations" />
					
				</form>
			</div>
			
			
		</div>
	</div>
</div>