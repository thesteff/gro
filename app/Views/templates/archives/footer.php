<script type="text/javascript">

	/* ******************* Gestion des tableaux ****************/
	$(function() {
		if ($(".is_playable").length && <?php echo $this->session->userdata('logged') ? 1 : 0 ?>) {  // il y a player que si membre et un élément playable dans la page
			$("#empty_div").css("padding-bottom","100");
			$("#footer").css("text-align","right");
			$("#footer").css("padding-right","15px");
		}
	});

</script>
	
	<!-- On ferme le contenu-->
	</div>
	
	
	<!-- Mentions légales !-->
	<div id="footer">
		<small><span class="soften">&copy; 2014 - 2017 | <a href="javascript:show_mentions()">mentions légales</a></span></small>
	</div>
	
	<!-- Div vide pour placer le player après le footer -->
	<div id="empty_div">
	</div>
	
	<!-- on ferme le corps-->
	</div>

<!-- on ferme le canevas-->
</div>


</body>
</html>