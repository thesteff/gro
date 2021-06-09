<!-- Editeur html -->
<script src="<?php echo base_url();?>ressources/ckeditor/ckeditor.js"></script>

<script type="text/javascript">

	$(function() {
		$("#date_repet").datepicker({
			dateFormat: "dd/mm/yy"
		});
	});

	$(document).ready(function() {
	
		$selected_item = -1;
	
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
		
		$("#inscrTab :first-child").addClass( "dark_bg" );
		
		// td des titre de morceaux
		/*$("#inscrTab tr td:first-child").each(function() {
			setListElemBehavior($(this),"#inscrTab tr td:first-child",'#CC2900','#FFC0B2','black','white');
		});*/

		
		// On ferme les catégories qui ne concernent pas l'utilisateur en parcourant tous les header
		$cat_header = $("#inscrTab tr:first-child").children();
		$cat_header.each(function(index) {
			if ($(this).text().trim() != $('#instru_cat1').text() && $(this).text().trim() != $('#instru_cat2').text()) {
				display_cat($(this).text().trim())
			}
		});
		
		// On vide les champs de saisie sur un reload
		resetForms();
		$('#select_repet').val("-1");
		
		
		// On remplit le CKEditor
		CKEDITOR.instances.editor1.setData($("#text_tab").html());
		
	});
	
	
	function resetForms() {
		for (i = 0; i < document.forms.length; i++) {
			document.forms[i].reset();
		}
	}
	
	/**** SET_SELECT  *****/
	function set_select(index) {
		$pos = Number(index) + 3;
		//alert($pos);
		if ($selected_item >= 0) $("#inscrTab tbody tr:nth-child("+$selected_item+")").removeClass("selected");
		$selected_item = $pos;
		$("#inscrTab tbody tr:nth-child("+$selected_item+")").addClass("selected");
	}
	
	
		
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
	
	
	/* ******************  Gestion des sections admin    ************************/
	/* **************************************************************************/
	
	// On gère l'ouverture et fermeture de section
	function trig_admin_section($target) {
		$("#admin_"+$target+"_item").hasClass("active") ? $("#admin_"+$target+"_item").removeClass("active") : $("#admin_"+$target+"_item").addClass("active");
		$("#admin_"+$target+"_section").css("display") == "block" ?	$("#admin_"+$target+"_section").css("display", "none") : $("#admin_"+$target+"_section").css("display", "block");
	}
	
	
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
	
	// Actualise l'affichage de l'édition d'une répétition
	function get_repetition() {		
	
		// On récupère le block répêt affiché avec l'id selectionné
		$repetId = $("#select_repet :selected").val();
		$repet = $("#repet_block > div[id='"+$repetId+"']");
		
		// On change le label du bouton submit en fonction de la selection (ajout ou modif item)
		if ($repetId == -1) {
			$("#admin_submit").prop("value","Ajouter");
			$("#suppr_icon").css("display","none");
		}
		else {
			$("#admin_submit").prop("value","Modifier");
			$("#suppr_icon").css("display","inline-block");
		}
		
		// On récupère le block répêt édité
		//alert(typeof($repet.html()));
		
		// On actualise la catégorie
		if (typeof($repet.children("h4").attr("id")) == 'undefined') $("#cat_repet").val(-1);
		else $("#cat_repet").val(  $repet.children("h4").attr("id")  );
		// On actualise le texte
		if (typeof($repet.html()) != "undefined") CKEDITOR.instances.editor2.setData($repet.children(".repet_txt").html());
		else CKEDITOR.instances.editor2.setData("");
		// On actualise la date_repet
		$("#date_repet").val(   $repet.find("h5").attr("value")   );
		// On actualise l'heure de début et defin
		$("#time_repet").val(   $repet.find("#heure_debut").html()   );
		$("#fin_repet").val(   $repet.find("#heure_fin").html()   );
		// On actualise le lieu
		$("#lieu_repet").val( $repet.find(".lieu").html() );

	}
	
	// Ajouter une répétition
	function add_repet() {

		
		// Requète ajax au serveur
		$.post("<?php echo site_url(); ?>/ajax/add_repet",
		
			{
			'slugJam':'<?php echo $jam_item['slug'] ?>',
			'login':'<?php echo $this->session->userdata('login')?>',
			'cat_repet': $("#cat_repet").val(),
			'date_repet': $("#date_repet").val(),
			'heure_debut': $("#time_repet").val(),
			'heure_fin': $("#fin_repet").val(),
			'lieu_label': $("#lieu_repet").val(),
			'text':CKEDITOR.instances.editor2.getData(),
			'repet_id': $("#select_repet").val()
			},
	
			function (msg) {
			
				// Le lieu spécifié n'est pas présent dans la base
				if (msg == "lieu_not_found") {
					$txt = "<p>Le lieu spécifié n'est pas présent dans la base de données.<br> Voulez-vous le créer ?</p>"
					$txt += "<p style='text-align:center'><input type='button' value='valider' onclick='javascript:create_location_box(\""+$("#lieu_repet").val()+"\")'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='button' value='annuler' onclick='javascript: TINY.box.show({html:\"La répétition n&rsquo;a pas été créée !\",boxid:\"error\",animate:false,width:650})' ></p>";
					TINY.box.show({html:$txt,boxid:'confirm',animate:true,width:650});
				}
				
				// La répétition a été ajoutée
				else if (msg == "added") TINY.box.show({html:"La répétition a été ajoutée.",boxid:'success',animate:false,width:650, closejs:function(){location.reload();}});
				
				// La répétition a été modifiée
				else if (msg == "updated") TINY.box.show({html:"La répétition a été mise à jour.",boxid:'success',animate:false,width:650, closejs:function(){location.reload();}});
			}
		);
	}
	
	
	// Formulaire de création de lieu
	function create_location_box($lieu_name) {
	
		$html = "<p><b><u>Ajouter un lieu</u></b></p>";
		$html += "<div class='formLayout'>";
		$html += "<label>Nom</label><input id='lieu_name' size='32' value='"+$lieu_name+"'><br>";
		$html += "<label>Adresse</label><textarea id='lieu_adresse' cols='32' rows='2' style='resize:none'></textarea><br>";
		$html += "<label>Web</label><input id='lieu_web' size='32' ><br>";
		$html += "</div>";
		$html += "<p style='text-align:center'><input type='button' value='ajouter' onclick='javascript:create_location()'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='button' value='annuler' onclick='javascript: TINY.box.show({html:\"La répétition n&rsquo;a pas été créée !\",boxid:\"error\",animate:false,width:650})' ></p>";
		TINY.box.show({html:$html,boxid:'confirm',animate:false,width:650});
	
	}
	
	// Création du lieu
	function create_location() {
	
		//alert($("#lieu_adresse").val());
		$lieu_name = $("#lieu_name").val()
		
		
		// Requète ajax au serveur
		$.post("<?php echo site_url(); ?>/ajax/add_location",
		
			{
			'name': $("#lieu_name").val(),
			'adresse': $("#lieu_adresse").val(),
			'web': $("#lieu_web").val()
			},
	
			function (msg) {
			
				if (msg == "success") TINY.box.show({html:"Le lieu a été ajouté à la base de donnée.",boxid:'success',animate:false,width:650, closejs:function(){ $("#lieux").append("<option value='"+$lieu_name+"'>"+$lieu_name+"</option>"); } });
				else TINY.box.show({html:"Le lieu est déjà présent dans la base de donnée !",boxid:'error',animate:false,width:650});
			}
		);
	}
	
	
	// Supprimer une répétition
	function suppr_repet() {
	
		$repetId = $("#select_repet :selected").val();
	
		// Popup confirm
		$confirm = "<p>Etes-vous sûr de vouloir supprimer cette répétition ?</p>";
		$confirm += "<p style='text-align:center'><input type='button' value='valider' onclick='javascript:suppr_repet_confirm("+$repetId+")'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='button' value='annuler' onclick='javascript:TINY.box.hide()'></p>";
		TINY.box.show({html:$confirm,boxid:'error',animate:true,width:650});

	}
	
	function suppr_repet_confirm($repetId) {
	
		// Requète ajax au serveur
		$.post("<?php echo site_url(); ?>/ajax/suppr_repet",
		
			{
			'login':'<?php echo $this->session->userdata('login')?>',
			'repetId':$repetId
			},
	
			function (msg) {
				if (msg != "success") TINY.box.show({html:msg,boxid:'error',animate:false,width:650});
				else TINY.box.show({html:"La répétition a été supprimée.",boxid:'success',animate:false,width:650, closejs:function(){location.reload();}});
			}
		);
	}
	
	
 </script>
 
<!-- Span caché pour obtenir les infos du membres -->
<span id="instru_cat1" style="display:none"><?php echo $instru_cat1; ?></span>
<span id="instru_cat2" style="display:none"><?php echo $instru_cat2; ?></span>

 
 <!-- Block infos -->
 <div class="main_block">
	<h3 class="block_title"><?php echo $page_title ?> : Tableau d'inscription</h3><br>
	<?php if ($jam_item["text_tab"] != "") : ?>
		<div class="small_message">
			<h4>infos</h4>
			<div id="text_tab"><?php echo $jam_item["text_tab"] ?>
			</div>
		</div>
	<?php endif; ?>
 </div>
 
 <!--------- Section d'admin pour le texte d'intro !----------->
<?php if ($this->session->userdata('admin') > 0) : ?>
	<!-- Barre pour ouvrir le cadre d'admin -->
	<div class="block_footer" style="margin-top:-10">
		<hr style="width:100%; margin:0;">
		<div class="soften">
			<a id="admin_info_item" href="javascript:trig_admin_section('info')" class="ui_elem soften"><img width="16" style="vertical-align: middle;" src="/images/icons/edit.png" alt="edit">  modifier</a>
		</div>
		<hr style="width:100%; margin:0; padding: 0 0 2 0;">
	</div>
<?php endif; ?>


<div id="admin_info_section" class="block" style="display:none; padding-bottom:0">
	<form action="javascript:update_text_tab()">
		<div>
			<textarea name="editor1" id="editor1">
			</textarea>
			<script>
				CKEDITOR.replace( 'editor1', { customConfig : 'config_medium.js' } );
				CKEDITOR.width="500";
			</script>
			<!-- Bouton !-->
			<div style="text-align:right;padding-bottom:0"><small><input id="admin_submit" class="button" type="submit" name="submit" value="Mettre à jour" /></small></div>
		</div>
	</form>
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
		<?php 
			$nbcol = 1;
			foreach ($cat_instru_list as $cat) {
				echo "<th class='tab_elem hidden_".$cat['name']." hidden_cell' style='display:none'>&nbsp;</th>";
				foreach ($cat['list'] as $instru) {
					if($instru) echo '<th class="tab_elem catelem_'.$cat['name'].'" idInstru="'.$instru.'">'.$this->instruments_model->get_instrument($instru).'</th>';
					// On enregistre le nombre de colonne du tableau
					$nbcol++;
				}
			}?>
	</tr>
	
	
	
	<!-- Ligne des morceaux !-->
	<?php foreach ($playlist_item['list'] as $key=>$ref): ?>
		<tr class="tab_elem <?php if ($ref->reserve_stage) echo "stage_elem";?>"
			idSong="<?php echo str_replace("'", "\'",$ref->idSong); ?>"
		>
		
			<!-- On gère les pauses !-->
			<?php if (str_replace("'", "\'",$ref->idSong) == -1) :?>
				<td colspan="<?php echo $nbcol; ?>"></td>
			<?php else : ?>
				<td onclick="update_player('<?php echo str_replace("'", "\'",$ref->idSong); ?>');  set_select('<?php echo $key; ?>');"
						idSong="<?php echo str_replace("'", "\'",$ref->idSong); ?>">
					<?php echo $ref->titre;
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
			<?php endif; ?>				
		</tr>
	<?php endforeach; ?>
	
	
</table>
</div>

</div> <!-- On ferme le spécial content !-->


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
	<div class="block_left" style="width:52%;">

		<h3 class="block_title">Ordre des choix</h3>

		<?php echo form_open('jam/inscriptions/'.$jam_item['slug']) ?>
					
			<div id="choice_list" class="list_content bright_bg" style="height:90; width:450;">
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


	<!----------- DOCUMENTS ------------>
	<?php if ( (isset($playlist_item["infos"]["pdfURL"]) && $playlist_item["infos"]["pdfURL"] != "") ||
				(isset($playlist_item["infos"]["zipmp3URL"]) && $playlist_item["infos"]["zipmp3URL"] != "") ): ?>
		<div class="block" style="width:38%; float:right;">
			<h3 class="block_title">Documents</h3><br>
			<?php if (isset($playlist_item["infos"]["pdfURL"]) && $playlist_item["infos"]["pdfURL"] != "") : ?>
				<div class="small_message" id="message1">
					<h4>pdf</h4>
					<div><p>Pdf rassemblant tous les morceaux de la jam avec sommaire : </p><p><b><a class="numbers" href="<?php echo base_url()."ressources/jam/".$playlist_item["infos"]["pdfURL"]; ?>" target="_blanck"><?php echo $playlist_item["infos"]["pdfURL"]; ?></a></p>
					</div>
				</div>
			<?php endif; ?>
			<?php if (isset($playlist_item["infos"]["zipmp3URL"]) && $playlist_item["infos"]["zipmp3URL"] != "") : ?>
				<div class="small_message" id="message2">
					<h4>mp3</h4>
					<div><p>Zip rassemblant tous les morceaux de la jam au format mp3 :</p><p><b><a class="numbers" href="<?php echo base_url()."ressources/jam/".$playlist_item["infos"]["zipmp3URL"]; ?>" target="_blanck"><?php echo $playlist_item["infos"]["zipmp3URL"]; ?></a></b></p>
					</div>
				</div>
			<?php endif; ?>
		</div>
	<?php endif; ?>

</div>

<br style="clear:both">
<br>


<!----------- REPETITIONS  ------------>
<?php if ($repetitions != 0 ||  $this->session->userdata('admin') > 0) : ?>
<div class="block" id="repet_block">
	<h3 class="block_title">Répétitions</h3><br>
	
	<?php if ($repetitions != 0) : ?>
	<?php foreach ($repetitions as $ref) : ?>
		<div id="<?php echo $ref['id']; ?>" class="small_message" style="min-height:120">
		
			<!-- on affiche la catégorie -->
			<h4 id="<?php echo $ref['catId']; ?>"><?php echo $ref['name']; ?></h4>
			
			<div class="repet_infos" style="float:left">
				<h5 class="repet_date" value="<?php echo ($ref['date_debut_fr'] ? $ref['date_debut_fr'] : '??'); ?>"><?php echo $ref['date_debut_norm']; ?></h5>
				<div class="numbers soften" style="float:right; margin:-5 -3 0 0"><small><b><span id="heure_debut"><?php echo $ref['heure_debut'].'</span>&#10145;<span id="heure_fin">'.$ref['heure_fin']; ?></span></b></small></div>
				<!-- lieu !-->
				<p><b><span class="lieu" style="font-size:115%"><?php echo $ref['lieuName']; ?></span></b><br>
				<?php echo $ref['adresse']; ?><br>
				<a href="http://<?php echo $ref['web']; ?>" target="_blanck"><?php echo $ref['web']; ?></a></p>
			</div>
			
			<div class="repet_txt"><?php echo $ref['text']; ?></div>
		</div>
	<?php endforeach; ?>
	<?php endif; ?>
</div>


	<!----------- Section d'ADMIN ------------>
	<?php if ($this->session->userdata('admin') > 0) : ?>
		<!-- Barre pour ouvrir le cadre d'admin -->
		<div class="block_footer">
			<hr style="width:100%; margin:0;">
			<div class="soften">
				<a id="admin_repet_item" href="javascript:trig_admin_section('repet')" class="ui_elem soften"><img style="vertical-align: middle;" src="/images/icons/add.png" alt="add">  admin</a>
			</div>
			<hr style="width:100%; margin:0; padding: 0 0 2 0;">
		</div>
		
		<div id="admin_repet_section" class="block" style="display:none">
		
			<br>
			<!-- liste des répét + nouvelle répêt !-->
			<select id="select_repet" name="select_repet" onchange="get_repetition()">
				<option value="-1">Ajouter une répétition</option>
				<?php foreach ($repetitions as $ref): ?>
					<option value="<?php echo $ref['id']; ?>"><?php echo $ref['date_debut_norm'].' ~ '.$ref['lieuName']; ?></option>
				<?php endforeach ?>
			</select>
			<!-- suppr icon !-->
			<div id="suppr_icon" style="display:none"><a href="javascript:suppr_repet()"><img class="rollOverImg" style="vertical-align: middle; margin-left:6; width:16" src="/images/icons/x.png" alt="supprimer" title='supprimer'></a></div>
			<br><br>
			
			<form action="javascript:add_repet()">		
				<div id="edit_section" class="small_block_info" style="width:95%;">
				
					<!-- Category !-->
					<p style="margin-bottom:5">
						<select id="cat_repet" name="cat_repet">
							<option value="-1">générale</option>
							<?php foreach ($instru_cat as $cat): ?>
								<option value="<?php echo $cat['id']; ?>"><?php echo $cat['name']; ?></option>
							<?php endforeach ?>
						</select>
					</p>
					<hr style="margin-bottom:15">

					
					<div>
					
						<!-- Colonne de gauche -->
						<div style="width:35%; display:inline-block; vertical-align:top; padding-top:30">
						
							<!-- Date !-->
							<p style="margin-bottom:7">
								<label for="date_repet"><img style="vertical-align: middle; height: 14; margin-right:5" src="/images/icons/cal.png" alt="date"></label>
								<input id="date_repet" name="date_repet" size="10" style="text-align:center" value="" required autocomplete="off" />
							</p>
							
							<!-- Heure !-->
							<p style="margin-bottom:7">						
								<label for="time_repet"><img style="vertical-align: middle; height: 13; margin-right:5" src="/images/icons/time.png" alt="date"></label>
								<input id="time_repet" type="datetime" list="horaires" class="numbers" name="time_repet" size="5" style="text-align:center" value="" autocomplete="off" />
								<datalist id="horaires">
								<?php
									$h = 0;$m = 0;
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
								&#10145;
								<input id="fin_repet" type="datetime" list="horaires" class="numbers" name="fin_repet" size="5" style="text-align:center" value="" autocomplete="off"  />
								<datalist id="horaires">
								<?php
									$h = 0;$m = 0;
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
							</p>
							
							<!-- Lieu !-->
							<p style="margin-bottom:5">
								<label for="lieu_repet"><img style="vertical-align: middle; height: 16; margin-right:5" src="/images/icons/lieu.png" alt="lieu"></label>
								<input id="lieu_repet" type="input" name="lieu" size="28" list="lieux" placeholder="Lieu de la répétition" value="<?php echo set_value('lieu'); ?>" required autocomplete="off" />
								<datalist id="lieux">
									<?php foreach ($list_lieux as $lieu): ?>
										<option value="<?php echo htmlentities($lieu['nom']) ?>"><?php echo htmlentities($lieu['nom']); ?></option>
									<?php endforeach ?>
									<?php echo form_error('lieu'); ?>
								</datalist>
							</p>
						</div>
					
						<!-- Colonne de droite -->
						<div style="width:60%; display:inline-block">
							<textarea name="editor2" id="editor2">
							</textarea>
							<script>
								CKEDITOR.replace( 'editor2', { customConfig : 'config_light.js' } );
								CKEDITOR.width="500";
							</script>
						</div>					
					
					</div>
					
					<!-- Bouton !-->
					<input id="admin_submit" class="button" type="submit" name="submit" value="Ajouter" />
					
				</div>
			</form>
			
		</div>
	<?php endif; ?>
	
<?php endif; ?>


