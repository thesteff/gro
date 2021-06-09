<!-- autoresize texarea !-->
<script type="text/javascript" src="<?php echo base_url();?>/ressources/script/autosize.js"></script>




<?php 
	// ******************************* SUPER ADMIN *****************************
	if ($isSuperAdmin == "1" ): 
?>
<script type="text/javascript">

	$(function() {
		
		// ****** NEWS MODALS ********
		$("[id$='NewsModal'").on("show.bs.modal", function(e) {
			var link = $(e.relatedTarget);
			$(this).find(".modal-body").load(link.attr("href"));
		});
		
		
		// Hack pour faire marcher le CKEditor dans une modal
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

	
	
	/********** DELETE NEWS ***************/
	function popup_delete_news($newsId) {
		
		// On récupère les infos de la news
		$newsTitle = $(".news[news_Id="+$newsId+"] .panel-heading h4 span").html();
		
		$text = "Etes-vous sûr de vouloir supprimer la news<br> <b>"+$newsTitle+"</b> ?";
		$confirm = "<div class='modal-footer'>";
			$confirm += "<button type='button' class='btn btn-default' data-dismiss='modal'>Annuler</button>";
			$confirm += "<button type='submit' class='btn btn-primary' onclick='javascript:delete_news("+$newsId+")'>Supprimer</button>";
		$confirm += "</div>";
		
		$("#modal_msg .modal-dialog").removeClass("error success");
		$("#modal_msg .modal-dialog").addClass("default");
		$("#modal_msg .modal-dialog").addClass("backdrop","static");
		$("#modal_msg .modal-header").html("Supprimer la news");
		$("#modal_msg .modal-body").html($text);
		$("#modal_msg .modal-footer").html($confirm);
		$("#modal_msg").modal('show');
	}
	
	
	function delete_news($newsId) {
		
		// On change le curseur
		document.body.style.cursor = 'wait';
		
		// Requète ajax au serveur
		$.post("<?php echo site_url('group/delete_news'); ?>",
	
			{'newsId':$newsId},
		
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
					$("#modal_msg .modal-header").html("News supprimée !");
					$("#modal_msg .modal-body").html($obj['data']);
					$("#modal_msg .modal-footer").html("<button type='button' class='btn btn-default' onclick='javascript:location.reload()'>Fermer</button>");
				}
				else {
					// Erreur
					$("#modal_msg .modal-dialog").removeClass("success");
					$("#modal_msg .modal-dialog").addClass("error");
					$("#modal_msg .modal-dialog").addClass("backdrop","static");
					$("#modal_msg .modal-header").html("Erreur !");
					$("#modal_msg .modal-body").html($obj['data']);
					$("#modal_msg .modal-footer").html("<button type='submit' class='btn btn-default' data-dismiss='modal'>Fermer</button>");
				}
				$("#modal_msg").modal('show');
			}
		);
	}
	
 </script>
<?php endif ?>



<script type="text/javascript">

	$(function() {
		
		<?php if ($logged == "1" ): ?>
			/************** SEND MESSAGE UI  ****************/
			autosize($('#messageInput'));
			
			// Pour gérer les event spéciaux du textarea (enter => send_message, ctrl+enter => saut de ligne, escape => empty textarea)
			$('#messageForm textarea').keydown(function(e) {
				if (e.key === "Escape") { // escape key maps to keycode `27`
					cancel_edit("send");
				}
				// Si "enter"
				else if (e.keyCode == 13) {
					// Si ctrl on ajoute un saut de ligne au texte
					if (e.ctrlKey || e.shiftKey) {
						var val = this.value;
						if (typeof this.selectionStart == "number" && typeof this.selectionEnd == "number") {
							var start = this.selectionStart;
							this.value = val.slice(0, start) + "\n" + val.slice(this.selectionEnd);
							this.selectionStart = this.selectionEnd = start + 1;
						} else if (document.selection && document.selection.createRange) {
							this.focus();
							var range = document.selection.createRange();
							range.text = "\r\n";
							range.collapse(false);
							range.select();
						}
						autosize.update($('#messageInput'));
					}
					// Sinon on send le message sur un "enter"
					else {
						// Si le message n'est pas vide
						if ($("#messageInput").val().length > 0) {
							$(this).prop('disabled', true);
							e.preventDefault();
							e.stopPropagation();
							send_message($("#messageInput").val(), "1", "1");
						}
						else return false;
					}
					return false;
				}
			});

		<?php endif ?>
		
		// On charge les messages	1 => group	1 => groupId
		load_messages("1", "1", "DESC");
	});
	
	
	/************** SEND MESSAGE   ****************/
	<?php if ($logged == "1" ): ?>
		function send_message($message, $targetTag, $targetId) {

			//console.log("send_message : { \""+$message+"\" ; \""+$targetTag+"\" ; "+$targetId+" }");

			// On change le curseur
			document.body.style.cursor = 'wait';
			
			// Requète ajax au serveur
			$.post("<?php echo site_url('ajax_discussion/send_message'); ?>",
		
				{
					'memberId': <?php echo $memberId ?>,
					'message': $message,
					'targetTag': $targetTag,
					'targetId': $targetId
				},
			
				function (return_data) {
					
					$obj = JSON.parse(return_data);
					
					// On change le curseur
					document.body.style.cursor = 'default';
					
					//console.log(return_data);
					
					// Modal
					if ($obj['state'] == 1) {
						// Succés
						
						// On reset le messageInput
						$("#messageInput").val("");
						autosize.update($("#messageInput"));
						$("#messageInput").prop('disabled', false);
						
						// On créé le message
						var msgBlock = jQuery("<div class='messageBlock'></div>");
						var msg = jQuery("<div class='message' id="+$obj['data']['id']+"><b>"+$("#memberLogin").attr("value")+"</b>&nbsp;&nbsp;<span class='content'>"+getCleanText($message)+"</span></div>");
						msgBlock.append(msg);

						// On rajoute le dropdown
						option = get_options($obj['data']['id'], msg);								
						msgBlock.append(option);
						
						// On ajoute le msgBlock à la liste
						msgBlock.css("display","none");
						$("#messageList").prepend(msgBlock);
						msgBlock.fadeIn("fast");
						
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
				
				//console.log(return_data);
				
				// Succés
				if ($obj['state'] == 1) {
					
					// On vide la liste
					$("#messageList").empty();
					
					// On rempli la liste
					$.each($obj['data'], function( key, value ) {
						
						// On créé le message
						var msgBlock = jQuery("<div class='messageBlock'></div>");
						var msg = jQuery("<div class='message' id="+value.id+"><b>"+value.pseudo+"</b>&nbsp;&nbsp;<span class='content'>"+getCleanText(value.text)+"</span></div>");
						msgBlock.append(msg);
						
						<?php if ($logged) : ?>
							// Si l'auteur du message est le membre connecté
							if (value.memberId == <?php echo $memberId ?>) {
								// On récupère le menu d'options eton l'ajoute au block
								option = get_options(value.id, msg);								
								msgBlock.append(option);
							}
						<?php endif ?>
						
						// On ajoute le msgBlock à la liste
						$("#messageList").append(msgBlock);
					});
				}
			}
		);
	}
	
	
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
	
	// Function inverse
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
	<?php if ($logged == "1" ): ?>
	
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
				$(this).find("button").css("opacity","0%");
			});
			
			return option;
			
		}
	
	<?php endif ?>
	
	
	
	/************* UPDATE MESSAGE ***************/
	<?php if ($logged == "1" ): ?>
	
		// On rentre dans le mode d'édition d'un message
		function edit_message($messageId) {
			
			// On récupère le messageBlock
			var msgBlock = $("#messageList .message[id='"+$messageId+"']").parent();
			
			// On masque le msgBlock
			msgBlock.addClass("hidden");
						
			// On clone et attache le updateBlock au bon endroit
			var updateForm = $("#infos #updateForm").clone().insertAfter(msgBlock);
			
			// On setup le textarea de l'update block en remplaçant les <br> par \n
			var str = msgBlock.find(".message span.content").html();
			var regex = /<br\s*[\/]?>/gi;
			str = str.replace(regex, "\n");
			// ... on remplace les &gt; par < ...
			updateForm.find("#updateInput").val(unescapeHTML(str));
			
			
			// On show l'updateForm
			updateForm.removeClass("hidden");

			// On adapte la taille du textarea
			autosize(updateForm.find("textarea"));
			
			// On prend le focus et on fixe le comportement si on perd le focus
			updateForm.find("textarea").focus().focusout( function() {
				cancel_edit("update");
			}).keydown(function(e) {
				if (e.key === "Escape") { // escape key maps to keycode `27`
					cancel_edit("update");
				}
				// Si "enter"
				else if (e.keyCode == 13) {
					// Si ctrl on ajoute un saut de ligne au texte
					if (e.ctrlKey) {
						var val = this.value;
						if (typeof this.selectionStart == "number" && typeof this.selectionEnd == "number") {
							var start = this.selectionStart;
							this.value = val.slice(0, start) + "\n" + val.slice(this.selectionEnd);
							this.selectionStart = this.selectionEnd = start + 1;
						} else if (document.selection && document.selection.createRange) {
							this.focus();
							var range = document.selection.createRange();
							range.text = "\r\n";
							range.collapse(false);
							range.select();
						}
						autosize.update($("#messageList #updateInput"));
					}
					// Sinon on update le message
					else {
						$(this).prop('disabled', true);
						msgId = $("#messageList").find(".messageBlock.hidden").children(".message").prop("id");
						msgContent = updateForm.find("textarea").val();
						update_message(msgId, msgContent);
					}
					return false;
				}
			});
		}
		
		// Sort du mode d'édition de message (send ou update)
		function cancel_edit(mode) {			
			// Action de cancel pour un update
			if (mode == "update") {
				$("#messageList").find("#updateForm").fadeOut("fast", function() { 
					// On réaffiche le message d'origine
					$("#messageList").find(".messageBlock.hidden").css("display","none").removeClass("hidden").fadeIn("fast");
					// On remove l'update form cloné
					$(this).remove();
				});
			}
			// Action de cancel pour un send
			else if (mode == "send") {
				$("#messageForm #messageInput").val("").focusout();
			}			
		}
		
		// On update via le serveur
		function update_message($messageId, $message) {

			//console.log("update_message : \""+$messageId+"\" ; \""+$message);

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
						// Succés

						// On update le message dans l'UI
						$("#messageList").find(".messageBlock.hidden").find(".message span.content").empty().html(getCleanText($message));
						
						// On sort de l'edit mode
						cancel_edit("update");
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
	<?php if ($logged == "1" ): ?>
	
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
						$("#messageList").find(".message[id="+$messageId+"]").parent().fadeOut("fast", function() { $(this).remove(); });
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



<div class="row">

	<!--  //////////////// NEWS //////////////// !-->
	<div id="main" class="col-sm-9 noGutter">

		<!-- CREER -->
		<?php if ($isSuperAdmin == "1" ):  ?>
			<div class="panel panel-transparent no-border no-shadow">
				<button class="btn btn-default btn-xs" href="<?php echo site_url("group/create_news"); ?>" data-remote="false" data-toggle="modal" data-target="#createNewsModal"><i class="glyphicon glyphicon-plus"></i>&nbsp;&nbsp;Créer</button>
			</div>
		<?php endif; ?>

		<!-- On parcourt chaque news !-->
		<?php foreach ($list_news as $news_item): ?>

			<div class="news panel panel-default" news_Id="<?php echo $news_item['id']; ?>"  <?php if ($news_item['top'] == '1') echo('style="background-color:rgba(255,255,255,0.75)";')?>>
			
				<!-- Titre et date de la news !-->
				<div class="row panel-heading <?php if ($news_item['top'] != '1') echo("top panel-transparent no-border")?>">
				
						<!-- title !-->
						<h4 <?php if ($news_item['top'] != '1') echo('class="nomarginbottom"')?>><span><?php echo $news_item['title']; ?></span>
						
							<!-- Dropdown !-->
							<?php if ($isSuperAdmin == "1" ):  ?>
							<div class="dropdown pull-right">
								<button class="btn btn-xs btn-default panel-transparent no-border dropdown-toggle" data-toggle="dropdown" type="button"><i class='glyphicon glyphicon-option-horizontal softer'></i>
								</button>
								<ul class="dropdown-menu dropdown-menu-right" role="menu" aria-labelledby="menu1">
									<!--<li><a role="menuitem" tabindex="-1" href="javascript:popup_update_news()">Modifier</a></li>!-->
									<li><a role="menuitem" tabindex="-1" href="<?php echo site_url();?>/group/update_news/<?php echo $news_item['id']; ?>" data-remote="false" data-toggle="modal" data-target="#updateNewsModal"><i class='glyphicon glyphicon-pencil'></i>&nbsp;&nbsp;&nbsp;&nbsp;Modifier</a></li>
									<li><a role="menuitem" tabindex="-1" href="javascript:popup_delete_news(<?php echo $news_item['id']; ?>)"><i class='glyphicon glyphicon-trash'></i>&nbsp;&nbsp;&nbsp;&nbsp;Supprimer</a></li>    
								</ul>
							</div>
							<?php endif ?>
							
						</h4>
						
						<!-- date !-->
						<?php if ($news_item['top'] != '1') : ?>
						<span class="softest subtitle"><?php echo '['.date("d/m/y",strtotime($news_item['date'])).']'; ?></span>
						<?php endif ?>
				</div>
				
				<!-- Contenu de la news !-->
				<div class="row panel-body nopaddingtop">
					<div class="col-lg-12">
						<p><?php echo $news_item['text']; ?></p>
					</div>
				</div>


			</div>
			
		<?php endforeach ?>
	</div>
	

	<!--  //////////////// RIGHT COL //////////////// !-->
	<div id="rightCol" class="col-sm-3">
	
	
		<!--  ******* Aperçu ******* !-->
		<div id="apercu" class="panel panel-default">
		
			<div class="row panel-heading alternate">
				Aperçu
			</div>
			
			<div class="row panel-body small">
				<p>
					<i class="glyphicon glyphicon-list-alt"></i>&nbsp;&nbsp;&nbsp;<?php echo $infos->nbMembers ?> membres inscrits
				</p>
				<p>
					<i class="glyphicon glyphicon-music"></i>&nbsp;&nbsp;&nbsp;<?php echo $infos->nbRef ?> références au <a href="<?php echo site_url("repertoire/") ?>">répertoire</a>
				</p>
			</div>
			
		</div>
		
		
		<!--  ******* MESSAGES ******* !-->
		<div id="infos" class="panel panel-default">
		
			<div class="row panel-heading alternate">
				Messages
			</div>
			
			<div class="row panel-body small">
				<?php if ($logged == "1" ): ?>
					<!-- Poster un message !-->
					<div class="container-fluid">
						<form id="messageForm" class="form-horizontal">
							<div class="form-group">
							
								<!-- Textarea autosize !-->
								<div class="col-sm-12 noGutter">
									<textarea id="messageInput" class="form-control autosize" name="message" placeholder="Votre message..." style="resize:none; font-size: 100%"></textarea>
								</div>
								
							</div>
						</form>
					</div>
				<?php endif ?>
				
				
				<!-- Form caché d'update !-->
				<div id="updateForm" class="container-fluid hidden">
					<form  class="form-horizontal">
						<div class="form-group">
						
							<!-- Textarea autosize !-->
							<div class="col-sm-12 noGutter">
								<textarea id="updateInput" class="form-control autosize" name="updateMsg" style="resize:none; font-size: 100%"></textarea>
							</div>
							
						</div>
					</form>
				</div>
				
				
				
				<!-- Liste des messages !-->
				<div id="messageList">
				</div>
				
			</div>
				
		</div>

	</div>
	
	
</div>


<!--  ************* MODAL ************* !-->

<?php if ($isSuperAdmin == "1" ): ?>
<!-- ******** MODAL CREATE News ******* !-->
<div id="createNewsModal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog modal-lg default">
	<div class="modal-content">
		<div class="modal-header lead">Créer une news</div>
		<div class="modal-body">
		...
		</div>
	</div>
	</div>
</div>

<!-- ******** MODAL UPDATE News ******* !-->
<div id="updateNewsModal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog modal-lg default">
	<div class="modal-content">
		<div class="modal-header lead">Modifier une news</div>
		<div class="modal-body">
		...
		</div>
	</div>
	</div>
</div>
<?php endif ?>
