
<script src="<?php echo base_url().'/ressources/' ?>jquery-1.11.1.js"></script>
<script>
$(document).ready(function(){
	$("#play-button").click(function(){
		$("#audioplayer")[0].play();
		alert('You have played the audio file!');
	})
	
	$("#pause-button").click(function(){
		$("#audioplayer")[0].pause();
		alert('You have paused the audio file!');
	})
})
</script>

	
	
<div class="block">
	<h3 class="block_title">Liste des titres du GRO</h3>

	<div class="list_content">
	
	<div class="audioplayer">
		<audio id="audioplayer" name="audioplayer" controls loop>
			<source src="<?php echo base_url();?>/ressources/mp3/54-46%20(was%20my%20number)%20-%20Toots%20and%20the%20Maytals.mp3" type="audio/mpeg">
			<div class="error">Votre navigateur ne supporte pas la lecture de mp3.</div>
		</audio>
	</div>
	
		
		<?php foreach ($songlist as $row): ?>
			<div class="song_block"><?php echo $row->titre ?></div>
			<?php
				if (isset($row->mp3URL)) {
					echo '<audio controls>
						<source src="'.base_url().'/ressources/mp3/'.$row->mp3URL.'.mp3" />
						<div class="error">Votre navigateur ne supporte pas la lecture de mp3.</div>
					</audio>';
				}
				else echo '<div class="error">L\'URL du media n\'a pas été spécifiée ou est incorrecte.</div>';
			?>
			<a id="play-button" href="#">Play!</a>
			<a id="pause-button" href="#">Pause!</a>
		<?php endforeach; ?>		
		
	</div>
</div>