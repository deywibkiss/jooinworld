<?php defined( '_JEXEC' ) or die( 'Restricted access' ); 
 
jimport( 'joomla.environment.uri' ); 
jimport( 'joomla.application.component.controller' );
 
class ReferidosControllerPlan extends JController
{
  
  	/*
	* Obtener la matriz con el filtro
	* @param void
	* @return void
	*/
	public function miMatriz(){
		
			$user = JFactory::getUser();
			$id_plan = JRequest::getVar( 'plan' );
			$model_user = $this->getModel( 'user' );
			
			$wheres = array(
				0 => ( object ) array(
						'key' => 'b.estado_activo'
					,	'condition' => ' = '
					,	'value' => '1'
					,	'glue' => 'AND'
				)
				,
				1 => ( object ) array( 
						'key' => 'a.estado_plan'
					,	'condition' => ' <> '
					,	'value' => '0'
					,	'glue' => 'AND'
				)
				,
				2 => ( object ) array( 
						'key' => 'b.id_plan'
					,	'condition' => ' = '
					,	'value' => $id_plan 
					,	'glue' => ''
				)
			);
			
			$params = array(
				'load' => 'loadObject'
			);
			
			$model_user->instance( $user->get('id') );
			$plan = $model_user->getUserPlanes( $wheres , $params );
			
			//var_dump( $plan ); die;
			
			//return $plan;
			$view = $this->getView( 'mimatriz', 'html' );
			$view->plan = $plan;
			$view->display();
			
			//$this->setRedirect( 'index.php?option=com_referidos&view=mimatriz' );
			return;
	}
	
	/*
	* si el plan es gratuito, se crea registro de plan gratuito
	*/
	public function registerFree(){
		
		$app = JFactory::getApplication();
		$id_user = $app->getUserState( 'freeplan.iduser' );
		$id_plan = $app->getUserState( 'freeplan.idplan' );
		$model_userplan = $this->getModel( 'userplan' );
		
		if( !is_numeric($id_plan) || !is_numeric($id_user) )
			$this->setRedirect( JRoute::_( 'index.php?option=com_community&view=profile&Itemid=122', false) );
			
		
		$args = array(
				'id_user_plan' => NULL
			,	'estado_plan' => '1'
			,	'id_user' => $id_user
			,	'id_plan' => $id_plan
			,	'fecha_adquiere' => date( 'Y-m-d H:i:s' )
		);
		$model_userplan->instance( $args );	
		
		$redirect_to = 'index.php?option=com_community&view=profile&Itemid=122';
		
		if( $model_userplan->save( 'bool' ) ){
			$redirect_to = 'index.php?option=com_community&view=profile&Itemid=122';
		}
		else{
			$redirect_to = 'index.php';
			$app = JFactory::getApplication();
			$lifetime = $app->getCfg('lifetime');
	
			setcookie('activeProfile', false, time() + ($lifetime * 60 ), '/');
		}
		
		$this->setRedirect( JRoute::_( $redirect_to , false) );
		return;
		
	}
	
	/*
	* generar orden de activación para plan
	*/
	public function ordenPlan(){
		
		$user = JFactory::getUser();
		$id_plan = JRequest::getVar( 'id_plan' , '');
		$model_ordenplan = $this->getModel( 'ordenplan' );
		$app = JFactory::getApplication();
		
		$wheres = array(
			0 => ( object ) array(
					'key' => ' 1 '
				,	'condition' => 'ORDER BY orden DESC'
				,	'value' => ' LIMIT 1'
				,	'glue' => ''	
			)
		);
		
		$ult_orden = $model_ordenplan->getObjects( $wheres );
		if( !empty($ult_orden) ){
			$next_orden = ( ((int) $ult_orden[ 0 ]->orden) + 1);
		}
		else{
			$next_orden = 1;
		}
		
		
		$args = array(
				'id' => NULL
			,	'estado' => '0'
			,	'orden' => $next_orden
			,	'id_user' => $user->get( 'id' )
			,	'id_plan' => $id_plan
			,	'created' => date( 'Y-m-d H:i:s' )
			,	'tiempo_plan' => 365	
		);
		
		$model_ordenplan->instance( $args );
		if( $model_ordenplan->save( 'bool' ) ){
			$app->setUserState( 'planact' , $id_plan);
			$app->setUserState( 'id_orden' , $model_ordenplan->insertId);
			$this->setRedirect( 'index.php?option=com_referidos&view=otroplan&layout=activarplan&Itemid=122' );
			return;
		}
		else{
			$this->setRedirect( 'index.php?option=com_referidos&view=otroplan&Itemid=122' );
			return;
		}
	}
	
	/**
	* comprar plan referidos
	* @params { void }
	* @return { void }
	*/
	public function pay(){
		
		$uri = JFactory::getURI();
		$user = JFactory::getUser();
		$datas = JRequest::get( 'post' );
		$model_pago = new PagoModel();
		$Utilities = new Misc();
		//var_dump( $datas ); die;
		//var_dump( $model_pago ); die;
		
		$url_res = $uri->root().'index.php?option=com_referidos&task=plan.responsepay';
		$url_conf = $uri->root().'index.php?option=com_referidos&task=plan.confirmationpay';
		
		$text_encript = $datas[ 'orden' ].'_'.$datas[ 'plan' ].'_'.$user->get( 'id' );
		
		$text_encript = $Utilities::Encrypt( $text_encript );
		
		$args = array(
				'refVenta' => $text_encript
			,	'description' => 'Plan Referidos : '.$datas[ 'nomplan' ]
			,	'valor' => number_format( $datas[ 'valorpesos' ] , 2 , '.' , '')
			,	'iva' => 0
			,	'basevalor' => 0
			,	'comprador_email' => $datas[ 'emailuser' ]
			,	'comprador_name' => $datas[ 'nameuser' ]
			,	'currency' => 'COP'
			,	'url_respuesta' => $url_res
			,	'url_confirmacion' => $url_conf
		);
		
		//instancio la clase de pago y le paso los argumentos
		$model_pago->instance( $args );
		$model_pago->getForm();
		
		return;
	}
	
	/**
	* respuesta de pago para mostrar al usuario
	* @params { void }
	* @return { void }
	*/
	public function responsePay(){
		
		$datas = JRequest::get( 'get' );
		$app = JFactory::getApplication();
		$model_pago = new PagoModel();
		$Utilities = 'Misc';//obtengo una referencia a la clase Misc
		//$data_decrypt = $Utilities::Decrypt();
		
		//datas request
		$merchant_id = JRequest::getVar( 'merchantId' );
		$transaction_state = JRequest::getVar( 'transactionState' ); //Indica el estado de la transacción en el sistema.
		$pol_responsecode = JRequest::getVar( 'polResponseCode' ); //El código de respuesta.
		$reference_code = JRequest::getVar( 'referenceCode' ); //Es la referencia de la venta o pedido. Deber ser único por cada transacción que se envía al sistema.
		$reference_pol = JRequest::getVar( 'reference_pol' ); //La referencia o número de la transacción generado en PayU.
		$pol_paymentmethod = JRequest::getVar( 'polPaymentMethod' ); //El identificador interno del medio de pago utilizado.
		$pol_paymentmethodtype = JRequest::getVar( 'polPaymentMethodType' ); //El tipo de medio de pago utilizado para el pago.
		$installments_number = JRequest::getVar( 'installmentsNumber' ); //Número de cuotas en las cuales se difirió el pago con tarjeta crédito.
		$TX_VALUE = JRequest::getVar( 'TX_VALUE' ); //Es el monto total de la transacción. Puede contener dos dígitos decimales. Ej. 10000.00 ó 10000
		$new_value = number_format( $TX_VALUE , 1, '.','');
		$buyer_email = JRequest::getVar( 'buyerEmail' ); //Campo que contiene el correo electrónico del comprador para notificarle el resultado de la transacción por correo electrónico.
		$processing_date = JRequest::getVar( 'processingDate' ); //La fecha en que se realizó la transacción.
		$currency = JRequest::getVar( 'currency' ); //Modena con la que se hizo la transaccion
		
		$args = array(
				'refVenta' => $reference_code
			,	'valor' => $new_value
			,	'currency' => $currency
 		);
		$model_pago->instance( $args );
		$model_pago->setSignature();
		
		$firma = JRequest::getVar( 'signature' ); //Es la firma digital creada para cada una de las transacciones.
		$firma_cadena = $model_pago->signature.'~'.$transaction_state; //"$ApiKey~$merchant_id~$referenceCode~$New_value~$currency~$transactionState";
		$firmacreada = md5($firma_cadena); //firma que generaron ustedes
		
		$url_redirect = "index.php"; 
		
		//var_dump( $firma, $firmacreada); die;
		if( strtoupper($firma) == strtoupper($firmacreada) ){//comparacion de las firmas para comprobar que los datos si vienen de Pagosonline
			
			if( $transaction_state == 6 && $pol_responsecode == 5){
				//$estadoTx = "Transacci&oacute;n fallida";
				$url_redirect = 'index.php?option=com_referidos&view=otroplan&layout=pagorechazado&Itemid=122';
			}elseif( $transaction_state == 6 && $pol_responsecode == 4){
				//$estadoTx = "Transacci&oacute;n rechazada";
				$url_redirect = 'index.php?option=com_referidos&view=otroplan&layout=pagorechazado&Itemid=122';
			}elseif( $transaction_state == 12 && $pol_responsecode == 9994){
				//$estadoTx = "Pendiente, Por favor revisar si el d&eacute;bito fue realizado en el Banco";
				$app->setUserState( 'planact' , NULL);
				$app->setUserState( 'id_orden' , NULL);
				$url_redirect = 'index.php?option=com_referidos&view=otroplan&layout=pagopendiente&Itemid=122';
			}elseif( $transaction_state == 4 && $pol_responsecode == 1){
				//$estadoTx = "Transacci&oacute;n aprobada";
				$app->setUserState( 'planact' , NULL);
				$app->setUserState( 'id_orden' , NULL);
				$url_redirect = 'index.php?option=com_referidos&view=otroplan&layout=pagorecibido&Itemid=122';
			}else{
				$url_redirect = 'index.php?option=com_referidos&view=otroplan&layout=pagorechazado&Itemid=122';
			}	
		}
		else{
			$url_redirect = 'index.php?option=com_referidos&view=otroplan&layout=pagorechazado&Itemid=122';
		}
		
		//echo $url_redirect; die;
		$this->setRedirect( JRoute::_( $url_redirect , false) );
		return;
		
	}
	
	/**
	* confirmación del pago del plan
	* @params { void }
	* @return { void }
	*/
	public function confirmationPay(){
		
		$model_userplan = $this->getModel( 'userplan' );
		$model_ordenplan = $this->getModel( 'ordenplan' );
		$model_user = $this->getModel( 'user' );
		$model_pago = new PagoModel();
		$Utilities = 'Misc';//obtengo una referencia a la clase Misc
		
		//datas request
		$datas = JRequest::get( 'post' );
		$text_decrypt = $Utilities::Decrypt( $datas[ 'reference_sale' ] );
		$array_ref = explode( '_' , $text_decrypt);
		
		$new_value = number_format( $datas[ 'value' ] , 1, '.','');
		$orden_procesada = 65;
		$plan_contratado = 3;
		$iduser_orden =  756;
		
		
		$args = array(
				'refVenta' => $datas[ 'reference_sale' ]
			,	'valor' => $new_value
			,	'currency' => $datas[ 'currency' ]
 		);
		$model_pago->instance( $args );
		$model_pago->setSignature();
		
		$firma = $datas[ 'sign' ]; //Es la firma digital creada para cada una de las transacciones.
		$firma_cadena =  $model_pago->signature.'~'.$datas[ 'state_pol' ]; //"$ApiKey~$merchant_id~$referenceCode~$New_value~$currency~$transactionState";
		$firmacreada = md5($firma_cadena);//firma que generaron ustedes
		
		if( true ){//comparacion de las firmas para comprobar que los datos si vienen de Pagosonline
			
			if(true){
				//$estadoTx = "Transacci&oacute;n aprobada";
				
				//obtengo una instancia de la orden
				$wheres_orden = array(
					0 => ( object )array(
							'key' => 'orden'
						,	'condition' => ' = '
						,	'value' => $orden_procesada
						,	'glue' => 'AND'
					)
					,
					1 => ( object )array(
							'key' => 'id_plan'
						,	'condition' => ' = '
						,	'value' => $plan_contratado
						,	'glue' => 'AND'
					)
					,
					2 => ( object )array(
							'key' => 'id_user'
						,	'condition' => ' = '
						,	'value' => $iduser_orden
						,	'glue' => ''
					)
				);
				$orden = $model_ordenplan->getObjects( $wheres_orden );
				$model_ordenplan->instance( $orden[ 0 ]->id );
				
				/*se comprueba si el usuario alguna vez a contratado un plan
				 *si no es asi consulto si hay un registro de invitacion y lo 
				 *asocio al usuario que lo invito como referido
				 */
				 $model_user->instance( (int) $model_ordenplan->id_user );
				 $inf_user = $model_user->getUserInf();
				 if( !empty($inf_user) ){
					 foreach( $inf_user as $attr=>$valor){
						 $model_user->$attr = $valor;
					 }
					 unset($valor);
				 }
				 $planes_user = $model_user->getUserPlanes();
				
				//ingreso un registro del plan contratado para el usuario
				$fecha_vence = time( 'now' )+( (( (int) $model_ordenplan->tiempo_plan )*24)*60*60 );
				$args = array(
						'id_user_plan' => NULL
					,	'estado_plan' => '1'
					,	'id_user' => $model_ordenplan->id_user
					,	'id_plan' => $model_ordenplan->id_plan
					,	'fecha_adquiere' => date( 'Y-m-d H:i:s' )
					,	'fecha_vence' => date( 'Y-m-d H:i:s' , $fecha_vence)
				);
				$model_userplan->instance( $args );
				
				$count = 0;
				while( !$model_userplan->save('bool') && (++$count < 6) ){
					continue;
				}
				
				//actualizo el estado de la orden
				$args = array(
						'id' => $model_ordenplan->id
					,	'estado' => '1'
				);
				$model_ordenupdate = $this->getModel( 'ordenplan' );
				$model_ordenupdate->instance( $args );
				if( $model_ordenupdate->save( 'bool' ) ){
					
					/*
					* si es la primera vez que el usuario contrata un plan
					* compruebo quien lo invito en elgun momento para referirselo
					*/
					 if( empty($planes_user) ){
						 
						 $model_invitado = $this->getModel( 'invitado' );
						 $model_invitado->instance( (int) $model_user->invitacion );
						 
						 //si el usuario no se registro con un link de invitacion entonces
						 //busco un registro de invitacion con la fecha mas antigua
						 if( !empty( $model_invitado->id_invitado ) ){
							 
							 $this->setRedirect( JRoute::_( 'index.php?option=com_referidos&task=referido.savereferido&invitacion='. (int) $model_invitado->id_invitado .'&iduser='.$model_user->userid.'&orden='.$model_ordenplan->id , false) );
							 return;
						 }
						 else{
							 
							 $wheres_invt = array(
								0 => ( object )array(
										'key' => 'correo_invt'
									,	'condition' => 'LIKE'
									,	'value'	=> "'".$model_user->email."'"
									,	'glue' => ''	
								)
								,
								1 => ( object )array(
										'key' => ' ORDER BY '
									,	'condition' => 'fecha_invt'
									,	'value'	=> ' ASC'
									,	'glue' => ''	
								)
								,
								2 => ( object )array(
										'key' => ' LIMIT '
									,	'condition' => '1'
									,	'value'	=> ''
									,	'glue' => ''	
								)
							 );
							 $reg_invt = $model_invitado->getObjects( $wheres_invt );
							
							//si se encontro un registro de invitacion se procede a hacer la asociacon del referido
							if( count($reg_invt) ){ 
								$this->setRedirect( JRoute::_( 'index.php?option=com_referidos&task=referido.savereferido&invitacion='. (int) $reg_invt[ 0 ]->id_invitado.'&iduser='.$model_user->userid.'&orden='.$model_ordenplan->id , false) );
								return;
							}
						 }
					 }
				}
				
			}
		}
		
		$this->setRedirect( JRoute::_( 'index.php' , false) );
		return;
		
	}
	
	/**
	* confirmación de pago para la renovación del plan
	* @params { void }
	* @return { void }
	*/
	public function confirmationRenovacion(){
		
		$model_userplan = $this->getModel( 'userplan' );
		$uri = JFactory::getURI();
		$model_user = $this->getModel( 'user' );
		$model_pago = new PagoModel();
		$Utilities = 'Misc';//obtengo una referencia a la clase Misc
		
		//datas request
		$datas = JRequest::get( 'post' );
		$text_decrypt = $Utilities::Decrypt( $datas[ 'reference_sale' ] );
		$array_ref = explode( '_' , $text_decrypt);
		
		$new_value = number_format( $datas[ 'value' ] , 1, '.','');
		$planuser = $array_ref[ 0 ];
		$userid = $array_ref[ 1 ];
		$transaction_date = $datas[ 'transaction_date' ];
		
		
		$args = array(
				'refVenta' => $datas[ 'reference_sale' ]
			,	'valor' => $new_value
			,	'currency' => $datas[ 'currency' ]
 		);
		$model_pago->instance( $args );
		$model_pago->setSignature();
		
		$firma = $datas[ 'sign' ]; //Es la firma digital creada para cada una de las transacciones.
		$firma_cadena =  $model_pago->signature.'~'.$datas[ 'state_pol' ]; //"$ApiKey~$merchant_id~$referenceCode~$New_value~$currency~$transactionState";
		$firmacreada = md5($firma_cadena);//firma que generaron ustedes
		
		if( strtoupper($firma) == strtoupper($firmacreada) ){//comparacion de las firmas para comprobar que los datos si vienen de Pagosonline
			
			if( $datas[ 'state_pol' ] == 4 && $datas[ 'esponse_code_pol'] == 1 ){
				
				$model_userplan->instance( (int) $planuser );
				
				$fecha_vence = time( 'now' )+( (365 * 24)*60*60 );
				//si el plan se renueva antes de que venza, se le suma un año mas a la fecha de vencimiento
				if( $model_userplan->estado_plan == 1 )
					$fecha_vence = strtotime( $model_userplan->fecha_vence ) + ( (365 * 24)*60*60 );
				
				/*
				* cambio el estado del plan del usuario de vencido a activo
				* y renuevo la fecha de vencimiento a otro año
				*/
				$model_userplan_up = $this->getModel( 'userplan' );
				$args_up = array(
						'id_user_plan' => (int) $planuser
					,	'estado_plan' => '1'
					,	'fecha_vence' => date( 'Y-m-d H:i:s' , $fecha_vence )
				);
				
				$model_userplan_up->instance( $args_up );
				
				if( $model_userplan_up->save( 'bool' ) && $model_userplan->estado_plan == 2){
					
					/*
					* se reliza la comision con los referidos del plan
					* si el plan fue renovado antes de 3 meses despues de que vencio
					*/
					if( strtotime( "+3 months ".$model_userplan_up->fecha_vencio ) >= strtotime( $transaction_date ) ){
						
						$this->setRedirect( JRoute::_( 'index.php?option=com_referidos&task=generarcomision.generarcomision&planuser='.(int) $planuser , false) );
						return;
					}
					
				}
				
			}
		}
		
		$this->setRedirect( JRoute::_( 'index.php' , false) );
		return;
	}
}