<script type="text/javascript">

	function show_playlist(url)  {
		window.location.replace(url);
    }
	
</script>


<div class="list_block">
	<div class="small_list_content" style="height:80;">
		<?php foreach ($list_playlist as $playlist_item): ?>
			<div class="list_elem<?php if ($this->uri->segment(2) == $playlist_item['slug'] || $this->uri->segment(3) == $playlist_item['slug']) echo ' selected';?>"
				id="playlist_elem<?php echo $playlist_item['id']; ?>"
				onclick="show_playlist('<?php echo site_url().'/playlist/'.$playlist_item['slug']; ?>')"
			>				
						<div style="float:left;"><?php echo $playlist_item['title']; ?></div>
						
			</div>
		<?php endforeach ?>
		
		<div class="list_elem" style="padding:10 0">
			<!-- Lien vers la création d'une playlist !-->
			<?php if ($this->uri->segment(2) == "create" || $this->uri->segment(2) == null) echo '<a class="selected" href="'.site_url().'/playlist/create">[Ajouter une playlist]</a>';
				else echo '<a href="'.site_url().'/playlist/create">-= Ajouter une playlist =-</a>'; ?>
		</div>
	</div>
</div>