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
	
			$("#right_list > #list_elem"+select_id).css('text-decoration','none');
		}
	}
	
</script>


<div class="main_block" style="display:flex; justify-content:center;">

	<div class="block_content_left">	
	
		<h3 class="block_title">Créer une playlist</h3>

		<?php echo form_open('playlist/create') ?>
		
			<div>
				<input type="input" name="title" size="45" placeholder="Titre de la playlist" value="<?php echo set_value('title'); ?>" autofocus />
				<?php echo form_error('title'); ?>
			</div>
			
			<br />
			<div id="left_list" class="list_content bright_bg" style="height:550; width:350;">
			</div>
			
			<select name="song_list[]" id="hidden_select" style="display:none" multiple>
			</select>
			
			<br />
			<input class="button" type="submit" name="submit" value="Créer" />
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