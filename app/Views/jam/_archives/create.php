<script type="text/javascript">

	$(function() {
	
		// Datepicker
		$("#jam_date").datepicker({
			altField:"#date_label",
			altFormat: "dd/mm/yy",
		});
		
		$("#stage_date_limit").datepicker();	
		$("#stage_date_debut").datepicker();
		
		
		// On récupère la/les playlist
		get_playlist();
		
		// Permet d'ouvrir les sections d'affichage dynamique si besoin
		$("input:checkbox:checked").each(function() {
			$("#"+$(this).prop("id")+"_infos").css("display","block");
		});
		
		// En cas de repopulate
		lieu_change();
		
		
		// On définit les Hover Btn
		$(".clickableBtn").each(function() {
			$(this).hover(function(){
				$(this).css("filter", "grayscale(0%)");
				}, function(){
				$(this).css("filter", "grayscale(100%)");
			});
		});
		
		/************** MAX_INSCR ************/
		
		$(":checkbox[name=got_max_inscr]").change(function () { 
			if($(this).is(":checked")) {
				$("#max_inscr").css('display','inline');
				$("#max_inscr").val(0);
			}
			else {
				$("#max_inscr").css('display','none');
				$("#max_inscr").val(-1);
			}
		});
		
		// On définit la class numbersOnly
		$('.numbersOnly').keyup(function () { 
			this.value = this.value.replace(/[^0-9]/g,'');
		});
		
	});
	
	
	/************** LIEU ************/
	
	function lieu_change($action) {

		if ($("#lieu").val() == "" && $action == "blur") return;
	
		// En cas de reset
		else if ($action == "reset") {
			$("#lieu").val("");
			$("#lieu_details").css("display","none");
			return;
		}
	
		if ($("#lieu").val() != "") {
			// Requète ajax au serveur
			$.post("<?php echo site_url(); ?>/ajax/get_location",
			
				{
				'lieu_name':$("#lieu").val()
				},
		
				function (msg) {
				console.log(msg);
					// Le lieu spécifié n'est pas présent dans la base
					if (msg == "lieu_not_found" && $action == "input") {
						if ($("#lieu_details").css("display") == "block") {
							$("#lieu_details").css("display","none");
							$("#lieu_adresse").empty();
							$("#lieu_web").empty();
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
						
						$("#lieu_details").css("display","block");
						if ($lieu.adresse.length) {
							$("#lieu_adresse").empty();
							$("#lieu_adresse").append($lieu.adresse);
							$("#lieu_adresse").css("display","block");
						}
						if ($lieu.web.length) {
							$("#lieu_web").empty();
							$("#lieu_web").append($lieu.web);
							$("#lieu_web").prop("href","http://"+$lieu.web);
							$("#lieu_web").css("display","block");
						}
					}
				}
			);
		}
		
	}
	
	
	// Formulaire de création de lieu
	function create_location_box($lieu_name) {
	
		$html = "<p><b><u>Ajouter un lieu</u></b></p>";
		$html += "<div class='formLayout'>";
		$html += "<label>Nom</label><input id='lieu_pop_name' size='32' value='"+decodeURI($lieu_name)+"'><br>";
		$html += "<label>Adresse</label><textarea id='lieu_pop_adresse' cols='32' rows='2' style='resize:none'></textarea><br>";
		$html += "<label>Web</label><input id='lieu_pop_web' size='32' ><br>";
		$html += "</div>";
		$html += "<p style='text-align:center'><input type='button' value='ajouter' onclick='javascript:create_location()'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='button' value='annuler' onclick='javascript:TINY.box.hide()' ></p>";
		TINY.box.show({html:$html,boxid:'confirm',animate:false,width:650});
	
	}
	
	// Création du lieu
	function create_location() {
	
		//alert($("#lieu_adresse").val());
		$lieu_name = $("#lieu_name").val()
		
		
		// Requète ajax au serveur
		$.post("<?php echo site_url(); ?>/ajax/add_location",
		
			{
			'name': $("#lieu_pop_name").val(),
			'adresse': $("#lieu_pop_adresse").val(),
			'web': $("#lieu_pop_web").val()
			},
	
			function (msg) {
				if (msg == "success") TINY.box.show({html:"Le lieu a été ajouté à la base de donnée.",boxid:'success',animate:false,width:650, closejs:function(){ $("#lieux").append("<option value='"+$lieu_name+"'>"+$lieu_name+"</option>"); lieu_change("blur"); } });
				else TINY.box.show({html:"Le lieu est déjà présent dans la base de donnée !",boxid:'error',animate:false,width:650});
			}
		);
	}
	

	/************** PLAYLIST ************/
	function get_playlist() {

		// Requète ajax au serveur
		$.post("<?php echo site_url(); ?>/ajax/get_playlist",
		
			{'idPlaylist':$("#playlist").val()},
		
			function (msg) {
				
				// On vide la liste actuellement affichée
				$("#playlist_infos tbody > *").remove();
				
				// On affiche la liste selectionnée
				$playlist = JSON.parse(msg);
				
				if ($playlist != null) {
					$.each($playlist["list"],function(index) {
						//$("#playlist_infos").append("<p class='list_elem'>"+$(this)[0].titre+"</p>");
						if ($playlist.list[index].versionId != -1) {
							mark = "<span style='display:none'>1</span><img style='height: 12;' src='/images/icons/ok.png'>";
							empty = "<span style='display:none'>0</span>";
							if ($playlist.list[index].choeurs == 1) choeurs = mark; else choeurs="";
							if ($playlist.list[index].soufflants == 1) soufflants = mark; else soufflants="";
							// Si stage
							classe = "";
							if ($("#playlist").val() >= 0 && $playlist.list[index].reserve_stage == 1)  {
								stage = mark;
								classe = " class='stage'";
							}
							else stage="";
							
							$("#playlist_infos tbody").append("<tr onclick='update_player("+$playlist.list[index].versionId+");' versionId='"+$playlist.list[index].versionId+"'><td"+classe+">"+$playlist.list[index].titre+" - <small class='soften'>"+$playlist.list[index].artisteLabel+"</small></td></td><td style='text-align: center'"+classe+">"+choeurs+"</td><td style='text-align: center'"+classe+">"+soufflants+"</td><td style='text-align: center'"+classe+">"+stage+"</td></tr>");
						}
						// On gère les pauses
						else $("#playlist_infos tbody").append("<tr versionId='"+$playlist.list[index].versionId+"'><td colspan='"+$("#playlist_infos th").children().length+"'>-= <i>pause</i> =-</td></tr>");
					});
					$("#playlist_infos").css("display","block");
				}
				else $("#playlist_infos").css("display","none");
			}
		);
		
		// On actualise l'input acces_inscr
		if ($("#playlist").val() > 0) {
			$("#acces_inscr_label").css("opacity","1.0");
			$("#acces_inscr_label").removeClass("soften");
			$("#acces_inscr").prop("disabled",0);
		}
		else {
			$("#acces_inscr_label").css("opacity","0.6");
			$("#acces_inscr_label").addClass("soften");
			$("#acces_inscr").attr("checked", false);
			$("#acces_inscr").prop("disabled",1);
		}

    }
	
	
	/************** COORGANISATEURS ************/
	/* !!! Les fonctions ajax ont besoin de jam_item['id'] or la jam n'exite pas encore !!
	
	/*function event_admin_input() {
		
		// Requète ajax au serveur
		$.post("<?php echo site_url(); ?>/ajax/get_member",
		
			{'pseudo':$("#adminInput").val()},
		
			function (msg) {
				
				$member = JSON.parse(msg);
				
				// On affiche l'addBtn que si le membre existe dans la BD mais pas déjà dans la liste d'amin
				if ($member != null && $("#admin_list").children(":contains('"+$("#adminInput").val()+"')").length == 0) $("#adminAddBtn").css("display","inline");
				else $("#adminAddBtn").css("display","none");
			}
		);
	}
	
	
	// Requête ajax d'ajout d'admin
	function add_event_admin_request() {

		// Requète ajax au serveur
		$.post("<?php echo site_url(); ?>/ajax/join_jam",
		
			{	
				'slugJam':'<?php echo $jam_item['slug']; ?>',
				'login': $("#adminInput").val(),
				'event_admin':1,
				'buffet':0,
				'billet':0,
				'balance':0
			},
		
			function (msg) {
				// On affiche le message d'info
				TINY.box.show({html:msg,boxid:'success',animate:false,width:650, closejs:function(){add_event_admin($("#adminInput").val());} });
			}
		);
	}
	
	
	// Ajout d'admin dans la liste
	function add_event_admin($pseudo) {
	
		// On ajoute l'admin à la liste
		$("#admin_list").append("<div class='list_elem'><span class='pseudo'>"+$pseudo+'</span>&nbsp;<img id="adminRemoveBtn" class="clickableBtn" style=\'height: 8px; display:inline; filter:opacity(50%)\' src=\'/images/icons/x.png\' title=\'retirer coorganisateur\'></div>');

		$new_elem = $("#admin_list").children(":contains('"+$pseudo+"')").first();
	
		// On définit les Hover Btn
		$new_elem.children(".clickableBtn").on({
			click: function() {
				remove_event_admin_request($(this).parent().index());
			},
			mouseover: function() {
				$(this).css("filter", "opacity(100%)");
			},
			mouseout: function() {
				$(this).css("filter", "opacity(50%)");
			}
		});

		// On vide l'input
		$("#adminAddBtn").css("display","none");
		$("#adminInput").val('');
		
		// On actualise l'affichage de la liste d'admin
		$("#admin_list").css("display","block");
	}
	
	
	// Requête ajax pour retirer un admin de l'event
	function remove_event_admin_request($index) {
		
		$pseudo = $("#admin_list").children(":nth-child("+($index+1)+")").children(".pseudo").html();
		
		// Requète ajax au serveur
		$.post("<?php echo site_url(); ?>/ajax/remove_event_admin",
		
			{	
				'jamId':'<?php echo $jam_item['id']; ?>',
				'pseudo': $pseudo
			},
		
			function (msg) {
				// On affiche le message d'info
				TINY.box.show({html:msg,boxid:'success',animate:false,width:650, closejs:function(){remove_event_admin($index);} });
			}
		);
	}
	
	// On retire un admin de la liste
	function remove_event_admin($index) {
		$("#admin_list").children(":nth-child("+($index+1)+")").remove();
		
		// On actualise l'affichage de la liste d'admin
		if ($("#admin_list").children().length == 0) $("#admin_list").css("display","none");
	}*/
	
	
	
	/************** BENEVOLE ************/
	function benevole_show_infos() {
		if ($("#benevole_infos").css("display") != "none")
			$("#benevole_infos").css("display","none");
		else $("#benevole_infos").css("display","block");
	}
	
	
	/************** STAGE ************/
	function stage_show_infos() {
		if ($("#stage_infos").css("display") != "none")
			$("#stage_infos").css("display","none");
		else $("#stage_infos").css("display","block");
	}
	
	
 </script>
	



<div class="main_block">

	<h3 class="block_title">Ajouter une jam</h3>
	<br>
	
	<div class="block_content_left">	

		<?php echo form_open('jam/create') ?>
		
			<!----- TITRE / DATE / TEXTAREA  ------->
			<p style="width:93%;">
				<input type="input" name="title" size="45" placeholder="Titre de la jam" value="<?php echo set_value('title'); ?>" autofocus required/>
				<input type="input" name="date_label" id="date_label" size="10" class="date" style="float:right;text-align:center;" readonly><br />
				<?php echo form_error('title'); ?>
			</p>
		
			<p>
				<textarea id="jam_textarea" name="text" placeholder="Texte de la jam"><?php echo set_value('text'); ?></textarea>
				<br><?php echo form_error('text'); ?>
			</p>

			<!-------- LIEU  --------->
			<div id="lieu_infos" class="small_block_info" style="display:flex;">
				<div style="align-self:center"><img style="height: 16; margin:0 16 0 16;" src="/images/icons/lieu.png" alt="lieu"></div>
				<div class="small_block_col">
					<p>
						<input id="lieu" type="input" name="lieu" size="30" list="lieux" placeholder="Lieu de la jam" value="<?php echo set_value('lieu'); ?>" autocomplete="off" oninput="lieu_change('input')" onblur="lieu_change('blur')" />
						<datalist id="lieux">
							<?php foreach ($list_lieux as $lieu): ?>
								<option value="<?php echo htmlentities($lieu['nom']) ?>"><?php echo htmlentities($lieu['nom']); ?></option>
							<?php endforeach ?>
							<?php echo form_error('lieu'); ?>
						</datalist>
					</p>
					<p id="lieu_details" class="soften" style="font-size: 90%">
						<span id="lieu_adresse"></span>
						<a id="lieu_web" target="_blanck" href=""></a>
					</p>
				</div>
			</div>
			
			<br>
			
			<!------ PLANNING  ------>
			<div id="time_infos" class="small_block_info">
				<p>
					<input type="input" name="date_bal" size="3" list="horaires" class="numbers" value="<?php echo set_value('date_bal'); ?>" autocomplete="off"  /> > balances<br>
					<?php echo form_error('date_bal'); ?>
					<input type="input" name="date_debut" size="3" list="horaires" class="numbers" value="<?php echo set_value('date_debut'); ?>" autocomplete="off" /> > début<br>
					<?php echo form_error('date_debut'); ?>
					<input type="input" name="date_fin" size="3" list="horaires" class="numbers" value="<?php echo set_value('date_fin'); ?>" autocomplete="off" /> > fin<br>
					<?php echo form_error('date_fin'); ?>

					<datalist style="width:150" id="horaires">
					<?php
						$h = 0;
						$m = 0;
						while ($h < 24) {
							if ($h < 10) $pref = "0"; else $pref = "";
							while ($m < 60) {
								if ($m < 10) $pref2 = "0"; else $pref2 = "";
								echo '<option value="'.$pref.strval($h).':'.$pref2.strval($m).'">'.$pref.strval($h).':'.$pref2.strval($m).'</option>';
								$m += 30;	// Pas des minutes dans la liste
							}
							$m = 0;
							$h++;
						}
					?>
					</datalist>
				</p>
			</div>
			
			<br>
			<hr>
			
			
			<!-- MAXIMUM d'inscrits  -->
			<p>
				<label for="max_inscr" style="margin-right:10px">Nombre maximum d'inscrits</label>
				<input type="checkbox" name="got_max_inscr" />
				<input id='max_inscr' type="input" name="max_inscr" size="2" list="nb_inscr" class="numbers numbersOnly" value="-1" style="text-align:right; display:none" /><br>
				<?php echo form_error('nb_inscr'); ?>
				<datalist style="width:150" id="nb_inscr">
				<?php
					$nb = 10;
					while ($nb <= 150) {
						echo '<option value="'.$nb.'">'.$nb.'</option>';
						$nb += 10;
					}
				?>
				</datalist>
			</p>
			
			<hr>
			
			<!------ PLAYLIST ------>
			<p>
				<label for="playlist">Playlist</label>
				<select id="playlist" name="playlist" onchange="get_playlist()">
					<option value="default">aucune</option>
					<?php
						foreach ($list_playlist as $playlist): ?>
							<option value="<?php echo $playlist['id']; ?>" <?php echo set_select('playlist',$playlist['id']); ?> ><?php echo ucfirst($playlist['title']); ?></option>
						<?php endforeach
					?>
				</select>
				&nbsp;&nbsp;
				<label id="acces_inscr_label" for="acces_inscr" class='soften' style='opacity:0.6'><small>Accès au tableau d'inscription</label></small>
				<input id="acces_inscr" name="acces_inscr" type="checkbox" value='1' <?php echo set_checkbox('acces_inscr','1'); ?> disabled>

			</p>
			
			<div id="playlist_infos" class="small_block_info">
				<table class="list_content listTab bright_bg" style="width:500;">
					<thead>
						<tr>
							<th></th>
							<th width="10" style="text-align:center"><span class="choeurs"><img style='height: 12;' src='/images/icons/heart.png' title='choeurs'></span></th>
							<th width="10" style="text-align:center"><span class="soufflants"><img style='height: 16; margin:0 2' src='/images/icons/tp.png' title='soufflants'></span></th>
							<th width="10" style="text-align:center"><span class="stage"><img style='height: 16;' src='/images/icons/metro.png' title='réservé aux stagiaires'></span></th>
						</tr>
					</thead>
					<tfoot>
						<tr>
							<th></th>
							<th style="text-align:center"><img style='height: 10;' src='/images/icons/heart.png'></th>
							<th style="text-align:center"><img style='height: 14; margin:0 2' src='/images/icons/tp.png'></th>
							<th width="10" style="text-align:center"><img style='height: 14; margin:0 2' src='/images/icons/metro.png'><span class="stage"></span></th>
						</tr>
					</tfoot>
					<tbody></tbody>
				</table>
			</div>
			
			
			<br>
			<hr>
			
			
			<!-- BENEVOLES -->
			<p>
			<label for="benevole">Appel à bénévoles</label>
			<input id="benevole" name="benevole" type="checkbox" value='2' <?php echo set_checkbox('benevole','2'); ?> onchange="benevole_show_infos()">
			</p>
			
			<div id="benevole_infos" style="display:none"><p>
			
				<div class="small_block_info">
				
					<p style="margin-bottom:5">
						Moyen de dynamiser la section...
					</p>

				</div>
				
			</p></div>

			<hr>
			
			
			<!------ STAGE ------>
			<p>
			<label for="stage">Stage</label>
			<input id="stage" name="stage" type="checkbox" value="stage" <?php echo set_checkbox('stage','stage'); ?> onchange="stage_show_infos()">
			</p>
			
			<div id="stage_infos" style="display:none"><p>
			
				<textarea id="stage_textarea" name="stage_text" placeholder="Texte du stage"><?php echo set_value('stage_text'); ?></textarea>
				<br><?php echo form_error('stage_text'); ?>
				
				<div class="small_block_info">
				
					<p>
						<label for="duree">Durée du stage en jours</label>
						<input style="float:right" id="duree" name="duree" size="4" value="<?php echo set_value('duree'); ?>">
						<br><?php echo form_error('duree'); ?>
					</p>
				
					<p>
						<label for="stage_date_debut">Date de début du stage </label>
						<input style="float:right" id="stage_date_debut" name="stage_date_debut" type="text">
						<br><?php echo form_error('stage_date_debut'); ?>
					</p>
				
					<p>
						<label for="stage_date_limit">Date limite des pré-inscriptions </label>
						<input style="float:right" id="stage_date_limit" name="stage_date_limit" type="text">
						<br><?php echo form_error('stage_date_limit'); ?>
					</p>

					<p>
						<label for="cotis">Montant de la cotisation en euros</label>
						<input style="float:right" id="cotis" name="cotis" size="4" value="<?php echo set_value('cotis'); ?>">
						<br><?php echo form_error('cotis'); ?>
					</p>
					
					<p>
						<label for="cotis">Ordre</label>
						<input style="float:right" id="ordre" name="ordre" size="35" value="<?php echo set_value('ordre'); ?>">
						<br><?php echo form_error('ordre'); ?>
					</p>
					
					<p>
						<label for="cotis">Adresse postale</label>
						<input style="float:right" id="adresse" name="adresse" size="35" value="<?php echo set_value('adresse'); ?>">
						<br><?php echo form_error('adresse'); ?>
					</p>
					
				</div>
				
			</p></div>
			
			
			<!-- SUBMIT -->
			<input class="button" type="submit" name="submit" value="Créer la jam" />

		</form>	
	</div>
	
	<div class="block_content_right">
		<small><div name="jam_date" id="jam_date" style="margin-right:15;"></div></small>
	</div>
	
</div>