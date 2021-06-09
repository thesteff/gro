<?php use CodeIgniter\I18n\Time; ?>

<!-- Pour le text ellipsis multi-line !-->
<script type="text/javascript" src="<?php echo base_url("ressources/script/jQuery.succinct.min.js"); ?>"></script>
<script type="text/javascript">

	$(function() {
		
		$('.truncate').succinct({
			size: 275
		});
		
		
		$('#tabs .nav a').click(function (e) {
						
			if (typeof $(this).attr("data-url") != 'undefined') {
			
				e.preventDefault();

				var url = $(this).attr("data-url");
				var href = this.hash;
				var pane = $(this);

				// ajax load from data-url
				$(href).load(url,function(result){      
					pane.tab('show');
				});
			}
		});
		
		
		
		// ****** CREATE JAM MODAL ********
		$("#createModal").on("show.bs.modal", function(e) {
			var link = $(e.relatedTarget);
			$(this).find(".modal-body").load(link.attr("href"));
		});
		
		
		// ***** TABS REMEMBER ********
		$('a[data-toggle="tab"]').on('show.bs.tab', function(e) {
			localStorage.setItem('activeTab', $(e.target).attr('href'));
		});

		var activeTab = localStorage.getItem('activeTab');
		if(activeTab) $('#menu_pills a[href="' + activeTab + '"]').tab('show');
		
		$("#tabs").css("display","block");
		
		

	});
	
	
	


</script>

	<div id="tabs" class="panel panel-default row" style="display:none">
	
		<ul id="menu_pills" class="nav nav-tabs">
			<li class="active"><a data-toggle="tab" href="#tocome">A venir</a></li>
			<li><a data-toggle="tab" href="#archive">Passées</a></li>
			
			<?php if ($isSuperAdmin == "1"): ?>
				<!-- CREER -->
				<li><button class="btn btn-default btn-xs" href="<?php echo site_url();?>/jam/create" data-remote="false" data-toggle="modal" data-target="#createModal"><i class="glyphicon glyphicon-plus"></i>&nbsp;&nbsp;Créer</button></li>
			<?php endif; ?>
		</ul>
	
	
		<div class="tab-content">
		
			<div id="tocome" class="tab-pane fade in active">
			
				<!-- Liste des jams à venir. On affiche la jam la plus proche en premier -> array_reverse !-->
				<?php foreach (array_reverse($list_jam) as $temp_jam_item): ?>
				
					<!-- On n'affiche pas les jam archivées !-->
					<?php if ($temp_jam_item['date'] >= date("Y-m-d")) : ?>
					
						<!-- On gère l'accès réservé aux admin !-->
						<?php if ($temp_jam_item['acces_jam'] > 0 || $temp_jam_item['is_admin']) : ?>
						
						<div class="list_item panel panel-default row">
					
							<!-- Date !-->
							<?php
								/*$month = strftime("%b", strtotime($temp_jam_item['date']));
								if (!env('online')) $month = utf8_encode($month);
								$month =  substr(strtoupper(no_accent($month)),0,3);*/
								helper('text_helper'); // text_helper pour le "no_accent"
								$time = new Time($temp_jam_item['date']);
								$month = substr(strtoupper(no_accent($time->toLocalizedString('MMM'))),0,3);
							?>
							<div class="date_box">
								<div><small><?php echo $month; ?></small></div>
								<div><strong><?php echo explode('-', $temp_jam_item['date'])[2] ?></strong></div>
							</div>
					
					
							<!-- Titre et adresse de la jam !-->
							<div class="list_item_title_box">
								<h4 class="panel-heading">
									<a href="<?php echo site_url('jam/').$temp_jam_item['slug']; ?>">
										<?php if ($temp_jam_item['acces_jam'] == 0) echo "<i class='bi bi-gear-fill bi_nopadding'></i>"; ?>
										<?php if ($temp_jam_item['acces_jam'] == 2) echo "<i class='bi bi-lock-fill bi_nopadding'></i>";  ?>
										<?php echo $temp_jam_item['title']; ?></a>
								</h4>
								<?php $br = array("<br>", "<br />"); ?>
								<div class="panel-body"><span class="text-muted"><small><?php echo $temp_jam_item['lieu']['nom']."<span class='hidden-xs'> · ".str_replace($br,"",$temp_jam_item['lieu']['adresse']); ?></span></small></span></div>
							</div>
							
							
							<!-- MIDDLE_BLOCK !-->
							<div class="list_item_middle_box">
								<div class="panel-body hidden-xs hidden-sm"><span class="text-muted truncate"><?php echo $temp_jam_item['text_html']; ?></span></div>
							</div>
							
							<!-- DATA_BLOCK !-->
							<div class="list_item_data_box">
								<div class="panel-body small">
									<div>
										<!-- JAM !-->
										<div class="info"><i class="glyphicon glyphicon-arrow-right"></i>&nbsp;&nbsp;
											<span class="hidden-md hidden-xs">Jam : 
												<?php
													if ($temp_jam_item['acces_jam'] == 0) echo "admin";
													else if ($temp_jam_item['acces_jam'] == 2) echo "privé";
													else if ($temp_jam_item['nbMembers'] >= $temp_jam_item['max_inscr'] && $temp_jam_item['max_inscr'] > 0) echo "complête";
													else echo "ouverte";
												?>
											</span>
											<span class="hidden-sm hidden-lg">
												<?php
													if ($temp_jam_item['acces_jam'] == 0) echo "adm";
													else if ($temp_jam_item['acces_jam'] == 2) echo "pri";
													else if ($temp_jam_item['nbMembers'] >= $temp_jam_item['max_inscr']) echo "X";
													else echo "ok";
												?>
											</span>
											<br>
										</div>
										<div class="info"><i class="glyphicon glyphicon-th-list"></i>&nbsp;&nbsp;<span class="hidden-md hidden-xs">Playlist : </span>
											<?php if ($temp_jam_item['nbSongs'] > 0) : ?>
												<?php echo $temp_jam_item['nbSongs'] ?><span class="hidden-md hidden-xs"> titre<?php if ($temp_jam_item['nbSongs'] > 1) echo 's'; ?></span>
											<?php else: ?>
												<span class="hidden-md hidden-xs">en attente</span>...
											<?php endif; ?>
											<br>
										</div>
										<div class="info"><i class="glyphicon glyphicon-user"></i>&nbsp;&nbsp;<span class="hidden-md hidden-xs">Participant<?php if (isset($temp_jam_item['nbMembers']) && $temp_jam_item['nbMembers'] > 1) echo 's'; ?> : </span><?php echo isset($temp_jam_item['nbMembers']) ? $temp_jam_item['nbMembers'] : '0'; ?></div>
									</div>
								</div>
							</div>
		

						</div>

						<?php endif ?>
					<?php endif ?>
				<?php endforeach ?>
			</div>
			
			
			
			<div id="archive" class="tab-pane fade">
			
				<?php $year = 10000; ?>
				<!-- Liste des jams archivées !-->
				<?php foreach ($list_jam as $temp_jam_item): ?>

					<!-- On affiche les jam archivées !-->
					<?php if ($temp_jam_item['date'] < date("Y-m-d")) : ?>
					
						<!-- On gère l'accès réservé aux admin !-->
						<?php if ($temp_jam_item['acces_jam'] > 0 || $temp_jam_item['is_admin']) : ?>

					
							<!-- On affiche l'année si besoin !-->
							<?php $jamyear = explode('-', $temp_jam_item['date'])[0]; ?>
							<?php if($jamyear < $year): ?>
								<?php $year = $jamyear ?>
								<div class='text-muted event_year small'><?php echo $year; ?></div>
							<?php else: ?>
							<?php endif; ?>
							
							<!-- On affiche la jam !-->
							<div class="list_item panel panel-default row">
						
								<!-- Date !-->
								<?php
									/*$month = strftime("%b", strtotime($temp_jam_item['date']));
									if (!getenv('online')) $month = utf8_encode($month);
									$month =  substr(strtoupper(no_accent($month)),0,3);*/
									helper('text_helper');
									$time = new Time($temp_jam_item['date']);
									$month = substr(strtoupper(no_accent($time->toLocalizedString('MMM'))),0,3);
								?>
								<div class="date_box">
									<div><small><?php echo $month; ?></small></div>
									<div><strong><?php echo explode('-', $temp_jam_item['date'])[2] ?></strong></div>
								</div>
						
						
								<!-- Titre et adresse de la jam !-->
								<div class="list_item_title_box">
									<h4 class="panel-heading">
										<a href="<?php echo site_url('jam/').$temp_jam_item['slug']; ?>">
											<?php if ($temp_jam_item['acces_jam'] == 0) echo "<i class='bi bi-gear-fill bi_nopadding'></i>"; ?>
										<?php if ($temp_jam_item['acces_jam'] == 2) echo "<i class='bi bi-lock-fill bi_nopadding'></i>";  ?>
											<?php echo $temp_jam_item['title']; ?></a>
									</h4>								<?php $br = array("<br>", "<br />"); ?>
									<div class="panel-body"><span class="text-muted"><small><?php echo $temp_jam_item['lieu']['nom']."<span class='hidden-xs'> · ".str_replace($br,"",$temp_jam_item['lieu']['adresse']); ?></span></small></span></div>
								</div>
								
								
								<!-- MIDDLE_BLOCK !-->
								<div class="list_item_middle_box">
									<?php $br = array("<br>", "<br />", "\n"); ?>
									<div class="panel-body hidden-xs hidden-sm"><span class="text-muted truncate"><?php echo $temp_jam_item['text_html']; ?></span></div>
								</div>
								
								<!-- DATA_BLOCK !-->
								<div class="list_item_data_box">
									<div class="panel-body small">
										<div>
											<div class="info"><i class="glyphicon glyphicon-arrow-right"></i>&nbsp;&nbsp;<span class="hidden-md hidden-xs">Jam : archivée</span><span class="hidden-sm hidden-lg">arch.</span><br></div>
											<div class="info"><i class="glyphicon glyphicon-th-list"></i>&nbsp;&nbsp;<span class="hidden-md hidden-xs">Playlist : </span>
												<?php if ($temp_jam_item['nbSongs'] > 0) : ?>
													<?php echo $temp_jam_item['nbSongs']; ?><span class="hidden-md hidden-xs"> titre<?php if ($temp_jam_item['nbSongs'] > 1) echo 's'; ?></span>
												<?php else: ?>
													<span class="hidden-md hidden-xs">en attente</span>...
												<?php endif ?>
											</div>
											<div class="info"><i class="glyphicon glyphicon-user"></i>&nbsp;&nbsp;<span class="hidden-md hidden-xs">Participant<?php if ($temp_jam_item['nbMembers'] > 1) echo 's'; ?> : </span><?php echo $temp_jam_item['nbMembers']; ?></div>
										</div>
									</div>
								</div>
			

							</div>
							
						<?php endif ?>
					<?php endif ?>
				<?php endforeach ?>
			</div>
			
			
			<div id="create" class="tab-pane fade"></div>
			
			
		</div>			
	</div>

	
	
<!-- ******** MODAL CREATE ******* !-->
<div id="createModal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog default">
	<div class="modal-content">
		<div class="modal-header lead">Créer une jam</div>
		<div class="modal-body">
		...
		</div>
	</div>
	</div>
</div>