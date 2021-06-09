<!-- Textarea resizable !-->
<!-- <script type="text/javascript" src="<?php echo base_url();?>ressources/script/readmore.min.js"></script> !-->
<script type="text/javascript" src="<?php echo base_url();?>ressources/script/readmore.min.js"></script>


<!-- bootstrapValidator !-->
<!-- doit être loadé ici car loading dynamique pour les inscriptions stage ne fonctionne pas !-->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-validator/0.5.3/js/bootstrapValidator.js"></script>
<!--<script type="text/javascript" src="<?php echo base_url();?>ressources/script/validator.js"></script>!-->

<script type="text/javascript">


	$(function() {
		
		// On masque les éléments inacessibles si besoin
		refresh_jam();
		
		// On gère les affichage d'instru dynamique		
		$("#member_list").on("mouseenter",".member", function() {
						$(this).find(".instru").css("display","inline");
					});
		$("#member_list").on("mouseleave",".member", function() {
						$(this).find(".instru").css("display","none");
					});
	
		
		
		// On active les handlers pour le player si besoin (playlist + logged)
		<?php 
			if (isset($player) && $player)
				echo "song_update();";
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
		
		
		// ********** TOOLTIP ***************
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
		if ('<?php echo ($this->session->userdata('logged')) ?>' != '1') {
			// Modal
			btnInscr = "<button class='btn btn-default' onclick='location.href=\"<?php echo site_url(); ?>/members/create\"'><li class='glyphicon glyphicon-user'></li>&nbsp;&nbsp;Inscription</button>";
			btnLog = "<button class='btn btn-default' data-toggle='modal' href='#modal_login'><li class='glyphicon glyphicon-log-in'></li>&nbsp;&nbsp;Connexion</button>";
			msg = "<p>Pour participer à la jam, vous devez d'abord devenir membre du <b>Grenoble Reggae Orchestra</b> en vous inscrivant sur le site ou vous identifier sur votre compte si vous êtes déjà membre.</p>";
			msg += "<div style='display:flex; justify-content:center'><div class='btn-toolbar'>" + btnInscr + btnLog + "</div></div>";
			$("#modal_msg").modal({backdrop: true});
			$("#modal_msg .modal-dialog").addClass("default");
			$("#modal_msg .modal-header").html("Participation impossible !");
			$("#modal_msg .modal-body").html(msg);
			$("#modal_msg").modal('show');
		}
		
		// L'utilisateur est loggé il peut joindre la joindre la jam
		else {
		
			/* Bénévoles */
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
	
	
	/****** JOIN JAM  *******/
	function join_jam() {

		// Gestion du bénévolat
		$buffet = $("input:checkbox[name=buffet]").prop("checked") ? 1 : 0;
		$billet = $("input:checkbox[name=billet]").prop("checked") ? 1 : 0;
		$balance = $("input:checkbox[name=balance]").prop("checked") ? 1 : 0;
		
		// On change le curseur
		document.body.style.cursor = 'wait';
	
		// Requète ajax au serveur
		$.post("<?php echo site_url(); ?>/ajax/join_jam",
		
			{	
				'slugJam':'<?php echo $jam_item['slug']; ?>',
				'id':'<?php echo $this->session->userdata('id')?>',
				'event_admin':0,
				'buffet':$buffet,
				'billet':$billet,
				'balance':$balance
			},
		
			function (return_data) {
				
				$obj = JSON.parse(return_data);
				
				// On change le curseur
				document.body.style.cursor = 'default';
				
				// Modal
				if ($obj['state'] == 1) {
					
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
			if ('<?php echo ($this->session->userdata('logged')) ?>' != '1') {
				// Modal
				btnInscr = "<button class='btn btn-default' onclick='location.href=\"<?php echo site_url(); ?>/members/create\"'><li class='glyphicon glyphicon-user'></li>&nbsp;&nbsp;Inscription</button>";
				btnLog = "<button class='btn btn-default' data-toggle='modal' href='#modal_login'><li class='glyphicon glyphicon-log-in'></li>&nbsp;&nbsp;Connexion</button>";
				msg = "<p>Pour faire une pré-inscription au stage, vous devez d'abord devenir membre du <b>Grenoble Reggae Orchestra</b> en vous inscrivant sur le site ou vous identifier sur votre compte si vous êtes déjà membre.</p>";
				msg += "<div style='display:flex; justify-content:center'><div class='btn-toolbar'>" + btnInscr + btnLog + "</div></div>";
				$("#modal_msg").modal({backdrop: true});
				$("#modal_msg .modal-dialog").addClass("default");
				$("#modal_msg .modal-header").html("Pré-inscription impossible !");
				$("#modal_msg .modal-body").html(msg);
				$("#modal_msg").modal('show');
			}
			
			// L'utilisateur est loggé mais ne participe pas à la jam
			else if ($('#attend').html() == '0') {

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
			}
			
			// On ouvre la modal de préinscription si l'utilisateur n'a pas déjà fait de préinscription
			else if ($("#attend_stage").html() == 0) {
				// On click sur le hidden btn permettant de charger une page dans la modal dynamiquement
				$("#hiddenStageBtn").trigger("click");
			}
		<?php endif; ?>
	}
	
	
	// Pour actualiser les participations à la jam en tant que bénévole
	/*function update_task()  {
		
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
    }*/

	
	
	
	/******* QUIT JAM  ********/
	function quit_jam()  {
		// Modal
		btn1 = "<button class='btn btn-default' onclick='javascript:really_quit_jam()'>Se désinscrire</button>";
		btn2 = "<button class='btn btn-default' data-dismiss='modal'>Annuler</button>";
		msg = "<p>En vous désinscrivant de cette jam vous perdrez toutes les informations qui y sont associées (inscriptions sur les morceaux).</p>";
		msg += "<div style='display:flex; justify-content:center'><div class='btn-toolbar'>" + btn1 + btn2 + "</div></div>";
		$("#modal_msg").modal({backdrop: true});
		$("#modal_msg .modal-dialog").addClass("default");
		$("#modal_msg .modal-header").html("Avertissement");
		$("#modal_msg .modal-body").html(msg);
		$("#modal_msg").modal('show');
    }
	

	function really_quit_jam()  {
		
		// On change le curseur
		document.body.style.cursor = 'wait';
		
		// Requète ajax au serveur
		$.post("<?php echo site_url(); ?>/ajax/quit_jam",
		
			{
				'slugJam':'<?php echo $jam_item['slug'] ?>',
				'id':'<?php echo $this->session->userdata('id') ?>'
			},
	
			function (return_data) {
				
				$obj = JSON.parse(return_data);
				// On change le curseur
				document.body.style.cursor = 'default';
				
				// On ferme la modal
				$("#modal_msg").modal('hide');
				
				// Modal
				if ($obj['state'] == 1) {
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
	
	
	
	<?php if ($is_admin): ?>
	// ******** PRESENTATION *********/
	function requestFullScreen(element) {
		// Supports most browsers and their versions.
		var requestMethod = element.requestFullScreen || element.webkitRequestFullScreen || element.mozRequestFullScreen || element.msRequestFullScreen;

		if (requestMethod) { // Native full screen.
			requestMethod.call(element);
		}
		else if (typeof window.ActiveXObject !== "undefined") { // Older IE.
			var wscript = new ActiveXObject("WScript.Shell");
			if (wscript !== null) {
				wscript.SendKeys("{F11}");
			}
		}
	}
	
	function launch_pres($jamTitle, $jamSlug) {
		
		//console.log("popup_launch_pres");

		var elem = document.getElementById("jamTabs");;
		requestFullScreen(elem);
		
	}
	<?php endif; ?>
	
	
	
	
	

	
	// ******  REFRESH_JAM   ***********/
	function refresh_jam($action) {
	
		//console.log("refresh_jam : "+$action);
	
		if ($action == "join_jam") {
			var newBtn = "<button class='btn btn-checked' type='submit' name='quit_jam' onclick='quit_jam()'><i class='glyphicon glyphicon-ok'></i>&nbsp;&nbsp;&nbsp;Je participe</button>";
			$("button[name=join_jam]").replaceWith(newBtn);
			
			// On change l'état de la span attend
			$('#attend').text("1");
			
			// On rend visible ce qui ne l'était pas
			$('.attend').css("display","block");
			$('.not_attend').css("display","none");
			
			// On active ce qui était disabled (jam_tabs)
			$('.attend_active').removeClass("disabled");
			
			// On ajoute le member à la liste des participants
			$("#member_list #list_alpha").append("<li class='member' idMember='<?php $this->session->userdata('id') ?>'><span>"+$("#memberLogin").html()+"</span></li>");
			
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
			$("#member_list #list_alpha li[idMember=<?php if ($member)echo $member->memberId; else echo "-1" ?>]").remove();
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

		// On actualise le nombre de jammeurs
		$("#nb_jammeur").empty().append($("#member_list #list_alpha").children().length);
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
		$.post("<?php echo site_url(); ?>/ajax_jam/delete_jam",
	
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
					$("#modal_msg .modal-footer").html('<a id="modal_close" href="<?php echo base_url().'index.php/jam'; ?>">Fermer</a>');
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
		$.post("<?php echo site_url(); ?>/jam/ajax_add_wish",
		
			{
			'slugJam':'<?php echo $jam_item['slug'] ?>',
			'id':'<?php echo $this->session->userdata('id')?>',
			'url':$wish_url
			},
			
			function (return_data) {
				
				$obj = JSON.parse(return_data);
				
				// On change le curseur
				document.body.style.cursor = 'default';
				
				// Modal
				if ($obj['state'] == 1) {
					
					// On insère le wish_elem
					//$("#wishlist").append('<div class=\"soften\"><small><?php echo $this->session->userdata('login'); ?> à proposé :</small></br><a href=\"'+$wish_url+'\" target=\"_blanck\">'+$obj['data']+'</a></div>');
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
	

	
	/***************************** MESSAGES  *******************************/
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
		$archived = <?php echo $jam_item['is_archived'] == 0 ? "false" : "true" ?>;
		$playlist = <?php echo $playlist_item != "null" ? "true" : "false" ?>;
		$affectations = <?php echo $jam_item['affectations_visibles'] == 1 ? "true" : "false"; ?>;
		$attend_stage = $('#attend_stage').text() == 1;
		$cheque_stage = $('#cheque_stage').text() == 1;
		
		
		// Message d'info d'acces aux inscriptons
		if (!$archived && $playlist && !$attend_stage) {
			<?php if ($jam_item['acces_inscriptions'] == 1): ?>
				$div = "<div class='list-group-item list-group-item-warning'>Les inscriptions aux morceaux sont ouvertes !</div>"
			<?php else: ?>
				$div = "<div class='list-group-item list-group-item-warning'>Les inscriptions aux morceaux ne sont pas encore accessibles.</div>"
			<?php endif; ?>	
		}
		
		// Message si jam archivée
		else if ($archived) {
			$div = "<div class='list-group-item list-group-item-warning'><i class='glyphicon glyphicon-warning-sign'></i>Cette jam est archivée.</div>"
		}
		
		// Message si pas de playlist
		else if (!$playlist) {
			$div = "<div class='list-group-item list-group-item-warning'><i class='glyphicon glyphicon-warning-sign'></i>La playlist n'a pas encore été fixée.</div>"
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
					$div.append(" et vous "+$text+" choisi "+$("#member_inscriptions").children().length+" morceaux sur lesquels jouer :");
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
				$text = "<b><i class='glyphicon glyphicon-warning-sign'></i> <u>Attente de réglement</b></u><br>";
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
		else {
			$div = $("<div class='list-group-item list-group-item-warning'>Vous ne participez pas à la jam.</div>");
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
		$.post("<?php echo site_url(); ?>/ajax_jam/get_files",
		
			{
			'jamId':'<?php echo $jam_item['id'] ?>',
			},
			
			function (return_data) {
				
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
		$.post("<?php echo site_url(); ?>/Ajax_file/remove_file",
		
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
		
		$("#list_alpha").toggle();
		$("#cat_icon").toggle();
	}


 </script>
 
 

<!-- **************  VARIABLES CACHEES pour JAVASCRIPT ***************************** -->
<div class="hidden">
	<span id="attend"><?php echo ($attend ? 1 : 0); ?></span>
	<span id="attend_stage"><?php echo ($attend_stage ? 1 : 0); ?></span>
	<span id="cheque_stage"><?php if (isset($cheque_stage)) echo ($cheque_stage ? 1 : 0); ?></span>
	<span id="appel_benevole"><?php echo ($jam_item['appel_benevole'] ? 1 : 0); ?></span>
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
			$this->load->helper('my_text_helper');
			$month = strftime("%b", strtotime($jam_item['date']));
			if (!$this->config->item('online')) $month = utf8_encode($month);
			$month =  substr(strtoupper(no_accent($month)),0,3);
		?>
		<div class="date_box">
			<div><small><?php echo $month; ?></small></div>
			<div><strong><?php echo explode('-', $jam_item['date'])[2] ?></strong></div>
		</div>


		<!-- Titre de la jam + Action bar !-->
		<div class="title_box">
			<h3><?php if ($jam_item['acces_jam'] == 0) echo "<i class='glyphicon glyphicon-cog'></i>&nbsp;"; ?><?php echo $jam_item['title']; ?></h3>
			
			
			<!-- **** ACTION BAR **** !-->
			<div class="row">
		
				<!-- Si la jam n'est pas archivée -->
				<?php if (!$jam_item['is_archived']) :?>
					
					<!-- BOUTTONS D'INSCRIPTIONS   -->
					<div class="action_bar btn-group btn-group-sm">
					
						<!-- Si l'utilisateur ne participe pas déjà, on affiche le boutons d'inscription -->
						<?php if (!$attend) :?>
							
							<!-- PARTICIPER   Bouton d'inscription à la jam -->
							<button class="btn btn-default" type="submit" name="join_jam"
									<?php if (sizeof($list_members) >= $jam_item['max_inscr'] && $jam_item['max_inscr'] != -1) echo "disabled" ?>
									onclick="inscription()">Participer</button>
						
						<!-- L'utilisateur participe, bouton de desinscription !-->
						<?php elseif ($attend) :?>
							<button class="btn btn-checked" type="submit" name="quit_jam" onclick="quit_jam()"><i class='glyphicon glyphicon-ok'></i>&nbsp;&nbsp;&nbsp;Je participe</button>
						<?php endif; ?>
						
					</div>
				<?php endif; ?>
				
			
				
				<!-- BOUTTONS D'ADMIN EVENT   -->
				<?php if($this->session->userdata('admin') == "1" || $is_admin ) : ?>
				<div class="action_bar btn-group btn-group-sm">
					<!-- MANAGE PARTICIPANTS -->
					<a class="btn btn-default" href='<?php echo site_url();?>/jam/manage/<?php echo $jam_item['slug'] ?>'><img style="height: 13px; vertical-align:text-top;" src="/images/icons/group.png" alt="Participants"><span class="hidden-xs">&nbsp;&nbsp;Participants</span></a>
					<!-- AFFECTATIONS -->
					<a class="btn btn-default" href='<?php echo site_url();?>/jam/affect/<?php echo $jam_item['slug'] ?>'><img style="height: 15px; vertical-align:text-top;" src="/images/icons/affectation.png" alt="Affectations"><span class="hidden-xs">&nbsp;&nbsp;Affectations</span></a>
				</div>	
				<?php endif ?>
				
				<!-- BOUTTONS SUPER ADMIN   -->
				<?php if($this->session->userdata('admin') == "1" ) : ?>
				<div class="action_bar btn-group btn-group-sm">
					<!-- MODIFIER -->
					<button class="btn btn-default" href="<?php echo site_url();?>/jam/update/<?php echo $jam_item['slug'] ?>" data-remote="false" data-toggle="modal" data-target="#updateModal"><img style="height: 15px; vertical-align:text-top;" src="/images/icons/edit.png" alt="Modifier"><span class="hidden-xs hidden-sm">&nbsp;&nbsp;Modifier</span></button>
					<!-- SUPRRIMER -->
					<button class="btn btn-default" onclick="javascript:popup_delete_jam('<?php echo str_replace("'", "\'", $jam_item['title']).'\',\''.$jam_item['slug']; ?>')"><img style="height: 15px; vertical-align:text-top;" src="/images/icons/trash.png" alt="Supprimer"><span class="hidden-xs hidden-sm">&nbsp;&nbsp;Supprimer</span></button>
					<!-- PRESENTATION -->
					<a class="btn btn-default" href='<?php echo site_url();?>/jam/presentation/<?php echo $jam_item['slug'] ?>'><i class='glyphicon glyphicon-expand soften'></i><span class="hidden-xs hidden-sm">&nbsp;&nbsp;Présentation</span></a>
				</div>	
				<?php endif ?>
			
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
					<div class='alert alert-danger'><b>Participation impossible</b> : le nombre maximum de participants a été atteint pour cette jam !</div>
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
						<?php if ($this->session->userdata('logged') == true) : ?>
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
					<button id="hiddenStageBtn" class="btn btn-default hidden" href="<?php echo site_url();?>/stage/inscription/<?php echo $jam_item['slug'] ?>" data-remote="false" data-toggle="modal" data-target="#preincsriptionModal"></button>		
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
				if ( ( $jam_item["acces_inscriptions"] == 0 || $jam_item['is_archived'] || $playlist_item == 'null') &&  ( $this->session->userdata('admin') == 1  ||  $is_admin  ) )
					$tag = "<small><i class='glyphicon glyphicon-cog'></i></small>&nbsp;&nbsp;";
			?>
			<li class="<?php if (!$attend) echo "disabled" ?> attend_active"><a data-toggle="tab" href="#inscriptions" data-url="<?php echo site_url(); ?>/jam/inscriptions/<?php echo $jam_item['slug'] ?>"><small><?php echo $tag ?><i class='glyphicon glyphicon-list-alt'></i></small><span class="hidden-xs">&nbsp;&nbsp;Inscription morceaux</span></a></li>
			<!-- Répétitions !-->
			<li class=""><a data-toggle="tab" href="#repetitions" data-url="<?php echo site_url(); ?>/jam/repetitions/<?php echo $jam_item['slug'] ?>"><small><i class='glyphicon glyphicon-calendar'></i></small><span class="hidden-xs">&nbsp;&nbsp;Répétitions</span></a></li>
			<!-- Discussions !-->
			<li class="disabled hidden"><a href="#discussions"><small><i class='glyphicon glyphicon-comment'></i></small><span class="hidden-xs">&nbsp;&nbsp;Discussions</span></a></li>
		</ul>
	
	
		<div class="tab-content">
	
			<!-- ******* TAB INFOS ******* !-->
			<div id="infos" class="tab-pane fade in active">
			<div class="row">
			<div class="panel panel-default">
			
				<div class="row block">
				
					<!-- *******************************  BLOCK DE GAUCHE  ************************************** -->
					<div class="col-md-5">
					<div id="playlistBlock" class="panel panel-default no-border">
					
						<!-- LISTE DES TITRES   -->
						
						<!-- **** HEADING **** !-->
						<div class="panel-heading">
							<span class="soften">
								Liste des titres
								<!-- Info admin -->
								<?php if ($this->session->userdata('admin') == "1" && ($playlist_item != "null" && $playlist_item['list'] != 0)): ?>
									<?php echo ' - '.$playlist_item['infos']['title'];?>
									<!-- Generate btn !-->
									<button id="gen_Btn" class="btn btn-xs btn-default pull-right"
											data-remote="false" data-toggle="modal" data-target="#genFileModal"
											href="<?php echo site_url();?>/jam/generate_file/<?php echo $jam_item['id'] ?>"
											title="Générer fichier" type="button">
										<i class='glyphicon glyphicon-cog soften'></i>
									</button>
								<?php endif;?>
							</span>
						</div>
						
						<?php if ($playlist_item != "null" && $playlist_item['list'] != 0): ?>

							<!-- **** LISTE DES MORCEAUX **** !-->
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
									<tr id="<?php echo $ref->versionId ?>" versionId="<?php echo $ref->versionId ?>">
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
								<div>Aucune liste de morceaux n'a été sélectionnée pour l'instant !</div>

								<div id="head_wishlist">
									<?php if (!$this->session->userdata('logged')) : ?>
										<p>
											Vous pouvez proposer vos titres ci-dessous en étant inscrit au site et en participant à la jam.
										</p>
									<?php else : ?>
										<p class="attend">
											Vous pouvez proposer vos titres ci-dessous.
										</p>
										<p class="not_attend">
											Vous pouvez proposer vos titres ci-dessous si vous participez à la jam.
										</p>
										<p>
											Merci de poster un lien vers de l'audio.<br>
											Sélectionner si possible un titre possédant :
											<ul><li>des soufflants</li><li>des choeurs</li></ul>
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
								<?php /*if ($this->session->userdata('logged') == true && $attend) :*/ ?>
								<form class="attend" action="javascript:add_wish()">
									<div class="form-group required">
										<label for="wish_url" class="control-label">Proposition</label>
										<input id="wish_url" class="form-control" type="url" name="wish_url" value="" required placeholder="URL"/>
									</div>
									<button class="btn btn-default pull-right" type="submit" name="submit">Proposer</button>
								</form>
								<?php /*endif; */?>

							</div>
						<?php endif; ?>
					
					</div>
					</div> <!-- liste panel !-->

					
					<!-- *******************************  BLOCK DU MILIEU  ************************************** -->
					<div class="col-md-4">
					
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
					
					

					<!-- *******************************  BLOCK DE DROITE  ************************************** -->
					<div class="col-md-3">
					<div id="jammersBlock" class="panel panel-default no-border">						
						
						<!-- *********  Liste des participants ********** !-->
						
						<!-- **** HEADING **** !-->
						<div class="panel-heading">
							<span class="soften pull-left">Liste des participants <small><?php if ($this->session->userdata('logged')) echo '(<span id="nb_jammeur">'.sizeof($list_members).'</span>)' ?></small></span>
							<?php if ($this->session->userdata('logged') && sizeof($list_members) > 0 ) :?>
								<!-- Options d'affichage !-->
								<button id="cat_icon" class="btn btn-default btn-xs pull-right transparent " onclick='javascript:change_display("pupitre")'><img style='height: 14px;' src='/images/icons/cat.png' title='afficher par categories'></button>
								<button id="list_icon" class="btn btn-default btn-xs pull-right transparent hidden" onclick='javascript:change_display("list")'><img style='height: 14px;' src='/images/icons/list.png' title='afficher la liste'></button>
							<?php endif; ?>
							<div class="clearfix"></div>
						</div>
						
						<!-- **** LISTE DES PARTICIPANTS **** !-->
						<div id="member_list" class="panel-body">
							
							<!-- Visiteur logué !-->
							<?php if ($this->session->userdata('logged')) : ?>
								
								<!-- liste des participants par orde alphabétique !-->
								<div id="list_alpha" style="word-wrap: break-word;">
									<?php foreach ($list_members as $tmember) {
										// On affiche le nom du participant (et son instru en hide)
										echo "<span class='label label-success' idMember='".$tmember->memberId."'>".$tmember->pseudo."</span> ";
									} ?>
								</div>
								
								<!-- liste des participants par pupitre !-->
								<ul id="list_pupitre" class="list-group hidden" style="padding-top: ">
									<?php foreach ($instrumentation_header as $header_item): ?>
										<li class="list-group-item" label="<?php echo $header_item['pupitreLabel']; ?>">
											<!-- Catégorie !-->
											<h4><img style="height:16px; vertical-align: text-top; margin: 0px 5px 2px 5px" src="<?php echo base_url().'images/icons/'.$header_item['iconURL']; ?>">
												<?php echo ucFirst($header_item['pupitreLabel']); ?>
											</h4>
											<ul class="cat_content" style='list-style-type: none; padding-left:10px;'>
												<?php foreach ($list_members as $tmember) {
													// On cherche si l'idInstru1 existe dans la catégorie
													//$key = array_search($tmember->idInstru1,$cat['list']);
													//if ($cat['list'][$key] == $tmember->mainInstruId) echo "<li class='member' idMember='".$tmember->memberId."'><span>".$tmember->pseudo.'</span><small><span class="instru soften" style="display:none"> > '.$tmember->mainInstruName.'</span></small></li>';
													if (property_exists($tmember,$header_item['pupitreLabel'])) echo "<li class='member' idMember='".$tmember->memberId."'><span class='label label-success'> ".$tmember->pseudo.'</span><small><span class="instru soften" style="display:none"> > '.$tmember->mainInstruName.'</span></small></li>';
												} ?>
											</ul>
										</li>
									<?php endforeach; ?>
								</ul>
								
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
<div id="updateTextTabModal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true" data-action="abort">
	<div class="modal-dialog default">
		<div class="modal-content">
			<div class="modal-header lead">Modifier le texte d'information</div>
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
