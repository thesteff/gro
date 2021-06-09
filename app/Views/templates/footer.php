	<script type="text/javascript">

		/* ******************* Gestion des tableaux ****************/
		$(function() {
			if ($(".is_playable").length && <?php echo session('logged') ? 1 : 0 ?>) {  // il y a player que si membre et un élément playable dans la page
				$("#empty_div").css("padding-bottom","100px");
				$("#footer").css("text-align","right");
			}
		});

	</script>
	
	
		<!-- Mentions légales !-->
		<div id="footer">
			<small><span class="soften">&copy; 2014 - <?php echo date("Y"); ?> | <a data-toggle="modal" href="#show_mentions">mentions légales</a></span></small>
		</div>
		
	
	<!-- On ferme le content -->
	</div>
	
	
	<!-- Div vide pour placer le player après le footer avec du padding -->
	<div id="empty_div">
	</div>


	<!-- Modal mentions legales -->
	<div id="show_mentions" class="modal fade" role="dialog">
		<div class="modal-dialog default">
			<div class="modal-content">
				<div class="modal-header lead">Mentions légales</div>
				<div class="modal-body">
					<p>Les informations recueillies sont nécessaires pour votre adhésion.<br>
						Elles font l’objet d’un traitement informatique et sont destinées au secrétariat de l’association. En application des articles 39 et suivants de la loi du 6 janvier 1978 modifiée, vous bénéficiez d’un droit d’accès et de rectification aux informations qui vous concernent. Si vous souhaitez exercer ce droit et obtenir communication des informations vous concernant, veuillez nous adresser un message à l'adresse suivante : <a href="mailto:contact@le-gro.com"><b>contact@le-gro.com</b></a>.</p>
				</div>
			</div>
		</div>
	</div>
	
	
	<!-- Permet de savoir dans quelle dimension est utilisé Bootstrap -->
	<div class="visible-md visible-lg" id="large-desktop-only-visible"></div>
	<div class="visible-sm visible-md visible-lg" id="desktop-only-visible"></div>
	<div class="visible-xs" id="mobile-only-visible"></div>


</body>
</html>