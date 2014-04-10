<?php defined( '_JEXEC' ) or die( 'Restricted access' ); 

jimport( 'joomla.application.component.controller' );
 
class ReferidosControllerConfComision extends JController{
	
	/**
	* Guarda configuracion comisión Referidos
	* @parmas void
	* @return void
	*/
	public function apply(){
			
		// Inicialización
		$args = array();
		$modelo_confcomision = $this->getModel( 'confcomision' );
		$id_confcomision = JRequest::getInt( 'id' );
		$user = JFactory::getUser();
	
		// Atrapar variables por POST
		$args = array(
				'id' => ( !empty( $id_confcomision ) ) ? $id_confcomision : NULL
			,	'semana_corte' => JRequest::getVar( 'semcorte' )
			,	'dia_corte' => JRequest::getVar( 'diacorte' )	
			,	'semana_pago' => JRequest::getVar( 'sempago' )
			,	'dia_pago' => JRequest::getVar( 'diapago' )
			,	'semana_corte2' => JRequest::getVar( 'semcorte2' )
			,	'dia_corte2' => JRequest::getVar( 'diacorte2' )
			,	'semana_pago2' => JRequest::getVar( 'sempago2' )
			,	'dia_pago2' => JRequest::getVar( 'diapago2' )
			,	'email_admin' => JRequest::getVar( 'email_admin' )
		);
		
		// Instanciar modelo y cargar atributos
		$modelo_confcomision->instance( $args );
		
		// Guardar del modelo
		if( $modelo_confcomision->save( 'bool' ) ){

			$this->setRedirect( JRoute::_( 'index.php?option=com_referidos&view=confcomisiones&layout=edit&id='.( !empty($modelo_confcomision->id) ) ? $modelo_confcomision->id : $modelo_confcomision->insertId , false) , 'La Configuración se guardo correctamente'  , 'message');
			return;
		}
		
		$this->setMessage( 'No se Guardo la Configuración' , 'error');
		$this->setRedirect( JRoute::_( 'index.php?option=com_referidos&view=confcomisiones&layout=edit&id='.( !empty($modelo_confcomision->id) ) ? $modelo_confcomision->id : $modelo_confcomision->insertId , false) );
		return;
	}
	
	/**
	* Guarda configuracion comisión Referidos
	* @parmas void
	* @return void
	*/
	public function save(){
		// Inicialización
		$args = array();
		$modelo_confcomision = $this->getModel( 'confcomision' );
		$id_confcomision = JRequest::getInt( 'id' );
		$user = JFactory::getUser();
	
		// Atrapar variables por POST
		$args = array(
				'id' => ( !empty( $id_confcomision ) ) ? $id_confcomision : NULL
			,	'semana_corte' => JRequest::getVar( 'semcorte' )
			,	'dia_corte' => JRequest::getVar( 'diacorte' )
			,	'semana_pago' => JRequest::getVar( 'sempago' )
			,	'dia_pago' => JRequest::getVar( 'diapago' )
			,	'semana_corte2' => JRequest::getVar( 'semcorte2' )
			,	'dia_corte2' => JRequest::getVar( 'diacorte2' )
			,	'semana_pago2' => JRequest::getVar( 'sempago2' )
			,	'dia_pago2' => JRequest::getVar( 'diapago2' )
			,	'email_admin' => JRequest::getVar( 'email_admin' )
		);
		
		// Instanciar modelo y cargar atributos
		$modelo_confcomision->instance( $args );
		
		// Guardar del modelo
		if( $modelo_confcomision->save( 'bool' ) ){

			$this->setRedirect( JRoute::_( 'index.php?option=com_referidos&view=comisiones' , false) , 'La Configuración se guardo correctamente'  , 'message');
			return;
		}
		
		$this->setMessage( 'No se Guardo la Configuración' , 'error');
		$this->setRedirect( JRoute::_( 'index.php?option=com_referidos&view=comisiones' , false) );
		return;
	}
	
	/*
	* redirecciona a la vista por defecto del componente
	*/
	public function cancel(){
		$this->setRedirect( 'index.php?option=com_referidos' );
		return;
	}
}