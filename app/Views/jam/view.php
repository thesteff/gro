<!-- Textarea resizable !-->
<script type="text/javascript" src="<?php echo base_url();?>/ressources/script/readmore.min.js"></script>

<!-- autoresize texarea !-->
<script type="text/javascript" src="<?php echo base_url();?>/ressources/script/autosize.js"></script>

<!-- bootstrapValidator !-->
<!-- doit être loadé ici car loading dynamique pour les inscriptions stage ne fonctionne pas !-->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-validator/0.5.3/js/bootstrapValidator.js"></script>
<!--<script type="text/javascript" src="<?php echo base_url();?>ressources/script/validator.js"></script>!-->


<?php
	// On récupère les variables de sessions
	$session = \Config\Services::session();
?>

<script type="text/javascript">

	// Conteur pour savoir quand on a fini les appels récursifs de messages
	var msgCounter = 0;
	
	
	$(function() {
		
		// On masque les éléments inacessibles si besoin
		refresh_jam();
		
		// On active le rafraichissement de la playlist
		//setInterval(refresh_playlistIndex, 5000);	
		
		
		// On active les handlers pour le player si besoin (playlist + logged)
		<?php 
			if (isset($player) && $player)
				echo "setupTriggers();";
		?>
		
		
		// ****** READMORE ********
		$('.jam #jamText').readmore({
			collapsedHeight: 200,
			moreLink: '<a class="pull-right btn btn-default btn-xs" style="margin-top:15px"><i class="glyphicon glyphicon-chevron-down"></i></a>',
			lessLink: '<a class="pull-right btn btn-default btn-xs" style="margin-top:15px"><i class="glyphicon glyphicon-chevron-up"></i></a>'
		});
		
		$(".jam #jamText").css("display","block");
		
		// ****** READMORE ********
		$('.jam #stageText').readmore({
			collapsedHeight: 200,
			moreLink: '<a class="pull-right" href="#"><i class="glyphicon glyphicon-chevron-down"></i></a>',
			lessLink: '<a class="pull-right" href="#"><i class="glyphicon glyphicon-chevron-up"></i></a>'
		});
		
		
		
		// ****** ACTIVITY ********
		$("#activity_infos button").each(function() {

			$(this).click(function(e) {
				
				// On récupère l'ancien et le nouveau item select
				$target = $(this).prop("id").replace("ActivityBtn","");
				$old_item = $("#activity_infos button.coloredItem").prop("id").replace("ActivityBtn","");
				
				// IHM
				if ( ! $(this).hasClass("coloredItem")) {
					$("#activity_infos button.coloredItem").removeClass("coloredItem");
					$(this).addClass("coloredItem");
				}
				
				// On hide l'ancien panel
				$(".jam .subPanel [id='"+$old_item+"TextPanel']").fadeOut("fast", function() {
					$(".jam .subPanel #"+$target.concat("TextPanel")).removeClass("hidden");
					// On affiche le nouveau panel
					$(".jam .subPanel #"+$target.concat("TextPanel")).fadeIn("fast");
				});
				
				// On hide l'ancien lieu
				$(".jam .subPanel [id='"+$old_item+"LieuPanel']").fadeOut("fast", function() {
					$(".jam .subPanel #"+$target.concat("LieuPanel")).removeClass("hidden");
					// On affiche le nouveau lieu
					$(".jam .subPanel #"+$target.concat("LieuPanel")).fadeIn("fast");
				});
				
				// On hide l'ancien planning
				$(".jam .subPanel [id='"+$old_item+"PlanningPanel']").fadeOut("fast", function() {
					$(".jam .subPanel #"+$target.concat("PlanningPanel")).removeClass("hidden");
					// On affiche le nouveau lieu
					$(".jam .subPanel #"+$target.concat("PlanningPanel")).fadeIn("fast");
				});

			});
		});
		
		
		// ********** TOOLTIP  ***************
		$('body').tooltip({
			selector: '[rel="tooltip"]'
		});
	
		
		// ****** DYNAMIC MODAL ********
		$("[id$='Modal']").on("show.bs.modal", function(e) {
			var link = $(e.relatedTarget);
			$(this).find(".modal-body").load(link.attr("href"));
		});
		
		
		// ***** TABS AJAX LOADING ********
		$('#jamTabs .nav a[data-toggle="tab"]').click(function (e) {

			// Si le LI est disabled, on ne fait rien
			if ($(this).parent().hasClass("disabled")) return false;
			
			// Sinon, si on a un data-url, on charge l'onglet dynamiquement
			else if (typeof $(this).attr("data-url") != 'undefined') {

				e.preventDefault();

				var url = $(this).attr("data-url");
				var href = this.hash;
				var pane = $(this);
	
				// On ne load que si nécessaire en testant la présence des Block
				if (  (url.indexOf("jam/inscriptions") >= 0 && $("#inscrBlock").length == 0) ||
						(url.indexOf("jam/repetitions") >= 0 && $("#repetBlock").length == 0)   ) {
							
					// ajax load from data-url
					$(href).load(url,function(result){
						pane.tab('show');
						// On update le css (center TD)
						update_style();
					});
				}
			}
		});
		
		
		// ***** TABS REMEMBER + REFRESH ********
		$('a[data-toggle="tab"]').on('show.bs.tab', function(e) {
			localStorage.setItem('activeTab'+<?php echo $jam_item["id"] ?>, $(e.target).attr('href'));
			// refresh
			if($(e.target).attr('href') == "#infos") {
				update_message_panel();
				update_ressource_panel();
			}
		});

		var activeTab = localStorage.getItem('activeTab'+<?php echo $jam_item["id"] ?>);
		if(activeTab) $('#menu_tabs a[href="' + activeTab + '"]').click(); // On simule un click pour lancer les chargements dynamiques
		
		$("#jamTabs").css("display","block");


		// ***** HACK CKEDITOR :: Hack pour faire marcher le CKEditor dans une modal
		$.fn.modal.Constructor.prototype.enforceFocus = function() {
			$( document )
			.off( 'focusin.bs.modal' ) // guard against infinite focus loop
			.on( 'focusin.bs.modal', $.proxy( function( e ) {
				if (
					this.$element[ 0 ] !== e.target && !this.$element.has( e.target ).length
					// CKEditor compatibility fix start.
					&& !$( e.target ).closest( '.cke_dialog, .cke' ).length
					// CKEditor compatibility fix end.
				) {
					this.$element.trigger( 'focus' );
				}
			}, this ) );
		};
		
	});
	
	
	
	/************** INSCRIPTION  ****************/
	function inscription() {

		// L'utilisateur n'est pas loggé
		if ($("#memberLogin").length == 0) {
			// Modal
			btnInscr = "<button class='btn btn-default' onclick='location.href=\"<?php echo site_url('members/create'); ?>\"'><li class='glyphicon glyphicon-user'></li>&nbsp;&nbsp;Inscription</button>";
			btnLog = "<button class='btn btn-default' data-toggle='modal' href='#modal_login'><li class='glyphicon glyphicon-log-in'></li>&nbsp;&nbsp;Connexion</button>";
			msg = "<p>Pour participer à la jam, vous devez d'abord devenir membre du <b>Grenoble Reggae Orchestra</b> en vous inscrivant sur le site ou vous identifier sur votre compte si vous êtes déjà membre.</p>";
			msg += "<div style='display:flex; justify-content:center'><div class='btn-toolbar'>" + btnInscr + btnLog + "</div></div>";
			$("#modal_msg").modal({backdrop: true});
			$("#modal_msg .modal-dialog").addClass("default");
			$("#modal_msg .modal-header").html("Participation impossible !");
			$("#modal_msg .modal-body").html(msg);
			$("#modal_msg").modal('show');
		}
		
		// L'utilisateur n'a pas validé son email
		else if (<?php echo (isset($member->validMail) && $member->validMail) ? 0 : 1 ?>) {
			// Modal erreur
			$("#modal_msg").modal({backdrop: true});
			$("#modal_msg .modal-dialog").addClass("default");
			$("#modal_msg .modal-header").html("Participation impossible !");
			$("#modal_msg .modal-body").html("<p>Pour participer à la jam, vous devez d'abord valider votre adresse email sur la page de votre <b><a href='<?php echo site_url('members/').(isset($member->slug) ? $member->slug : 0) ?>'>profil</a></b>.</p>");
			$("#modal_msg .modal-footer").html('<a id="modal_close" href="#" data-dismiss="modal">Fermer</a>');
			$("#modal_msg").modal('show');			
		}
		
		// L'utilisateur est loggé il peut joindre la joindre la jam
		else {
			join_jam();
		}
    }
	
	
	/****** JOIN JAM  *******/
	function join_jam() {
		
		// On change le curseur
		document.body.style.cursor = 'wait';
	
		// Requète ajax au serveur
		$.post("<?php echo site_url('ajax_jam/join_jam'); ?>",
		
			{	
				'slugJam':'<?php echo $jam_item['slug']; ?>',
				'id':'<?php echo isset($member->id) ? $member->id : -1 ?>',
				'event_admin':0
			},
		
			function (return_data) {
				
				$obj = JSON.parse(return_data);
				
				// On change le curseur
				document.body.style.cursor = 'default';
				
				// Modal
				if ($obj['state'] == 1) {
					
					// Si la jam est privée on fait un reload de la page entière
					if ($("#privee").html() == 1) location.reload();
					
					// On actualise les données affichées
					refresh_jam("join_jam");
					
					// Si l'utilisateur essaye de s'inscrire au stage, on ferme la modal de participation à la jam et on ouvre celle de la préinscription
					if (($("#modal_msg").data('bs.modal') || {}).isShown) {
						$('#modal_msg').on('hidden.bs.modal', function () {
							$("#hiddenStageBtn").trigger("click");
						});
						$("#modal_msg").modal("hide");
					}
					
				}
				else {
					// Erreur
					$("#modal_msg .modal-dialog").removeClass("success");
					$("#modal_msg .modal-dialog").addClass("error");
					$("#modal_msg .modal-dialog").addClass("backdrop","static");
					$("#modal_msg .modal-header").html("Erreur !");
					$("#modal_msg .modal-body").html($obj['data']);
					$("#modal_msg .modal-footer").html('<a id="modal_close" href="#" data-dismiss="modal">Fermer</a>');
				}
			}
		);
    }
	
	
	
	/************** STAGE  ****************/
	/**************************************/
	function popup_Stage_preinscription()  {

		// Pas de popup si la jam est archivée
		<?php if (!$jam_item['is_archived']) :?>
	
			$("#modal_msg .modal-header").empty();
			$("#modal_msg .modal-body").empty();
			$("#modal_msg .modal-footer").empty();
			
			// L'utilisateur n'est pas loggé
			if ($("#memberLogin").length == 0) {
				// Modal
				btnInscr = "<button class='btn btn-default' onclick='location.href=\"<?php echo site_url('members/create/'); ?>\"'><li class='glyphicon glyphicon-user'></li>&nbsp;&nbsp;Inscription</button>";
				btnLog = "<button class='btn btn-default' data-toggle='modal' href='#modal_login'><li class='glyphicon glyphicon-log-in'></li>&nbsp;&nbsp;Connexion</button>";
				msg = "<p>Pour faire une pré-inscription au stage, vous devez d'abord devenir membre du <b>Grenoble Reggae Orchestra</b> en vous inscrivant sur le site ou vous identifier sur votre compte si vous êtes déjà membre.</p>";
				msg += "<div style='display:flex; justify-content:center'><div class='btn-toolbar'>" + btnInscr + btnLog + "</div></div>";
				$("#modal_msg").modal({backdrop: true});
				$("#modal_msg .modal-dialog").addClass("default");
				$("#modal_msg .modal-header").html("Pré-inscription impossible !");
				$("#modal_msg .modal-body").html(msg);
				$("#modal_msg").modal('show');
			}
			
			// L'utilisateur est loggé mais ne participe pas à la jam  !!!! => un stagiaire peut forcément se préinscrire
			/*else if ($('#attend').html() == '0') {

				// Modal
				msg = "<p>Pour faire une pré-inscription au stage, vous devez d'abord indiquer que vous participez à la jam.</p>";
				$clonedBtn = $(".action_bar button[name=join_jam]").clone();
				$domMsg = $($.parseHTML(msg)).append("<div style='display:flex; justify-content:center'><div class='btn-toolbar'></div></div>");
				
				$("#modal_msg").modal({backdrop: true});
				$("#modal_msg .modal-dialog").addClass("default");
				$("#modal_msg .modal-header").html("Pré-inscription impossible !");
				$("#modal_msg .modal-body").append($domMsg);
				$("#modal_msg .modal-body .btn-toolbar").append($clonedBtn);
				$("#modal_msg").modal('show');
			}*/
			
			// On ouvre la modal de préinscription si l'utilisateur n'a pas déjà fait de préinscription
			else if ($("#attend_stage").html() == 0) {
				// On click sur le hidden btn permettant de charger une page dans la modal dynamiquement
				$("#hiddenStageBtn").trigger("click");
			}
		<?php endif; ?>
	}
	
	
	
	/*************** QUIT JAM  *********************/
	function quit_jam_modal()  {
		// Modal
		btn1 = "<button class='btn btn-default' onclick='javascript:quit_jam()'>Se désinscrire</button>";
		btn2 = "<button class='btn btn-default' data-dismiss='modal'>Annuler</button>";
		msg = "<p>En vous désinscrivant de cette jam vous perdrez toutes les informations qui y sont associées (inscriptions sur les morceaux).</p>";
		msg += "<div style='display:flex; justify-content:center'><div class='btn-toolbar'>" + btn1 + btn2 + "</div></div>";
		$("#modal_msg").modal({backdrop: true});
		$("#modal_msg .modal-dialog").addClass("default");
		$("#modal_msg .modal-header").html("Avertissement");
		$("#modal_msg .modal-body").html(msg);
		$("#modal_msg").modal('show');
    }
	

	function quit_jam()  {
		
		// On change le curseur
		document.body.style.cursor = 'wait';
		
		// Requète ajax au serveur
		$.post("<?php echo site_url('ajax_jam/quit_jam'); ?>",
		
			{
				'slugJam':'<?php echo $jam_item['slug'] ?>',
				'id':'<?php echo isset($member) ? $member->id : -1 ?>'
			},
	
			function (return_data) {
				
				$obj = JSON.parse(return_data);
				// On change le curseur
				document.body.style.cursor = 'default';
				
				// On ferme la modal
				$("#modal_msg").modal('hide');
				
				// Modal
				if ($obj['state'] == 1) {
					
					// Si la jam est privée on fait un reload de la page entière
					if ($("#privee").html() == 1) location.reload();
					
					// Sinon on refresh
					refresh_jam("quit_jam");
				}
				else {
					// Erreur
					$("#modal_msg").modal({backdrop: 'static', keyboard: true });
					$("#modal_msg .modal-dialog").removeClass("error");
					$("#modal_msg .modal-dialog").addClass("success");
					$("#modal_msg .modal-header").html("Erreur !");
					$("#modal_msg .modal-body").html($obj['data']);
					$("#modal_msg .modal-footer").html('<a id="modal_close" href="#" data-dismiss="modal">Fermer</a>');
					$("#modal_msg").modal('show');
				}
			}
		);
	}
	
	
	
	// ***************  REFRESH_JAM   **********************/
	
	// Refresh pour les action d'inscription ou desinscription (texte d'info, btn d'inscription, liste des membres)
	function refresh_jam($action) {
	
		//console.log("refresh_jam : "+$action);
	
		if ($action == "join_jam") {
			var newBtn = "<button class='btn btn-checked' type='submit' name='quit_jam' onclick='quit_jam_modal()'><i class='glyphicon glyphicon-ok'></i>&nbsp;&nbsp;&nbsp;Je participe</button>";
			$("button[name=join_jam]").replaceWith(newBtn);
			
			// On change l'état de la span attend
			$('#attend').text("1");
			
			// On rend visible ce qui ne l'était pas
			$('.attend').css("display","block");
			$('.not_attend').css("display","none");
			
			// On active ce qui était disabled (jam_tabs)
			$('.attend_active').removeClass("disabled");
			
			// On récupère l'avatar
			$img = '<img class="img-circle miniAvatar" src="'+$("#memberLogin #miniAvatar").prop("src")+'" width="16" height="16">';
			
			// On ajoute le member à la liste des participants (alphabétique)
			var i=1;
			while ($("#member_list #list_alpha .member").length > i && $("#member_list #list_alpha .member:nth-child("+i+")").attr("value").toUpperCase() < $("#memberLogin").attr("value").toUpperCase() ) {
				i++;
			}
			$("#member_list #list_alpha #list_main .member:nth-child("+i+")").before("<a class='label label-success member' idMember='<?php if (isset($member)) echo $member->id; else echo "-1" ?>'>"+$img+$("#memberLogin").attr("value")+"</a> ");
			
			// On ajoute le member à la liste des participants (pupitre)
			$("#member_list #list_pupitre li[label="+$("#mainPupitreLabel").html()+"] .pupitre_content").append("<span class='label label-success member' idMember='<?php if (isset($member)) echo $member->id; else echo "-1" ?>'>"+$img+$("#memberLogin").attr("value")+"</span> ");
		}
		else if ($action == "quit_jam" || $('#attend').text() == 0) {
			
			var newBtn = "<button class='btn btn-default' type='submit' name='join_jam' <?php if (sizeof($list_members) >= $jam_item['max_inscr'] && $jam_item['max_inscr'] != -1) echo "disabled" ?> onclick='inscription()'>Participer</button>";
			$("button[name=quit_jam]").replaceWith(newBtn);
			
			// On change l'état de la span attend
			$('#attend').text("0");
			
			// On rend visible ce qui ne l'était pas
			$('.attend').css("display","none");
			$('.not_attend').css("display","block");
			
			// On disabled ce qui était active
			$('.attend_active').addClass("disabled");
						
			// On simule un click sur le premier onglet (info) toujours accessible au cas où on quitte la jam sur un onglet attend_active
			if ($("#jamTabs .nav-tabs .active").hasClass("disabled")) $('#menu_tabs li:first-child a').click();
			
			// On retire le member de la liste des participants
			$("#member_list #list_alpha .member[idMember=<?php if (isset($member)) echo $member->id; else echo "-1" ?>]").remove();
			$("#member_list #list_pupitre li[label="+$("#mainPupitreLabel").html()+"] .pupitre_content span[idMember=<?php if (isset($member)) echo $member->id; else echo "-1" ?>]").remove();
		}
		
		
		<?php if (isset($stage_item)) : ?>
		// Si la jam a un stage et est archivée, on disabled le bouton d'inscription
			<?php if ($jam_item['is_archived']) :?>
				$("#stagePreinscrBtn").addClass("disabled");
			<?php else: ?>
				// Si la jam a un stage, on gère l'état du bouton du formulaire d'inscription à la jam
				if ($("#attend_stage").html() == 1) {
					// On n'a pas encore reçu le chèque donc on désactive juste le bouton de préinscription stagePreinscrBtn
					if ($("#cheque_stage").html() == 0) {
						$("#stagePreinscrBtn").addClass("disabled");
						$("#stagePreinscrBtn").attr("rel", "tooltip");
						$("#stagePreinscrBtn").attr("data-title", "Un formulaire de pré-inscription a déjà été envoyé !");
					}
				}
			<?php endif; ?>
		<?php endif; ?>
		
		
		// On actualise le msg_panel et rsc_panel
		update_message_panel();
		update_ressource_panel();

		<?php if (isset($member) && $jam_item["acces_jam"] != 2) : ?>
			// On actualise le nombre de jammeurs
			$("#nb_jammeur").empty().append($("#member_list #list_alpha #list_main .member").length);
		<?php endif ?>
	}
	
	
	
	// ***************  REFRESH PLAY INDEX   **********************/
	function refresh_playlistIndex() {
		
		// On change le curseur
		//document.body.style.cursor = 'wait';
	
		// Requète ajax au serveur
		$.post("<?php echo site_url('ajax_jam/get_playlistIndex'); ?>",
		
			{	
				'jamId':"<?php echo $jam_item['id']; ?>",
			},
		
			function (return_data) {
	
				$obj = JSON.parse(return_data);
				// On change le curseur
				//document.body.style.cursor = 'default';
				
				if ($obj['state'] == 1) {
					
					$playlistIndex = $obj['data'];
					// On break si le playlistIndex n'a pas changé
					if ($(".listTab tbody tr:nth-child("+$playlistIndex+") td:first-child i.glyphicon-play").length > 0) return;
					// Sinon set l'icon du play dans la playlist
					else {
						$(".listTab tbody tr td:first-child i.glyphicon-play").remove();
						$(".listTab tbody tr:nth-child("+$playlistIndex+") td:first-child").prepend("<i class='glyphicon glyphicon-play soften'></i>");
					}
				}
			}
		);
	}
	
	
	/********** DELETE JAM ***************/
	function popup_delete_jam(title,slug) {
		$text = "Etes-vous sûr de voulour supprimer la jam <b>"+title+"</b> et tous les fichiers qui lui sont associés ?";
		$confirm = "<div class='modal-footer'>";
			$confirm += "<button type='button' class='btn btn-default' data-dismiss='modal'>Annuler</button>";
			$confirm += "<button type='submit' class='btn btn-primary' onclick='javascript:delete_jam(\""+slug+"\")'>Supprimer</button>";
		$confirm += "</div>";
		
		$("#modal_msg .modal-dialog").removeClass("error success");
		$("#modal_msg .modal-dialog").addClass("default");
		$("#modal_msg .modal-dialog").addClass("backdrop","static");
		$("#modal_msg .modal-header").html("Supprimer la jam");
		$("#modal_msg .modal-body").html($text);
		$("#modal_msg .modal-footer").html($confirm);
		$("#modal_msg").modal('show');
	}
	
	
	function delete_jam(slug) {
		
		// On change le curseur
		document.body.style.cursor = 'wait';
		
		// Requète ajax au serveur
		$.post("<?php echo site_url('ajax_jam/delete_jam'); ?>",
	
			{'jamSlug':slug},
		
			function (return_data) {
				
				$obj = JSON.parse(return_data);
				
				// On change le curseur
				document.body.style.cursor = 'default';
				
				// Modal
				if ($obj['state'] == 1) {
					// Succés
					$("#modal_msg .modal-dialog").removeClass("error");
					$("#modal_msg .modal-dialog").addClass("success");
					$("#modal_msg .modal-dialog").addClass("backdrop","static");
					$("#modal_msg .modal-header").html("Jam supprimée !");
					$("#modal_msg .modal-body").html($obj['data']);
					$("#modal_msg .modal-footer").html('<a id="modal_close" href="<?php echo site_url("jam/"); ?>">Fermer</a>');
				}
				else {
					// Erreur
					$("#modal_msg .modal-dialog").removeClass("success");
					$("#modal_msg .modal-dialog").addClass("error");
					$("#modal_msg .modal-dialog").addClass("backdrop","static");
					$("#modal_msg .modal-header").html("Erreur !");
					$("#modal_msg .modal-body").html($obj['data']);
					$("#modal_msg .modal-footer").html('<a id="modal_close" data-dismiss="modal">Fermer</a>');
				}
				$("#modal_msg").modal('show');
			}
		);
	}
	
	
	
			
	/*************** WISHLIST  *******************/
	function add_wish() {

		if ($('#attend').text() == "0") {
			$msg = "Vous devez participer à la jam pour pouvoir proposer des titres.";
			$("#modal_msg .modal-dialog").removeClass("error success");
			$("#modal_msg .modal-dialog").addClass("default");
			$("#modal_msg .modal-dialog").addClass("backdrop","static");
			$("#modal_msg .modal-header").html("Action impossible");
			$("#modal_msg .modal-body").html($msg);
			$("#modal_msg .modal-footer").html('<a id="modal_close" href="#" data-dismiss="modal">Fermer</a>');
			$("#modal_msg").modal('show');
			return;
		}

		$wish_url = $('#wish_url').val();
		
		// On change le curseur
		document.body.style.cursor = 'wait';
		
		// Requète ajax au serveur
		$.post("<?php echo site_url('jam/ajax_add_wish'); ?>",
		
			{
			'slugJam':'<?php echo $jam_item['slug'] ?>',
			'id':'<?php echo isset($member->id) ? $member->id : -1 ?>',
			'url':$wish_url
			},
			
			function (return_data) {
				
				$obj = JSON.parse(return_data);
				
				// On change le curseur
				document.body.style.cursor = 'default';
				
				// Modal
				if ($obj['state'] == 1) {
					
					// On insère le wish_elem
					$("#wishlist").append('<div class=\"soften\"><small>'+$("#memberLogin").html()+' à proposé :</small></br><a href=\"'+$wish_url+'\" target=\"_blanck\">'+$obj['data']+'</a></div>');
					
					// On clean les champs de formulaires
					$("#wish_url").val("");
				}
				else {
					// Erreur
					$("#modal_msg .modal-dialog").removeClass("success");
					$("#modal_msg .modal-dialog").addClass("error");
					$("#modal_msg .modal-dialog").addClass("backdrop","static");
					$("#modal_msg .modal-header").html("Erreur !");
					$("#modal_msg .modal-body").html($obj['data']);
					$("#modal_msg .modal-footer").html('<a id="modal_close" href="#" data-dismiss="modal">Fermer</a>');
				}
			}
		);
    }
	

	
	/***************************** MESSAGES / PANEL INFOS  *******************************/
	function update_message_panel() {
		//console.log("************ update_message_panel");
		
		$panel = $("#msgBlock .list-group");
		$panel.empty();
		
		// ************ Message d'info aux admin
		<?php if ($is_admin): ?>
			$div = "<div class='list-group-item list-group-item-warning'><i class='glyphicon glyphicon-cog'></i>Vous êtes administrateur de la jam.</div>"
			$panel.append($div);
			<?php if ($jam_item['acces_jam'] == 1): ?>
				//$div = "<div class='list-group-item list-group-item-warning'><i class='glyphicon glyphicon-cog'></i>L'accès à la jam est public.</div>"
			<?php else: ?>
				$div = "<div class='list-group-item list-group-item-warning'><i class='glyphicon glyphicon-cog'></i>L'accès à la jam est réservé aux administrateurs.</div>"
				$panel.append($div);
			<?php endif; ?>
		<?php endif; ?>
		
		
		// ************* Messages d'état de la jam
		$div = "";
		$privee = <?php echo $jam_item['acces_jam'] == 2 ? "true" : "false" ?>;
		$invited = <?php if (isset($is_invited)) echo $is_invited ? "true" : "false"; else echo "false"; ?>;
		$archived = <?php echo $jam_item['is_archived'] == 0 ? "false" : "true" ?>;
		$playlist = <?php echo $playlist_item != "null" ? "true" : "false" ?>;
		$affectations = <?php echo $jam_item['affectations_visibles'] == 1 ? "true" : "false"; ?>;
		$attend_stage = $('#attend_stage').text() == 1;
		$cheque_stage = $('#cheque_stage').text() == 1;
		
		
		// Message d'info d'acces aux inscriptons
		if (!$archived && $playlist && !$attend_stage && !$privee) {
			<?php if ($jam_item['acces_inscriptions'] == 1): ?>
				$div = "<div class='list-group-item list-group-item-warning'>Les inscriptions aux morceaux sont ouvertes !</div>"
			<?php else: ?>
				$div = "<div class='list-group-item list-group-item-warning'>Les inscriptions aux morceaux ne sont pas encore accessibles.</div>"
			<?php endif; ?>	
		}
		
		// Message si jam archivée
		else if ($archived) {
			$div = "<div class='list-group-item list-group-item-warning'><i class='bi bi-exclamation-triangle-fill'></i>Cette jam est archivée.</div>"
		}
		
		// Message si pas de playlist
		else if (!$playlist) {
			$div = "<div class='list-group-item list-group-item-warning'><i class='bi bi-exclamation-triangle-fill'></i>La playlist n'a pas encore été fixée.</div>"
		}
		
		// Message si la jam est privée
		else if ($privee) {
			$div = "<div class='list-group-item list-group-item-warning'><i class='bi bi-exclamation-triangle-fill'></i></i>Cette jam est privée et n'est accessible que sur invitation.</div>"
		}
		
		$panel.append($div);
		
		
		// ****************** Message état d'inscription
		if ($('#attend').text() == 1) {
			
			if (!$archived) $text = "Vous participez à la jam";
			else $text = "Vous avez participé à la jam";
			if ($attend_stage && $cheque_stage) $text += " en tant que stagiaire";
			else if ($attend_stage && !$cheque_stage) {
				$text += " et votre inscription au stage est en attende de validation";
			}
			$div = $("<div class='list-group-item list-group-item-warning'>"+$text+"</div>");
	
			// Récap des inscriptions
			// On récupère les inscriptions
			$acces_inscriptions = <?php echo $jam_item['acces_inscriptions'] == 1 ? "true" : "false"; ?>;
			if ($acces_inscriptions) { // && !$attend_stage) {   => un stagiaire peut demander des morceaux..
				if ($("#member_inscriptions").children().length == 0) {
					if (!$archived) $text = " mais vous ne vous êtes inscris sur aucun morceau. Choisissez les titres sur lesquels vous aimeriez jouer en allant sur le tableau d'inscription aux morceaux (<i class='glyphicon glyphicon-list-alt inText'></i>).";
					else $text = " mais vous ne vous êtiez inscris sur aucun morceau.";
					$div.append($text);
				}
				else if ($playlist) {
					if (!$archived) $text = "avez";
					else $text = "aviez";
					$nbMorceau = $("#member_inscriptions").children().length;
					if ($nbMorceau == 1) $div.append(" et vous "+$text+" choisi "+$nbMorceau+" morceau sur lequel jouer :");
					else if ($nbMorceau > 1) $div.append(" et vous "+$text+" choisi "+$nbMorceau+" morceaux sur lesquels jouer :");
					$div.append("<small><ul class='choiceList'>"+$("#member_inscriptions").html()+"</ul></small>");
					if (!$archived && !$affectations) $div.append("Vous serez avertis prochainement des morceaux sur lesquels vous serez affecté.");
				}
			}
			else {
				$div.append(".");
			}
			$panel.append($div);
			
			
			// Rappel ordre si attente de chèque
			<?php if (isset($stage_item) && $attend_stage) : ?>
			if ($attend_stage && !$cheque_stage) {
				$text = "<b><i class='bi bi-exclamation-triangle-fill'><u>Attente de réglement</b></u><br>";
				$text += "<div style='margin: 5px 0px 10px 0px; line-height:95%'><small>Vous vous êtes inscris au stage de cette jam le <b><?php echo $stage_date_inscr; ?></b> et nous n'avons actuellement toujours pas reçu votre réglement.<br><br>";
				$text += "Si cet envoi a été fait depuis, ne tenez pas compte de ce message. Dans le cas contraire, nous vous rappelons que nous attendons un réglement de </small><b><?php echo $stage_item['cotisation']."&euro;"; ?></b><small> par chèque à l'adresse suivante </small></div>";
				$text += "<div class='text-center'><b><?php echo $stage_item['ordre'].'<br>'.$stage_item['adresse_cheque']; ?></b></div>";
				$div = $("<div class='list-group-item list-group-item-danger text-justify'>"+$text+"</div>");
				$panel.append($div);
			}
			<?php endif; ?>
			
		
			// Affectations aux morceaux
			if ($affectations) {
				$div = $("<div class='list-group-item list-group-item-warning'></div>");
				if ($("#member_affectations").children().length == 0) {
					$div.append("L'équipe organisatrice du GRO ne vous a affecté aucun morceau pour le moment.");
				}
				else {
					if ($archived) $text = "vait";
					else $text = '';
					$div.append("L'équipe organisatrice du GRO vous a"+$text+" affecté sur les morceaux suivants :");
					$div.append("<small><ul class='choiceList'>"+$("#member_affectations").html()+"</ul></small>");
				}
				$panel.append($div);
			}
		}
		
		// Ne participe pas
		else if (!$privee) {
			$div = $("<div class='list-group-item list-group-item-warning'>Vous ne participez pas à la jam.</div>");
			$panel.append($div);
		}
		
		// Jam privée et est invité mais ne participe pas encore
		else if ($privee && $invited) {
			$div = $("<div class='list-group-item list-group-item-warning'>Vous avez été invité à cette jam par <b><?php if (isset($is_invited) && $is_invited) echo $invitation[0]['sender']->pseudo ?></b>.</div>");
			$panel.append($div);
		}
		
		
		// Si pas de message, on masque le panel
		if ($panel.children().length > 0) $panel.removeClass("hidden");
		else $panel.addClass("hidden");
	}
	
	
	
	/********************** RESSOURCES  *************************/
	function update_ressource_panel() {
		
		//console.log("************ update_ressource_panel");
		
		// Si l'utilisateur ne participe pas, on n'affiche pas les ressources
		if ($('#attend').text() == '0') {
			$("#rscBlock").addClass("hidden");
			return;
		}
		
		$panel = $("#rscBlock .list-group");
		$panel.empty();
		
		
		// On récupère la liste de fichiers liés à la jam
		// Requète ajax au serveur
		$.post("<?php echo site_url('ajax_jam/get_files'); ?>",
		
			{
			'jamId':'<?php echo $jam_item['id'] ?>',
			},
			
			function (return_data) {
				
				//console.log("ajax_jam/get_files ::"+return_data);
				$obj = JSON.parse(return_data);
				
				// On change le curseur
				document.body.style.cursor = 'default';
				
				// Modal
				if ($obj['state'] == 1) {
					
					// On parcourt la liste de fichier à afficher
					for (i = 0; i < $obj['data'].length; i++) {
						$div = "<div class='list-group-item list-group-item-warning resItem' type='file' resId='"+$obj['data'][i].id+"' resName='"+$obj['data'][i].fileName+"'>";
						
							$div += "<div class='row'>";
						
							// Bouton d'infos
							$div += '<button class="btn btn-default btn-xs resInfosBtn" href=""><i class="glyphicon glyphicon-chevron-right"></i></button>';
							
							// Lien du fichier
							$div += "&nbsp;&nbsp;<a href='"+$obj['jamURL']+'/'+$obj['data'][i].fileName+"' target='_blanck'>"+$obj['data'][i].fileName+"</a>";
							
							<!-- ADMIN SECTION -->
							<?php if ($is_admin): ?>
								$div += '<div class="btn-group btn-group-xs pull-right resAdmin">';
									<!-- Modifier -->
									$div += '<button class="btn btn-default update_btn disabled" href="" data-remote="false" data-toggle="modal" data-target="#updateFileModal" title="Modifier fichier"><i class="glyphicon glyphicon-pencil"></i></button>';
									<!-- Supprimer -->
									$div += '<button class="btn btn-default delete_btn" title="Supprimer fichier"><i class="glyphicon glyphicon-trash"></i></button>';
								$div += '</div>';
							<?php endif; ?>
							
							$div += "</div>";
							
							$div += "<div class='row resInfos' style='padding-top: 7px; display:none'>";
							$div += $obj['data'][i].text;
							$div += "</div>";

						$div += "</div>";
						$panel.append($div);
					}
					
					
					// ***** LISTENER
					// On fixe le comportement du deploy
					$('#rscBlock .resInfosBtn').each(function(index) {
						$(this).on("click", function() {
							$infos = $(this).parents(".resItem").find(".resInfos");
							if ($infos.css("display") == 'none') {
								$(this).children(".glyphicon").removeClass("glyphicon-chevron-right");
								$(this).children(".glyphicon").addClass("glyphicon-chevron-down");
								$(this).parents(".resItem").find(".resInfos").show(100);
							}
							else {
								$(this).children(".glyphicon").addClass("glyphicon-chevron-right");
								$(this).children(".glyphicon").removeClass("glyphicon-chevron-down");
								$(this).parents(".resItem").find(".resInfos").hide(100);
							}
						});
					});
					
					
					// ADMIN
					<?php if ($is_admin): ?>
						// On fixe le comportement des bouttons d'admin de delete
						$('#rscBlock .resAdmin .delete_btn').each(function(index) {
							$(this).on("click", function() {
								$resId = $(this).closest(".resItem").attr("resId");
								$resName = $(this).closest(".resItem").attr("resName");
								popup_delete_res($resId, $resName);
							});
						});
					<?php endif; ?>
					
					// On actualise l'affichage du panel en fonction du nombre de fichier
					if ($obj['data'].length > 0) {
						$panel.removeClass("hidden");
						$("#rscBlock").removeClass("hidden");
					}
					else {
						$panel.addClass("hidden");
						$("#rscBlock").addClass("hidden");
					}
					
				}
				else {
					// Erreur
					/*$("#modal_msg .modal-dialog").removeClass("success");
					$("#modal_msg .modal-dialog").addClass("error");
					$("#modal_msg .modal-dialog").addClass("backdrop","static");
					$("#modal_msg .modal-header").html("Erreur !");
					$("#modal_msg .modal-body").html($obj['data']);
					$("#modal_msg .modal-footer").html('<a id="modal_close" href="#" data-dismiss="modal">Fermer</a>');*/
				}
			}
		);
	}
	
	
	
	
	<?php if ($is_admin): ?>	
	// ******** DELETE RESSOURCES *********/
	function popup_delete_res($resId, $resName) {
		$text = "Etes-vous sûr de vouloir supprimer la ressource <b>"+$resName+"</b> ?";
		$confirm = "<div class='modal-footer'>";
			$confirm += "<button type='button' class='btn btn-default' data-dismiss='modal'>Annuler</button>";
			$confirm += "<button type='submit' class='btn btn-primary' onclick='javascript:delete_res(\""+$resId+"\")'>Supprimer</button>";
		$confirm += "</div>";
		
		$("#modal_msg .modal-dialog").removeClass("error success");
		$("#modal_msg .modal-dialog").addClass("default");
		$("#modal_msg .modal-dialog").addClass("backdrop","static");
		$("#modal_msg .modal-header").html("Supprimer la ressource");
		$("#modal_msg .modal-body").html($text);
		$("#modal_msg .modal-footer").html($confirm);
		$("#modal_msg").modal('show');
	}
	
	function delete_res($resId) {
		
		// On change le curseur
		document.body.style.cursor = 'progress';

		// Requète ajax au serveur
		$.post("<?php echo site_url('ajax_file/remove_file'); ?>",
		
			{
			'fileId':$resId
			},
		
			function (return_data) {
				
				$obj = JSON.parse(return_data);
				
				// On change le curseur
				document.body.style.cursor = 'default';
				
				// Modal
				if ($obj['state'] == 1) {
					update_ressource_panel();
					$("#modal_msg").modal("hide");
				}
				else {
					// Erreur
					$("#modal_msg .modal-dialog").removeClass("success");
					$("#modal_msg .modal-dialog").addClass("error");
					$("#modal_msg .modal-dialog").addClass("backdrop","static");
					$("#modal_msg .modal-header").html("Erreur !");
					$("#modal_msg .modal-body").html($obj['data']);
					$("#modal_msg .modal-footer").html('<a id="modal_close" href="#" data-dismiss="modal">Fermer</a>');
					$("#modal_msg").modal("show");
				}
			}
		);
	}
	
	<?php endif; ?>
	

	
	/********** AFFICHAGE PARTICIPANTS  *********/
	function change_display($type) {    // type = pupitre ou list
			
		// On affiche les catégories		
		if ($("#list_icon").hasClass("hidden")) $("#list_icon").removeClass("hidden");
		else $("#list_icon").toggle();
		
		if ($("#list_pupitre").hasClass("hidden")) $("#list_pupitre").removeClass("hidden");
		else $("#list_pupitre").toggle();
		
		$("#list_participant").toggle();
		$("#cat_icon").toggle();
	}



	/********** AVATAR  *********/
	/* Charge les images d'avatar et les affiches pour les discussions et les listes de participants */
	function set_avatar() {
	
		nbComplete = 0;
	
		// On récupère les avatar de chaque membre inscrit à la jam
		$("#member_list #list_alpha .member").each(function() {
			
			// Avatar par défaut
			$img = '<img class="img-circle miniAvatar avatarNotSet" src="<?php echo base_url("images/icons/avatar2.png"); ?>" width="16" height="16">'
			$(this).prepend($img);
			
			// On actualise l'avatar dans la liste par pupitre + liste des référents
			$("#member_list #list_pupitre .member[idMember="+$(this).attr("idMember")+"]").prepend($img);
			
			// On récupère que quand on est sûr qu'il existe un avatar
			if ($(this).attr("hasAvatar") == 1) {
				
				// On récupère les infos nécessaires pour récup l'image de l'avatar
				idMember = $(this).attr("idMember");
				fileName = idMember+'.png';
				
				$.ajax({
					url:'<?php echo base_url("images/avatar"); ?>'+'/'+fileName,
					type:'HEAD',
					context: $(this),
					success: function() {
						// On actualise l'avatar dans les listes
						$("#member_list #list_alpha .member[idMember="+$(this).attr('idMember')+"]").children("img").prop("src",'<?php echo base_url("images/avatar"); ?>'+'/'+$(this).attr('idMember')+'.png').removeClass("avatarNotSet");
						$("#member_list #list_referent .member[idMember="+$(this).attr('idMember')+"]").children("img").prop("src",'<?php echo base_url("images/avatar"); ?>'+'/'+$(this).attr('idMember')+'.png').removeClass("avatarNotSet");
						$("#member_list #list_pupitre .member[idMember="+$(this).attr("idMember")+"]").children("img").prop("src",'<?php echo base_url("images/avatar"); ?>'+'/'+$(this).attr('idMember')+'.png').removeClass("avatarNotSet");
						// On actualise l'avatar dans les messages (suppose que les messages ont déjà été chargés...)
						$("#messageList .message[idMember="+$(this).attr('idMember')+"]").prev("img").prop("src",'<?php echo base_url("images/avatar"); ?>'+'/'+$(this).attr('idMember')+'.png').removeClass("avatarNotSet");
					},
					error: function() {
						// On actualise l'avatar dans les listes
						$("#member_list #list_alpha .member[idMember="+$(this).attr('idMember')+"]").children("img").removeClass("avatarNotSet");
						$("#member_list #list_referent .member[idMember="+$(this).attr('idMember')+"]").children("img").removeClass("avatarNotSet");
						$("#member_list #list_pupitre .member[idMember="+$(this).attr("idMember")+"]").children("img").removeClass("avatarNotSet");
						// On actualise l'avatar dans les messages (suppose que les messages ont déjà été chargés...)
						$("#messageList .message[idMember="+$(this).attr('idMember')+"]").prev("img").removeClass("avatarNotSet");
					},
					complete: function() {
						nbComplete++;
						// On vérifie si on a parcouru tous les profils
						if (nbComplete == $("#member_list #list_alpha .member[hasAvatar=1]").length) 
							set_other_avatar();
					}
				});
			}
		});
	}
	
	// Affiche les avatar des gens ayant quitté la jam et ayant posté un message
	function set_other_avatar() {
		
		// On récupère les idMember (sans doublons) de ceux qui n'ont pas d'avatar ansi que la liste concernée
		var array = new Array();
		
		// Compteur pour détecter la fin de chargement de toutes les images d'avatar
		nbComplete = 0;
		
		$(".avatarNotSet[hasAvatar=1]").each(function() {
			
			// On récupère l'id du membre concerné
			list = $(this).parents(".list-group").attr("id");
			if (list == "messageList") id = $(this).next(".message").attr("idMember");
			else if (list == "list_referent") id = $(this).parent(".member").attr("idMember");
			
			// On regarde si on n'a pas déjà l'id dans l'array
			find = array.find( item => item.id === id );
			// On push si on n'a pas trouvé
			if (typeof find === 'undefined') {
				item = { id: id };
				array.push(item);
			}
			
		});
		
		//console.log("array : "+JSON.stringify(array));
		
		// Pour chacun de ces membres...
		array.forEach(function(item) {
			
			// Avatar par défaut
			$img = "<img class='img-circle miniAvatar avatarNotSet' src='<?php echo base_url('images/icons/avatar2.png'); ?>' width='16' height='16'>";
			
			// ... on insère dans une liste hidden,
			$("#member_list #list_alpha #list_linked").append("<a class='label label-success member' idMember='"+item.id+"' value='"+item.id+"'>"+$img+item.id+"</a>");
			
			// On récupère les infos des membres liés à la jam (référent, ayant postés mais désinscrits) 
			fileName = item.id+'.png';

			// ... on récupère l'avatar on l'affiche là où il faut
			$.ajax({
				url:'<?php echo base_url("images/avatar"); ?>'+'/'+fileName,
				type:'HEAD',
				success: function() {
					// On actualise l'avatar dans la hidden list_linked
					$("#member_list #list_alpha #list_linked .member[idMember="+item.id+"] img.miniAvatar").prop("src",'<?php echo base_url("images/avatar"); ?>'+'/'+item.id+'.png');
				},
				complete: function() {
					nbComplete++;
					// Quant on a récupéré tous les profils manquants
					if (nbComplete == $("#member_list #list_alpha #list_linked .member").length) {
						
						// On parcour à nouveau tous les avatar manquant
						$(".avatarNotSet").each(function() {
							// On récupère l'id du membre concerné
							list = $(this).parents(".list-group").attr("id");
							if (list == "messageList") {
								id = $(this).next(".message").attr("idMember");
								$("#messageList .message[idMember="+id+"]").prev("img").prop("src",$("#member_list #list_alpha #list_linked .member[idMember="+id+"] img.miniAvatar").prop("src")).removeClass("avatarNotSet");
							}
							else if (list == "list_referent") {
								id = $(this).parent(".member").attr("idMember");
								$("#member_list #list_referent .member[idMember="+id+"]").children("img").prop("src",$("#member_list #list_alpha #list_linked .member[idMember="+id+"] img.miniAvatar").prop("src")).removeClass("avatarNotSet");
							}
						});
					}
				}
			});
			
		});
	}
	
	
	// Activer les popover
	function set_popover() {
		// ********** POPOVER ***************
		$('[data-toggle="popover"]').popover({
			trigger: "manual",
			html: true,
			container: "body",
			content: function() {
				var div_id =  "tmp-id-" + $.now();
				return details_in_popup($(this).attr("idMember"), div_id, $(this).parents(".list-group").attr("id"));
			}
		}).on("mouseenter", function() {
			var _this = this;
			$(this).popover("show");
			$(".popover").on("mouseleave", function() {
				$(_this).popover('hide');
			});
		}).on("mouseleave", function() {
			var _this = this;
			setTimeout(function() {
				if (!$(".popover:hover").length) {
					$(_this).popover("hide");
				}
				}, 300);
		});
	}
		
	// Récupère dynamiquement les données du membre pointé
	function details_in_popup($memberId, div_id, list) {
		
		// On vérifie que les détails n'ont pas déjà été loadés
		if ($("#member_list #list_alpha .member[idmember="+$memberId+"] .details").length) {
			return '<div class="memberDetails" id="'+ div_id +'">'+$("#member_list #list_alpha .member[idMember="+$memberId+"] .details").html()+'</div>';
		}
		
		// Il n'y a pas encore eu d'appel ajax pour récupérer les details
		var context = {
			page: "jam",
			pageId: <?php echo $jam_item["id"] ?>,
			<?php if (isset($member)): ?>
				userId: <?php echo $member->id ?>,
				attend: parseInt($(".hidden #attend").html(),10),
				admin: <?php echo $is_admin ? 1 : 0 ?>
			<?php endif ?>
		}
		
		$.ajax({
			url: "<?php echo site_url('ajax_members/get_details'); ?>",
			type: 'POST',
			data:  { 	
						memberId: $memberId,				// membre pointé
						context: JSON.stringify(context)	// membre qui pointe
					},
			success: function(return_data) {
				//console.log("success : "+return_data);
				$obj = JSON.parse(return_data);
				
				if ($obj['state'] == 1) {
					
					$mainContainer = jQuery("<div class=''></div>");
					
					$container = jQuery("<div class='infoContainer'></div>");
					
					// Avatar
					//$imgSrc = $("#member_list #"+list+" .member[idmember="+$memberId+"]").find("img").attr("src");
					$imgSrc = $("#member_list .member[idmember="+$memberId+"]").find("img").attr("src");
					if (typeof($imgSrc) == "undefined") $imgSrc = "<?php echo base_url('images/icons/avatar2.png'); ?>";
					$img = '<img class="img-circle miniAvatar" src="'+$imgSrc+'" width="96" height="96">';
					//console.log($imgSrc);
					
					$title = '<div class="title">'+$obj['data']['pseudo']+'</div>';
					$name = '<div class="name">'+$obj['data']['prenom']+' '+$obj['data']['nom']+'</div>';
					
					// Details
					$details = jQuery("<div class='details softer small'></div>");
					
					// Par défaut on affiche le mainInstru
					$details.append('<div class="item"><i class="glyphicon glyphicon-music"></i>&nbsp;&nbsp;&nbsp;'+$obj['data']['mainInstruName']+'</div>');
					
					// On parcours les autres details
					for (var i in $obj['data']) {
						switch (i) {
							case "mobile" :
								if ($obj['data']['mobile'].length == 0) break;
								str = $obj['data']['mobile'].replace(/(.{2})/g,"$& ");
								$details.append('<div class="item"><i class="glyphicon glyphicon-phone"></i>&nbsp;&nbsp;&nbsp;'+str+'</div>');
								break;
							case "email" :
								$details.append('<div class="item"><i class="glyphicon glyphicon-envelope"></i>&nbsp;&nbsp;&nbsp;'+$obj['data']['email']+'</div>');
								break;
							case "referent" :
								$details.prepend('<div class="item"><b><i class="glyphicon glyphicon-flag"></i>&nbsp;&nbsp;&nbsp;'+$obj['data']['referent']+'</b></div>');
								break;
						}
					}
					//console.log(JSON.stringify($details));
					
					$container.append($img).append("<div class='infos'>" + $title + $name + $details.get(0).outerHTML +"</div>");
					$mainContainer.append($container);
					
					// ******  FOOTER ****** Boutton d'action
					// Si membre pointé != membre qui pointe...
					if ($memberId != context["userId"]) {
						// ... On propose "Envoyer un message"
						$footer = "<div class='footer btn btn-primary' onclick='window.open(\"<?php echo site_url('message'); ?>/<?php echo url_title($session->login); ?>/"+$obj['data']['slug']+"\",\"_blank\")'>";
							//$footer += "<img class='actionIcon' src='<?php echo base_url("images/icons/dialog.png"); ?>' width='20' height='20'>";
							$footer += "<i class='actionIcon bi-chat'></i>";
							$footer += "<span>Envoyer un message</span></div>";
					}
					else {
						// ... Sinon on propose "Modifier profil"
						$footer = "<div class='footer btn btn-primary' onclick='window.open(\"<?php echo site_url('members'); ?>/<?php echo url_title($session->login); ?>/"+$obj['data']['slug']+"\",\"_blank\")'>";
							$footer += "<i class='glyphicon glyphicon-pencil'></i>&nbsp;&nbsp;&nbsp;<span>Modifier le profil</span></div>";
					}
					
					$mainContainer.append($footer);
					
					
					// On rempli les détails du membre en hidden pour éviter les reload dynamiques
					if (!$("#member_list #list_alpha .member[idmember="+$memberId+"] .details").length) {
						$hiddenDetails = $mainContainer.clone().addClass("hidden details")
						$("#member_list #list_alpha .member[idmember="+$memberId+"]").append($hiddenDetails);
					}
					
					// On rempli la popover
					$('#'+div_id).html($mainContainer.html());
					//console.log($mainContainer.html());
					
					// On reposition la popover
					$("[data-toggle='popover'][idMember="+$memberId+"]").popover('reposition');
				}
			}
		});
		return '<div class="memberDetails" id="'+ div_id +'">Loading...</div>';
	}
	
 </script>	
 
		
 
 <script type="text/javascript">
								/**********************************************************************/
								/****************************   MESSAGE  ******************************/
								/**********************************************************************/
	$(function() {
		
		<?php if (isset($member)): ?>
			/************** SEND MESSAGE UI  ****************/
			autosize($('#messageInput'));
			
			// Pour gérer les event spéciaux du textarea (enter => send_message ou update_message, ctrl+enter => saut de ligne, escape => empty textarea)
			$('#msgBoard textarea').keydown(function(evt) {
				
				// On récupère le formulaire contenant le textarea concerné
				$context = $(evt.target).parents("[id$='Form']");
				
				if (evt.key === "Escape") { // escape key maps to keycode `27`
					cancel_edit();
				}
				// Si "enter"
				else if (evt.keyCode == 13 && !evt.shiftKey) {
					if (this.value.length > 0) {
						$(this).prop('disabled', true);
						evt.preventDefault();
						evt.stopPropagation();
						// On détermine si on fait un send, un answer ou un update
						if ($context.prop("id") == "messageForm") {
							send_message(this.value, "3", <?php echo $jam_item['id'] ?>);
						}
						else if ($context.prop("id") == "answerForm") {
							// On récupère le messageId
							//$messageId = $context.parent().prev().find(".message").prop("id");
							
							// On retrouve le messageBlock d'origine
							tempBlock = $context.parent().prev();
							while (tempBlock.hasClass("answer") || tempBlock.hasClass("uiBlock")) tempBlock = tempBlock.prev();
							
							// On récupère le messageId
							$messageId = tempBlock.find(".message").prop("id");

							send_message(this.value, "5", $messageId);
						}
						else {
							//console.log("update : \'"+this.value+"\'");
							update_message($context.prop("messageId"), this.value);
						}
					}
					else return false;
				}
			});

		<?php endif ?>
		
		
		// On charge les messages	3 => jam
		load_messages("3", <?php echo $jam_item['id'] ?>, "ASC");
	});
	
	
	/************** SEND MESSAGE   ****************/
	<?php if (isset($member)): ?>
		function send_message($message, $targetTag, $targetId) {

			//console.log("send_message : { \""+$message+"\" ; \""+$targetTag+"\" ; "+$targetId+" }");

			// On change le curseur
			document.body.style.cursor = 'wait';
			
			// Requète ajax au serveur
			$.post("<?php echo site_url('ajax_discussion/send_message'); ?>",
		
				{
					'memberId': <?php echo $member->id ?>,
					'message': $message,
					'targetTag': $targetTag,
					'targetId': JSON.stringify($targetId)
				},
			
				function (return_data) {
					
					$obj = JSON.parse(return_data);
					
					// On change le curseur
					document.body.style.cursor = 'default';
					
					//console.log(return_data);
					
					// Modal
					if ($obj['state'] == 1) {
						// Succés
						// Si send message à la jam
						if ($targetTag == 3) {
							// On reset le messageInput
							$("#messageForm #messageInput").val("");
							autosize.update($("#messageForm #messageInput"));
							$("#messageForm #messageInput").prop('disabled', false);
						}
						
						// Si send message à un autre message (réponse)
						else {
							// On doit virer le messageForm qui vient d'être utilisé
							tempBlock = $("#messageList .messageBlock .message[id="+$targetId+"]").parents(".messageBlock");
							while (tempBlock.next().hasClass("answer") || tempBlock.next().hasClass("uiBlock")) tempBlock = tempBlock.next();
							tempBlock.remove();
						}
						
						value = {
							id : $obj['data']['id'],
							memberId : <?php echo $member->id ?>,
							pseudo : $("#memberLogin").attr("value"),
							text : $obj['data']['text'],
							createdReadable : $obj['data']['createdReadable'],
							timeAgo : $obj['data']['timeAgo'],
							targetTag : $targetTag,
							targetId : $targetId
						}
						set_message(value, true);						
					}
					else {
						// Erreur
						$("#modal_msg .modal-dialog").removeClass("success");
						$("#modal_msg .modal-dialog").addClass("error");
						$("#modal_msg .modal-dialog").addClass("backdrop","static");
						$("#modal_msg .modal-header").html("Erreur !");
						$("#modal_msg .modal-body").html($obj['data']);
						$("#modal_msg .modal-footer").html("<button type='submit' class='btn btn-default' data-dismiss='modal'>Fermer</button>");
						$("#modal_msg").modal('show');
					}
				}
			);
		}
	<?php endif ?>
	
	/************** LOAD MESSAGES   ****************/
	function load_messages($targetTag, $targetId, $order) {
		
		// On change le curseur
		document.body.style.cursor = 'wait';
		
		// On est dans un nouvel appel
		msgCounter++;
		
		// Requète ajax au serveur
		$.post("<?php echo site_url('ajax_discussion/get_messages'); ?>",
	
			{
				'targetTag': $targetTag,
				'targetId': $targetId,
				'order': $order
			},
		
			function (return_data) {
				
				$obj = JSON.parse(return_data);
				
				// On change le curseur
				document.body.style.cursor = 'default';
				
				//console.log("load_messages : "+return_data);
				
				// Succés
				if ($obj['state'] == 1) {
					
					// On rempli la liste des messages
					for (var i=0; i < $obj['data'].length; i++) {
						
						// Visible si message destiné à la jam
						if ($targetTag == 3) visible = true;
						// Visible si dernier d'une liste de réponse
						else visible = (i == $obj['data'].length - 1);
						
						// On insère le message dans l'UI
						set_message($obj['data'][i], visible);
						
						// On charge les réponses
						load_messages(5, $obj['data'][i]['id'], "ASC");
					};
					
					// Si plus d'une réponse, on a masqué les messages sauf le dernier, on affiche le lien pour afficher
					if ($targetTag == 5 && $obj['data'].length > 1) {
						
						// On créé le block d'affichage
						nbHide = $obj['data'].length -1;
						modifier = nbHide == 1 ? "" : "s";
						var msgBlock = jQuery("<div class='uiBlock'><i class='develop'>&nbsp;&nbsp;&nbsp;&nbsp;</i><a class='develop' href=''>Afficher "+($obj['data'].length-1)+" autre"+modifier+" réponse"+modifier+"</a></div>");
						
						// On insère le block au bon endroit
						targetMsgBlock = $("#messageList .message[id="+$targetId+"]").parents(".messageBlock");
						msgBlock.insertAfter(targetMsgBlock);
						
						// On définit le comportement d'un click sur le develop link
						$('#msgBoard .uiBlock a').on("click", function(evt) {
							
							// On disable l'action stadard du <a>
							evt.preventDefault();
							evt.stopPropagation();
							
							// On affiche les messages hidden
							tempBlock = $(this).parents(".uiBlock").next();
							while (tempBlock.hasClass("hidden")) {
								tempBlock.removeClass("hidden");
								tempBlock = tempBlock.next();
							}
							
							// On remove l'uiBlock
							$(this).parents(".uiBlock").remove()
						});
					}
					
					msgCounter--;
					// On a chargé tous les messages
					if (msgCounter == 0) {
						// On lance la récup des image d'avatar
						set_avatar();
						set_popover();
					}
				}
			}
		);
	}
	
	
	// *********** SET MESSAGE dans l'UI **********
	function set_message(value, visible) {
		
		//console.log("***** set_message ****  "+JSON.stringify(value)+"    "+visible);
		
		// On créé le message
		var hidden = visible ? "" : " hidden";
		var msgBlock = jQuery("<div class='messageBlock"+hidden+"'></div>");
		var msgRow = jQuery("<div class='messageRow'></div>");
		
		// On gère l'avatar (si msgCounter == 0, il s'agit d'un nouveau message qui vient d'être posté forcément par un participants à la jam)
		if (msgCounter == 0) {
			var img = '<img class="img-circle miniAvatar" src="'+$("#member_list #list_alpha .member[idMember="+value.memberId+"] img").prop("src")+'" width="28" height="28">';
		}
		else var img = '<img class="img-circle miniAvatar avatarNotSet" src="<?php echo base_url("images/icons/avatar2.png"); ?>" idMember="'+value.memberId+'" hasAvatar="'+value.hasAvatar+'" data-toggle="popover" data-trigger="hover" data-placement="top" width="28" height="28">';
		
		// On gère les messages supprimés
		if (value.updatedReadable !== undefined && value.deletedReadable == value.updatedReadable) {
			var msg = jQuery(img+"<div class='message' id="+value.id+" idMember="+value.memberId+"><b>"+value.pseudo+"</b>&nbsp;&nbsp;<span class='content soften'><em>L'auteur a supprimé le contenu de ce message.</em></span></div>");
		}
		else var msg = jQuery(img+"<div class='message' id="+value.id+" idMember="+value.memberId+"><b>"+value.pseudo+"</b>&nbsp;&nbsp;<span class='content'>"+getCleanText(value.text)+"</span></div>");
		msgRow.append(msg);
		
		// On créé le "..." d'action
		<?php if (isset($member)) : ?>
			// S'il ne s'agit pas d'un message supprimé
			if (value.deletedReadable === undefined || value.deletedReadable != value.updatedReadable) {
				// Si l'auteur du message est le membre connecté
				if (value.memberId == <?php echo $member->id ?>) {
					// On récupère le menu d'options et on l'ajoute au block
					option = get_options(value.id, msg);								
					msgRow.append(option);
				}
			}
		<?php endif ?>
		
		// On ajoute la ligne de message au block
		msgBlock.append(msgRow);
		
		// On créé la ligne de messageSub (répondre + date)
		var answerLink = "";
		<?php if (isset($member)) : ?>
			answerLink = "<a class='answerLink' href='#'>Répondre</a>&nbsp;·&nbsp;";
		<?php endif ?>

		// On affiche le timeAgo normal
		if (value.created_at == value.updated_at || value.updated_at === undefined) {
			var msgSub = jQuery("<div class='messageSub small'>"+answerLink+"<span class='softer' data-toggle='tooltip' data-placement='right' title='"+value.createdReadable+"'>"+value.timeAgo+"</span></div>");
		}
		// Modifié il y a ...
		else if (value.deleted_at != value.updated_at) {
			var modStr = value.timeAgo.toLowerCase() == "à l'instant" ? "Modifié à l'instant" : "Modifié il y a "+value.timeAgo;
			var msgSub = jQuery("<div class='messageSub small'>"+answerLink+"<span class='softer' data-toggle='tooltip' data-placement='right' title='"+value.updatedReadable+"'>"+modStr+"</span></div>");
		}
		// Supprimé il y a ...
		else {
			var modStr = value.timeAgo.toLowerCase() == "à l'instant" ? "Supprimé à l'instant" : "Supprimé il y a "+value.timeAgo;
			var msgSub = jQuery("<div class='messageSub small'>"+answerLink+"<span class='softer' data-toggle='tooltip' data-placement='right' title='"+value.updatedReadable+"'>"+modStr+"</span></div>");
		}
		
		msgBlock.append(msgSub);
		
		<?php if (isset($member)) : ?>
			// On définit l'action du answerLink
			msgBlock.find('.messageSub .answerLink').on("click", function(evt) {
				
				// On disable l'action stadard du <a>
				evt.preventDefault();

				// On récupère le messageBlock auquel on souhaite répondre
				msgBlock = $(evt.target).parents(".messageBlock");
						
				// On clone et on rename en updateForm
				var mainDiv = $("#infos #messageForm").parent().clone("true");
				mainDiv.find("textarea").val("");
				mainDiv.find("#messageForm").prop("id","answerForm");
				
				// On attache le messageForm au bon endroit en cherchant la dernière réponse
				tempBlock = msgBlock;
				while ((tempBlock.next().hasClass("answer") && !tempBlock.next().hasClass("container-fluid")) || tempBlock.next().hasClass("uiBlock"))
					tempBlock = tempBlock.next();
				
				
				// Si le form est déjà open, on prend just le focus
				if (tempBlock.next().hasClass("container-fluid")) tempBlock.next().find("textarea").focus();
				else mainDiv.insertAfter(tempBlock);
				
				// On ajoute l'info permettant une indentation vers la droite
				mainDiv.addClass("answer");
				
				// On adapte la taille du textarea
				autosize(mainDiv.find("textarea"));
				
				// On prend le focus
				mainDiv.find("textarea").focus();
				
			});
		<?php endif ?>
		
		// On active le tooltip
		msgBlock.find('.messageSub [data-toggle="tooltip"]').tooltip();
		
		// On gère les réponses
		if (value.targetTag == 5) {
			msgBlock.addClass("answer");
			targetMsgBlock = $("#messageList .message[id="+value.targetId+"]").parents(".messageBlock");
			while (targetMsgBlock.next().hasClass("answer") || targetMsgBlock.next().hasClass("uiBlock")) targetMsgBlock = targetMsgBlock.next();
			msgBlock.insertAfter(targetMsgBlock);
		}
		
		// On ajoute le msgBlock à la liste + son messageSub
		else {
			$("#messageList").append(msgBlock);
		}
		
	}
	
	
	// ******************* Fonctions globales pour formater le textInput du msgBoard
	// Function ne pas traiter les balises html
	function escapeHtml(text) {
		var map = {
			'&': '&amp;',
			'<': '&lt;',
			'>': '&gt;',
			'"': '&quot;',
			"'": '&#039;'
		};
		return text.replace(/[&<>"']/g, function(m) { return map[m]; });
	}
	
	// Fonction inverse
	function unescapeHTML(escapedHTML) {
		// Si deux <br> à la fin ou plus, on en enlève un
		if (escapedHTML.length > 8 && escapedHTML.substr(escapedHTML.length - 8) == "<br><br>") escapedHTML = escapedHTML.substr(0,escapedHTML.length - 4);

		// On setup le textarea de l'update block en remplaçant les <br> par \n
		var regex = /<br\s*[\/]?>/gi;
		str = escapedHTML.replace(regex, "\n");
		
		// ... on remplace les &gt; par < ...
		text = str.replace(/&lt;/g,'<').replace(/&gt;/g,'>').replace(/&amp;/g,'&');
		return text;
	}
	
	
	// Fonction pour cleaner l'affichage des text de messages
	function getCleanText(text) {
		// On enlève l'html
		text = escapeHtml(text);
		// On garde quand même les retours de ligne en ajoutant un <br>
		text = text.replace(/(?:\r\n|\r|\n)/g, '<br>');
		// Si on finit avec un <br>, la ligne n'est pas visible. On rajoute donc artificiellement un <br>
		if (text.length > 4 && text.substr(text.length - 4) == "<br>") text += "<br>";
		return text;
	}
	
	
	
	/************** GET OPTIONS ***************/
	<?php if (isset($member)): ?>
	
		// Permet de factoriser la création du dropdown lié à un message écrit par le user
		function get_options(messageId, $msg) {
			
			//console.log("get_options : "+messageId);
			
			// Dropdown pour l'édition des messages déjà postés
			var option = jQuery("<div class='option dropdown'></div>");

			// ...
			option.append('<button class="btn btn-xs btn-default panel-transparent no-border dropdown-toggle" data-toggle="dropdown" type="button" ><i class="glyphicon glyphicon-option-horizontal"></i></button>');

			option.find('[data-toggle="tooltip"]').tooltip();

			// menu du dropdown
			var ul = jQuery('<ul class="dropdown-menu dropdown-menu-right" role="menu" aria-labelledby="menu1"></ul>');
			
			// items du dropdown
			$('<li class="small"><a role="menuitem" tabindex="-1" href="javascript:edit_message('+messageId+')"><i class="glyphicon glyphicon-pencil"></i>&nbsp;&nbsp;&nbsp;&nbsp;Modifier</a></li>').appendTo(ul);
			$('<li class="small"><a role="menuitem" tabindex="-1" href="javascript:popup_delete_message('+messageId+')"><i class="glyphicon glyphicon-trash"></i>&nbsp;&nbsp;&nbsp;&nbsp;Supprimer</a></li>').appendTo(ul);;
			
			option.append(ul);


			// UI
			$msg.hover(
				function() { 
					if ($(this).next().find("ul").css("display") != "block") $(this).next().find("button").css("opacity","30%");	
				},
				function() { 
					if ($(this).next().find("ul").css("display") != "block") $(this).next().find("button").css("opacity","0%"); 
				}
			);
			
			option.hover(
				function() { $(this).find("button").css("opacity","60%");	},
				function() {
					if ($(this).find("ul").css("display") != "block") $(this).find("button").css("opacity","0%");
				}
			);
			
			option.on('hidden.bs.dropdown', function(){
				//$(this).find("button").css("opacity","0%");
			});
			
			return option;
			
		}
	
	<?php endif ?>
	
	
	
	/************* UPDATE MESSAGE ***************/
	<?php if (isset($member)): ?>
	
		// On rentre dans le mode d'édition d'un message
		function edit_message($messageId) {
			
			// On récupère le messageBlock
			var msgBlock = $("#messageList .message[id='"+$messageId+"']").parents(".messageBlock");
			
			// On masque le msgBlock
			msgBlock.addClass("hidden edited");
					
			// On clone et attache le messageForm au bon endroit et on rename en updateForm et on rajoute l'info du messageId
			var mainDiv = $("#infos #messageForm").parent().clone("true");
			mainDiv.find("#messageForm").prop("id","updateForm").prop("messageId",$messageId);
			
			// On setup le textarea de l'update block en remplaçant les <br> par \n + on remplace les &gt; par <
			mainDiv.find("textarea").val(unescapeHTML(msgBlock.find(".message span.content").html()));

			// On vérifie s'il s'agit d'une réponses
			if (msgBlock.hasClass("answer")) mainDiv.addClass("answer");

			// On insert le updateForm
			mainDiv.insertAfter(msgBlock);
			
			// On adapte la taille du textarea
			autosize(mainDiv.find("textarea"));
			
			// On prend le focus et on fixe le comportement si on perd le focus
			mainDiv.find("textarea").focus().focusout( function() {
				cancel_edit();
			});
		}
		
		
		// Sort du mode d'édition de message (send ou update)
		function cancel_edit() {	
		
			// On récupère le textarea qui a le focus
			$target = $('#msgBoard').find("textarea:focus").parents("[id$=Form]").prop("id");

			// Action de cancel pour un update
			if (typeof $target === "undefined" || $target.startsWith("update")) {
				$("#messageList").find("#updateForm").parents(".container-fluid").fadeOut("fast", function() { 
					// On réaffiche le message d'origine
					$("#messageList").find(".messageBlock.edited").css("display","none").removeClass("hidden edited").fadeIn("fast");
					// On remove l'update form cloné
					$(this).remove();
				});
			}
			// Action de cancel pour un send
			else {
				$('#msgBoard').find("textarea:focus").val("").focusout();
			}
			
		}
		
		// On update via le serveur
		function update_message($messageId, $message) {

			//console.log("update_message : \""+$messageId+"\" ; \""+$message+"\"");

			// On change le curseur
			document.body.style.cursor = 'wait';
			
			// Requète ajax au serveur
			$.post("<?php echo site_url('ajax_discussion/update_message'); ?>",
		
				{
					'messageId': $messageId,
					'message': $message
				},
			
				function (return_data) {
					
					$obj = JSON.parse(return_data);
					
					// On change le curseur
					document.body.style.cursor = 'default';
					
					//console.log(return_data);
					
					// Modal
					if ($obj['state'] == 1) {
						
						// On update le message dans l'UI
						$("#messageList").find(".messageBlock.edited").find(".message span.content").empty().html(getCleanText($message));
						// On update le msgSub
						$("#messageList").find(".messageBlock.edited").find(".messageSub span.softer").empty().html("Modifié à l'instant");
						
						// On sort de l'edit mode
						cancel_edit();
					}
					else {
						// Erreur
						$("#modal_msg .modal-dialog").removeClass("success");
						$("#modal_msg .modal-dialog").addClass("error");
						$("#modal_msg .modal-dialog").addClass("backdrop","static");
						$("#modal_msg .modal-header").html("Erreur !");
						$("#modal_msg .modal-body").html($obj['data']);
						$("#modal_msg .modal-footer").html("<button type='submit' class='btn btn-default' data-dismiss='modal'>Fermer</button>");
						$("#modal_msg").modal('show');
					}
				}
			);
		}
		
		
	<?php endif ?>
			
			
			
	
	/************* DELETE MESSAGE ***************/
	<?php if (isset($member)): ?>
	
		function popup_delete_message($messageId) {
			
			$text = "Etes-vous sûr de vouloir supprimer ce message ?";
			$confirm = "<div class='modal-footer'>";
				$confirm += "<button type='button' class='btn btn-default' data-dismiss='modal'>Annuler</button>";
				$confirm += "<button type='submit' class='btn btn-primary' onclick='javascript:delete_message("+$messageId+")'>Supprimer</button>";
			$confirm += "</div>";
			
			$("#modal_msg .modal-dialog").removeClass("error success");
			$("#modal_msg .modal-dialog").addClass("default");
			$("#modal_msg .modal-dialog").addClass("backdrop","static");
			$("#modal_msg .modal-header").html("Supprimer un message");
			$("#modal_msg .modal-body").html($text);
			$("#modal_msg .modal-footer").html($confirm);
			$("#modal_msg").modal('show');
		}
		
		
		function delete_message($messageId) {
			
			// On change le curseur
			document.body.style.cursor = 'wait';
			
			// Requète ajax au serveur
			$.post("<?php echo site_url('ajax_discussion/delete_message'); ?>",
		
				{'messageId':$messageId},
			
				function (return_data) {
					
					$obj = JSON.parse(return_data);
					
					// On change le curseur
					document.body.style.cursor = 'default';
					
					// Modal
					if ($obj['state'] == 1) {
						// Succés
						$("#modal_msg").modal('hide');
						
						// On supprime le messageBlock concerné
						//$("#messageList").find(".message[id="+$messageId+"]").parents(".messageBlock").fadeOut("fast", function() { $(this).remove(); });
						$("#messageList").find(".message[id="+$messageId+"]").find("span.content").empty().addClass("soften").html("<em>L'auteur a supprimé le contenu de ce message.</em>");
						// On update le msgSub
						$("#messageList").find(".message[id="+$messageId+"]").parents(".messageBlock").find(".messageSub span.softer").empty().html("Supprimé à l'instant");

					}
					else {
						// Erreur
						$("#modal_msg .modal-dialog").removeClass("success");
						$("#modal_msg .modal-dialog").addClass("error");
						$("#modal_msg .modal-dialog").addClass("backdrop","static");
						$("#modal_msg .modal-header").html("Erreur !");
						$("#modal_msg .modal-body").html($obj['data']);
						$("#modal_msg .modal-footer").html("<button type='submit' class='btn btn-default' data-dismiss='modal'>Fermer</button>");
						$("#modal_msg").modal('show');
					}
				}
			);
		}
	<?php endif ?>
	
 </script>
 
 

<!-- **************  VARIABLES CACHEES pour JAVASCRIPT ***************************** -->
<div class="hidden">
	<span id="attend"><?php echo ($attend ? 1 : 0); ?></span>
	<span id="privee"><?php echo ($jam_item["acces_jam"] == 2 ? 1 : 0); ?></span>
	<span id="attend_stage"><?php echo ($attend_stage ? 1 : 0); ?></span>
	<span id="cheque_stage"><?php if (isset($cheque_stage)) echo ($cheque_stage ? 1 : 0); ?></span>
	<span id="mainPupitreLabel"><?php if (isset($member->mainPupitre['pupitreLabel'])) echo $member->mainPupitre['pupitreLabel']; else echo -1; ?></span>
	<ul id="member_inscriptions">
		<?php
			if (isset($member_inscriptions)) {
				foreach ($member_inscriptions as $item) {
					echo "<li posteId='".$item['posteId']."' versionId='".$item['versionId']."'><b><span class='choicePos'>".$item['choicePos']."</span>. <span class='titre'>".$item['titre']."</span></b> - <small class='soften instru'>".$item['posteLabel']."</small></li>";
				}
			}
		?>
	</ul>
	<ul id="member_affectations">
		<?php 
			if (isset($member_affectations)) {
				foreach ($member_affectations as $item) {
					echo "<li posteId='".$item['posteId']."' versionId='".$item['versionId']."'><b><span class='titre affected'>&nbsp;".$item['titre']."&nbsp;</span></b> - <small class='soften instru'>".$item['posteLabel']."</small></li>";
				}
			}
		?>
	</ul>
</div>

	
<!-- **********************************  INFOS JAM  **********************************-->
<div class="jam panel panel-default row">


	<!-- **** HEADING **** !-->
	<div class="panel panel-heading panel-bright">
				
		<!-- Date !-->
		<?php
			$month = strftime("%b", strtotime($jam_item['date']));
			//if (!$this->config->item('online')) $month = utf8_encode($month);
			if (!env('online')) $month = utf8_encode($month);
			$month =  substr(strtoupper(no_accent($month)),0,3);
		?>
		<div class="date_box">
			<div><small><?php echo $month; ?></small></div>
			<div><strong><?php echo explode('-', $jam_item['date'])[2] ?></strong></div>
		</div>


		<!-- Titre de la jam + Action bar !-->
		<div class="title_box">
			<h3>
				<?php
					if ($jam_item['acces_jam'] == 0) echo "<i class='bi bi-gear-fill bi_nopadding'></i>";
					else if ($jam_item['acces_jam'] == 2) echo "<i class='bi bi-lock-fill bi_nopadding'></i>"; 
				?>
				<?php echo $jam_item['title']; ?>
			</h3>
			
			<!-- **** ACTION BAR **** !-->
			<div class="actionBar">
			
				<div class="text-left">
					<!-- Si la jam n'est pas archivée -->
					<?php if (!$jam_item['is_archived']) :?>
						
						<!-- BOUTTONS D'INSCRIPTIONS   -->
						<div class="btn-group btn-group-sm">
						
							<!-- Si l'utilisateur ne participe pas déjà, on affiche le boutons d'inscription -->
							<?php if (!$attend) :?>
								
								<!-- PARTICIPER   Bouton d'inscription à la jam -->
								<button class="btn btn-default" type="submit" name="join_jam"
										<?php if (sizeof($list_members) >= $jam_item['max_inscr'] && $jam_item['max_inscr'] != -1) echo "disabled" ?>
										<?php if ( !isset($is_invited)  ||  ($jam_item['acces_jam'] == 2 && !$is_invited)) echo "disabled" ?>
										onclick="inscription()" >Participer</button>
							
							<!-- L'utilisateur participe, bouton de desinscription !-->
							<?php elseif ($attend) :?>
								<button class="btn btn-checked" type="submit" name="quit_jam" onclick="quit_jam_modal()"><i class='glyphicon glyphicon-ok'></i>&nbsp;&nbsp;&nbsp;Je participe</button>
							<?php endif; ?>
							
						</div>
					<?php endif; ?>
				</div>
				
				
				<div class="text-right">
				
					<!-- BOUTTONS D'ADMIN EVENT VISIBLES   -->
					<?php if($is_admin) : ?>
						<!-- MANAGE PARTICIPANTS -->
						<div>
							<a class="btn btn-default btn-sm" role="button" href='<?php echo site_url("jam/manage/").$jam_item['slug'] ?>'><i class="bi bi-people-fill"></i><span class="hidden-xs">Participants</span></a>
						</div>
					<?php endif ?>
					<!-- BOUTTONS SUPER ADMIN VISIBLES  -->
					<?php if($isSuperAdmin) : ?>
						<!-- MODIFIER -->
						<div>
							<button class="btn btn-default btn-sm" href="<?php echo site_url("jam/update/".$jam_item['slug']) ?>" data-remote="false" data-toggle="modal" data-target="#updateModal"><i class="bi bi-pencil"></i><span class="hidden-xs">Modifier</span></button>
						</div>	
					<?php endif ?>
					
					
					<!-- BOUTTONS D'ADMIN EVENT EN DROPDOWN	-->
					<?php if($is_admin) : ?>
					<div class="dropdown">
						<button class="dropdown-toggle btn btn-default btn-sm" data-toggle="dropdown" href="#"><i class="bi bi-three-dots bi_nopadding"></i></button>
						<ul class="dropdown-menu dropdown-menu-right">
							<!-- INVITATIONS -->
							<li><a class="small" href='<?php echo site_url("jam/invitations/").$jam_item['slug'] ?>' data-remote="false" data-toggle="modal" data-target="#invitModal"><i class="bi bi-envelope"></i><span>Invitations</span></a></li>
							<!-- AFFECTATIONS -->
							<li><a class="small" href='<?php echo site_url("jam/affect/").$jam_item['slug'] ?>'><i class="bi bi-list-check"></i><span>Affectations</span></a></li>
							<!-- PRESENTATION -->
							<li><a href='<?php echo site_url("jam/presentation/".$jam_item['slug']); ?>'><i class="bi bi-play-btn"></i><span class="small">Présentation</span></a></li>
							
							<!-- BOUTTONS SUPER ADMIN   -->
							<?php if($isSuperAdmin) : ?>
								<!-- SUPPRIMER -->
								<li class="divider"></li>
								<li><a onclick="javascript:popup_delete_jam('<?php echo str_replace("'", "\'", $jam_item['title']).'\',\''.$jam_item['slug']; ?>')" href="#"><i class="bi bi-trash"></i><span class="small">Supprimer</span></a></li>
							<?php endif ?>

						</ul>
					</div>
					<?php endif ?>
					
					
				</div>
		
			</div>
			
		</div>
		
	</div>

	
	
	<!-- ******************* SUB PANEL ********************* !-->
	<div class="row subPanel">
	
		<?php if (!$jam_item['is_archived']) :?>
		<!-- ALERT PANEL !-->
		<div class="row">
		<div id="alertPanel" class="col-lg-12">
			
			<!-- La jam est full !-->
			<?php if (sizeof($list_members) >= $jam_item['max_inscr'] && $jam_item['max_inscr'] != -1) : ?>
				<?php if ($attend) : ?>
					<div class='alert alert-warning fade in'>
						<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
						<b>Jam complète</b> : le nombre maximum de participants a été atteint pour cette jam !
					</div>
				<?php else : ?>
					<div class='alert alert-danger'>
						<p><b>Participation impossible</b> : le nombre maximum de participants a été atteint pour cette jam !</p>
						<?php if (isset($stage_item)) : ?><p><b>Stagiaires</b> : vous pouvez accéder aux préinscriptions en cliquant ci-dessous sur &lt;Stage&gt;</p><?php endif; ?>
					</div>
				<?php endif; ?>
			<?php endif; ?>
			
		</div>
		</div>
		<?php endif; ?>
	
	
		<div class="col-lg-5">
		
			<!-- **** ACTIVITY INFOS **** !-->
			<?php if (isset($stage_item)) : ?>
			<div id="activity_infos">
				<div class="list-group">
					<button id="jamActivityBtn" class="list-group-item big_item coloredItem">Jam</button>
					<button id="stageActivityBtn" class="list-group-item big_item">Stage</button>
					<!--<li class="list-group-item big_item">Bénévoles</li>!-->
				</div>
			</div>
			<?php endif; ?>
		
			<!-- **** LIEU **** !-->
			<div id="jamLieuPanel" class="panel panel-default no-border" style="display:flex;">
				<!-- Picto !-->
				<div style="align-self:center"><img style="height: 18px; margin:0px 16px;" src="/images/icons/lieu.png" alt="lieu"></div>
				<!-- Block !-->
				<div>
					<!-- Nom du lieu !-->
					<div><h6><b><?php echo $lieu_item['nom']; ?></b></h6></div>
					<!-- On n'affiche pas si pas de donnée !-->
					<?php if ($lieu_item['adresse'] != "" || $lieu_item['web'] != ""): ?>
						<p id="lieu_details" class="soften" style="font-size: 90%">
							<span id="lieu_adresse" style="display:<?php echo $lieu_item['adresse'] == "" ? "none" : "block" ?>"><?php echo $lieu_item['adresse']; ?></span>
							<a id="lieu_web" target="_blanck" style="display:<?php echo $lieu_item['web'] == "" ? "none" : "block" ?>" href="http://<?php echo $lieu_item['web']; ?>"><?php echo $lieu_item['web']; ?></a>
						</p>
					<?php endif; ?>
				</div>
			</div>
			
			<?php if (isset($stage_item)) : ?>
			<div id="stageLieuPanel" class="panel panel-default no-border hidden" style="display:flex;">
				<!-- Picto !-->
				<div style="align-self:center"><img style="height: 18px; margin:0px 16px;" src="/images/icons/lieu.png" alt="lieu"></div>
				<!-- Block !-->
				<div>
					<!-- Nom du lieu !-->
					<div><h6><b><?php echo $stage_item['nom']; ?></b></h6></div>
					<!-- On n'affiche pas si pas de donnée !-->
					<?php if ($stage_item['adresse'] != "" || $stage_item['web'] != ""): ?>
						<p id="stage_lieu_details" class="soften" style="font-size: 90%">
							<span id="stage_lieu_adresse" style="display:<?php echo $stage_item['adresse'] == "" ? "none" : "block" ?>"><?php echo $stage_item['adresse']; ?></span>
							<a id="stage_lieu_web" target="_blanck" style="display:<?php echo $stage_item['web'] == "" ? "none" : "block" ?>" href="http://<?php echo $stage_item['web']; ?>"><?php echo $stage_item['web']; ?></a>
						</p>
					<?php endif; ?>
				</div>
			</div>
			<?php endif; ?>
			
			
			<!-- **** PLANNING INFOS **** !-->
			<div id="jamPlanningPanel" class="panel panel-default no-border" style="display:flex;">
				<!-- Picto !-->
				<div style="align-self:center"><img style="height: 13px; margin:0px 16px;" src="/images/icons/time.png" alt="time"></div>
				<!-- Block !-->
				<?php if ($jam_item['date_debut'] == $jam_item['date_fin']) : ?>
					<h6><b>planning non défini</b></h6>
				<?php else: ?>
				<div class="soften small" style="margin-top: 10px">
					<p>
						<?php if (isset($member)) : ?>
							<span class="numbers"><?php echo $jam_item['date_bal']; ?></span> > balances<br>
						<?php endif; ?>
						<span class="numbers"><?php echo $jam_item['date_debut']; ?></span> > début<br>
						<span class="numbers"><?php echo $jam_item['date_fin']; ?></span> > fin
					</p>
				</div>
				<?php endif; ?>
			</div>
			
			<?php if (isset($stage_item)) : ?>
			<div id="stagePlanningPanel" class="panel panel-default no-border hidden" style="display:flex;">
				<!-- Picto !-->
				<div style="align-self:center"><img style="height: 13px; margin:0px 16px;" src="/images/icons/time.png" alt="time"></div>
				<!-- Block !-->
				<div class="soften small" style="margin-top: 10px">
					<p>
						<span class="numbers"><?php echo $stage_item['date_debut']; ?></span> > début du stage<br>
					</p>
				</div>
			</div>
			<?php endif; ?>
			
		</div>
		
		
		
		<!-- ********************** SUB PANEL RIGHT => TEXTE ****************** !-->
		<div id="jamTextPanel" class="col-lg-7 <?php if(!$jam_item['text_html']) echo "hidden" ?>">
			<!-- **** JAM TEXT **** !-->
			<div class="panel panel-body panel-default no-border small">
				<div id="jamText" style="overflow:hidden; display:none; transition: height 400ms">
					<?php echo $jam_item['text_html']; ?>
				</div>
			</div>
		</div>

		
		<?php if (isset($stage_item)) : ?>
		<div id="stageTextPanel" class="col-lg-7 hidden">
			<!-- **** STAGE TEXT **** !-->
			<div class="panel panel-body panel-default no-border small">
				<p id="stageText" style="overflow:hidden; transition: height 400ms"><?php echo $stage_item['text_html']; ?></p>
				<br>
				<div class="text-center">
					<button id="stagePreinscrBtn" class="btn btn-default" onclick="javascript:popup_Stage_preinscription()">Pré-inscription stagiaire</button>
					<!-- Boutton invisible pour le chargement dynamique de modal !-->
					<button id="hiddenStageBtn" class="btn btn-default hidden" href="<?php echo site_url("stage/inscription/".$jam_item['slug']) ?>" data-remote="false" data-toggle="modal" data-target="#preincsriptionModal"></button>		
				</div>
				<br>
			</div>
		</div>
		<?php endif; ?>
	
	</div> <!-- sub panel !-->
	
</div> <!-- jam panel !-->


<!-- ************************************************************************* !-->
<!-- ********************* MAIN PANEL **************************************** !-->
<!-- ************************************************************************* !-->

<div class="main row">
	
	
	<!-- ********** MENU TABS  ********** !-->
	<div id="jamTabs" class="row" style="display:none;">
	
		<ul id="menu_tabs" class="nav nav-tabs">
			<!-- Informations !-->
			<li class="active"><a data-toggle="tab" href="#infos"><small><i class='glyphicon glyphicon-info-sign'></i></small><span class="hidden-xs">&nbsp;&nbsp;Informations</span></a></li>
			<!-- Inscriptions !-->
			<!-- Accès admin !-->
			<?php
				$tag = "";
				if ( ( $jam_item["acces_inscriptions"] == 0 || $jam_item['is_archived'] || $playlist_item == 'null') &&  ( $isSuperAdmin == 1  ||  $isSuperAdmin  ) )
					$tag = "<small><i class='glyphicon glyphicon-cog'></i></small>&nbsp;&nbsp;";
			?>
			<li class="<?php if (!$attend) echo "disabled" ?> attend_active"><a data-toggle="tab" href="#inscriptions" data-url="<?php echo site_url('jam/inscriptions/'.$jam_item['slug']); ?>"><small><?php echo $tag ?><i class='glyphicon glyphicon-list-alt'></i></small><span class="hidden-xs">&nbsp;&nbsp;Inscription morceaux</span></a></li>
			<!-- Répétitions !-->
			<li class=""><a data-toggle="tab" href="#repetitions" data-url="<?php echo site_url("jam/repetitions/".$jam_item['slug']) ?>"><small><i class='glyphicon glyphicon-calendar'></i></small><span class="hidden-xs">&nbsp;&nbsp;Répétitions</span></a></li>
			<!-- Discussions !-->
			<li class="disabled hidden"><a href="#discussions"><small><i class='glyphicon glyphicon-comment'></i></small><span class="hidden-xs">&nbsp;&nbsp;Discussions</span></a></li>
		</ul>
	
	
		<div class="tab-content">
	
			<!-- ******* TAB INFOS ******* !-->
			<div id="infos" class="tab-pane fade in active">
			<div class="row">
			<div class="panel panel-default">
			
				<div id="infosBlock" class="row block">
				
					<!-- *******************************  BLOCK DE GAUCHE (PLAYLIST)  ************************************** -->
					<div class="col-md-9 noGutter">
					
						<div class="col-md-7">
						<div id="playlistBlock" class="panel panel-default no-border">
						
							<!-- ///////// LISTE DES TITRES   -/////////////->
							
							<!-- **** HEADING **** !-->
							<div class="panel-heading">
								<span class="soften">
									Liste des titres
									<!-- Info admin -->
									<?php if ($isSuperAdmin == "1" && ($playlist_item != "null" && $playlist_item['list'] != 0)): ?>
										<?php echo ' - <a class="black_link" target="_self" href="'.site_url('playlist/update/'.$playlist_item['infos']['id']).'">'.$playlist_item['infos']['title'].'</a>';?>

										<!-- Generate btn !-->
										<button id="gen_Btn" class="btn btn-xs btn-default pull-right"
												data-remote="false" data-toggle="modal" data-target="#genFileModal"
												href="<?php echo site_url("jam/generate_file/".$jam_item['id']) ?>"
												title="Générer fichier" type="button">
											<i class='glyphicon glyphicon-cog soften'></i>
										</button>
									<?php endif;?>
								</span>
							</div>
							
							<?php if ($playlist_item != "null" && $playlist_item['list'] != 0): ?>

								<!-- **** TABLEAU LISTE DES MORCEAUX **** !-->
								<table class="listTab is_playable" playlistId="<?php echo $playlist_item['infos']['id']; ?>">
									<thead>
										<tr>
											<th></th>
											<th style="text-align:center"><span class="choeurs"><img style='height: 12px;' src='/images/icons/heart.png' title='choeurs'></span></th>
											<th style="text-align:center"><span class="cuivres"><img style='height: 16px; margin:0 2' src='/images/icons/tp.png' title='cuivres'></span></th>
											<?php if (isset($stage_item)):?>
												<th style="text-align:center"><span class="stage"><img style='height: 16px;' src='/images/icons/metro.png' title='réservé aux stagiaires'></span></th>
											<?php endif; ?>
										</tr>
									</thead>
									<tfoot>
										<tr>
											<th></th>
											<th style="text-align:center"><img style='height: 10px;' src='/images/icons/heart.png'></th>
											<th style="text-align:center"><img style='height: 14px; margin:0 2' src='/images/icons/tp.png'></th>
											<?php if (isset($stage_item)):?>
												<th width="10px" style="text-align:center"><img style='height: 14px; margin:0 2' src='/images/icons/metro.png'><span class="stage"></span></th>
											<?php endif; ?>
										</tr>
									</tfoot>
									<tbody id="playlist_body">
									<?php foreach ($playlist_item['list'] as $key=>$ref): ?>
										<tr id="<?php echo $ref->versionId ?>" versionId="<?php echo $ref->versionId ?>" class="<?php if ($ref->reserve_stage) echo "stage_elem";?>">
										<?php if ($ref->versionId != -1): ?>
											<td><?php echo $ref->titre ?><small class="soften"> - <?php echo $ref->artisteLabel ?></small></td>
											<td style="text-align: center"><?php if ($ref->choeurs == 1) echo "<img style='height: 12px;' src='/images/icons/ok.png'>";?></td>
											<td style="text-align: center"><?php if ($ref->soufflants == 1) echo "<img style='height: 12px;' src='/images/icons/ok.png'>";?></td>
											<?php if (isset($stage_item)):?>
												<td style="text-align: center"><?php if ($ref->reserve_stage == 1) echo "<img style='height: 12px;' src='/images/icons/ok.png'>";?></td>
											<?php endif; ?>
										<?php else: ?>
											<td colspan=4>-== pause ==-</td>
										<?php endif; ?>
										</tr>
									<?php endforeach; ?>
									</tbody>
								</table>
							<?php endif; ?>
							
							
							<!-- ***** WISHLIST **** -->
							<?php if ($playlist_item == "null" || $playlist_item['list'] == 0): ?>
								<div id="whishlist_content" class="panel-body">
									<div class="alert alert-warning">Aucune liste de morceaux n'a été sélectionnée pour l'instant !</div>

									<div id="head_wishlist" class="small">
										<?php if (!isset($member)) : ?>
											<p>
												Vous pouvez proposer vos titres ci-dessous en étant inscrit au site et en participant à la jam.
											</p>
										<?php else : ?>
											<p class="attend">
												Vous pouvez proposer vos titres ci-dessous.
											</p>
											<p class="not_attend">
												Vous pourrez proposer vos titres ici si vous participez à la jam.
											</p>
											<p class="attend">
												Merci de poster un lien vers de l'audio.<br>
												Sélectionner si possible un titre possédant :
												<ul><li class="attend">des soufflants</li><li class="attend">des choeurs</li></ul>
											</p>
										<?php endif ?>
									</div>
									
									<hr>
									
									<!-- liste -->
									<?php if ($wishlist != "null"): ?>
									<div id="wishlist">
										<?php foreach ($wishlist as $wish_elem): ?>
											<p class="soften"><small><?php echo $wish_elem['pseudo'] ?> à proposé :</small></br>
												<a href="<?php echo $wish_elem['url'] ?>" target="_blanck"><?php echo $wish_elem['titre'] ?></a>
											</p>
										<?php endforeach ?>
									</div>
									<hr>
									<?php endif ?>
									
									
									<!-- formulaire -->
									<?php if (isset($member) && $attend) : ?>
									<form class="attend" action="javascript:add_wish()">
										<div class="form-group required">
											<label for="wish_url" class="control-label">Proposition</label>
											<input id="wish_url" class="form-control" type="url" name="wish_url" value="" required placeholder="URL"/>
										</div>
										<button class="btn btn-default pull-right" type="submit" name="submit">Proposer</button>
									</form>
									<?php endif; ?>

								</div>
							<?php endif; ?>
						
						</div>
						</div> <!-- liste panel !-->

						
						<!-- *******************************  BLOCK DU MILIEU (INFOS)  ************************************** -->
						<div class="col-md-5">
						
							<!-- *********  NOTIFICATION PANEL ********** !-->
							<div id="msgBlock" class="panel panel-default no-border">
					
								<!-- **** HEADING **** !-->
								<div class="panel-heading">
									<span class="soften">Panneau d'informations</span>
								</div>
								
								<!-- **** LISTE DES MESSAGES **** !-->
								<small>
								<div class="list-group hidden">
								</div>
								</small>
							</div>  <!-- msg panel !-->
							
							
							<!-- *********  RESSOURCES PANEL ********** !-->
							<div id="rscBlock" class="panel panel-default no-border hidden">
					
								<!-- **** HEADING **** !-->
								<div class="panel-heading">
									<span class="soften">Ressources</span>
								</div>
								
								<!-- **** LISTE DES RESSOURCES **** !-->
								<small>
								<div class="list-group hidden">
								</div>
								</small>
							</div>  <!-- ressources panel !-->
							
							
						</div>
						
						
						<!-- *******************************  BLOCK DE GAUCHE/MILIEU BIS (MSGBOARD)  ************************************** -->
						<?php if (isset($member) &&  ($jam_item["acces_jam"] != 2  ||  $attend) ) : ?>
							<div class="col-md-12">
								<div id="msgBoard" class="panel panel-default no-border" style="margin-bottom: 0px">
								
									<!-- **** HEADING **** !-->
									<div class="panel-heading">
										<span class="soften">Messages</span>
									</div>
									
									
									<div class="panel-body small">
									
										<!-- **** LISTE DES MESSAGES **** !-->
										<div id="messageList" class="list-group answerable" style="margin-bottom: 0px">
										</div>							
									
										<!-- **** POSTER UN MESSAGE **** !-->
										<?php if (isset($member)): ?>
											<div class="container-fluid">
												<form id="messageForm" class="form-horizontal">
													<div class="form-group">
														<!-- Textarea autosize !-->
														<textarea id="messageInput" class="form-control autosize" name="message" placeholder="Votre message..." rows="1" style="resize:none; font-size: 100%"></textarea>
													</div>
												</form>
											</div>
										<?php endif ?>
										
									</div>
									
									<!-- Form caché d'update !-->
									<!--<div id="updateForm" class="container-fluid hidden">
										<form  class="form-horizontal">
											<div class="form-group">!-->
											
												<!-- Textarea autosize !-->
												<!--<div class="col-sm-12 noGutter">
													<textarea id="updateInput" class="form-control autosize" name="updateMsg" style="resize:none; font-size: 100%"></textarea>
												</div>
												
											</div>
										</form>
									</div>!-->
										
								</div>
								
							</div>
						<?php endif; ?>
					
					</div>
					
					
					
					<!-- *******************************  BLOCK DE DROITE  ************************************** -->
					<div class="col-md-3 noGutter">
					<div id="jammersBlock" class="panel panel-default no-border">						
						
						<!-- *********  Liste des participants ********** !-->
						
						<!-- **** HEADING **** !-->
						<div class="panel-heading">
							<span class="soften pull-left">Liste des participants <small>(<span id="nb_jammeur"><?php echo sizeof($list_members) ?></span>)</small></span>
							<?php if (isset($member) && sizeof($list_members) > 0  && $jam_item["acces_jam"] != 2) :?>
								<!-- Options d'affichage !-->
								<button id="cat_icon" class="btn btn-default btn-xs pull-right transparent " onclick='javascript:change_display("pupitre")'><img style='height: 14px;' src='/images/icons/cat.png' title='afficher par categories'></button>
								<button id="list_icon" class="btn btn-default btn-xs pull-right transparent hidden" onclick='javascript:change_display("list")'><img style='height: 14px;' src='/images/icons/list.png' title='afficher la liste'></button>
							<?php endif; ?>
							<div class="clearfix"></div>
						</div>
						
						<!-- **** LISTE DES PARTICIPANTS **** !-->
						<div id="member_list" class="panel-body">
							
							<!-- Visiteur logué !-->
							<?php if (isset($member) && ($jam_item["acces_jam"] != 2  ||  $attend) ) : ?>
								
								<!-- liste des encadrants et des participants par orde alphabétique !-->
								<div id="list_participant" style="word-wrap: break-word;">
									
									<?php if (sizeof($list_referent) > 0) : ?>
									<ul id="list_referent" class="list-group">
										<li class="list-group-item" label="encadrant">
											<h4>Référents</h4>
											<?php foreach ($list_referent as $tref) {
												echo "<div class='referent'>";
													
													// On affiche la famille, pupitre, instrument, poste concerné
													echo '<small class="softer">'.$tref->tag2Title." <i>".$tref->tag2Label."</i></small>";
													
													// On affiche le nom du participant (et son instru en hide)
													echo "<a class='label label-success member' idMember='".$tref->tag1Val."' value='".$tref->pseudo."' data-toggle='popover' data-trigger='hover' data-placement='top'>
																<img class='img-circle miniAvatar avatarNotSet' src='".base_url('images/icons/avatar2.png')."' width='16' height='16' hasAvatar='".$tref->hasAvatar."'>
																".$tref->pseudo."</a> ";
																
												echo "</div>";
											} ?>
										</li>
									</ul>
									<?php endif; ?>
									
									<ul id="list_alpha" class="list-group">
										<li id="list_main" class="list-group-item" label="participant">
											<h4>Participants</h4>
											<?php foreach ($list_members as $tmember) {
												// On affiche le nom du participant (et son instru en hide)
												echo "<a class='label label-success member' idMember='".$tmember->id."' hasAvatar='".$tmember->hasAvatar."' value='".$tmember->pseudo."' 
															data-toggle='popover' data-trigger='hover' data-placement='top'>".$tmember->pseudo."</a> ";
											} ?>
										</li>
										<!-- Liste des membres liés à la jam mais non inscrits !-->
										<li id="list_linked" class="list-group-item hidden">
										</li>
									</ul>
								</div>
								
								<!-- liste des participants par pupitre !-->
								<?php if (isset($instrumentation_header) && $instrumentation_header != false) : ?>
								<ul id="list_pupitre" class="list-group hidden">
									<?php foreach ($instrumentation_header as $header_item): ?>
										<li class="list-group-item" label="<?php echo $header_item['pupitreLabel']; ?>">
											<!-- Catégorie !-->
											<h4><img style="height:16px; vertical-align: text-top; margin: 0px 5px 2px 5px" src="<?php echo base_url().'/images/icons/'.$header_item['iconURL']; ?>">
												<?php echo ucFirst($header_item['pupitreLabel']); ?>
											</h4>
											<div class="pupitre_content" style='list-style-type: none; padding-left:10px; word-wrap: break-word;'>
												<?php foreach ($list_members as $tmember) {
													// On cherche si l'idInstru1 existe dans la catégorie
													//$key = array_search($tmember->idInstru1,$cat['list']);
													//if ($cat['list'][$key] == $tmember->mainInstruId) echo "<li class='member' idMember='".$tmember->id."'><span>".$tmember->pseudo.'</span><small><span class="instru soften" style="display:none"> > '.$tmember->mainInstruName.'</span></small></li>';
													if (property_exists($tmember,$header_item['pupitreLabel'])) {
														echo "<a class='label label-success member' idMember='".$tmember->id."' value='".$tmember->pseudo."' 
															data-toggle='popover' data-trigger='hover' data-placement='top'>".$tmember->pseudo."</a> ";
													}
												} ?>
											</div>
										</li>
									<?php endforeach; ?>
								</ul>
								<?php endif ?>
								
							<!-- Visiteur non logué !-->
							<?php else: ?>
								<?php if (!$jam_item['is_archived']) :?>
									<small>Il y a actuellement <?php echo sizeof($list_members)?> participant.e.s à cette jam.</small>
								<?php else: ?>
									<small><?php echo sizeof($list_members)?> personnes ont participés à cette jam.</small>
								<?php endif; ?>
							<?php endif; ?>
						</div>

					</div>
					</div> <!-- participant panel !-->
					
				</div> <!-- main row !-->
			
			</div>
			</div>
			</div> <!-- tab infos !-->
			
			
			<!-- ******* TAB INSCRIPTIONS ******* !-->
			<div id="inscriptions" class="tab-pane fade in active">
			</div>
			
			
			<!-- ******* TAB REPETITIONS ******* !-->
			<div id="repetitions" class="tab-pane fade in active">
			</div>
			
			
		
		</div>  <!-- tab content !-->
	</div>  <!-- tab panel content !-->
	
</div>


<!-- ******** MODAL ******* !-->
<div id="modal_msg" class="modal fade" role="dialog">
	<div class="modal-content">
		<div class="modal-header lead"></div>
		<div class="modal-body"></div>
		<div class="modal-footer"></div>
	</div>
</div>


<!-- ******** MODAL UPDATE JAM ******* !-->
<div id="updateModal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog default">
		<div class="modal-content">
			<div class="modal-header lead">Modifier la jam</div>
			<div class="modal-body">
			...
			</div>
		</div>
	</div>
</div>

<!-- ******** MODAL UPDATE TEXT TAB JAM ******* !-->
<div id="updateTextTabModal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog default">
		<div class="modal-content">
			<div class="modal-header lead">Modifier le texte d'information</div>
			<div class="modal-body">
			...
			</div>
		</div>
	</div>
</div>


<!-- ******** MODAL INVITATIONS JAM ******* !-->
<div id="invitModal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog default">
		<div class="modal-content">
			<div class="modal-header lead">Invitations</div>
			<div class="modal-body">
			...
			</div>
		</div>
	</div>
</div>



<!-- ******** MODAL PRE-INSCRIPTION ******* !-->
<div id="preincsriptionModal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog default">
		<div class="modal-content">
			<div class="modal-header lead">Pré-inscription au stage</div>
			<div class="modal-body">
			...
			</div>
		</div>
	</div>
</div>



<!-- ******** MODAL GENERATE FILE ******* !-->
<div id="genFileModal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog default">
	<div class="modal-content">
		<div class="modal-header lead">Générer un fichier de la playlist</div>
		<div class="modal-body">
		...
		</div>
	</div>
	</div>
</div>
