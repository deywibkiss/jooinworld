<?php // No direct access to this filedefined('_JEXEC') or die('Restricted access');jimport( 'joomla.environment.uri' );$toolbar = CToolbarLibrary::getInstance();$uri = JFactory::getURI();$doc = JFactory::getDocument();$doc->addScriptDeclaration( '	$( document ).ready( function(){				window.onbeforeunload = function(e) {			console.log( "SE CARGA");		  			};			});' );echo '<div id="community-wrap">'.$toolbar->getHTML().'</div>';?><div class="content-referidos"><?php if( $this->tieneplan )	echo '<div class="content-barra"><h4>Activar Otro Plan</h4>' .ToolBarReferidos::render( 'Activar Otro Plan' ). '</div>';?><?php if( !is_null($this->orden->orden) && !empty($this->orden->orden) ){?><h4 class="til-referidos">Tus datos de orden</h4><?php /*?><form name="form-orden" id="form-orden" method="post" action="<?php echo JRoute::_( 'index.php?option=com_referidos&task=plan.pay' )?>"><?php */?><div class="data-active">	<ul>    	<li><span class="title-orden">Orden Nº:</span><span class="data-orden"><?php echo str_pad( $this->orden->orden , 9 , "0" , STR_PAD_LEFT)?></span></li>        <li><span class="title-orden">Nombre:</span><span class="data-orden"><?php echo $this->user->name?></span></li>        <li><span class="title-orden">Correo:</span><span class="data-orden"><?php echo $this->user->email?></span></li>        <li><span class="title-orden">Plan a activar:</span><span class="data-orden"><?php echo $this->plan->nom_plan?></span></li>        <li><span class="title-orden">Valor suscripción:</span><span class="data-orden">COP:<?php echo ' $'.number_format( $this->plan->valor_pesos, 2, ',', '.' )?> - USD:<?php echo ' $'.number_format( $this->plan->valor_dolares, 2, ',', '.' )?></span></li>        <li><span class="title-orden">Tiempo suscripción:</span><span class="data-orden"><?php echo (int) $this->orden->tiempo_plan?> Días</span></li>    </ul></div><div class="link-activa"><?php 	$params = array(			'btn_input' => '<input type="image" src="'.$uri->root().'components/com_referidos/assets/images/img-pay.png" alt="Pagar desde Payu Latam" title="Pagar desde Payu Latam" />'		,	'orden' => $this->orden->orden		,	'plan' => $this->plan->id_plan		,	'nomplan' => $this->plan->nom_plan		,	'valorpesos' => number_format( $this->plan->valor_pesos , 2 , '.' ,'')		,	'valordolar' => number_format( $this->plan->valor_dolares , 2 , '.' , '')		,	'emailuser' => $this->user->email		,	'nameuser' => $this->user->name.' '.$this->user->apellidos	);		$this->pay( $params );?></div><?php }else{?><div class="link-activa">	<a href="<?php echo JRoute::_( 'index.php?option=com_referidos&view=otroplan' )?>" class="btn btn-primary btn-form-right">&lt;- Volver</a></div>    <?php }?></div>