<script type="text/javascript">
	
	function suppress_playlist(title,slug) {
		var r = confirm("Supprimer la playlist \"".concat(title).concat("\""));
		if (r == true) {
			window.location.replace("./suppress/".concat(slug));
		}
    }
	
 </script>


<div class="main_block">
	<h3 id="playlist_title" style="display:flex" class="block_title"><?php echo $playlist_item['infos']['title'] ?>
			&nbsp;&nbsp;
			<a href="<?php echo site_url();?>/playlist/update/<?php echo $playlist_item['infos']['slug'] ?>"><img class="action_icon" src="/images/icons/edit.gif" alt="Modifier"></a>
			<img class="action_icon" src="/images/icons/suppr.gif" alt="Supprimer" onclick="suppress_playlist('<?php echo str_replace("'", "\'", $playlist_item['infos']['title']).'\',\''.$playlist_item['infos']['slug']; ?>')">
	</h3>
	
	<div id="playlist_content" class="block_content">
		<div id="left_list" class="list_content bright_bg" style="height:630; width:400;">
			<?php foreach ($playlist_item['list'] as $ref): ?>
				<div class="list_elem" onclick="update_player('<?php echo str_replace("'", "\'",$ref->idSong); ?>')">
					<span class="song_title"><?php echo $ref->titre ?></span>
					<br />
				</div>
			<?php endforeach; ?>
		</div>	
	</div>
	
</div>

