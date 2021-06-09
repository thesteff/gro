<!-- autoresize texarea !-->
<script type="text/javascript" src="<?php echo base_url();?>/ressources/script/autosize.js"></script>

<!-- flexdatalist pour les input !-->
<script type="text/javascript" src="<?php echo base_url();?>/ressources/script/jquery-flexdatalist-2.2.4/jquery.flexdatalist.min.js"></script>
<link href="<?php echo base_url();?>/ressources/script/jquery-flexdatalist-2.2.4/jquery.flexdatalist.min.css" rel="stylesheet" type="text/css" />

<script type="text/javascript">

	$(function() {

		// Avatar		
		var image = document.getElementById('imageAvatar');
		
		// On charge l'image de l'avatar
		$.ajax({
			url:'<?php echo base_url("images/avatar"); ?>'+'/'+$("#memberLogin").attr("idKey")+'.png',
			type:'HEAD',
			success: function() {
				$("#left-header #avatar img").prop("src",'<?php echo base_url("images/avatar"); ?>'+'/'+$("#memberLogin").attr("idKey")+'.png');
			}
		});
		
		// Si la taille de la fenêtre change on actualise la taille de la liste de discussion
		$(window).resize(function() {
			refresh();
		});
		
		
		
		// *************** DISCUSSION IHM
		
		// Action sur clic de discussion
		$("#discussion-list .list-group-item").click(function() {
			//console.log("item : "+$(this).attr("id")+" == newDisc ? "+ ($(this).attr("id")== "newDisc"));
			
			// On return si clic sur la discussion active
			if ($(this).hasClass("active")) return;
			
			// On actualise l'item actif
			$("#discussion-list .list-group-item.active").removeClass("active");
			$(this).addClass("active").removeClass("warning");
			
			// On retranche le nb_unread du menu dialog
			//console.log("nb_unread : "+$(this).attr("nb_unread"));
			if ($(this).attr("nb_unread") > 0) {
				
				// On change le curseur
				document.body.style.cursor = 'wait';
			
				// Requète ajax au serveur
				$.post("<?php echo site_url('ajax_members/update_nb_unread_message'); ?>",
				
					{	'delta': $(this).attr("nb_unread")*-1	},
				
					function (return_data) {
			
						$obj = JSON.parse(return_data);
						
						// On change le curseur
						document.body.style.cursor = 'default';
						
						if ($obj['state'] == 1) {
							
							$newBadge = $obj['data'];
							
							if ($newBadge <= 0) {
								$(".navbar-right #dialog").css("padding-right",$(".navbar-right #dialog").css("padding-left"));
								$(".navbar-right #dialog .badge").addClass("hidden");
							}
							else $(".navbar-right #dialog .badge").html($newBadge);
						}
					}
				);
				
				
				/*$newBadge = $(".navbar-right #dialog .badge").html() - $(this).attr("nb_unread");
				
				if ($newBadge <= 0) {
					$(".navbar-right #dialog").css("padding-right",$(".navbar-right #dialog").css("padding-left"));
					$(".navbar-right #dialog .badge").addClass("hidden");
				}
				else $(".navbar-right #dialog .badge").html($newBadge);*/
			}
			
			// Nouvelle discussion et pas de dest dans l'url
			if ($(this).attr("id") == "newDisc") {

				$("#main-col #mainDestBar").addClass("hidden");
				$("#main-col #destBar").removeClass("hidden");
				
				// On vide la message-list
				$("#main-col #message-list").empty();
				$("#main-col #mainDestBar").addClass("hidden");
				$("#main-col #destBar").removeClass("hidden");
				
				// On break
				return;
			}
		
			// On affiche la mainDestBar et on masque l'input
			$("#main-col #mainDestBar").removeClass("hidden");
			$("#main-col #destBar").addClass("hidden");
			
			// On actualise la mainDestbar
			$("#main-col #mainDestBar #pseudo h5").empty().append($(this).find(".info-block .pseudo").html());
			updateMainDestAvatar();
			
			// On init le messageInput
			$('#messageInput').prop('disabled', false).empty();
			
			// On charge les messages de la discussion
			discId = $(this).attr("id").substr(4);
			if ($(this).attr("id") != "newDisc") {
				load_messages(6, discId, "desc");
			}
			
		});
		
		// Affichage dynamique de la scrollbar
		$("#discussion-list").mouseenter(function() {
			realSize = parseInt($("#discussion-list .list-group-item:not(.hidden)").first().css("height"),10) * $("#discussion-list .list-group-item").length;
			if(realSize > parseInt($("#discussion-list").css("height"),10)) {
				$(this).css("scrollbar-width","thin");
			}
		});
		$("#discussion-list").mouseleave(function() {
			$(this).css("scrollbar-width","none");
		});


		// Nouvelle discussion
		$("#newMsgBtn").click(function() {
			// On déselectionne la discussion d'avant
			$("#discussion-list .active").removeClass("active");
			// On affiche la nouvelle discussion
			$("#discussion-list #newDisc").removeClass("hidden").addClass("active");
			// On masque la mainDestBar
			$("#main-col #mainDestBar").addClass("hidden");
			// On affiche la selectBar et on la vide
			$('#destInput').flexdatalist("value", "");
			$("#main-col #destBar").removeClass("hidden");
			$("#main-col #destBar #destInput-flexdatalist").focus();
			// On vide la message-list
			$("#main-col #message-list").empty();
		});
		
		// Remove la nouvelle discussion
		$("#discussion-list #newDisc #removeBtn").click(function() {
			// On reset la newDisc
			$("#discussion-list #newDisc .info-block .pseudo").empty().append("Nouveau message");
			$("#discussion-list #newDisc .info-block .miniMsg").addClass("hidden").find("span").empty();
			// On hide la nouvelle discussion
			$("#discussion-list #newDisc").addClass("hidden");
			// On hide la selectBar
			$("#main-col #destBar").addClass("hidden");
			$('#destInput').flexdatalist("value", "");
		});
		
		
		
		// On charge les discussions
		loadDiscussions();
		refresh();
		
		
		
		// *********** DESTBAR == FLEXDATALIST des membres
		// FLEXDATALIST MEMBRES
		$('#destInput').flexdatalist({
				minLength: 2,
				selectionRequired: true,
				removeOnBackspace: false,   // ne marche pas
				url: "<?php echo site_url('ajax_members/get_members'); ?>",
				searchIn: ["pseudo","prenom","nom"],
				visibleProperties: ["thumb","pseudo","prenom","nom","mainInstruName"],
				searchByWord: true,
				searchContain: true,
				multiple: true,
				valueProperty: ['id','pseudo'],	// on envoie l'attribut 'id' quand on appelle la méthode val()
				params: { "memberId": $("#memberLogin").attr("idKey") }
		});
		
		
		// FLEXDATALIST :: CHANGE
		$('#destInput').on('change:flexdatalist.data', function(event, set, options) {
			//console.log("destInput :: change : "+JSON.parse(set.value));
			//$value = JSON.parse(set.value);
			selectDiscussion($('#destInput').flexdatalist("value"));
		});

		
		// FLEXDATALIST :: LOAD DATA
		$('#destInput').on('before:flexdatalist.data', function() {			
			// On change le curseur
			document.body.style.cursor = 'wait';
		});
		
		$('#destInput').on('after:flexdatalist.data', function() {
			//console.log("******after:flexdatalist.data");
			// On change le curseur
			document.body.style.cursor = 'default';
		});
		
		$('#destInput').on('before:flexdatalist.search', function() {
			//console.log("before:flexdatalist.search");
		});
		
		
		// ********** TOOLTIP ***************
		$('body').tooltip({
			selector: '[rel="tooltip"]'
		});
		
		
	});
	
	// Génère des pseudo aléatoire
	function makeid(length) {
		var result           = '';
		var characters       = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
		var charactersLength = characters.length;
		for ( var i = 0; i < length; i++ ) {
		result += characters.charAt(Math.floor(Math.random() * charactersLength));
		}
		return result;
	}
	
	
	function refresh() {
		//console.log(":: refresh()");
		// On ajuste la taille de la liste de discussion
		panelHeight = parseInt($("#left-col").css("height"),10) - parseInt($("#left-header").css("height"),10) - 2;
		$("#discussion-list").css("height",panelHeight+"px").removeClass("hidden");
		
		// On ajuste la taille du main panel
		mainPanelHeight = parseInt($("#main-col").css("height"),10) - parseInt($("#main-header").css("height"),10) - 2 - parseInt($("#main-footer").css("height"),10);
		$("#main-panel").css("height",mainPanelHeight+"px");
		
	}
	
	
	// ****************** UI des DISCUSSIONS
	//*****************************
	
	// Load des discussions
	function loadDiscussions() {
		
		//console.log("*********** loadDiscussions()");
		
		// On change le curseur
		document.body.style.cursor = 'wait';
		
		// Requète ajax au serveur
		$.post("<?php echo site_url('ajax_discussion/get_discussions'); ?>",
	
			{
				'memberId': $("#memberLogin").attr("idKey")
			},
		
			function (return_data) {
				
				$obj = JSON.parse(return_data);
				
				// On change le curseur
				document.body.style.cursor = 'default';
				//console.log(return_data);
				
				// Modal
				if ($obj['state'] == 1) {
					// Succés
					// On parcourt chaque discussion
					$obj['data'].forEach(function(discItem) {
						
						// On clone une discussion et l'init
						$newDisc = $("#discussion-list #newDisc").clone(true);
						$newDisc.attr("id","disc"+discItem.discussionId);
						
						// Nombre de message non-lus de la discussion
						$newDisc.attr("nb_unread",discItem.nb_unread)
						if (discItem.nb_unread > 0) $newDisc.addClass("warning");
						
						// On récupère les pseudo des destinataires et leur ID
						$dest = "";
						$ids = "";
						discItem.destList.forEach(function(item, index, arr) {
							if (index == 0) {
								$dest += item.pseudo;
								$ids += item.membresId;
							}
							else {
								$dest += ", "+item.pseudo;
								$ids += " "+item.membresId;
							}
						});
						$newDisc.find(".info-block .pseudo").empty().append($dest).attr("ids",$ids);
						
						// On récupère le dernier message et on le truncate + ajout de la date
						$vous = discItem.lastMessage["membresId"] == $("#memberLogin").attr("idKey") ? "Vous : " : "";
						$lastMsg = discItem.lastMessage == null ? "Pas encore de message" : $vous+discItem.lastMessage["text"];
						$newDisc.find(".info-block .miniMsg span:first").empty().append($lastMsg);
						$newDisc.find(".info-block .miniMsg span.date").append(" · "+discItem.lastMessage["flexDate"]);
						
						// On actualise le comportement des boutons
						$newDisc.find("#removeBtn").addClass("hidden");
						$newDisc.find("#actionBtn").removeClass("hidden");
						
						$newDisc.find(".info-block .miniMsg").removeClass("hidden");
						
						// On affiche la discussion loadée
						$("#discussion-list").append($newDisc);
						$newDisc.removeClass("hidden");
						

						// ******** AVATAR
						// On rajoute les images manquantes si besoin
						if (discItem.destList.length >= 3) {
							avatar = $("#discussion-list #disc"+discItem.discussionId+" #discAvatar");
							img = avatar.children("img").removeClass("img-circle").css("position","absolute");
							img.addClass("leftHalfCircle").css("left","-10px");
							img.clone().attr("id","avatar2").addClass("upQuarterCircle").removeClass("leftHalfCircle").css({"left":"10px","top":"-10px"}).appendTo(avatar);
							img.clone().attr("id","avatar3").addClass("downQuarterCircle").removeClass("leftHalfCircle").css({"left":"10px","top":"10px"}).appendTo(avatar);
						}
						else if (discItem.destList.length == 2) {
							avatar = $("#discussion-list #disc"+discItem.discussionId+" #discAvatar");
							img = avatar.children("img").removeClass("img-circle").css("position","absolute");
							img.addClass("leftHalfCircle").css("left","-10px");
							img.clone().attr("id","avatar2").addClass("rightHalfCircle").removeClass("leftHalfCircle").css("left","10px").appendTo(avatar);
						}
						
						function testInt(string) {
							var match = string.match(/[0-9]$/);
							return match ? match[0] : '';
						}
						
						// On récupère les avatars
						discItem.destList.forEach(function(item, index, arr) {
							target = "avatar"+(index+1);
							multiple = discItem.destList.length > 1;
							// Récupère l'AVATAR en cours
							$.ajax({
								url:'<?php echo base_url("images/avatar"); ?>'+'/'+item.membresId+'.png',
								type:'HEAD',
								context:{target:target, multiple: multiple, avatarId:item.membresId},
								success: function() {
									$("#discussion-list #disc"+discItem.discussionId+" #discAvatar img#"+this.target).prop("src",'<?php echo base_url("images/avatar"); ?>'+'/'+item.membresId+'.png');
									// hidden avatar complet si plusieurs destinataires
									if (testInt(this.target) && this.multiple) {
										var hiddenAvatar = "<img id='hiddenAvatar' memberId='"+this.avatarId+"' class='img-circle hidden' src='<?php echo base_url("images/avatar"); ?>"+'/'+item.membresId+".png' width='38' height='38'>";
										$("#discussion-list #disc"+discItem.discussionId+" #discAvatar").append(hiddenAvatar);
									}
									// On actualise la mainDestBar si besoin
									if (discItem.discussionId == $("#discussion-list .list-group-item.active").attr("id").substr(4)) updateMainDestAvatar();
								},
								error: function() {
									$("#discussion-list #"+discItem.discussionId+" #discAvatar img#"+this.target).prop("src",'<?php echo base_url("images/icons/avatar1.png") ?>');
									var hiddenAvatar = "<img id='hiddenAvatar' memberId='"+this.avatarId+"' class='img-circle hidden' src='<?php echo base_url("images/icons/avatar1.png") ?>' width='38' height='38'>";
									$("#discussion-list #disc"+discItem.discussionId+" #discAvatar").append(hiddenAvatar);
								}
							});
						});
					});
					
					// On regarde s'il y a un destinataire défini via l'url
					<?php if (isset($dest_item)) : ?>
						$("#newMsgBtn").click();
						$('#destInput').flexdatalist("value", <?php echo $dest_item->id ?>);
						var dest = [{"id":"<?php echo $dest_item->id ?>","pseudo":"<?php echo $dest_item->pseudo ?>"}];
						selectDiscussion(dest);
					<?php else: ?>
						// On select la discussion avec le post le plus récent
						if ($obj['data'].length > 0) {
							$("#discussion-list .list-group-item:not(.hidden)").first().click();
						}
					
					<?php endif; ?>
						
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
	
	
	// Select la discussion
	function selectDiscussion(memberArray) {
		
		//console.log("selectDiscussion : "+memberArray[0]["id"]);
		
		// On cherche si la discussion n'existe pas déjà en parcourant toutes les discussion
		existantDisc = null;
		$("#discussion-list .list-group-item:not(#newDisc)").each(function() {
			
			// On récupère les destinataires
			var $ids = $(this).find(".info-block .pseudo").attr("ids");
			var destArray = $ids.split(" ");
			
			// on break si même pas le même nombre de dest
			nbDest = ($ids.match(/ /g)||[]).length +1;
			if (nbDest != memberArray.length) return;
			
			// On parcours les dest
			var same = true;
			var i = 0;
			while (same && i < memberArray.length) {
				same = destArray.includes(memberArray[i]["id"]);
				i++;
			}
			// La discussion existe déjà
			if (same && i == nbDest) {
				existantDisc = $(this).attr("id");
				return;
			}
		});

		// Si la discussion est nouvelle
		if (existantDisc == null) {
			
			//console.log("new Discussion");
			
			// On réinit la new discussion au cas où
			$("#discussion-list .list-group-item.active").removeClass("active");
			$("#discussion-list #newDisc").addClass("active");
			$("#discussion-list #newDisc").removeClass("hidden");
			
			// On vide la message-list
			$("#main-col #message-list").empty();
			
			// On init le discAvatar
			$("#discussion-list #newDisc #discAvatar img:not(:first)").remove();
			$("#discussion-list #newDisc #discAvatar img:first").removeClass("leftHalfCircle").css("left","0px").addClass("img-circle");
		
			// Si la discussion n'est pas trouvé, on update l'affichage de la discussion (nouveau message...)
			if (memberArray.length >= 1) {
				
				// On actualise l'item actif (newDisc)
				$("#discussion-list .list-group-item.active").removeClass("active");
				$("#discussion-list #newDisc").removeClass("hidden");
				$("#discussion-list #newDisc").addClass("active");
				
				// On récupère les pseudo
				var pseudoStr = "";
				for (i = 0; i < memberArray.length; i++) {
					if (i == 0) pseudoStr = memberArray[i]["pseudo"];
					else pseudoStr += ", " + memberArray[i]["pseudo"];
				}
				
				// On rempli l'info-block
				$("#discussion-list #newDisc .info-block .pseudo").empty().append(pseudoStr);
				$("#discussion-list #newDisc .info-block .miniMsg span.truncate").empty().append("Nouveau message");
				$("#discussion-list #newDisc .info-block .miniMsg span.date").empty();
				$("#discussion-list #newDisc .info-block .miniMsg").removeClass("hidden");
	
				
				// ******** AVATAR
				// On rajoute les images manquantes si besoin
				if (memberArray.length >= 3) {
					avatar = $("#discussion-list #newDisc #discAvatar");
					img = avatar.children("img").removeClass("img-circle").css("position","absolute");
					img.addClass("leftHalfCircle").css("left","-10px");
					img.clone().attr("id","avatar2").addClass("upQuarterCircle").removeClass("leftHalfCircle").css({"left":"10px","top":"-10px"}).appendTo(avatar);
					img.clone().attr("id","avatar3").addClass("downQuarterCircle").removeClass("leftHalfCircle").css({"left":"10px","top":"10px"}).appendTo(avatar);
				}
				else if (memberArray.length == 2) {
					avatar = $("#discussion-list #newDisc #discAvatar");
					img = avatar.children("img").removeClass("img-circle").css("position","absolute");
					img.addClass("leftHalfCircle").css("left","-10px");
					img.clone().attr("id","avatar2").addClass("rightHalfCircle").removeClass("leftHalfCircle").css("left","10px").appendTo(avatar);
				}
				
				function testInt(string) {
					var match = string.match(/[0-9]$/);
					return match ? match[0] : '';
				}
				
				// On récupère les avatars
				memberArray.forEach(function(item, index, arr) {
					target = "avatar"+(index+1);
					multiple = memberArray.length > 1;
					// Récupère l'AVATAR en cours
					$.ajax({
						url:'<?php echo base_url("images/avatar"); ?>'+'/'+item.id+'.png',
						type:'HEAD',
						context:{target:target, multiple: multiple, avatarId:item.id},
						success: function() {
							$("#discussion-list #newDisc #discAvatar img#"+this.target).prop("src",'<?php echo base_url("images/avatar"); ?>'+'/'+item.id+'.png');
							// hidden avatar complet si plusieurs destinataires
							if (testInt(this.target) && this.multiple) {
								var hiddenAvatar = "<img id='hiddenAvatar' memberId='"+this.avatarId+"' class='img-circle hidden' src='<?php echo base_url("images/avatar"); ?>"+'/'+item.id+".png' width='38' height='38'>";
								$("#discussion-list #newDisc #discAvatar").append(hiddenAvatar);
							}
						},
						error: function() {
							$("#discussion-list #newDisc #discAvatar img#"+this.target).prop("src",'<?php echo base_url("images/icons/avatar1.png") ?>');
							var hiddenAvatar = "<img id='hiddenAvatar' memberId='"+this.avatarId+"' class='img-circle hidden' src='<?php echo base_url("images/icons/avatar1.png") ?>' width='38' height='38'>";
							$("#discussion-list #newDisc #discAvatar").append(hiddenAvatar);
						}
					});
				});
				
			}

			// Si personne dans la destBar
			else {
				$("#discussion-list #newDisc .info-block .pseudo").empty().append("Nouveau message");
				$("#discussion-list #newDisc .info-block .miniMsg").addClass("hidden").find("span").empty();
				$("#discussion-list #newDisc #discAvatar img").prop("src",'<?php echo base_url("images/icons/avatar1.png") ?>');
			}
		}
		
		// La discussion existe déjà, on select la discussion et on update l'affichage de la message-list
		else {
			
			// On masque l'item de nouvelle discussion
			$("#discussion-list #newDisc").addClass("hidden");
			
			// On actualise l'item actif
			$("#discussion-list .list-group-item.active").removeClass("active");
			$("#discussion-list #"+existantDisc+"").addClass("active");
			
			// On charge les messages
			load_messages(6, existantDisc.substr(4), "desc");
		}
	}
	
	// Actualise les avatar de la mainDestBar en fonction de la discussion active
	function updateMainDestAvatar() {
		// Avatar
		destAvatar = $("#discussion-list .list-group-item.active").find("#discAvatar").clone().attr("id","destAvatar").css({"position":"relative","width":"40px","height":"40px"});
		// Pour chaque image d'avatar
		destAvatar.find("img:not(.hidden)").each(function() {
			$(this).attr({width:"40",height:"40"});	// on redimensionne
			// on replace
			if ($(this).hasClass("leftHalfCircle")) $(this).css("left","-8px");
			else if ($(this).hasClass("rightHalfCircle")) $(this).css("left","8px");
			else if ($(this).hasClass("upQuarterCircle")) $(this).css({"left":"8px","top":"-8px"});
			else if ($(this).hasClass("downQuarterCircle")) $(this).css({"left":"8px","top":"8px"});
			// Clippath
			var className = $(this).attr("class");	// marche car qu'une classe
			if (className != "img-circle") {
				$(this).removeClass(className);
				$(this).addClass(className+"2");
			}
		});
		$("#main-col #mainDestBar #destAvatar").replaceWith(destAvatar);
	}
	
</script>



<script type="text/javascript">

	$(function() {
		$.fn.setCursorPosition = function(pos) {
			if (this.setSelectionRange) {
				this.setSelectionRange(pos, pos);
			}
			else if (this.createTextRange) {
				var range = this.createTextRange();
				range.collapse(true);
				if(pos < 0) {
					pos = $(this).val().length + pos;
				}
				range.moveEnd('character', pos);
				range.moveStart('character', pos);
				range.select();
			}
		}
	});
	
	$(function() {
		$.fn.getCursorPosition = function() {
			var el = $(this).get(0);
			var pos = 0;
			if('selectionStart' in el) {
				pos = el.selectionStart;
			} else if('selection' in document) {
				el.focus();
				var Sel = document.selection.createRange();
				var SelLength = document.selection.createRange().text.length;
				Sel.moveStart('character', -el.value.length);
				pos = Sel.text.length - SelLength;
			}
			return pos;
		}
	});


	$(function() {

		/************** SEND MESSAGE UI  ****************/
		/************************************************/
		autosize($('#messageInput'));
		
		// Pour gérer les event spéciaux du textarea (enter => send_message ou update_message, ctrl+enter => saut de ligne, escape => empty textarea)
		$('#messageInput').keydown(function(evt) {
			
			//console.log("messageInput.keydown : shift="+evt.shiftKey+"  alt="+evt.altKey);
			
			/*if (evt.key === "Escape") { // escape key maps to keycode `27`
				cancel_edit();
			}*/

			// Si "enter"
			/*else*/ 
			if (evt.keyCode == 13 && !evt.shiftKey) {
				
				// Si le message n'est pas vide
				if (this.value.length > 0) {
					
					evt.preventDefault();
					evt.stopPropagation();
					
					// Si pas de destinataire ou quit
					if ($("#discussion-list .active").length == 0) return;
					
					//console.log($(this));
					
					// On disable l'input
					$(this).prop('disabled', true);

					// Nouvelle discussion
					if ($("#discussion-list .active").attr("id") == "newDisc") {
						// On récupère le.s destinataire.s
						$dest = $('#destInput').val() ? JSON.parse($('#destInput').val()) : null;
						if ($dest && $dest.length > 0) {
							send_message(this.value, "2", $dest);
						}
					}
					// Discussion existante
					else if ($("#discussion-list .active").attr("id") != "undefined") {
						// On récupère son id
						$idDisc = $("#discussion-list .active").attr("id").substring(4);
						//console.log($idDisc);
						send_message(this.value, "6", $idDisc);
					}
					
					else return;
					
				}
				else return false;
			}
		});
		
	});
	
	
	/************** SEND MESSAGE   ****************/
	function send_message($message, $targetTag, $targetId) {

		console.log("send_message : { \""+$message+"\" ; \""+$targetTag+"\" ; "+JSON.stringify($targetId)+" }");

		// On change le curseur
		document.body.style.cursor = 'wait';
		
		// Requète ajax au serveur
		$.post("<?php echo site_url('ajax_discussion/send_message'); ?>",
	
			{
				'memberId': $("#memberLogin").attr("idKey"),
				'message': $message,
				'targetTag': $targetTag,					// 2 (nouvelle disc) ou 6 (disc existante)
				'targetId': JSON.stringify($targetId)		// tableau d'id ou discussionId
			},
		
			function (return_data) {
				
				$obj = JSON.parse(return_data);
				
				// On change le curseur
				document.body.style.cursor = 'default';
				
				//console.log(return_data);
				
				// Modal
				if ($obj['state'] == 1) {
					// Succés
					
					// On regarde s'il s'agit d'une nouvelle discussion (targetId pas présent dans la liste de discussion)
					if ($("#discussion-list .list-group-item#disc"+$obj['data']['targetId']).length == 0) {
					
						// On créé l'item correspondant à la nouvelle discussion
						$newDisc = $("#discussion-list #newDisc").clone(true);
						$("#discussion-list #newDisc").attr("id",("disc"+$obj['data']['targetId']));
						
						// On reset la newDisc
						$newDisc.find(".info-block .pseudo").empty().append("Nouveau message");
						$newDisc.find(".info-block .miniMsg").addClass("hidden").find("span").empty();
						$newDisc.addClass("hidden").removeClass("active");
						$("#discussion-list").prepend($newDisc);
						
					}
					
					// On set le nouveau message en bas de liste
					value = {
						id : $obj['data']['id'],
						memberId : $("#memberLogin").attr("idKey"),
						pseudo : $("#memberLogin").attr("value"),
						text : $obj['data']['text'],
						newThread : $obj['data']['newThread'],
						createdReadable : $obj['data']['createdReadable'],
						flexDate : $obj['data']['flexDate'],
						flexMiniDate : $obj['data']['flexMiniDate'],
						targetId : $obj['data']['targetId']
					}
					set_message(value, true, "after");
					
					// On descend l'ascenseur
					$("#main-panel").animate({ scrollTop: $('#main-panel').prop("scrollHeight")}, 1000);
					
					// On reset le messageInput
					$('#messageInput').prop('disabled', false);
					$('#messageInput').val("");
					autosize.update($('#messageInput'));
					
					// On actualise la discussion
					$vous = value.memberId == $("#memberLogin").attr("idKey") ? "Vous : " : "";
					$("#discussion-list .list-group-item#disc"+$obj['data']['targetId']+" .info-block .miniMsg span.truncate").empty().append($vous+$obj['data']['text']);
					$("#discussion-list .list-group-item#disc"+$obj['data']['targetId']+" .info-block .miniMsg span.date").empty().append($obj['data']['flexDate']);
					
					// On place la discussion en haut de liste
					temp = $("#discussion-list .list-group-item#disc"+$obj['data']['targetId']).detach();
					$("#discussion-list #newDisc").after(temp);
					
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
	
	/************** LOAD MESSAGES   ****************/
	function load_messages($targetTag, $targetId, $order) {
		
		//console.log("load_messages : "+$targetTag+" "+$targetId+" "+$order);
		
		// On change le curseur
		document.body.style.cursor = 'wait';
		
		// On vide la message-list
		$("#main-panel #message-list").empty();
		
		// Requète ajax au serveur
		$.post("<?php echo site_url('ajax_discussion/get_messages'); ?>",
	
			{
				'targetTag': $targetTag,
				'targetId': $targetId,
				'order': $order,
				'memberId': $("#memberLogin").attr("idKey")		// permet d'actualiser le read_at de la discussion
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
						// On insère le message dans l'UI (en remontant le fil)
						set_message($obj['data'][i], true, "before");
					};
					
					// On cale la scrollbar en bas
					$("#main-panel").scrollTop($("#main-panel").prop("scrollHeight"));
				}
			}
		);
	}
	
	
	// *********** SET MESSAGE dans l'UI **********
	function set_message(value, visible, direction) {
		
		//console.log("***** set_message ****  "+JSON.stringify(value)+"    "+visible);
	
		// On vérifie que la bonne discussion est select
		if (value.targetId != $("#discussion-list .list-group-item.active").attr("id").substr(4)) return;
		
		// On définis s'il y'a plus d'un correspondant dans la discussion
		var multiple = $("#discussion-list .active img.hidden").length > 0;
		
		// On créé le message
		var hidden = visible ? "" : " hidden";
		var msgRow = jQuery("<div class='messageRow"+hidden+"'></div>");
		var msgBlock = jQuery("<div class='messageBlock' memberId='"+value.memberId+"' data-toggle='tooltip' data-placement='left' title='"+value.flexDate+"'></div>");
		
		
		// Position du message et du tooltip
		if (value.memberId == $("#memberLogin").attr("idKey")) {
			msgRow.addClass("right");
			msgBlock.attr("data-placement","right");
		}
		
		// On récupère les infos du message précédent
		precDirection = direction == "before" ? "first" : "last";
		var precPos = $("#message-list .messageRow:"+precDirection+"-child").hasClass("right") ? "right" : "left";
		var precId = $("#message-list .messageRow:"+precDirection+"-child .messageBlock").attr("memberId");
		if ($("#message-list .messageRow:"+precDirection+"-child").length == 0) precPos = "undefined";
		var same = (precPos == "right" && value.memberId == $("#memberLogin").attr("idKey") || (precPos == "left" && value.memberId == precId )  );

		
		// On gère les bordures...
		if ( same && (  (direction == "before" && precPos != "undefined") || (direction == "after" && !value.newThread)  ) ) {
			borderPos = direction == "before" ? "bottom" : "top";
			borderInverse = direction == "before" ? "top" : "bottom";
			msgBlock.css("border-"+borderPos+"-"+precPos+"-radius","5px");
			$("#message-list .messageRow:"+precDirection+"-child").find(".messageBlock").css("border-"+borderInverse+"-"+precPos+"-radius","5px");
		}
		// ... et le positionnement (on padde quand nouvel interlocuteur dans la même disc)
		if ( !same && !$("#message-list .messageRow:"+precDirection+"-child").hasClass("date") && precPos != "undefined" && direction != "after") {
			msgRow.css("padding-bottom","12px");
		}
		
		/*console.log("precId : "+precId);
		console.log("multiple : "+multiple);*/
		
		// On gère l'avatar si message d'un correspondant à gauche
		if (value.memberId != $("#memberLogin").attr("idKey") && (value.memberId != precId || (typeof(precId) == "undefined"))) {
			//console.log("NEW AVATAR !!"+multiple);
			// On récupère l'avatar dans la discussion
			if (multiple) {
				var img = $("#discussion-list .active #hiddenAvatar[memberId="+value.memberId+"]").clone().attr({"width":"28","height":"28"}).removeClass("hidden");
			}
			else var img = $("#discussion-list .active #avatar1").clone().attr({"width":"28","height":"28"});
			// On ajoute le tooltip
			img.attr("data-toggle","tooltip").attr("data-placement","left").attr("title",value.pseudo);
			img.tooltip();
			msgRow.append(img);
		}
		// Même destinataire, il faut juste padder
		else if (value.memberId != $("#memberLogin").attr("idKey")) msgRow.css("padding-left","40px");
		
		
		// On gère les messages supprimés
		if (value.updatedReadable !== undefined && value.deletedReadable == value.updatedReadable) {
			var msg = jQuery("<div class='message' id="+value.id+"><span class='content soften'><em>L'auteur a supprimé le contenu de ce message.</em></span></div>");
		}
		else var msg = jQuery("<div class='message' id="+value.id+"><span class='content'>"+getCleanText(value.text)+"</span></div>");
		msgBlock.append(msg);
		
		// On créé le "..." d'action
		// S'il ne s'agit pas d'un message supprimé
		/*if (value.deletedReadable != value.updatedReadable) {
			// Si l'auteur du message est le membre connecté
			if (value.memberId == $("#memberLogin").attr("idKey")) {
				// On récupère le menu d'options eton l'ajoute au block
				option = get_options(value.id, msg);								
				msgRow.append(option);
			}
		}*/
		
		// On ajoute la ligne de message au block
		msgRow.append(msgBlock);
		
		// On affiche le timeAgo normal
		if (value.created_at == value.updated_at || value.updated_at === undefined) {
			//var msgSub = jQuery("<div class='messageSub small'><span class='softer' data-toggle='tooltip' data-placement='right' title='"+value.createdReadable+"'>"+value.timeAgo+"</span></div>");
		}
		// Modifié il y a ...
		else if (value.deleted_at != value.updated_at) {
			var modStr = value.timeAgo.toLowerCase() == "à l'instant" ? "Modifié à l'instant" : "Modifié il y a "+value.timeAgo;
			//var msgSub = jQuery("<div class='messageSub small'><span class='softer' data-toggle='tooltip' data-placement='right' title='"+value.updatedReadable+"'>"+modStr+"</span></div>");
		}
		// Supprimé il y a ...
		else {
			var modStr = value.timeAgo.toLowerCase() == "à l'instant" ? "Supprimé à l'instant" : "Supprimé il y a "+value.timeAgo;
			//var msgSub = jQuery("<div class='messageSub small'><span class='softer' data-toggle='tooltip' data-placement='right' title='"+value.updatedReadable+"'>"+modStr+"</span></div>");
		}
		//msgBlock.append(msgSub);
	
		// On active le tooltip
		msgRow.find(".messageBlock").tooltip();
		
		//console.log("msgRow : "+msgRow.html());
		
		// Si besoin, on créé le bloc de la date
		var dateDiv;
		if (value.newThread) var dateDiv = jQuery("<div class='messageRow date ultraSoft'>"+value.flexMiniDate+"</div>");
		
		// On insère le message (+éventuellement la date)
		if (direction == "before") {
			$("#message-list").prepend(msgRow);
			$("#message-list").prepend(dateDiv);
		}
		else {
			$("#message-list").append(dateDiv);
			$("#message-list").append(msgRow);
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

	// Permet de factoriser la création du dropdown lié à un message écrit par le user
	function get_options(messageId, $msg) {
		
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
	
	
	
	/************* UPDATE MESSAGE ***************/

	// On rentre dans le mode d'édition d'un message
	function edit_message($messageId) {
		
		// On récupère le messageRow
		var msgBlock = $("#messageList .message[id='"+$messageId+"']").parents(".messageRow");
		
		// On masque le msgRow
		msgBlock.addClass("hidden");
				
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
			$("#messageList").find("#updateForm").fadeOut("fast", function() { 
				// On réaffiche le message d'origine
				$("#messageList").find(".messageRow.hidden").css("display","none").removeClass("hidden").fadeIn("fast");
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
		$.post("<?php echo site_url('ajax/update_message'); ?>",
	
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
					$("#messageList").find(".messageRow.hidden").find(".message span.content").empty().html(getCleanText($message));
					// On update le msgSub
					$("#messageList").find(".messageRow.hidden").find(".messageSub span.softer").empty().html("Modifié à l'instant");
					
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
			
			
			
	
	/************* DELETE MESSAGE ***************/
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
		$.post("<?php echo site_url('ajax/delete_message'); ?>",
	
			{'messageId':$messageId},
		
			function (return_data) {
				
				$obj = JSON.parse(return_data);
				
				// On change le curseur
				document.body.style.cursor = 'default';
				
				// Modal
				if ($obj['state'] == 1) {
					// Succés
					$("#modal_msg").modal('hide');
					
					// On supprime le messageRow concerné
					//$("#messageList").find(".message[id="+$messageId+"]").parents(".messageRow").fadeOut("fast", function() { $(this).remove(); });
					$("#messageList").find(".message[id="+$messageId+"]").find("span.content").empty().addClass("soften").html("<em>L'auteur a supprimé le contenu de ce message.</em>");
					// On update le msgSub
					$("#messageList").find(".message[id="+$messageId+"]").parents(".messageRow").find(".messageSub span.softer").empty().html("Supprimé à l'instant");

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
	
 </script>



<div id="discussions">

	<!-- Block principal !-->
	<div class="panel panel-default">
	
		<!-- Block de gauche => title + liste discussion !-->
		<div id="left-col" class="item panel panel-default nomarginbottom">

			<!-- Header => avatar + titre + options !-->
			<div id="left-header" class="panel-heading">
				<!-- Avatar !-->
				<div id="avatar">
					<img class='img-circle' src='<?php echo base_url("images/icons/avatar1.png"); ?>' width="38" height="38">
				</div>
				
				<!-- Titre Discussions !-->
				<div id="title" class="bottom-align-text">
					<h4>Discussions</h4>
				</div>
				
				<!-- Icon new msg !-->
				<button id="newMsgBtn" class="btn btn-default btn-xs">
					<img id="newMsgIcon" src='<?php echo base_url("images/icons/new_msg.png"); ?>' width="20" height="20">
				</button>
				
			</div>
			
			<!-- List discussions !-->
			<div id="discussion-list" class="list-group">
			
				<!-- Discussion empty hidden !-->
				<div id="newDisc" class="list-group-item hidden">
				
					<!-- Avatar !-->
					<div id="discAvatar">
						<img id="avatar1" class='img-circle' src='<?php echo base_url("images/icons/avatar1.png"); ?>' width="50" height="50">
					</div>
					
					<!-- Infos !-->
					<div class="info-block">
						<div class="pseudo truncate">Nouveau message</div>
						<div class="miniMsg softest hidden small"><span class="truncate"></span><span class="date"></span></div>
					</div>
					
					<!-- Remove Button !-->
					<button id="removeBtn" class="btn btn-default btn-xs"><i class="glyphicon glyphicon-remove"></i></button>
					<!-- Action Button !-->
					<button id="actionBtn" class="btn btn-xs btn-default panel-transparent no-border dropdown-toggle hidden" data-toggle="dropdown" type="button" ><i class="glyphicon glyphicon-option-horizontal"></i></button>
					
				</div>
				
			</div>
			
		</div>	<!-- end left-col !-->
		
		<!-- Block central => liste correspondants + messages !-->
		<div id="main-col" class="item panel panel-default nomarginbottom">
		
			<!-- Header => avatar(s) + pseudo(s) !-->
			<div id="main-header" class="panel-default">

				<!-- La barre du/des correspondant !-->
				<div id="mainDestBar" class="panel-heading hidden">
					<!-- Avatar(s) !-->
					<div id="destAvatar">
						<img class='img-circle' src='<?php echo base_url("images/icons/avatar1.png"); ?>' width="38" height="38">
					</div>
					
					<!-- Pseudo(s) !-->
					<div id="pseudo">
						<h5>Pseudo</h5>
					</div>
				</div>
				
				
				<!-- La barre d'input de destinataires !-->
				<div id="destBar" class="panel-heading alternateBis hidden">
					<input id="destInput" class="flexdatalist" type="input" name="destInput" placeholder="Saisissez le nom ou le pseudo d'une personne">
				</div>
				
			</div>
			
			
			
			<!-- Main-panel !-->
			<div id="main-panel" class="">
			
				<div id="message-list">
				</div>
			</div>
			
			
			<!-- Footer => textarea !-->
			<div id="main-footer" class="panel-footer">
			
				<!-- Textarea autosize !-->
				<textarea id="messageInput" class="form-control autosize" name="message" placeholder="Votre message..." rows='1' style="resize:none; font-size: 100%;"></textarea>

			</div>
		</div>
		
	</div>
</div>


<!-- Dialogue box de resultat !-->
<div id="modal_msg" class="modal fade" role="dialog" data-keyboard="true" data-backdrop="static">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header lead"></div>
			<div class="modal-body"></div>
			<div class="modal-footer"></div>
		</div>
	</div>
</div>


<!-- Path de clipping pour les avatars !-->
<svg width="0" height="0">
	<defs>
		<clipPath id="leftHalfCircle">
			<path d="M34,0 a25,25 0 1,0 0,50"/>
		</clipPath>
		<clipPath id="rightHalfCircle">
			<path d="M15,0 a25,25 0 1,1 0,50"/>
		</clipPath>
		<clipPath id="upQuarterCircle">
			<path d="M15,34 L15,10  a25,25 1 0,1 25,24 z"/>
		</clipPath>
		<clipPath id="downQuarterCircle">
			<path d="M15,15 L40,15  a25,25 1 0,1 -25,25 z"/>
		</clipPath>
		
		<clipPath id="leftHalfCircle2">
			<path d="M27,0 a20,20 0 1,0 0,40"/>
		</clipPath>
		<clipPath id="rightHalfCircle2">
			<path d="M12,0 a20,20 0 1,1 0,40"/>
		</clipPath>
		<clipPath id="upQuarterCircle2">
			<path d="M12,27 L12,8  a20,20 1 0,1 20,19 z"/>
		</clipPath>
		<clipPath id="downQuarterCircle2">
			<path d="M12,12 L32,12  a20,20 1 0,1 -20,20 z"/>
		</clipPath>
	</defs>
</svg>
