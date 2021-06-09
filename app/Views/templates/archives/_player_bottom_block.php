<script type="text/javascript">
	
	$(document).ready(function() {
		
	});
	
	
	function update_player(idSong,play) {
	console.log("UPDATE");
		$("#bottom_block_title").html("...");
		$player = document.getElementById('player');
		
		$("#bottom_pdf_block").html("...");
		
		$.post("<?php echo site_url(); ?>/ajax/get_song",
			
			{'idSong':idSong},
		
			function (msg) {
			
				var song = JSON.parse(msg);
				// on actualise le block gauche
				$("#bottom_block_title").html(song.song_obj.titre+' - <small>'+song.artiste+'</small>');
				$player.src = '<?php echo base_url(); ?>ressources/mp3/'+song.song_obj.mp3Ref+'.mp3';

				// On lance le player si nécessaire
				if (play) $player.play();
				
				// on actualise le block droit
				var pdf_img = "<img src='<?php echo base_url(); ?>images/icons/pdf_icon_16.png' />";
				var ref_pdf = "<a href='<?php echo base_url(); ?>ressources/pdf/"+song.song_obj.pdfRef1.replace(/\s/g,'%20')+".pdf' target='_blanck'>pdf principal</a>";
				$("#bottom_pdf_block").empty();
				$("#bottom_pdf_block").append(pdf_img+ref_pdf);
			}
		);
    }
	
 </script>

	
<div class="bottom_block">
	<div class="bottom_block_content">
	
		<div style="float:left; width:44%;">
			<h3 id="bottom_block_title">Player</h3>
			<audio id="player" controls preload="none" autoplay="false"
					onended="play_next()">
				<source id="player_src"/>
				<div class="error">Votre navigateur ne supporte pas la lecture de mp3.</div>
			</audio><br>
			<!--<div><small>play all</small></div>!-->
		</div>
		
		
		<div class="right" style="width:45%; padding-top:5; text-align:left;">
			<p>Liste des documents</p>
			<div id="bottom_pdf_block" class="pdf_block">
			</div>
		</div>

	</div>
</div>