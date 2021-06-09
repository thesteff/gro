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
			update_player($(this).closest("tr").attr("idSong"));
		});
		
		
		////////////////// Aspect du tableau   ///////////////////////:
		
		$("#inscrTab").children().addClass( "dark_bg" );
		$("#inscrTab tbody :first-child").addClass( "dark_bg" );

	
		// On ferme les catégories qui ne concernent pas l'utilisateur en parcourant tous les header		
		$(".cat_header").each(function(index) {
			display_cat($(this).text().trim());
		});

		// On complête le tableau de récap (nombre de morceaux joués)
		init_count();
		
		
		/* *************** Gestion des ajouts/suppressions de list_elem   *************/
		
		// On permet de connaître la valeur précédente d'un select (utile pour actualiser le count)
		$("#inscrTab select").each(function() {
			$(this).data("prev",$(this).val());
		});

		// On définit le change sur un select
		$("#inscrTab select").on("change", function() {

			// On récupère l'id du membre (val de l'option selected)
			$idMember = $(this).val();	// 0 si on ne selectionne personne
			// On récupère l'id de la song
			$idSong = $(this).closest("tr").attr("idSong");
			// On récupère l'id d'instrument
			$tdIndex = $(this).closest("td").index()+1;    // On récupère la position de la td où se trouve le select
			$idInstru = $(this).closest("table").find("#instru_head th:nth-child("+$tdIndex+")").attr("idInstru");
			
			// On supprime l'ancienne selection de la hidden_select
			$("#hidden_select option[idSong*='"+$idSong+"']").each(function() {
				if ($(this).attr("idInstru") == $idInstru) $(this).remove();
			});
			
			// On ajoute l'élément au formulaire caché
			$("#hidden_select").append("<option idSong='"+$idSong+"' idInstru='"+$idInstru+"' selected>"+$idSong+" - "+$idInstru+" - "+$idMember+"</option>");
			
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
	function init_count($mode) {
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
	
	// Permet de retrouver un titre de morceau à partir de l'idSong
	function get_songTitle(idSong) {
		$songTitle = "";
		$songList = $("#inscrTab tr");
		$songList.each(function(index) {
			if (index > 1 && idSong == $(this).children(":first-child").attr("idSong"))
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
	
 </script>

 
<div class="block_list_title soften">Tableau d'affectation aux morceaux de la jam : <?php echo $page_title ?>
</div>

<div style="overflow:auto">
<table id="inscrTab" class="is_playable" style="width:100%;">

	<!--=========== Ligne des headers de colonne !========================-->
	<!-- Headers de colonne catégories d'instruments !-->
	<thead>
	<tr id="cat_head">
		<th style="width:80;">&nbsp </th>
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
	<!-- Headers de colonne instruments !-->
	<tr id="instru_head">
		<th>&nbsp </th>
		<?php 
			$nbcol = 1;
			foreach ($cat_instru_list as $cat) {
				echo "<th class='hidden_".$cat['name']." hidden_cell' style='display:none'>&nbsp;</th>";
				foreach ($cat['list'] as $instru) {
					if($instru) echo '<th class="catelem_'.$cat['name'].' soften" idInstru="'.$instru.'">'.$this->instruments_model->get_instrument($instru).'</th>';
					// On enregistre le nombre de colonne du tableau
					$nbcol++;
				}
			}?>
	</tr>
	</tfoot>
	
	
	<tbody>
	<!-- Ligne des morceaux !-->
	<?php foreach ($playlist_item['list'] as $ref): ?>
		<tr class="<?php if ($ref->reserve_stage) echo "stage_elem";?>"
			idSong="<?php echo str_replace("'", "\'",$ref->idSong); ?>"
		>
		
			<!-- On gère les pauses !-->
			<?php if (str_replace("'", "\'",$ref->idSong) == -1) :?>
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
							echo '<td class="catelem_'.$cat['name'].'">';
							
							// On affiche le pseudo du membre affecté si besoin
							if (true) {
								// On recherche l'id des affectés par rapport au titre de la ligne $titresong
								$keys = searchForId($titreSong,$affectations,"titre");
								$affected_pseudo = "";
								if (isset($keys)) {
									$find = false;
									// Pour chaque référence, on affiche le pseudo
									foreach ($keys as $key) {
										if($idInstru == $affectations[$key]['instruId']) {
											$find = true;
											//echo "<p class='affected'>".$affectations[$key]['pseudo']."</p>";
											$affected_pseudo = $affectations[$key]['pseudo'];
										}
									}
									//if ($find) echo $affected_pseudo.'<hr style="margin:inherit">';
								}
							}
							
							// On remplit le select des noms des inscrits sur ce morceau
							echo "<select class='affect_select' id=\"".$ref->morceauxId."-".$idInstru."\">";
								echo "<option value='0'>&nbsp;</option>";
								foreach ($list_members as $member) {
									if ($member->idInstru1 == $idInstru || $member->idInstru2 == $idInstru) {
										if ($affected_pseudo == $member->pseudo) echo "<option value='".$member->id."' selected>".$member->pseudo."</option>";
										else echo "<option style='color: black' value='".$member->id."'>".$member->pseudo."</option>";
									}
								}
							echo "</select>";
							
							
							
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

<br>

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
											echo '<img style="height: 16;" src="'.base_url().'images/icons/'.$instru_cat[$instru_list[$tmember->idInstru1 - 1]['categorie']-1]['iconURL'].'" title="'.$instru_cat[$instru_list[$tmember->idInstru1 - 1]['categorie']-1]['name'].'">';
										echo '</td>';
										echo '<td>'.$instru_list[$tmember->idInstru1 - 1]['name'].'</td>';
										echo '<td class="pseudo" idMember="'.$tmember->id.'"><b>'.$tmember->pseudo.'</b></td>';
										echo '<td class="count number" style="font-size:120%; font_weight:bold; text-align:center">0</td>';
									echo '</tr>';
								}
							?>
						</tbody>
					</table>
				</div>
			</div>
			
			
			<div style="text-align:center">
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
								echo "<option idSong='".$affect_elem['idSong']."'idInstru='".$affect_elem['instruId']."' selected>".$affect_elem['idSong']." - ".$affect_elem['instruId']." - ".$affect_elem['memberId']."</option>";
							}
						?>
					</select>
					
					<!-- Affectations visibles -->
					<label for="visibleCb">Affectations visibles </label>
					<input id="visibleCb" name="visibleCb" style="vertical-align: bottom;" type="checkbox" <?php if($jam_item['affectations_visibles']) echo "checked"; ?>/>
					
					<input class="button" type="submit" name="submit" value="Modifier affectations" />
					
				</form>
			</div>
			
			
		</div>
	</div>
</div>