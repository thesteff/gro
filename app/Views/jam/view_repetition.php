<!-- autoresize texarea !-->
<script type="text/javascript" src="<?php echo base_url();?>/ressources/script/autosize.js"></script>

<script type="text/javascript">

	$(function() {

		// On initialise le autoresize
		$('.autosize').autosize({append: "\n"});
		
		// On actualise le titre de la modal pour indiquer la catégorie
		$("#viewRepeModal .modal-header").empty().append("Répétition <?php echo $repet_item['name']?> du <?php echo $repet_item['date_label'] ?>");
		
	});

	
	/************** LIEU ************/
	function lieu_change($action) {

		if ($("#view_repe_form #lieu").val() == "" && $action == "blur") return;
	
		// En cas de reset
		else if ($action == "reset") {
			$("#view_repe_form #lieu").val("");
			$("#view_repe_form #lieu_details").css("display","none");
			return;
		}
	
		// Requète ajax au serveur
		$.post("<?php echo site_url(); ?>/ajax/get_location",
		
			{
			'lieu_name':$("#view_repe_form #lieu").val()
			},
	
			function (msg) {
			
				// Le lieu spécifié n'est pas présent dans la base
				if (msg == "lieu_not_found" && $action == "input") {
					if ($("#view_repe_form #lieu_details").css("display") == "block") {
						$("#view_repe_form #lieu_details").css("display","none");
						$("#view_repe_form #lieu_adresse").empty();
						$("#view_repe_form #lieu_web").empty();
					}
				}
				// Le lieu spécifié n'est pas présent dans la base et on propose de le créer
				else if (msg == "lieu_not_found" && $action == "blur") {
					$txt = "<p>Le lieu spécifié n'est pas présent dans la base de données.<br> Voulez-vous le créer ?</p>"
					$txt += "<p style='text-align:center'><input type='button' value='valider' onclick='javascript:create_location_box(\""+encodeURI($("#lieu").val())+"\")'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='button' value='annuler' onclick='javascript:TINY.box.hide(); lieu_change(\"reset\");' ></p>";
					TINY.box.show({html:$txt,boxid:'confirm',animate:true,width:650, closejs:function(){lieu_change("reset");}});
				}
				
				// Si on a trouvé le lieu, on affiche les détails
				else {
					$lieu = JSON.parse(msg);
					
					$("#view_repe_form #lieu_details").css("display","block");
					if ($lieu.adresse.length) {
						$("#view_repe_form #lieu_adresse").empty();
						$("#view_repe_form #lieu_adresse").append($lieu.adresse);
						$("#view_repe_form #lieu_adresse").css("display","block");
					}
					if ($lieu.web.length) {
						$("#view_repe_form #lieu_web").empty();
						$("#view_repe_form #lieu_web").append($lieu.web);
						$("#view_repe_form #lieu_web").prop("href","http://"+$lieu.web);
						$("#view_repe_form #lieu_web").css("display","block");
					}
				}
			}
		);
		
	}

 </script>

 

<!-- Formulaire !-->
<div id="view_repe_form" class="container-fluid">
	<form class="form-horizontal">

		<!-------- LIEU --------->
		<div class="form-group">
			<label for="lieu" class="control-label col-sm-2">Lieu</label>
			<div class="col-sm-9">
				<input disabled id="lieu" class="form-control" list="lieux" type="text" name="lieu" value="<?php echo $repet_item['lieuName']; ?>"
						autocomplete="off" oninput="lieu_change('input')" onblur="lieu_change('blur')" />
				<!-- On affiche les détails s'il y en a !-->
				<?php if ($lieu_item['adresse'] != "" || $lieu_item['web'] != ""): ?>
					<div id="lieu_details" class="soften small panel panel-default" style="padding: 5px 10px; margin-bottom: 0px">
						<span id="lieu_adresse" style="display:<?php echo $lieu_item['adresse'] == "" ? "none" : "block" ?>"><?php echo $lieu_item['adresse']; ?></span>
						<a id="lieu_web" target="_blanck" style="display:<?php echo $lieu_item['web'] == "" ? "none" : "block" ?>" href="http://<?php echo $lieu_item['web']; ?>"><?php echo $lieu_item['web']; ?></a>
					</div>
				<?php endif; ?>
			</div>
		</div>
		
		<hr>
		
		<!----------------- PLANNING --------------------->		
		<!-- **** DEBUT **** !-->
		<div class="form-group">
			<label for="date_debut" class="control-label col-sm-2 col-xs-4 adjust-xs">Début</label>
			<div class="col-sm-2 col-xs-4">
				<input disabled id="date_debut" class="form-control text-center" type="input" name="date_debut" class="numbers" autocomplete="off" value="<?php echo $repet_item['date_debut']; ?>" />
			</div>
		</div>
		
		<!-- **** FIN **** !-->
		<div class="form-group">
			<label for="date_fin" class="control-label col-sm-2 col-xs-4 adjust-xs">Fin</label>
			<div class="col-sm-2 col-xs-4">
				<input disabled id="date_fin" class="form-control text-center" type="input" name="date_fin" class="numbers" autocomplete="off" value="<?php echo $repet_item['date_fin']; ?>" />
			</div>
		</div>
				
		<hr>
		
		<!-------- TEXTE --------->
		<div class="form-group">
			<div class="row">
				<div class="col-sm-12">
					<div class="well" style="margin-bottom: 0px"><?php echo $repet_item['text_html'] ?></div>
				</div>
			</div>
		</div>


		<hr>
		
		<div class="modal-footer">
			<button type="button" class="btn btn-default" data-dismiss="modal">Fermer</button>
		</div>

	</form>
</div>