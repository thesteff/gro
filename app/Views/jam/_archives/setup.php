<script type="text/javascript" src="<?php echo base_url();?>ressources/tableSorter/jquery.tablesorter.js"></script>
<link rel="stylesheet" href="<?php echo base_url();?>images/icons/sand/style.css" type="text/css" media="print, projection, screen" />

<script type="text/javascript">

	$(function() {
		$.tablesorter.defaults.widgets = ['zebra']; 
		$("#affectations").tablesorter();
	});

	$(document).ready(function() {
	
		$(".cat_header").mouseover(function() {
			document.body.style.cursor = 'pointer';
		});
		$(".cat_header").mouseout(function() {
			document.body.style.cursor = 'default';
		});
	
	
		// On remplit la choice_list en fonction de la hidden_select list (remplit par le php en cas d'update)
		/*$("#hidden_select option").each(function() {
			add_list_elem($(this).attr("idSong"), get_songTitle($(this).attr("idSong")), $(this).attr("idInstru"), get_instruName($(this).attr("idInstru")), false);
		});*/
	
	
		////////////////// Aspect du tableau   ///////////////////////:
		$("#inscrTab tr:even").addClass( "bright_bg" );
		$("#inscrTab :first-child").addClass( "dark_bg" );
		
		// tr des instruments
		$("#inscrTab tr:nth-child(2)").addClass( "dark_bg" );
		
		// td des titre de morceaux
		$("#inscrTab tr td:first-child").each(function() {
			setListElemBehavior($(this),"#inscrTab tr td:first-child",'#CC2900','#FFC0B2','black','white');
		});
	
		// On ferme les catégories qui ne concernent pas l'utilisateur en parcourant tous les header
		$cat_header = $("#inscrTab tr:first-child").children();
		$cat_header.each(function(index) {
			if ($(this).text().trim() != $('#instru_cat1').text() && $(this).text().trim() != $('#instru_cat2').text()) {
				display_cat($(this).text().trim())
			}
		});
		
		
		// td des noms de jammeurs selectionnables
		/*$("#inscrTab td:not(:empty)").each(function() {
			// p des noms de jammeurs
			$(this).
			setListElemBehavior($(this),"#inscrTab tr td:first-child",'#CC2900','#FFC0B2','black','white');
		});*/
		
		
		// On colore différemment les morceaux dédiés au stage
		$("#inscrTab .stage_elem").each(function() {
			if ($(this).hasClass("bright_bg")) $(this).addClass( "bright_stage_elem" );
			else $(this).addClass( "stage_elem" );
		});

		
	});
	
	/* *************** Gestion des ajouts/suppressions de list_elem *************/
	/* **************************************************************************/
	function change_elem(idSelect) {
	
		val = idSelect.split("-");
		idSong = val[0];
		idInstru = val[1];
		pseudo = $("#"+idSelect).val();
		
		// On ajoute l'élément au tableau de gestion
		//$("#affectations")

		// On supprime l'ancienne selection de la hidden_select
		$("#hidden_select option[idSong*='"+idSong+"']").each(function() {
			if ($(this).attr("idInstru") == idInstru) $(this).remove();
		});
		
		// On ajoute l'élément au formulaire caché
		$("#hidden_select").append("<option idSong='"+idSong+"' idInstru='"+idInstru+"' selected>"+idSong+" - "+idInstru+" - "+pseudo+"</option>");
	}
	
	
	/*function add_elem(idSelect) {
	
		val = idSelect.split("-");
		idSong = val[0];
		idInstru = val[1];
		pseudo = $("#"+idSelect).val();
		
		// On ajoute l'élément au tableau de gestion
		//$("#affectations")

		// On ajoute l'élément au formulaire caché
		$("#hidden_select").append("<option idSong='"+idSong+"' idInstru='"+idInstru+"' selected>"+idSong+" - "+idInstru+" - "+pseudo+"</option>");
	}*/
	
	
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
		$thInstru = $("#inscrTab tr:nth-child(2)");//.children(":nth-child(2)");
		$thInstru.children().each(function(index) {
			if (index > 1 && $(this).attr("idInstru") == idInstru)
				$instruName = $(this).html();
		});
		return $instruName;
	}
	
 </script>
 
<!-- Span caché pour obtenir les infos du membres -->
<span id="instru_cat1" style="display:none"><?php echo $instru_cat1; ?></span>
<span id="instru_cat2" style="display:none"><?php echo $instru_cat2; ?></span>

 
<div class="block_list_title soften">Tableau d'affectation aux morceaux de la jam : <?php echo $page_title ?>
</div>

<div style="overflow:auto">
<table id="inscrTab" style="width:100%;">

	<!--=========== Ligne des headers de colonne !========================-->
	<!-- Headers de colonne catégories d'instruments !-->
	<tr>
		<th style="width:80;">&nbsp </th>
		<?php foreach ($cat_instru_list as $cat): ?>
			<th class="tab_elem cat_header"
				id="cat_<?php echo $cat['name']?>"
				colspan="<?php echo sizeof($cat['list']); ?>"
				onclick="display_cat('<?php echo $cat['name']?>')"				
				>
				
				<?php echo ($cat['name']=="hors catégorie" ? $cat['name'] : $cat['name']);?> <!--<span onclick="display_cat('<?php echo $cat['name']?>')">+</span>-->
			</th>
		<?php endforeach; ?>
	</tr>
	
	<!-- Headers de colonne instruments !-->
	<tr>
		<th>&nbsp </th>
		<?php foreach ($cat_instru_list as $cat) {
			echo "<th class='tab_elem hidden_".$cat['name']." hidden_cell' style='display:none'>&nbsp;</th>";
			foreach ($cat['list'] as $instru) {
				if($instru) echo '<th class="tab_elem catelem_'.$cat['name'].'" idInstru="'.$instru.'">'.$this->instruments_model->get_instrument_name($instru).'</th>';
			}
		}?>
	</tr>
	
	
	
	<!-- Ligne des morceaux !-->
	<?php foreach ($playlist_item['list'] as $ref): ?>
		<tr class="tab_elem <?php if ($ref->reserve_stage) echo "stage_elem";?>">
			<td class="dark_bg"
				<?php if ($this->session->userdata('logged') == true) : ?>
					onclick="update_player('<?php echo str_replace("'", "\'",$ref->idSong); ?>')"
					idSong="<?php echo str_replace("'", "\'",$ref->idSong); ?>"
				<?php endif; ?>
			>
				<?php echo $ref->titre ;
					$titreSong = $ref->titre; 
				?>
			</td>
			
			
			<?php foreach ($cat_instru_list as $cat): ?>
				<?php echo "<td class='tab_elem hidden_".$cat['name']." hidden_cell' style='display:none'></td>";
				foreach ($cat['list'] as $idInstru): ?>
				
				
				
					<?php if($idInstru) {
						echo '<td class="tab_elem catelem_'.$cat['name'].'">';
						
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
						echo "<select style='color: red' id=\"".$ref->morceauxId."-".$idInstru."\" onchange='change_elem(\"".$ref->morceauxId."-".$idInstru."\")'>";
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
					
					
					// On recherche l'id des affectés par rapport au titre de la ligne $titresong
					/*$keys = searchForId($titreSong,$affectations,"titre");
					$affected_pseudo = "";
					if (isset($keys)) {
						// Pour chaque référence, on préselectionne le pseudo dans le select et on l'insère dans la hidden_list
						foreach ($keys as $key) {
							if($idInstru == $affectations[$key]['instruId']) {
								$affected_pseudo = $affectations[$key]['pseudo'];
							}
						}
					}*/
					?>
					
					
					</td>
				<?php endforeach; ?>
			<?php endforeach; ?>
		</tr>
	<?php endforeach; ?>
	
	
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
	
	
	// Retourne la position d'un choix ($key) de membre ($idJamMembre)
	/*function getChoicePos($idJamMembre, $key, $inscr) {
		$index = 0;
		$pos = 0;
		$find = false;
		while (!$find && $index < 200 && $index < sizeof($inscr)) {
			if ($inscr[$index]['jam_membresId'] == $idJamMembre) $pos++;
			if ($index == $key) $find = true;
			$index++;
		}
		return $pos;
	}*/
	
	
	
	/*foreach ($inscriptions as $inscr) {
		echo "Sur ".$inscr['titre']." à l'instrument ".$inscr['name']." c'est ".$inscr['pseudo']." qui joue.<br>";
		//echo $inscr['pseudo']." joue de ".$inscr['name']." sur le morceau ".$inscr['titre']."<br>";
	}*/
	
	//$key = searchForId('Naturality',$inscriptions); //array_search('steff',$inscriptions);
	//echo $inscriptions[$key]['pseudo'].'//<br>';
	
	/*echo "<br>===============<br>";
	foreach ($playlist_item['list'] as $ref) {
		$keys = searchForId($ref->titre,$inscriptions);
		if (isset($keys)) {
			foreach ($keys as $key) {
				echo $ref->titre." :: ".$inscriptions[$key]['pseudo']."<br>";
			}
		}
	}*/
?>	

<br>

<!--========================= Formulaire =========================-->
<div class="content">

<div class="main_block">
	<div id="manage_content" class="block_content">	
		<div class="block_head">
			<h3 id="manage_title" class="block_title">Gérer les affectations de la jam : <?php echo $page_title; ?></h3>
			<hr>
		</div>
		
		<!-- Affichage des affectations -->
		<div class="small_block_list_title soften">Liste des affectations <span class="soften"><small>(<?php echo sizeof($list_members); ?>)</small></span></div>
		<div id="affect_list">
			<table id="affectations" class="tablesorter" cellspacing="0">
				<thead>
					<tr>
						<th>Instrument</th>
						<th>Pseudo</th>
						<th>Nb morceaux</th>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<th>Instrument</th>
						<th>Pseudo</th>
						<th>Nb morceaux</th>
					</tr>
				</tfoot>
				<tbody>
					<?php 
					/*	foreach ($list_members as $tmember) {
							echo '<tr>';
								echo '<td class="selector"><input type="checkbox"/></td>';
								echo '<td>'.$tmember->pseudo.'</td>';
								echo '<td>'.$tmember->admin.'</td>';
								echo '<td>'.$instru_cat[$instru_list[$tmember->idInstru1 - 1]['categorie']-1]['name'].'</td>';
								echo '<td>'.$instru_list[$tmember->idInstru1 - 1]['name'].'</td>';
								echo '<td>'.$instru_list[$tmember->idInstru2 - 1]['name'].'</td>';
								echo '<td class="email_used">'.$tmember->email.'</td>';
								echo '<td>'.$tmember->mobile.'</td>';
								echo '<td><input type="checkbox" disabled ';
									if ($tmember->benevole == '1') echo 'checked=""';
								echo 'onclick="return false;" /></td>';
							echo '</tr>';
						}
					*/?>
				</tbody>
			</table>
		</div>
		
		
		<?php echo form_open('jam/setup/'.$jam_item['slug']) ?>
			<select id="hidden_select" name="affect_list[]" multiple style="display:none">
				<?php
					// On remplit la hidden_select avec la liste des affectations
					foreach ($affectations as $affect_elem) {
						echo "<option idSong='".$affect_elem['idSong']."'idInstru='".$affect_elem['instruId']."' selected>".$affect_elem['idSong']." - ".$affect_elem['instruId']." - ".$affect_elem['memberId']."</option>";
					}
				?>
			</select>
			<input style="margin-right:80" class="right button" type="submit" name="submit" value="Modifier affectations" />
		</form>
		
				
	</div>
</div>