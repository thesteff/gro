<html>
<head>

	<link rel="icon" href="<?php echo base_url();?>images/favicon-GRO.ico" />
	
	<!--<link rel="stylesheet" href="<?php echo base_url();?>css/normalize.css" />!-->
	<link rel="stylesheet" href="<?php echo base_url();?>css/style.css" />
	<link rel="stylesheet" href="<?php echo base_url();?>ressources/jquery-ui-1.11.4.custom/jquery-ui.min.css" />

	
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	
	<!-- Pour les vignettes réseaux sociaux (Open Graph) !-->
	<meta property="og:title" content="<?php echo isset($page_title)?$page_title:$title;?>" />
	<meta property="og:description" content="<?php echo isset($page_description)?$page_description:""; ?>" />
	<meta property="og:image" content="<?php echo base_url()."images/logo_small.png" ?>" />
	<meta property="og:width" content="200" />
	<meta property="og:height" content="200" />
	
	<title>
		<?php echo isset($page_title)?$page_title:$title;?> - Grenoble Reggae Orchestra
	</title>
	
	
	<!--
	<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
	<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.11.1/jquery-ui.min.js"></script>
	!-->
	
	<script src="https://code.jquery.com/jquery-3.2.1.min.js"
			  integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4="
			  crossorigin="anonymous">
	</script>
	
	<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"
			  integrity="sha256-VazP97ZCwtekAsvgPBSUwPFKdrwD3unUfSGVYrahUqU="
			  crossorigin="anonymous">
	</script>
	
	
	<!--
	<script type="text/javascript" src="<?php echo base_url();?>ressources/script/jquery-3.2.1.min.js"></script>
	<script type="text/javascript" src="<?php echo base_url();?>ressources/jquery-ui-1.11.4.custom/jquery-ui.min.js"></script>
	<script type="text/javascript" src="<?php echo base_url();?>ressources/jquery-ui-1.11.4.custom/jquery.ui.datepicker-fr.js"></script>
	!-->
	
	<!-- Gestion des popups !-->
	<script type="text/javascript" src="<?php echo base_url();?>ressources/tinybox2/packed.js"></script>	

	
	<!-- VUE JS !-->
	<script type="text/javascript" src="<?php echo base_url();?>ressources/script/vue.min.js"></script>

	
	<script type="text/javascript">

	$.datepicker.setDefaults($.datepicker.regional['fr']);
	
	
	function show_mentions() {
		// POPUP
		$confirm = "<p>Les informations recueillies sont nécessaires pour votre adhésion.\nElles font l’objet d’un traitement informatique et sont destinées au secrétariat de l’association. En application des articles 39 et suivants de la loi du 6 janvier 1978 modifiée, vous bénéficiez d’un droit d’accès et de rectification aux informations qui vous concernent. Si vous souhaitez exercer ce droit et obtenir communication des informations vous concernant, veuillez nous adresser un message à l'adresse suivante :\ncontact@le-gro.com.</p>";
		TINY.box.show({html:$confirm,boxid:'confirm',animate:true,width:650});
	}
	
	</script>
	
	
</head>


 <body>
	<div id="canevas">
		<div id="entete">
			<div id="page_title">
				<a href="<?php echo base_url();?>index.php/"><h1>Grenoble<br><?php echo str_repeat("&nbsp;",3)?>Reggae<br><?php echo str_repeat("&nbsp;",5)?>Orchestra</h1></a>
			</div>
			<div id="logo"></div>
		</div>
		
		<div id="corps">
		