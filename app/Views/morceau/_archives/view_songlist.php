<script type="text/javascript" src="<?php echo base_url();?>ressources/tableSorter/jquery.tablesorter.js"></script>
<link rel="stylesheet" href="<?php echo base_url();?>images/icons/sand/style.css" type="text/css" media="print, projection, screen" />


<script type="text/javascript">

	$(function() {
		$.tablesorter.defaults.widgets = ['zebra']; 
		$("#songlist").tablesorter();
		
		//setCellElemBehavior("#songlist",'#CC2900','#FFC0B2','#851f00','white');
		setCellElemBehavior("#songlist",'#255625','#BEEFBE','#851f00','white');
		
	});

	
	function get_playlists() {

		//alert($("#select_evt").val());
	
		// Requète ajax au serveur
		$.post("<?php echo site_url(); ?>/ajax/get_playlist",
		
			{'idPlaylist':$("#select_playlist").val()},
		
			function (msg) {
				// On vide la liste actuellement affichée
				$("#songlist_body").empty();
				
				// On rempli le tableau avec les nouvelles valeurs
				$playlist = JSON.parse(msg);
				$.each($playlist.list,function(index) {
					$("#songlist_body").append("<tr id='list_elem' onclick='update_player("+$playlist.list[index].idSong+")' idSong='"+$playlist.list[index].idSong+"'><td>"+$playlist.list[index].titre+"</td><td>"+$playlist.list[index].label+"</td></tr>");
				});
				
				$("#songlist").trigger("update");
				setCellElemBehavior("#songlist",'#255625','#BEEFBE','#851f00','white');
				
				// On actualise le nombre de ref affichées
				$("#nbRef").empty();
				$("#nbRef").append($playlist.list.length);
				
			}
		);

    }

	
 </script>

<div class="main_block">

	<div id="songlist_content" class="block_content">
		
		<!-- SELECT playlist + nb ref -->
		<div>
			<select id="select_playlist" name="select_playlist" onchange="get_playlists()">
				<option value="-1">GRO</option>
				<?php foreach ($playlist as $list): ?>
					<option value="<?php echo $list['id']; ?>"><?php echo $list['title']; ?></option>
				<?php endforeach ?>
			</select>
			
			
		<!-- Tool bar -->
		<div class="submenu_block list_menu" style="height:30; position:relative;">	
			<!-- Items de droite (archives) !-->
			<div class="right_list_bar">
			<?php // Lien vers la création d'une playlist !
					//if ($this->uri->segment(2) == "create" || $this->uri->segment(2) == null) echo '<a class="selected" href="'.site_url().'/playlist/create">ajouter</a>';
					echo '<a class="ui_elem action_icon soften" href="'.site_url().'/jam/create"><img style="vertical-align: text-bottom;" src="/images/icons/add.png" alt="add">  ajouter</a>';
				?>
			</div>
			<!-- Items de gauche !-->
			<div class="list_bar soften" >
				<a href="" class="ui_elem action_icon soften"><img class="action_icon" style="height: 15; vertical-align:  text-bottom;" src="/images/icons/edit.png" alt="Modifier"> modifier</a>
				|
				<a href="<?php echo site_url().'/repertoire' ?>" class="ui_elem action_icon soften<?php if ($this->uri->segment(1) == "repertoire") echo " active"?>"><img style="height: 15; vertical-align: text-bottom;" src="/images/icons/suppr.png" alt="repert"> supprimer</a>
			</div>
		</div>
		
		</div>
		
		
		<br>
		
		<!-- Affichage de nbRef -->	
		<div class="small_block_list_title soften right"><small><span class="soften">(<span id="nbRef"><?php echo sizeof($songlist); ?></span> références)</small></span></div>
		<!-- Affichage de la songlist -->		
		<table id="songlist" class="tablesorter" cellspacing="0">
			<thead>
				 <tr>
					<th>Titre</th>
					<th>Artiste</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<th>Titre</th>
					<th>Artiste</th>
				</tr>
			</tfoot>
			<tbody id="songlist_body">
				<?php foreach ($songlist as $song): ?>
					<tr id="list_elem<?php echo $song->idSong; ?>"
						<?php if ($this->session->userdata('logged') == true) : ?>
							onclick="update_player('<?php echo str_replace("'", "\'",$song->idSong); ?>')"
							idSong="<?php echo str_replace("'", "\'",$song->idSong); ?>"
						<?php endif; ?>
					>
						<td><?php echo $song->titre; ?></td>
						<td><?php echo $this->artists_model->get_artist($song->idArtiste); ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		
	</div>

</div>