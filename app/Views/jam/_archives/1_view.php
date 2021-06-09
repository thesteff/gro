
<script type="text/javascript">

	$(function() {
		$("#jam_date").datepicker({
			dateFormat: "dd/mm/yy"
		});
		$("#jam_date").datepicker( "option", "disabled", true );
		$("#jam_date").datepicker( "setDate", $("#date_label").html() );
		
		$selected_item = -1;
		
		// Masque les catégories vides
		refresh_cat();
		
		// On gère les affichage d'instru dynamique		
		$("#member_list").on("mouseenter",".member", function() {
						$(this).find(".instru").css("display","inline");
					});
		$("#member_list").on("mouseleave",".member", function() {
						$(this).find(".instru").css("display","none");
					});
		
		
	});

	
	/**** INSCRIPTION  *****/
	function inscription() {

		// On vérifie si l'utilisateur n'est pas loggé
		if ('<?php echo ($this->session->userdata('logged')) ?>' != '1') {
				// Popup
				msg = "<p>Pour participer à la jam, vous devez d'abord devenir membre du <b>Grenoble Reggae Orchestra</b> en vous inscrivant sur le site ou vous identifier sur votre compte si vous êtes déjà membre.</p>";
				msg += "<p style='text-align:center'><a href='<?php echo site_url(); ?>/members/create'>s'incrire au site</a><?php echo nbs(8); ?><a href='<?php echo site_url(); ?>/members/login'>s'indentifier</a></p>";
				TINY.box.show({html:msg,boxid:'confirm',animate:true,width:750});
		}
		
		else {
		
			if (<?php echo $jam_item['appel_benevole']; ?>) {
				// Popup bénévole jam
				msg = "<p>Nous avons besoin de bénévoles pour gérer au mieux l'évènement.<br>";
				msg += "Si vous pensez avoir un peu d'énergie à nous consacrer, n'hésitez pas à nous le faire savoir !</p>";
				msg += "<p>"
				msg += "<label><input style='margin-right:10' type='checkbox' name='buffet' value=''>Je veux bien participer au buffet et apporter de quoi boire ou manger.</label><br>";
				msg += "<label><input style='margin-right:10' type='checkbox' name='billet' value=''>Je suis ponctuellement dispo pendant la soirée pour participer à la billeterie.</label><br>";
				msg += "<label><input style='margin-right:10' type='checkbox' name='balance' value=''>Je suis dispo pour les balances.</label><br>";
				msg += "</p>";
				msg += "<p>Et n'oubliez pas que sans vous, l'évènement n'existerait pas !</p>";
				msg += "<div style='text-align:center'>";
				msg += "<input style='padding:6' type='button' value='Valider inscription' onclick='join_jam()'>";
				msg += "</div>";
				TINY.box.show({html:msg,boxid:'confirm',animate:true,width:750});
			}
			
			else join_jam();
		}
    }
	
	
	/**** JOIN JAM  *****/
	function join_jam() {

		// Gestion du bénévolat
		$buffet = $("input:checkbox[name=buffet]").prop("checked") ? 1 : 0;
		$billet = $("input:checkbox[name=billet]").prop("checked") ? 1 : 0;
		$balance = $("input:checkbox[name=balance]").prop("checked") ? 1 : 0;

	
		// Requète ajax au serveur
		$.post("<?php echo site_url(); ?>/ajax/join_jam",
		
			{	
				'slugJam':'<?php echo $jam_item['slug']; ?>',
				'login':'<?php echo $this->session->userdata('login')?>',
				'buffet':$buffet,
				'billet':$billet,
				'balance':$balance
			},
		
			function (msg) {
			
				// On affiche le message d'info
				TINY.box.show({html:msg,boxid:'success',animate:false,width:650, closejs:function(){refresh_jam();}});
			}
		);
    }
	
	
	// Pour actualiser les participations à la jam en tant que bénévole
	function update_task()  {
		
		$chk_buffet = '';
		$chk_billet = '';
		$chk_balance = '';
		
		if ($("#buffet").css("display") == "block") $chk_buffet = "checked";
		if ($("#billet").css("display") == "block") $chk_billet = "checked";
		if ($("#balance").css("display") == "block") $chk_balance = "checked";
		
		// Popup bénévole jam
		msg = "<p>Nous avons besoin de bénévoles pour gérer au mieux l'évènement.<br>";
		msg += "Si vous pensez avoir un peu d'énergie à nous consacrer, n'hésitez pas à nous le faire savoir !</p>";
		msg += "<p>"
		msg += "<label><input style='margin-right:10' type='checkbox' name='buffet' value='' "+$chk_buffet+">Je veux bien participer au buffet et apporter de quoi boire ou manger.</label><br>";
		msg += "<label><input style='margin-right:10' type='checkbox' name='billet' value='' "+$chk_billet+">Je suis ponctuellement dispo pendant la soirée pour participer à la billeterie.</label><br>";
		msg += "<label><input style='margin-right:10' type='checkbox' name='balance' value='' "+$chk_balance+">Je suis dispo pour les balances.</label><br>";
		msg += "</p>";
		msg += "<p>Et n'oubliez pas que sans vous, l'évènement n'existerait pas !</p>";
		msg += "<div style='text-align:center'>";
		msg += "<input style='padding:6' type='button' value='Modifier inscription' onclick='update_joined_jam()'>";
		msg += "</div>";
		TINY.box.show({html:msg,boxid:'confirm',animate:true,width:750});
    }
	
	
	function update_joined_jam() {

		// Gestion du bénévolat
		$buffet = $("input:checkbox[name=buffet]").prop("checked") ? 1 : 0;
		$billet = $("input:checkbox[name=billet]").prop("checked") ? 1 : 0;
		$balance = $("input:checkbox[name=balance]").prop("checked") ? 1 : 0;

	
		// Requète ajax au serveur
		$.post("<?php echo site_url(); ?>/ajax/update_joined_jam",
		
			{	
				'slugJam':'<?php echo $jam_item['slug']; ?>',
				'login':'<?php echo $this->session->userdata('login')?>',
				'buffet':$buffet,
				'billet':$billet,
				'balance':$balance
			},
		
			function (msg) {
			
				// On affiche le message d'info
				TINY.box.show({html:msg,boxid:'success',animate:false,width:650, closejs:function(){refresh_task();}});
			}
		);
    }
	
	
	
	/**** QUIT JAM  *****/
	function quit_jam()  {
		$confirm = "<p>En vous désinscrivant de cette jam vous perdrez toutes les informations qui y sont associées (inscriptions sur les morceaux).</p>";
		$confirm += "<p style='text-align:center'><input type='button' value='se désinscrire' onclick='javascript:really_quit_jam()'><?php echo nbs(8); ?><input type='button' value='annuler' onclick='javascript:TINY.box.hide()'></p>";
		TINY.box.show({html:$confirm,boxid:'error',width:650});
    }

	function really_quit_jam()  {
		// Requète ajax au serveur
		$.post("<?php echo site_url(); ?>/ajax/quit_jam",
		
			{'slugJam':'<?php echo $jam_item['slug'] ?>','login':'<?php echo $this->session->userdata('login')?>'},
	
			function (msg) {
				// On affiche le message d'info
				TINY.box.show({html:msg,boxid:'success',animate:false,width:650, closejs:function(){window.location.reload();}});
			}
		);
	}
	
	
	// Actualiser l'affichage après une inscription sans faire de reload
	function refresh_jam() {
		// On masque le button de participation
		$("#join_btn").css('display','none');
		
		// On actualise l'affichage de la participation (bénévoles)
		refresh_task();
		
		// On ajoute l'utilisateur à la liste des participants
		$("#list_alpha").append("<li class='member'><span><?php echo $this->session->userdata('login'); ?></span><small><span class='instru soften' style='display:none'></span></small></li>");
		$("#nb_jammeur").text((Number($("#nb_jammeur").text()) + 1));
		
		// On récupère l'instrument principal joué par le membre inscrit
		$.post("<?php echo site_url(); ?>/ajax/get_instru_member",
			{'login':'<?php echo $this->session->userdata('login')?>'},
				function (msg) {
					$info_instru = JSON.parse(msg);
					// On remplit l'instru dans la list_alpha
					$("#list_alpha li span:contains('<?php echo $this->session->userdata('login')?>')").parent().find(".instru").append(" > "+$info_instru.instru);
					// On remplit la list_cat
					$("#list_cat li[id='"+$info_instru.cat+"'] ul").append("<li class='member'><span><?php echo $this->session->userdata('login'); ?></span><small><span class='instru soften' style='display:none'> > "+$info_instru.instru+"</span></small></li>");
					// On rafraichit l'affichage
					refresh_cat();
				}
		);
		
		// On affiche le lien de désinscription
		$("#quit_link").css('display',"inline");
		
		// On affiche le lien vers les inscriptions
		$("#inscr_link").css('display','block');
		
		// On change l'état de la span attend
		$('#attend').text("1");
	}
	
	
	// Actualiser l'affichage de la participation (bénévoles)
	function refresh_task() {
		// On affiche les infos de participation si la jam fait bien des appels à bénévoles
		if ($("#appel_benevole").text() == 1) {
			$("#benevole").css('display','block');
			if ($buffet) $("#buffet").css('display','block');  else $("#buffet").css('display','none');
			if ($billet) $("#billet").css('display','block');  else $("#billet").css('display','none');
			if ($balance) $("#balance").css('display','block');  else $("#balance").css('display','none');
			if (!$buffet && !$billet && !$balance) $("#vide").css('display','block');  else $("#vide").css('display','none');
		}
	}
	
	
	
	/**** JOIN STAGE  *****/
	function join_stage(slug) {

		// On vérifie si l'utilisateur n'est pas loggé
		if ('<?php echo ($this->session->userdata('logged')) ?>' != '1') {
				// Popup
				msg = "<p>Pour vous inscrire au stage, vous devez d'abord devenir membre du <b>Grenoble Reggae Orchestra</b> en vous inscrivant sur le site ou vous identifier sur votre compte si vous êtes déjà membre.</p>";
				msg += "<p style='text-align:center'><a href='<?php echo site_url(); ?>/members/create'>s'incrire au site</a><?php echo nbs(8); ?><a href='<?php echo site_url(); ?>/members/login'>s'indentifier</a></p>";
				TINY.box.show({html:msg,boxid:'confirm',animate:true,width:750});
		}
		
		else {
			window.open("<?php echo site_url(); ?>/stage/inscription/"+slug,"_self");
		}
    }
	
	
			
	/**** ADD WISH  *****/
	function add_wish() {

		if ($('#attend').text() == "0") {
			alert("Vous devez vous inscrire à la jam pour pouvoir proposer des titres.");
			return;
		}

		$wish_titre = $('#wish_titre').val();
		$wish_url = $('#wish_url').val();
		
		// Requète ajax au serveur
		$.post("<?php echo site_url(); ?>/ajax/add_wish",
		
			{
			'slugJam':'<?php echo $jam_item['slug'] ?>',
			'login':'<?php echo $this->session->userdata('login')?>',
			'titre':$wish_titre,
			'url':$wish_url
			},
	
			function () {
				// On insère le wish_elem
				$("#wishlist").append('<div class=\"soften\"><small><?php echo $this->session->userdata('login'); ?> à proposé :</small></br><a href=\"'+$wish_url+'\" target=\"_blanck\">'+$wish_titre+'</a></div><hr>');
				// On clean les champs de formulaires
				$("#wish_titre").val("");
				$("#wish_url").val("");
			}
		);
    }
	
	
	/**** SET_SELECT  *****/
	function set_select(index) {
		if ($selected_item >= 0) $(".listTab tbody tr:nth-child("+$selected_item+")").removeClass("selected");
		$selected_item = ++index;
		$(".listTab tbody tr:nth-child("+$selected_item+")").addClass("selected");
	}
	
	
	/**** CHANGE DISPLAY des Membres participants  *****/
	function change_display($type) {    // type = cat ou list
		// On change l'affichage de la liste
		$cat = $type == "cat" ? "none" : "block";
		$list = $type == "list" ? "none" : "block";
		$("#cat_icon").css("display",$cat);
		$("#list_alpha").css("display",$cat);
		$("#list_cat").css("display",$list);
		$("#list_icon").css("display",$list);
	}
		
	// Permet de masquer les catégories vides et afficher les pleines
	function refresh_cat() { 
		$("#list_cat ul").each(function($index) {
			// Si aucun nom, on masque la catégorie
			if ($(this).html().trim() == "") $(this).parent().css("display","none");
			else $(this).parent().css("display","block");
		});
		
	}
	
	
	/**** PLAY_NEXT  *****/
	function play_next() {
	
		if ($selected_item >= 0 && $selected_item < $(".listTab tbody tr").length)  {
			set_select($selected_item);
			
			// On saute les pauses
			if ($(".listTab tbody tr:nth-child("+$selected_item+")").attr("idSong") == -1) play_next();
			
			// On sélectionne le titre suivant et on le joue
			else update_player($(".listTab tbody tr:nth-child("+$selected_item+")").prop("id"),true);
		}
		// On a fini de lire la liste et on selectionne le premier titre
		else {
			set_select(0);
			update_player($(".listTab tbody tr:nth-child("+$selected_item+")").prop("id"),false);
		}
	
	}
		
 </script>

<!-- permet à javascript de savoir si l'utilisateur participe à la jam ou pas -->
<span id="attend" style="display:none"><?php echo ($attend ? 1 : 0); ?></span>
<span id="appel_benevole" style="display:none"><?php echo ($jam_item['appel_benevole'] ? 1 : 0); ?></span>

 

<div class="main_block">
	
	<div id="jam_content" class="block_content">	
		<div class="block_head" style="display:block;">
			<h3 style="float:left; display:flex" id="jam_title" class="block_title"><?php echo $jam_item['title']; ?></h3>
			
			<div style="padding-top:5; float:right;"><small class="soften"><?php echo '['.$jam_item['date'].']'; ?></small></div>
			<div id="date_label" style="display:none"><?php echo $jam_item['date']; ?></div>
			<hr style="clear:both;">
		</div>
		<p><?php echo $jam_item['text_html']; ?></p>
	</div>
	
	<!-- /////////////////////////  BLOCKS DE DROITE  /////////////////////////////////////-->
	<div class="block_content_right" style="width:34%;">
		<center>
		<!-- DATEPICKER  -->
		<small><div name="jam_date" id="jam_date"></div></small>
		<br>
		
		<!-- Si l'utilisateur ne participe pas déjà et si la jam n'est pas archivée, on affiche les boutons d'inscription -->
		<?php if (!$attend && !$attend_stage && !$jam_item['is_archived']) :?>
			
			<!-- Bouton d'inscription à la jam -->
			<div id="join_btn" style="display:block;">
				<br>
				<input type="submit" name="join_jam" style="margin 5 auto; font-family: BorisBlackBloxx; padding:5 25" 
						value="PARTICIPER A LA JAM"
						onclick="inscription()"
				/>
				<br>
				<br>
				
				<!-- Bouton d'inscription au stage -->
				<?php if ($stage_item != "null") :?>
					<input type="submit" name="join_stage" style="margin 5 auto; font-family: BorisBlackBloxx; padding:5 25" 
							value="PRE-INSCRIPTION STAGIAIRES"
							onclick="join_stage('<?php echo $jam_item['slug']; ?>')"
					/>
					<br>
					<br>
				<?php endif;?>
			</div>
		<?php endif;?>

		</center>
		
		<!-- Bénévoles -->
		<div id="benevole" style="font-size: 85%; display:<?php echo ($attend && !$jam_item['is_archived'] && $jam_item['appel_benevole']) ? "block" : "none" ; ?>">
			<?php if ($this->session->userdata('logged')): ?>
				<small>
				<table class="block_info soften" style="padding: 10 10 0 10; width:90%">
					<tr id="buffet" style="display:<?php if (isset($member) && $attend) echo ($member->buffet) ? "block" : "none"; else echo "none"; ?>">
						<td style="width:17"><img style='height: 16;' src='/images/icons/fork.png' title='Buffet'></td>
						<td>Je participe au buffet</td>
					</tr>
					<tr id="billet" style="display:<?php if (isset($member) && $attend) echo ($member->billet) ? "block" : "none"; else echo "none";  ?>">
						<td style="width:17"><img style='height: 16;' src='/images/icons/ticket.png' title='Billeterie'></td>
						<td>Je participe à la billeterie</td>
					</tr>
					<tr id="balance" style="display:<?php if (isset($member) && $attend) echo ($member->balance) ? "block" : "none"; else echo "none";  ?>">
						<td style="width:17"><img style='height: 16;' src='/images/icons/mix.png' title='Balance'></td>
						<td>Je suis présent aux balances</td>
					</tr>
					<tr id="vide" style="display:<?php if (isset($member) && $attend) echo (!$member->buffet && !$member->billet && !$member->balance) ? "block" : "none"; else echo "none";  ?>">
						<td style="width:17"><img style='height: 16;' src='/images/icons/croche.png' title='Balance'></td>
						<td>Je participe à la soirée en tant que musicien uniquement</td>
					</tr>
					
					<tr>
						<td colspan="2"><div id="update_link" style="float:right; margin-right:-5"><small><a href="javascript:update_task()">modifier</a></div></td>
					</tr>
				</table>
				
				</small>
			<?php endif; ?>
		</div>
		<br>
		
		
		<!-- *********  Liste des participants ********** !-->
		<div class="small_block_list_title soften">Liste des participants <small><?php if (($this->session->userdata('logged'))) echo '(<span id="nb_jammeur">'.sizeof($list_members).'</span>)' ?></small></div>
		<div id="member_list" class="block_info" style="font-size: 85%;">
			
			<!-- Visiteur logué !-->
			<?php if ($this->session->userdata('logged')) : ?>
			
				<!-- Sous menu d'affichage !-->
				<div class="small_block_menu">
					<a id="cat_icon" href='javascript:change_display("cat")'><img style='height: 14;' src='/images/icons/cat.png' title='afficher par categories'></a>
					<a id="list_icon" href='javascript:change_display("list")' style='display:none'><img style='height: 14;' src='/images/icons/list.png' title='afficher la liste'></a>
				</div>

				<!-- liste des participants par orde alphabétique !-->
				<ul id="list_alpha" style='list-style-type: none; padding:0; cursor:default'>
					<?php foreach ($list_members as $tmember) {
						// On affiche le nom du participant (et son instru en hide)
						echo "<li class='member'><span>".$tmember->pseudo.'</span><small><span class="instru soften" style="display:none"> > '.$this->instruments_model->get_instrument($tmember->idInstru1).'</span></small></li>';
					} ?>
				</ul>
				
				<!-- liste des participants par catégorie !-->
				<ul id="list_cat" style='list-style-type: none; padding:0; cursor:default; display:none'>
					<?php foreach ($cat_instru_list as $cat): ?>
						<li id="<?php echo $cat['name']; ?>">
							<!-- Catégorie !-->
							<h4><img style="height:16px; vertical-align: text-top;" src="<?php echo base_url().'images/icons/'.$cat['iconURL']; ?>">
								<?php echo strtoupper($cat['name']) ?>
							</h4>
							<ul class="cat_content" style='list-style-type: none; padding:0; margin-top:-10'>
								<?php foreach ($list_members as $tmember) {
									// On cherche si l'idInstru1 existe dans la catégorie
									$key = array_search($tmember->idInstru1,$cat['list']);
									if ($cat['list'][$key] == $tmember->idInstru1) echo "<li class='member'><span>".$tmember->pseudo.'</span><small><span class="instru soften" style="display:none"> > '.$this->instruments_model->get_instrument($tmember->idInstru1).'</span></small></li>';
								} ?>
							</ul>
						</li>
					<?php endforeach; ?>
				</ul>
				
				<!-- lien pour se désinscrire !-->
				<?php if (!$jam_item['is_archived']) echo '<div id="quit_link" style="float:right;'. ($attend ? "display:inline;" : "display:none;") .'margin-right:-10"><small><a href="javascript:quit_jam()">se désinscrire</a></small></div>'; ?>
				
			<!-- Visiteur non logué !-->
			<?php else: ?>
				<small>Il y a actuellement <?php echo sizeof($list_members)?> participant.e.s à cette jam.</small>
			<?php endif; ?>
		</div>

	</div>
	<!------------------------------------------->
	
	
	<br>
	<br>
	<!-- /////////////////////////  BLOCKS DE GAUCHE  /////////////////////////////////////-->
	<div style="width:551; max-width:630px;">
	
	
		
		<!-------- LIEU  --------->
			<div id="lieu_infos" class="small_block_info" style="display:flex;">
				<div style="align-self:center"><img style="height: 16; margin:0 16 0 16;" src="/images/icons/lieu.png" alt="lieu"></div>
				<div class="small_block_col">
					<p>
						<b><?php echo $lieu_item['nom']; ?></b>
					</p>
					<!-- On n'affiche pas si pas de donnée !-->
					<?php if ($lieu_item['adresse'] != "" || $lieu_item['web'] != ""): ?>
						<p id="lieu_details" class="soften" style="font-size: 90%">
							<span id="lieu_adresse" style="display:<?php echo $lieu_item['adresse'] == "" ? "none" : "block" ?>"><?php echo $lieu_item['adresse']; ?></span>
							<a id="lieu_web" target="_blanck" style="display:<?php echo $lieu_item['web'] == "" ? "none" : "block" ?>" href="http://<?php echo $lieu_item['web']; ?>"><?php echo $lieu_item['web']; ?></a>
						</p>
					<?php endif; ?>
				</div>
			</div>
		
		<!-- PLANNING INFOS  -->
		<?php if ($jam_item['date_debut'] < $jam_item['date_fin']) : ?>
		<div id="time_infos" class="small_block_info">
			<p class="soften"><small>
				<?php if ($this->session->userdata('logged') == true) : ?>
					<span class="numbers"><?php echo $jam_item['date_bal']; ?></span> > balances<br>
				<?php endif; ?>
				<span class="numbers"><?php echo $jam_item['date_debut']; ?></span> > début<br>
				<span class="numbers"><?php echo $jam_item['date_fin']; ?></span> > fin
			</small></p>
		</div>
		<?php endif; ?>
		
		<br>
		
	
		<!-- INFOS STAGIAIRES   -->
		<?php if ($attend_stage): ?>
			<!-- Chèque non reçu   -->
			<?php if ($attend_stage[0]->cheque == 0): ?>
				<div id="stagiaire_infos" class="small_block_alert">
					<p>
						<b><u>Attente de réglement</b></u><br>
						Vous vous êtes inscris au stage de cette jam le <b><?php echo $stage_date_inscr; ?></b> et nous n'avons actuellement toujours pas reçu votre réglement.<br>
						Si cet envoi a été fait depuis, ne tenez pas compte de ce message. Dans le cas contraire, nous vous rappelons que nous attendons un réglement de <b><?php echo $stage_item['cotisation']."&euro;"; ?></b> par chèque à l'adresse suivante :
					</p>
					<p style="margin:10 0 10 50"><b><?php echo $stage_item['ordre'].'<br>'.$stage_item['adresse']; ?></b>
					</p>
				</div>
			<?php else : ?>
				<div id="stagiaire_infos" class="small_block_confirm">
					<p>
						<b><u>Réglement reçu</b></u><br>
						Nous avons bien reçu votre réglement. Des informations vous seront communiquées en temps voulu à cette adresse : <br><b><?php echo $attend_stage[0]->email_tuteur ? $attend_stage[0]->email_tuteur : $member->email ; ?></b>.<br>
						Nous vous rappelons par ailleurs que le début du stage aura lieu le <b><?php echo $stage_date_debut_norm; ?></b> à <b><?php echo $lieu_item['nom']; ?></b>.
					</p>
				</div>
			<?php endif;?>
			<br>
		<?php endif;?>
		
		
		
		<!-- LIEN VERS LES INSCRIPTIONS   -->
		<?php if ($playlist_item != "null" && ( (  $jam_item["acces_inscriptions"] > 0  &&  $jam_item['is_archived'] == false ) ||  $this->session->userdata('admin') > 0  )): ?>
		<div id="inscr_link" class="green_alert" style="text-align:center; margin-left:20; display:<?php if ($attend) echo "block"; else echo "none" ?>">
			<a href="<?php echo site_url()."/jam/inscriptions/".$jam_item['slug']; ?>">Inscription aux morceaux<?php if ( ( $jam_item["acces_inscriptions"] == 0 || $jam_item['is_archived']  )&&  $this->session->userdata('admin') > 0  ) echo " - accès spécial admin"?></a>
		</div>
		<?php endif; ?>
		
		<br>
		
		<!-- LISTE DES TITRES   -->
		<?php if ($playlist_item != "null"): ?>
			<div class="block_list_title soften">Liste des titres
					<!-- /* admin */ --><?php if ($this->session->userdata('admin') == "1") echo ' - '.$playlist_item['infos']['title'] ?></div>
				<table class="list_content listTab bright_bg is_playable" style="width:550;">
					<thead>
						<tr>
							<th></th>
							<th width="10" style="text-align:center"><span class="choeurs"><img style='height: 12;' src='/images/icons/heart.png' title='choeurs'></span></th>
							<th width="10" style="text-align:center"><span class="cuivres"><img style='height: 16; margin:0 2' src='/images/icons/tp.png' title='cuivres'></span></th>
							<?php if ($stage_item != "null"):?>
								<th width="10" style="text-align:center"><span class="stage"><img style='height: 16;' src='/images/icons/metro.png' title='réservé aux stagiaires'></span></th>
							<?php endif; ?>
						</tr>
					</thead>
					<tfoot>
						<tr>
							<th></th>
							<th style="text-align:center"><img style='height: 10;' src='/images/icons/heart.png'></th>
							<th style="text-align:center"><img style='height: 14; margin:0 2' src='/images/icons/tp.png'></th>
							<?php if ($stage_item != "null"):?>
								<th width="10" style="text-align:center"><img style='height: 14; margin:0 2' src='/images/icons/metro.png'><span class="stage"></span></th>
							<?php endif; ?>
						</tr>
					</tfoot>
					<tbody id="playlist_body">
					<?php foreach ($playlist_item['list'] as $key=>$ref): ?>
						<tr class="list_elem" id="<?php echo $ref->idSong ?>" idSong="<?php echo $ref->idSong ?>"
							<?php if ($this->session->userdata('logged') == true && $ref->idSong != -1) : ?>
								onclick="update_player('<?php echo str_replace("'", "\'",$ref->idSong); ?>',false); set_select('<?php echo $key; ?>');"
							<?php endif; ?>
						>
						<?php if ($ref->idSong != -1): ?>
								<td><?php echo $ref->titre ?><small class="soften"> - <?php echo $ref->label ?></small></td>
								<td style="text-align: center"><?php if ($ref->choeurs == 1) echo "<img style='height: 12;' src='/images/icons/ok.png'>";?></td>
								<td style="text-align: center"><?php if ($ref->cuivres == 1) echo "<img style='height: 12;' src='/images/icons/ok.png'>";?></td>
								<?php if ($stage_item != "null"):?>
									<td style="text-align: center"><?php if ($ref->reserve_stage == 1) echo "<img style='height: 12;' src='/images/icons/ok.png'>";?></td>
								<?php endif; ?>
						<?php else: ?>
								<td colspan=4>-== pause ==--</td>
						<?php endif; ?>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>	
		<?php endif; ?>
		
		
		<!-- WISHLIST -->
		<?php if ($playlist_item == "null"): ?>
			<div class="block_list_title soften">Aucune liste de morceaux n'a été sélectionnée pour l'instant !</div>
				<div id="whishlist_content" class="block_list_content" style="margin-left:20;">
					<div class="list_content bright_bg" style="width:500;">
					
					
						<div id="head_wishlist">
							Vous pouvez proposer vos titres ci-dessous<?php if (!$this->session->userdata('logged')) echo " en étant inscrit au site" ?>.<br>
							<?php if ($this->session->userdata('logged')) {
								echo "Merci de poster un lien vers de l'audio.<br>";
								echo "Sélectionner si possible un titre reggae possédant :<br>";
								echo "<ul><li>des soufflants</li><li>des choeurs</li><li>dont les paroles sont trouvables sur internet</li></ul>";
								echo "</div>";
							}?>
						
						<hr>
						
						<div style="margin-left:25" id="wishlist">
						<?php if ($wishlist != "null"): ?>
							<?php foreach ($wishlist as $wish_elem): ?>
								<div class="soften"><small><?php echo $wish_elem['pseudo'] ?> à proposé :</small></br>
									<a href="<?php echo $wish_elem['url'] ?>" target="_blanck"><?php echo $wish_elem['titre'] ?></a>
								</div>
								<hr>
							<?php endforeach; ?>
						<?php endif; ?>
						</div>
						<br>
						
						<?php if ($this->session->userdata('logged') == true) : ?>
						<div>
						<form action="javascript:add_wish()">
							<div style="margin:10 35"><u>Votre proposition</u></div>
							<div style="height:20; margin:5 60">
								<label for="wish_titre">Titre</label>
								<input id="wish_titre" class="right" type="text" name="wish_titre" size="45" value="" required/>
							</div>
							<div style="height:20; margin:5 60">
								<label for="wish_url">URL</label>
								<input id="wish_url" class="right" type="url" name="wish_url" size="45" value="" required/>
							</div>
							<input style="margin-right:80" class="right button" type="submit" name="submit" value="Proposer" />
						</form>
						</div>
						<?php endif; ?>

					</div>
				</div>
			<br>
		<?php endif; ?>
	
	</div>
	
</div>