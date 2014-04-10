<?php
defined('_JEXEC') or die ;
$doc = JFactory::getDocument();
$doc -> addStyleSheet('templates/' . $this -> template . '/css/plantilla.css');
$doc -> addStyleSheet('templates/' . $this -> template . '/css/bootstrap/bootstrap.min.css');
// $doc->addStyleSheet('templates/'.$this->template.'/css/bootstrap/bootstrap.min.css');
// $doc->addStyleSheet('templates/'.$this->template.'/css/bootstrap-responsive.css');
// $doc->addStyleSheet('http://fonts.googleapis.com/css?family=Open+Sans:300italic,400italic,600italic,700italic,800italic,400,300,700,600,800');
$doc -> addScript('/templates/' . $this -> template . '/js/main.js', 'text/javascript');
?>

<!DOCTYPE html>
<html>
	<head>

		<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js" type="text/javascript"></script>
		<jdoc:include type="head" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0">

	</head>
	<body id="index" onload="">
		<header id="cabecera">
			<div class="container">
				<div class="row">
					<div class="col-md-3" id="logotipo">
						<a href="#"> <img src="templates/jooinworld/images/logo.png" alt="" /> </a>
					</div>
					<div class=".col-md-6 .col-md-offset-3">
						<jdoc:include type="modules" name="cabecera" style="xhtml" />
					</div>
				</div>
			</div>
		</header>
		<section class="container">

			<div id="banner-arriba" class="row">
				<jdoc:include class="col-md-12" type="modules" name="banner-arriba" style="xhtml" />
			</div>
			<nav id="menu" class="nav">
				<jdoc:include type="modules" name="menu" style="xhtml" />
			</nav>
			<div class="mensaje" >
				<jdoc:include type="message" id="messsage" />

			</div>
			
			
			<div class="contenedor row">
				
				<div class="izquierda col-md-9" id="contenido">

					<div id="banner">
						<jdoc:include type="modules" name="banner" style="xhtml" />
					</div>
					<div class="contenido" id="content">
						<jdoc:include type="component" />
					</div>
				</div>
				<div class="derecha col-md-3" id="sidebar">
					<jdoc:include type="modules" name="sidebar" style="xhtml" />
				</div>
			</div>
			<div id="bottom">
				<jdoc:include type="modules" name="bottom" style="xhtml" />
			</div>
			
			
			
		</section>
		<footer>
			<jdoc:include type="modules" name="footer" style="xhtml" />
		</footer>
	</body>

</html>