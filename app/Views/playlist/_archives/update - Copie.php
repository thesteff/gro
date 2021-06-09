<script type="text/javascript">
		
	/* ******************* Gestion des sélections ****************/
	var select_id = 0;
	
	function select_song(id) {
		select_id = id;
    }
	
	/* ******************* Gestion des actions ****************/

	function move_elem(action) {
		
		if (select_id == 0) return;

		if (action == "to_left") {
		
			// Si l'élément est déjà présent, on ne l'ajoute pas
			if ($("#left_list > #list_elem"+select_id).length > 0) return;
			
			// Sinon, on le clone et on le déselectionne
			selected_elem = $("#right_list > #list_elem"+select_id);
			new_item = selected_elem.clone(true);
			new_item.removeClass('selected');
			new_item.css("background-color","initial");
			new_item.css("color","initial");
			new_item.appendTo($("#left_list"));
			
			// On barre l'original
			selected_elem.css('text-decoration','line-through');
			
			// On ajoute l'élément au formulaire caché
			$("#hidden_select").append("<option song_id='"+select_id+"' selected>"+select_id+" - "+selected_elem.html()+"</option>");
		}
		
		
		else if (action == "to_right") {
			$("#left_list > #list_elem"+select_id).remove();
			$("[song_id='"+select_id+"']").remove();
			// On rétabli le style de l'original
			$("#right_list > #list_elem"+select_id).css('text-decoration','none');
		}
		
		
		else if (action == "to_top") {
			selected_elem = $("#left_list > .selected");
			var selected_index = 0;
			
			// S'il n'y a pas d'élément selectionné à gauche, on sort
			if (! selected_elem.length > 0) return;
			
			// On traite la liste affichée
			$list = $("#left_list").children();
			$list.each(function(index) {
				if ($(this).hasClass('selected')) {
					// Si l'élément selectionné est le premier, on sort de la boucle
					if (index == 0) return;
					selected_elem.insertBefore($("#left_list > div:eq("+(index-1)+")"));
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
			selected_elem = $("#left_list > .selected");

			// S'il n'y a pas d'élément selectionné à gauche, on sort
			if (! selected_elem.length > 0) return;
			
			// On traite la liste affichée
			$list = $("#left_list").children();
			$list.each(function(index) {
				if ($(this).hasClass('selected')) {
					selected_elem.insertAfter($("#left_list > div:eq("+(index+1)+")"));
					selected_index = index;
					return;
				}
			});
			
			// On traite la liste cachée
			hidden_elem = $("#hidden_select > option:eq("+selected_index+")");
			hidden_elem.insertAfter($("#hidden_select > option:eq("+(selected_index+1)+")"));
		}
		
	}
	
	
	// Gestion d'un tri de liste
	function sort_list(id_list,type) {
		// On traite la liste affichée
		$("#"+id_list+" div").sort(asc_sort).appendTo("#"+id_list);
		
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
		str1 = $(a).text().trim();
		str1 = str1.substr(str1.indexOf('-')+1);
		str2 = $(b).text().trim();
		str2 = str2.substr(str2.indexOf('-')+1);
		return (str2 < str1) ? 1 : -1;       
	}
	
	
	$(document).ready(function() {
		
		// On actualise l'état de la hidden list et de la liste originale (right)
		$("#left_list > .list_elem").each(function(index) {
			// On barre l'original
			var temp_id = $(this).attr('id').substr(9);
			selector = "#list_elem"+temp_id;
			$("#right_list > "+selector).css('text-decoration','line-through');
			// On ajoute l'élément dans la hidden list
			$("#hidden_select").append("<option song_id='"+temp_id+"' selected>"+temp_id+" - "+$("#right_list > "+selector).html()+"</option>");
		});
		
	});
	
 </script>


<div class="main_block" style="display:flex; justify-content:center;">

	<!--  //////  BLOCK LEFT !-->
	
	<div class="block_content_left">	
	
		<h3 class="block_title">Modifier une playlist</h3>

		<?php echo form_open('playlist/update/'.$playlist_item['infos']['slug']) ?>
		
			<div>
				<input type="input" name="title" size="45" value="<?php echo $playlist_item['infos']['title'] ?>" autofocus />
				<?php echo form_error('title'); ?>
			</div>
			
			<br />
			<div id="left_list" class="list_content bright_bg" style="height:550; width:350;">
				<?php foreach ($playlist_item['list'] as $ref): ?>
					<div class="list_elem" id="list_elem<?php echo $ref->morceauxId; ?>" onclick="select_song(<?php echo $ref->morceauxId; ?>);">
						<span class="song_title"><?php echo $ref->titre ?></span>
						<span class="note">- <?php echo $this->artists_model->get_artist($ref->idArtiste); ?></span>
						<br />
					</div>
				<?php endforeach; ?>
			</div>
			
			<!-- Boutons de tri -->
			<div class="list_action_elem">
				<input type="button" class="flat_button" name="up" value="   up   " onclick="move_elem('to_top')"/>
				<input type="button" class="flat_button" name="down" value="  down   " onclick="move_elem('to_bottom')" />&nbsp;&nbsp;&nbsp;
				<input type="button" class="flat_button" name="alpha" value="abc" onclick="sort_list('left_list','alpha')" />
			</div>
				
			<select name="song_list[]" id="hidden_select" style="display:none" multiple>
			</select>
			
			<br />
			<input class="button" type="submit" name="submit" value="Modifier" />
		</form>	
	</div>
	
	
	<!--  //////  BLOCK CENTER !-->
	
	<div class="block_content_center" style="display:flex; align-items: center;">
		<div>
			<input type="button" style="margin:5 0;" name="remove" value=">>" onclick="move_elem('to_right')"/><br />
			<input type="button" name="add" value="<<" onclick="move_elem('to_left')" />
		</div>
	</div>
	
	
	<!--  //////  BLOCK RIGHT !-->
	<div class="block_content_right" style="margin-right:0;"></>
	
		<div id="right_list" class="list_content bright_bg" style="height:630; width:400;">
			<?php foreach ($songlist as $row): ?>
				<div class="list_elem" id="list_elem<?php echo $row->idSong; ?>" onclick="select_song(<?php echo $row->idSong; ?>);">
					<span class="song_title"><?php echo $row->titre ?></span>
					<span class="note">- <?php echo $this->artists_model->get_artist($row->idArtiste); ?></span>
					<br />
				</div>
			<?php endforeach; ?>		
		</div>
	</div>
	
</div>
