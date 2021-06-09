

<!-- Tablesorter: required -->
<link rel="stylesheet" href="<?php echo base_url();?>ressources/tablesorter-master/css/theme.sand.css">
<script src="<?php echo base_url();?>ressources/tablesorter-master/js/jquery.tablesorter.js"></script>

<!-- Tablesorter: filter -->
<script src="<?php echo base_url();?>ressources/tablesorter-master/js/widgets/widget-filter.js"></script>

<!-- Tablesorter: pager -->
<link rel="stylesheet" href="<?php echo base_url();?>ressources/tablesorter-master/addons/pager/jquery.tablesorter.pager.css">
<script src="<?php echo base_url();?>ressources/tablesorter-master/js/widgets/widget-pager.js"></script>


<script type="text/javascript">

	/* ******************* Gestion des tableaux ****************/
	$(function() {
	
		$table1 = $( '#songlist' )
		.tablesorter({
			theme : 'sand',
			// Le tableau n'est pas triable
			headers: {'.titre, .artiste, .choeurs, .cuivres, .stage' : {sorter: false}
			},
			
			// initialize zebra and filter widgets
			widgets : [ "zebra", "filter", "pager" ],

			widgetOptions: {
				// output default: '{page}/{totalPages}'
				// possible variables: {page}, {totalPages}, {filteredPages}, {startRow}, {endRow}, {filteredRows} and {totalRows}
				pager_output: '{page}/{totalPages}',

				pager_removeRows: false,

				// include child row content while filtering, if true
				filter_childRows  : true,
				// class name applied to filter row and each input
				filter_cssFilter  : 'tablesorter-filter',
				// search from beginning
				filter_startsWith : false,
				// Set this option to false to make the searches case sensitive
				filter_ignoreCase : true
			}

		});
		
		// Pour activer la selection des tr
		$(".is_playable tbody tr").on("click", function() {
			// On déselectionne la tr précédente
			$(this).closest("tbody").find(".selected").removeClass("selected");
			// La tr devient selected
			$(this).addClass("selected");
			update_player($(this).attr("idSong"));
		});	


	});

	
	/* ******************* Gestion des sélections ****************/
	var select_id = 0;
	
	function select_song(id) {
		select_id = id;
    }
	
	
	/* ******************* Gestion des actions ****************/

	function add_break(apply_to_hidden = true) {
	
		// On créé et insère la nouvelle tr
		new_tr = "<tr id='list_elem-1' onclick='select_song(-1);' idSong='-1'><td style='line-height:0.7; background-color:#dddddd; text-align:center;' colspan='4'><small>-= <i>pause</i> =-</small></td></td></tr>";
		$("#left_list_body").append(new_tr);
		new_tr = $("#left_list_body tr:last");
			
		// On donne le comportement à la tr
		$selected_color = '#255625';
		$break_selected_color_bg = '#eeeeee';
		$over_color = '#851f00';
		$over_color_bg = 'white';

		
		new_tr.click(function() {
			// On deselectionne les autres éléments 'selected' de toutes les autres tables
			$temp_tr_list = $("table tbody > tr");
			
			$temp_tr_list.each(function(index) {
				if ($(this).hasClass('selected')) {
					$(this).removeClass('selected');
					this.style.color = this.old_color;
					// On parcourt les td pour changer le bgcolor
					$(this).children().each(function(index) {
						this.style.backgroundColor = this.old_color_bg;
					});
				}
			});
			
			// L'élément cliqué devient 'selected'
			$(this).addClass('selected');
			this.style.backgroundColor = $break_selected_color_bg;
			this.style.color = $selected_color;
			// On parcourt les td pour changer le bgcolor
			$(this).children().each(function(index) {
				this.style.backgroundColor = $break_selected_color_bg;
			});
		});
		
		
		new_tr.mouseover(function() {
			if ( ! $(this).hasClass('selected')) {
				this.old_color = this.style.color;
				this.style.color = $over_color;
				// On parcourt les td pour changer le bgcolor
				$(this).children().each(function(index) {
					this.old_color_bg = this.style.backgroundColor;
					this.style.backgroundColor = "white"; //$over_color_bg;
				});
				
			}
			document.body.style.cursor = 'pointer';
		});
		new_tr.mouseout(function() {
			if ( ! $(this).hasClass('selected')) {
				this.style.color = this.old_color;
				// On parcourt les td pour changer le bgcolor
				$(this).children().each(function(index) {
					this.style.backgroundColor = this.old_color_bg;
				});
			}
			document.body.style.cursor = 'default';
		});
		
		// On ajoute l'élément au formulaire caché
		if (apply_to_hidden) $("#hidden_select").append("<option song_id='-1' selected>-1</option>");
		
	}
	
	
	/***************   MOVE_ELEM    *************/
	function move_elem(action, apply_to_hidden = true) {
		
		if (select_id == 0) return;

		if (action == "to_left") {
		
			// Si l'élément est déjà présent, on ne l'ajoute pas
			if ($("#left_list_table #list_elem"+select_id).length > 0) return;
			
			// On récupère les données de la selection
			selected_elem = $("#songlist #list_elem"+select_id);
			titre = selected_elem.children("td:first-child").html();
			auteur = selected_elem.children("td:nth-child(2)").html();
			choeurs = selected_elem.children("td:nth-child(3)").html();
			cuivres = selected_elem.children("td:nth-child(4)").html();

			
			// On créé et insère la nouvelle tr
			new_tr = "<tr id='list_elem"+select_id+"' onclick='update_player(\""+select_id+"\"); select_song("+select_id+");' idSong='"+select_id+"'><td style='background-color:#FFF5CE'><b>"+titre+"</b> <small>-<small> "+auteur+"</small></small></td>";
			new_tr += "<td style='background-color:#FFF5CE; text-align:center'>"+choeurs+"</td><td style='background-color:#FFF5CE; text-align:center'>"+cuivres+"</td><td style='background-color:#FFF5CE; text-align:center'><input type='checkbox' onchange='updateCB("+select_id+")' /></td></tr>";
			$("#left_list_body").append(new_tr);
			new_tr = $("#left_list_body tr:last")
			
			
			// On donne le comportement à la tr
			$selected_color = '#255625';
			$selected_color_bg = '#BEEFBE';
			$over_color = '#851f00';
			$over_color_bg = 'white';

			
			new_tr.click(function() {
				// On deselectionne les autres éléments 'selected' de toutes les autres tables
				$temp_tr_list = $("table tbody > tr");
				
				$temp_tr_list.each(function(index) {
					if ($(this).hasClass('selected')) {
						$(this).removeClass('selected');
						this.style.color = this.old_color;
						// On parcourt les td pour changer le bgcolor
						$(this).children().each(function(index) {
							this.style.backgroundColor = this.old_color_bg;
						});
					}
				});
				
				// L'élément cliqué devient 'selected'
				$(this).addClass('selected');
				this.style.backgroundColor = $selected_color_bg;
				this.style.color = $selected_color;
				// On parcourt les td pour changer le bgcolor
				$(this).children().each(function(index) {
					this.style.backgroundColor = $selected_color_bg;
				});
			});
			
			
			new_tr.mouseover(function() {
				if ( ! $(this).hasClass('selected')) {
					this.old_color = this.style.color;
					this.style.color = $over_color;
					// On parcourt les td pour changer le bgcolor
					$(this).children().each(function(index) {
						this.old_color_bg = this.style.backgroundColor;
						this.style.backgroundColor = "white"; //$over_color_bg;
					});
					
				}
				document.body.style.cursor = 'pointer';
			});
			new_tr.mouseout(function() {
				if ( ! $(this).hasClass('selected')) {
					this.style.color = this.old_color;
					// On parcourt les td pour changer le bgcolor
					$(this).children().each(function(index) {
						this.style.backgroundColor = this.old_color_bg;
					});
				}
				document.body.style.cursor = 'default';
			});
			
			

			// On barre l'original
			selected_elem.css('text-decoration','line-through');
			
			// On ajoute l'élément au formulaire caché
			if (apply_to_hidden) $("#hidden_select").append("<option song_id='"+select_id+"' stage='0' selected>"+select_id+" - 0 "+selected_elem.html()+"</option>");
			
			
		}
		else if (action == "to_right") {
			// Permet de sortir une ref de gauche en ayant une ref de droite selectionnée
			if (select_id != -1) {
				$("#left_list_table #list_elem"+select_id).remove();
				$("[song_id='"+select_id+"']").remove();
			}
			// On gère les pause multiples. Marche si la hidden_select est triée pareil que le la left_lit
			else {
				select_pos = $("#left_list_table .selected").index() + 1;
				$("#left_list_table .selected").remove();
				$("#hidden_select :nth-child("+select_pos+")").remove();
			}
			
			$("#songlist #list_elem"+select_id).css('text-decoration','none');
		}
		
		else if (action == "to_top") {
			selected_elem = $("#left_list .selected");
			var selected_index = 0;
			
			// S'il n'y a pas d'élément selectionné à gauche, on sort
			if (! selected_elem.length > 0) return;
			
			// On traite la liste affichée
			$list = $("#left_list_body").children();
			
			$list.each(function(index) {
				if ($(this).hasClass('selected')) {
					// Si l'élément selectionné est le premier, on sort de la boucle
					if (index == 0) return;
					selected_elem.insertBefore($("#left_list_body tr:eq("+(index-1)+")"));
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
			selected_elem = $("#left_list  .selected");

			// S'il n'y a pas d'élément selectionné à gauche, on sort
			if (! selected_elem.length > 0) return;
			
			// On traite la liste affichée
			$list = $("#left_list_body").children();
			$list.each(function(index) {
				if ($(this).hasClass('selected')) {
					selected_elem.insertAfter($("#left_list_body tr:eq("+(index+1)+")"));
					selected_index = index;
					return;
				}
			});
			
			// On traite la liste cachée
			hidden_elem = $("#hidden_select > option:eq("+selected_index+")");
			hidden_elem.insertAfter($("#hidden_select > option:eq("+(selected_index+1)+")"));
		}
		
		
		
		// On compte les pauses
		nb_break = $("#hidden_select [song_id='-1']").length;
		
		// On actualise le nombre de ref
		if (action == "to_right" || action == "to_left") {
			$("#nbSelect").html($("#hidden_select").children().length - nb_break);
		}
		
	}
	
	// Gestion d'un update de checkbox
	function updateCB(id) {
		// On récupère l'état de la CB (pas obligé mais pour sécu)
		$state = $("#left_list_body #list_elem"+id+" input[type=checkbox]").prop("checked") ? "1" : "0";
		// On récupère le titre
		$titre = $("#left_list_body #list_elem"+id+" td:first").html();
		// On modifie la hidden_select
		$("#hidden_select > option[song_id='"+id+"']").prop("stage",$state);
		$("#hidden_select > option[song_id='"+id+"']").html("<option song_id='"+id+"' stage='"+$state+"' selected>"+id+" - "+$state+" "+$titre+"</option>");
	}
	
	
	// Gestion d'un tri de liste
	function sort_list(id_list,type) {
		// On traite la liste affichée
		$("#"+id_list+" tr").sort(asc_sort).appendTo("#"+id_list);
		
		// On traite la liste cachée
		$("#hidden_select option").sort(alpha_special_sort).appendTo("#hidden_select");
	}
	
	
	// Fonctions de tri
	function asc_sort(a, b) {
		str1 = $(a).text().trim();
		str2 = $(b).text().trim();
		return (str2 < str1) ? 1 : -1;
	}
	function alpha_special_sort(a, b) {
		arr1 = $(a).text().split(' ');
		str1 = arr1[3];
		arr2 = $(b).text().split(' ');
		str2 = arr2[3];
		// On remonte les pauses
		if (arr1[0] == -1) return -1;
		else if (arr2[0] == -1) return 1;
		else return (str2 < str1) ? 1 : -1;
	}
	
	
	
	$(document).ready(function() {
		
		// On remplit la #left_list avec la hidden_list
		$("#hidden_select option").each(function() {
			select_id = $(this).attr("song_id");
			select_id == -1 ? add_break(false) : move_elem("to_left", false);
		});
		// On populate la checkbox du stage si besoin
		$("#hidden_select option").each(function() {
			if ($(this).attr("stage") == 1) {
				select_id = $(this).attr("song_id");
				$("#left_list_table #list_elem"+select_id+" :checkbox").prop("checked",true);
			}
		});
		
	});
	
</script>


<div class="main_block" style="display:flex; justify-content:center;">


	<!--  //////  BLOCK LEFT !-->
	<div class="block_content_left">	
	
		<h3 class="block_title"><?php echo $page_title; ?></h3>
		
		<?php echo form_open('playlist/update/'.$playlist['infos']['id']) ?>
		
			<div>
				<input type="input" name="title" size="35" placeholder="Titre de la playlist" value="<?php echo set_value('title', $playlist['infos']['title']); ?>" autofocus />
				<?php echo form_error('title'); ?>
			</div>
			
			<br />
			<!-- ///////  PLAYLIST CREEE !-->
			<!-- Affichage de nbRef -->	
			<div class="small_block_list_title soften" style="text-align:right;"><small><span class="soften">(<span id="nbSelect">0</span> réf.)</small></span></div>

			<div id="left_list" class="bright_bg" style="height:550; width:330; overflow:auto;">
				<table id="left_list_table" class="tablesorter-sand" cellspacing="0" style="margin:0">
					<thead>
						<tr>
							<th>Titre</th>
							<th width="10" style="text-align:center"><img style='height: 12;' title='choeurs' src='/images/icons/heart.png'></th>
							<th width="10" style="text-align:center"><img style='height: 16; margin:0 2' title='cuivres' src='/images/icons/tp.png'></th>
							<th width="10" style="text-align:center"><img style='height: 16;' src='/images/icons/metro.png' title='réservé au stage'></th>
						</tr>
					</thead>
					<tbody id="left_list_body">
					</tbody>
				</table>
			</div>
			
			<!-- Boutons de tri -->
			<div class="list_action_elem">
				<input type="button" class="flat_button" name="up" value="   up   " onclick="move_elem('to_top')"/>
				<input type="button" class="flat_button" name="down" value="  down   " onclick="move_elem('to_bottom')" />&nbsp;
				<input type="button" class="flat_button" name="alpha" value="abc" onclick="sort_list('left_list_body','alpha')" />&nbsp;&nbsp;&nbsp;
				<input type="button" class="flat_button" name="add" value="pause" onclick="add_break()" />
			</div>

			<!-- HIDDEN_SELECT -->
			<select id="hidden_select" name="song_list[]" multiple style="display:none">
				<?php
					////// On insère dans la hidden_select list la liste des morceaux correspondant au update
					foreach ($playlist['list'] as $ref) {
						echo "<option song_id='".$ref->morceauxId."' stage='".$ref->reserve_stage."' selected>".$ref->morceauxId." - ".$ref->reserve_stage." ".$ref->titre."</option>";
					}
				?>
			</select>
				
			<br />
			<input class="button" type="submit" name="submit" value="Modifier" />
		</form>	
	</div>
	
	
	<!--  //////  BLOCK CENTER !-->
	
	<div class="block_content_center" style="display:flex; align-items: center;">
		<div>
			<input type="button" style="margin:5 0;" name="remove" value=">>" onclick="move_elem('to_right')"/><br />
			<input type="button" name="add" value="<<" onclick="move_elem('to_left')" /><br /><br />
		</div>
	</div>
	
	
	<!--  //////  BLOCK RIGHT !-->
	
	<div style="margin-right:0; width:100%;">
	
		<!-- PAGER -->	
		<div class="pager">
			<small>
			<select class="pagesize" style="display:none;">
				<option value="25" selected>30</option>
			</select>
			&nbsp;&nbsp;&nbsp;
			<img src="/ressources/tablesorter-master/addons/pager/icons/first.png" class="first" alt="First" title="First page" />
			<img src="/ressources/tablesorter-master/addons/pager/icons/prev.png" class="prev" alt="Prev" title="Previous page" />
			<span class="pagedisplay"></span> <!-- this can be any element, including an input -->
			<img src="/ressources/tablesorter-master/addons/pager/icons/next.png" class="next" alt="Next" title="Next page" />
			<img src="/ressources/tablesorter-master/addons/pager/icons/last.png" class="last" alt="Last" title= "Last page" />
			</small>
		</div>
		
	
		<!-- Affichage de nbRef -->	
		<div class="small_block_list_title soften" style="text-align:right; margin-bottom:-13;"><small><span class="soften">(<span id="nbRef"><?php echo sizeof($songlist); ?></span> références)</small></span></div>

		<div>
			<!-- Affichage de la songlist -->		
			<table id="songlist" class="tablesorter focus-highlight is_playable" cellspacing="0">
				<thead>
					<tr>
						<th>Titre</th>
						<th>Artiste</th>
						<th width="10" style="text-align:center"><img style='height: 12;' src='/images/icons/heart.png'></th>
						<th width="10" style="text-align:center"><img style='height: 16; margin:0 2' src='/images/icons/tp.png'></th>
						<th width="10">Joué</th>
						<th width="54"><small>+ récent</small></th>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<th>Titre</th>
						<th>Artiste</th>
						<th style="text-align:center"><img style='height: 10;' src='/images/icons/heart.png'></th>
						<th style="text-align:center"><img style='height: 14; margin:0 2' src='/images/icons/tp.png'></th>
						<th>Joué</th>
						<th><small>+ récent</small></th>
					</tr>
				</tfoot>
				<tbody id="songlist_body">
					<?php foreach ($songlist as $song): ?>
						<tr id="list_elem<?php echo $song->idSong; ?>" idSong="<?php echo $song->idSong ?>">
							<td><?php echo $song->titre; ?></td>
							<td><?php echo $this->artists_model->get_artist($song->idArtiste); ?></td>
							<td style="text-align: center"><?php if ($song->choeurs == 1) echo "<span style='display:none'>1</span><img style='height: 12;' src='/images/icons/ok.png'>"; else echo "<span style='display:none'>0</span>"; ?></td>
							<td style="text-align: center"><?php if ($song->cuivres == 1) echo "<span style='display:none'>1</span><img style='height: 12;' src='/images/icons/ok.png'>"; else echo "<span style='display:none'>0</span>"; ?></td>
							<td style="text-align: center"><?php echo $this->songs_model->nbPlayed($song->idSong); ?></td>
							<td style="text-align: center"><?php
								$lastDate = $this->songs_model->lastTimePlayed($song->idSong);
								if ($lastDate) {
									$tempDate = date_create_from_format("Y-m-d",$lastDate);
									echo date_format($tempDate,"d/m/Y");
								}
								else echo "jamais";
							?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	</div>
	
</div>