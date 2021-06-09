<script type="text/javascript">

	$(document).ready(function() {
	
		$(".cat_header").mouseover(function() {
			document.body.style.cursor = 'pointer';
		});
		$(".cat_header").mouseout(function() {
			document.body.style.cursor = 'default';
		});
	
	
		// On remplit la choice_list en fonction de la hidden_select list (remplit par le php en cas d'update)
		$("#hidden_select option").each(function() {
			add_list_elem($(this).attr("idSong"), get_songTitle($(this).attr("idSong")), $(this).attr("idInstru"), get_instruName($(this).attr("idInstru")), false);
		});
	
	
		// On fixe le comportement des checkboxes
		$("input:checkbox").each(function() {	
		
			$(this).click(function() {
				
				// On récupère les infos du checkbox cliqué
				idSong = $(this).parent().parent().parent().children(":first-child").attr("idSong");
				nameSong = get_songTitle(idSong); //$(this).parent().parent().children(":first-child").html();
				idInstru = $(this).attr("idInstru");
				labelInstru = get_instruName(idInstru);
				//alert("idSong : "+idSong+"   idInstru : "+idInstru);
				
				
				// Comportement d'un checkbox
				if (this.checked) add_list_elem(idSong, nameSong, idInstru, labelInstru, true);	
				
				// On retire la song de la choice_list
				else {
					elemHTML = $("#choice_list div");
					//alert($("#choice_list div").html());
					elemHTML.each(function(index) {
						if ($(this).attr('idSong') == idSong && $(this).attr('idInstru') == idInstru) $(this).remove();
					});
					// On retire l'élément du formulaire caché
					$("#hidden_select [idSong='"+idSong+"'][idInstru='"+idInstru+"']").remove();

				}

			});
		});
		
		////////////////// Aspect du tableau   ///////////////////////:
		$("#inscrTab tr:even").addClass( "bright_bg" );
		$("#inscrTab theader :first-child").addClass( "dark_bg" );
		
		// tr des instruments
		$("#inscrTab tr:nth-child(2)").addClass( "dark_bg" );
		
		// td des titre de morceaux
		/*$("#inscrTab tr td:first-child").each(function() {
			setListElemBehavior($(this),"#inscrTab tr td:first-child",'#CC2900','#FFC0B2','black','white');
		});*/
		
		
		// On colore différemment les morceaux dédiés au stage
		$("#inscrTab .stage_elem").each(function() {
			if ($(this).hasClass("bright_bg")) $(this).addClass( "bright_stage_elem" );
			else $(this).addClass( "stage_elem" );
		});

		
		// On ferme les catégories qui ne concernent pas l'utilisateur en parcourant tous les header
		$cat_header = $("#inscrTab tr:first-child").children();
		$cat_header.each(function(index) {
			if ($(this).text().trim() != $('#instru_cat1').text() && $(this).text().trim() != $('#instru_cat2').text()) {
				display_cat($(this).text().trim())
			}
		});
		
	});
	
		
	/* *************** Gestion des ajouts/suppressions de list_elem *************/
	/* **************************************************************************/
	function add_list_elem(idSong, nameSong, idInstru, labelInstru, to_hidden) {
		// On créé la div insérée dans la choice_list
		elemHTML = "<div class='list_elem' idSong='"+idSong+"' idInstru='"+idInstru+"'>";   /* A COMPLETER !!!!!  //	onclick="select_song(<?php echo $ref->morceauxId; ?>);">*/
		elemHTML += "<span class='song_title'>"+nameSong+"</span>";
		elemHTML += "<span class='note'> - "+labelInstru+"</span>";
		elemHTML += "<br></div>";
		
		// On insère la div dans la choice list
		$("#choice_list").append(elemHTML);
		
		// On récupère l'objet jQuery pour lui ajouter la gestion des mouseEvents
		obj = $("#choice_list div:last")
		setListElemBehavior(obj);
		
		// On ajoute l'élément au formulaire caché
		if (to_hidden) $("#hidden_select").append("<option idSong='"+idSong+"' idInstru='"+idInstru+"' selected>"+idSong+" - "+idInstru+"</option>");
	}
	
	
	function suppr_list_elem() {
	}
	
	
	/* ****************** Gestion des actions to top et bottom ******************/
	/* **************************************************************************/
	function move_elem(action) {
	
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

 
 <!-- Block infos -->
 <div class="main_block">
	<h3 class="block_title"><?php echo $page_title ?> : Tableau d'inscription</h3><br>
	<div class="small_message" id="message1">
		<h4>générale</h4>
		<div><p>Bienvenue sur cette nouvelle page d'inscription!</p>
			<p>La première partie de <b>liste en vert est réservée aux stagiaires</b> sauf le lead que les stagiaires n'assureront pas. Pour les autres postes, vous pouvez vous y inscrire mais vous aurez moins de chance d'y être affecté.</p>
			<p>Noubliez pas de bien préciser votre ordre de préférence sur les inscriptions avec le tableau ci-contre.</p>
			<p>Encore merci pour toutes les propositions faites via le site. Toutes n'ont pas été retenues mais ce n'est que partie remise, nous conserverons bien au chaud cette liste !!</p>
			<p>Nous vous tiendrons au courant pour les affectations qui devraient avoir lieu début avril.</p>
			<p>A bientôt!</p>
		</div>
	</div>
 </div>
 
 <br>

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
				if($instru) echo '<th class="tab_elem catelem_'.$cat['name'].'" idInstru="'.$instru.'">'.$this->instruments_model->get_instrument($instru).'</th>';
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
			
			
			<?php foreach ($cat_instru_list as $cat) {
				echo "<td class='tab_elem hidden_".$cat['name']." hidden_cell' style='display:none'></td>";
				foreach ($cat['list'] as $idInstru) {
					if($idInstru) {
						echo '<td class="tab_elem catelem_'.$cat['name'].'">';
						
						// On affiche le pseudo du membre affecté si besoin
						if (true) {
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
								//if ($find) echo '<hr style="margin:inherit">';
							}
						}
						
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
									// Si l'inscription est effective, on coche la checkbox
									if ($set_checkbox && $inscriptions[$key]['pseudo'] == $member->pseudo) {
										echo '<input type="checkbox" name="" value="" idInstru="'.$idInstru.'"'.set_checkbox("top", "set_top",true).' />&nbsp';
										$is_set = true;
									}
									//echo getChoicePos($inscriptions[$key]['jam_membresId'],$key,$inscriptions).".".$inscriptions[$key]['pseudo']."</p>";
									echo $inscriptions[$key]['choicePos'].".".$inscriptions[$key]['pseudo'];
									echo "</p>";
								}
							}
							// Si il n'y a pas d'inscription, on affiche la checkbox décochée.
							if ($set_checkbox && !$is_set) echo '<p style="background-color:inherit"><input type="checkbox" name="" value="" idInstru="'.$idInstru.'"'.set_checkbox("top", "set_top").' /></p>';
						}

					}
					else echo '&nbsp';
					
					echo '</td>';
				}
			}?>			
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


<div>
	<!----------- BLOCK DE GAUCHE  ------------>
	<div class="block_left" style="width:42%;">

		<div class="block_content_left" >	
		
			<h3 class="block_title">Ordre des choix</h3>

			<?php echo form_open('jam/inscriptions/'.$jam_item['slug']) ?>
						
				<div id="choice_list" class="list_content bright_bg" style="height:90; width:350;">
				</div>
				
				<!-- Boutons de tri -->
				<div class="list_action_elem">
					<input type="button" class="flat_button" name="up" value="   up   " onclick="move_elem('to_top')"/>
					<input type="button" class="flat_button" name="down" value="  down   " onclick="move_elem('to_bottom')" />&nbsp;&nbsp;&nbsp;
				</div>
					
				<select id="hidden_select" name="choice_list[]" multiple style="display:none">
					<?php
						////// On insère dans la hidden_select list la liste des morceaux sur lequel le membre est inscrit
						$keys = searchForId($member->pseudo,$inscriptions,"pseudo");
						if (isset($keys)) {
							// Pour chaque référence, "idSong - idInstru"
							foreach ($keys as $key) {
								echo "<option idSong='".$inscriptions[$key]['idSong']."'idInstru='".$inscriptions[$key]['instruId']."' selected>".$inscriptions[$key]['idSong']." - ".$inscriptions[$key]['instruId']."</option>";
							}
						}
					?>
				</select>
				
				<br />
				<input class="button" type="submit" name="submit" value="  Inscription  " />
			</form>	
		</div>
		
		
		
	</div>


	<!----------- BLOCK DE DROITE  ------------>
	<div class="block_info" style="width:48%; float:right;">
		<h3 class="block_title">Infos</h3><br>
		<div class="small_message" id="message1">
			<h4>générale</h4>
			<div><p>Bienvenue sur cette nouvelle page d'inscription!</p>
				<p>La première partie de <b>liste en vert est réservée aux stagiaires</b> sauf le lead que les stagiaires n'assureront pas. Pour les autres postes, vous pouvez vous y inscrire mais vous aurez moins de chance d'y être affecté.</p>
				<p>Noubliez pas de bien préciser votre ordre de préférence sur les inscriptions avec le tableau ci-contre.</p>
				<p>Encore merci pour toutes les propositions faites via le site. Toutes n'ont pas été retenues mais ce n'est que partie remise, nous conserverons bien au chaud cette liste !!</p>
				<p>Nous vous tiendrons au courant pour les affectations qui devraient avoir lieu début avril.</p>
				<p>A bientôt!</p>
			</div>
		</div>
		<div class="small_message" id="message4">
			<h4>pdf</h4>
			<div><p>Pdf rassemblant tous les morceaux de la jam avec index : <br><a href="<?php echo base_url();?>ressources/pdf/jam/Spring Reggae Jam.pdf" target="_blanck">Spring Reggae Jam.pdf</a></p>
				<p>A bientôt!</p>
			</div>
		</div>
	</div>

</div>

<br style="clear:both">
<br>

<div class="block_info" style="display:inline-block; float:none; width:96%">
	<h3 class="block_title">Répétitions</h3><br>

	<div class="small_message" id ="message2">
		<h4>choeurs</h4>
		<div><p>Chanter en choeurs ne s'improvise que difficilement. Afin d'avoir une bonne cohésion vocale, vous êtes sollicités, choristes, à 2 répétitions les <b>dimanches 22 et 29 mars</b> au <b>680 Rue Aristide Berges 38330 Montbonnot-Saint-Martin</b> à partir de <b>14h30</b>.</p>
			<p>Notre chef de choeur (Maëva) sera intransigeante sur la nécessité d'être présent(e) à ces répétitions pour pouvoir monter sur scène le soir de la jam.</p>
		</div>
	</div>
	
	<div class="small_message" id ="message3">
		<h4>générale</h4>
		<div><p>Il y aura une répétition "générale" le <b>samedi 4 avril de 18h à 22h à la bobine dans le studio bleu</b>. Cette dernière n'est pas obligatoire et l'intérêt de ce rendez-vous est de mettre en place certains passages délicats du répertoire mais surtout de se rencontrer les uns les autres. Pour ceux qui appréhendent la jam, c'est le moment de tester votre titre !</p>
		</div>
	</div>
</div>