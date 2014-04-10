<?php/**  * @category    Plugins  * @package        JomSocial  * @copyright (C) 2011 by Codigonexo  * @license        GNU/GPL, see LICENSE.php  */ // no direct accessdefined('_JEXEC') or die('Restricted access');//Importamos las dependencias a clases necesarias require_once( JPATH_ROOT . DS . 'components' . DS . 'com_community' . DS . 'libraries' . DS . 'core.php'); jimport('joomla.application.component.model'); JModel::addIncludePath( JPATH_SITE.'/components/com_referidos/models', 'ReferidosModel'); /** * Class exists checking */if (!class_exists('plgCommunityRestrictionPlan')) {    /**     * Plugin entrypoint     */    class plgCommunityRestrictionPlan extends CApplications {        var $name = 'Restriction Plan';        var $_name = 'restrictionplan';        var $_user = null;		        /**         *         * @param type $subject         * @param type $config         */        public function plgCommunityRestrictionPlan(& $subject, $config) {            parent::__construct($subject, $config);            $this->db = JFactory::getDbo();            $this->_my = CFactory::getUser();                }        /**         * Ajax function to save a new wall entry         *         * @param message	A message that is submitted by the user         * @param uniqueId	The unique id for this group         * @return type         */        function onAfterRender(){						$model     = CFactory::getModel('profile');         			$my        = CFactory::getUser();             			$user    = CFactory::getRequestUser(); 			$userid = $user->id;			$restricciones = array();			            $this->loadLanguage();			//Cargamos los parametros del plugin para el usuario             $this->loadUserParams();						$def_limit = $this->userparams->get('count', 10);            $limit = JRequest::getVar('limit', $def_limit, 'REQUEST');            $limitstart = JRequest::getVar('limitstart', 0, 'REQUEST');			            $mainframe = JFactory::getApplication();			$componentload = JRequest::getVar('option');			//var_dump( $componentload ); die;						//Archivo de lenguaje            JPlugin::loadLanguage( 'plg_myarticles', JPATH_ADMINISTRATOR );               // Attach CSS            $document =& JFactory::getDocument();			//$css = ( C_JOOMLA_15 )   ? JURI::base() . 'plugins/community/restrictionplan/style.css' : JURI::base() . 'plugins/community/restrictionplan/restrictionplan/style.css'; 			//$document->addStyleSheet($css);						//Importamos las dependencias a clases necesarias            jimport('joomla.application.component.model');            JModel::addIncludePath( JPATH_SITE.'/components/com_referidos/models', 'ReferidosModel'); 						// Instanciar modelos requeridos de com_referidos			$model_refdefault = JModel::getInstance('default', 'ReferidosModel' );      		$model_user = JModel::getInstance('user', 'ReferidosModel' );			$model_caracteristica = JModel::getInstance('caracteristica', 'ReferidosModel' );			$model_userplan = JModel::getInstance('userplan', 'ReferidosModel' );			$model_plan = JModel::getInstance('plan', 'ReferidosModel' );						//obtener el Html			$html = JResponse::getBody();						$scriptUpload = '				<script type="text/javascript">					(function($ , window, document){						$(document).ready(function(){							$(".quitarelm").remove();						});											})(jQuery, this, this.document , "undefined");				</script>				';			$html = str_ireplace( '</head>' , $scriptUpload.' </head>' , $html) ;						//obtengo los planes del usuario			$wheres_planuser = array(				0 => (object) array(						'key' => 'id_user'					,	'condition' => ' = '					,	'value' => $userid					,	'glue' => 'AND'				)				,				1 => (object) array(						'key' => 'estado_plan'					,	'condition' => ' = '					,	'value' => '1 GROUP BY id_plan'					,	'glue' => ''				)			);			$planesuser = $model_userplan->getObjects( $wheres_planuser );							//recorro los planes del usuario para comprobar sus caracteristicas			if( count( $planesuser ) ){				foreach( $planesuser as $key=>$planuser ){										$model_plan = JModel::getInstance('plan', 'ReferidosModel' );					$model_plan->instance( $planuser->id_plan );										$caracteristicas_plan = $model_plan->getCaracteristicasPlan(  $params_plan );										//se obtiene el valor y el tipo de la caracteristica para el plan en un array					foreach( $caracteristicas_plan as $pos=>$caracteristica ){												$valor_permiso = false;												if( !is_numeric($caracteristica->valor) ){							switch( strtolower( trim($caracteristica->valor) ) ){								case 'si': $valor_permiso = true; break;								case 'no': $valor_permiso = false; break;								default : $valor_permiso = false; break;							}						}else{							$valor_permiso = (int) $caracteristica->valor;						}												if( !isset( $restricciones[ $caracteristica->id_caracteristica ] ) || $valor_permiso == 0 || $valor_permiso ){							$restricciones[ $caracteristica->id_caracteristica ][ 'valorbool' ] = $valor_permiso;							$restricciones[ $caracteristica->id_caracteristica ][ 'tipo' ] = $caracteristica->tipo_caracteristica;						}					}					unset( $caracteristica );									}				unset( $planuser );								//recorro todas las caracteristicas				foreach( $restricciones as $idcaracteristica=>$valores ){										//llamada a los metodos de restricción de acceso					switch( $valores[ 'tipo' ] ){						case '1': 								$html = $this->restrictionLike( $html , $valores[ 'valorbool' ] ); 								break;						case '2':								$html = $this->restrictionComment( $thml , $valores[ 'valorbool' ] );								break;						case '3':								$html = $this->restrictionNotificaciones( $html , $valores[ 'valorbool' ] );								break;						case '4':								$html = $this->loadPhotos( $html , $valores[ 'valorbool' ] );								$html = $this->loadPhotosWall( $html , $valores[ 'valorbool' ] );								break;						case '5':								$html = $this->restrictionPhotoComment( $html , $valores[ 'valorbool' ] );								break;										case '6':								$html = $this->restrictionPhotoTag( $html , $valores[ 'valorbool' ] ); //se oculta pero daña la estructura								break;						case '7':								$html = $this->loadVideos( $html , $valores[ 'valorbool' ] );								$html = $this->loadVideosWall( $html , $valores[ 'valorbool' ] );								break;								case '8':								$html = $this->restrictionEvents( $html , $valores[ 'valorbool' ] );								break;							case '9':								$html = $this->restrictionGroups( $html , $valores[ 'valorbool' ] );								break;								case '10':								$html = $this->restrictionPrivacy( $html , $valores[ 'valorbool' ] );								break;								case '12':								$html = $this->restrictionSearchFriends( $html , $valores[ 'valorbool' ] );								break;						case '13':								$html = $this->restrictionInvitarAmigos( $html , $valores[ 'valorbool' ] );								break;							case '14':								$html = $this->restrictionBadejaMensajes( $html , $valores[ 'valorbool' ] );								break;						case '16':								$html = $this->restrictionSolicitudesAmistad( $html , $valores[ 'valorbool' ] );								break;								case '18':								$html = $this->restrictionEventsLast( $html , $valores[ 'valorbool' ] );								break;						case '19':								$html = $this->restrictionMiPerfil( $html , $valores[ 'valorbool' ] );								break;										case '20':								$html = $this->restrictionPhotoLast( $html , $valores[ 'valorbool' ] );								break;							case '21':								$html = $this->restrictionVideosLast( $html , $valores[ 'valorbool' ] );								break;																										}				}				unset( $valores );			}						/*si intentan acceder al componente de joomsocial directamente por la url			* sin estar registrados se redireccionan al home			*/			if( $userid == 0 && $componentload == 'com_community'){				$mainframe->redirect( 'index.php' );				return false;			}						//$html = $this->restrictionCommunityMembers( $html , false );						// Load only for site			//establecer el html con las restricciones			if( $mainframe->isSite() )				JResponse::setBody( $html );        }				/**		* Metodo restringe la opcion de Me gusta a las publicaciones		* @param { string } el html que se va ha cargar		* @param { bool } false = restringe el modulo; true = no lo restringe		*/		protected function restrictionLike( $htmlfil , $bool ){						$exprex = '/<span(\s*[\w\.\-\=\"\:\(\)\;\'\s]*)>(\s*)<a(\s+[\w\.\-\=\"\:\(\)\;\'\s]*)id(\s*)=(\s*)"like_id([0-9]{1,})"(\s+)href(\s*)=(\s*)"\#(like|unlike)"(\s+[\w\.\-\=\"\:\(\)\;\'\s]*)>[a-zA-Z0-9\s]+<\/a>(\s*)<\/span>/mi';			/*preg_match_all( $exprex , $htmlfil , $coincidencias );			var_dump($coincidencias);*/						if( !$bool )				$htmlfil = preg_replace( $exprex , '' , $htmlfil);						return $htmlfil;		}				/**		* Metodo restringe la opcion de comentar publicaciones		* @param { string } el html que se va ha cargar		* @param { bool } false = restringe el modulo; true = no lo restringe		*/		protected function restrictionComment( $htmlfil , $bool ){						$exprex = '/<span(\s*[\w\.\-\=\"\:\(\)\;\'\s]*)>(\s*)<a(\s+[\w\.\-\=\"\:\(\)\;\'\s]*)onclick(\s*)=(\s*)"joms\.miniwall\.show\((\s*)\'([0-9]{1,})\'(\s*),(\s*)this(\s*)\);(\s*)return(\s+)false;(\s*)"(\s*[\w\.\-\=\"\:\(\)\;\'\s]*)>[a-zA-Z0-9_\s]+<\/a>(\s*)<\/span>/mi';						/*preg_match_all( $exprex , $htmlfil , $coincidencias );			var_dump($coincidencias);*/						if( !$bool )				$htmlfil = preg_replace( $exprex , '' , $htmlfil);						return $htmlfil;		}				/**		* Metodo restringe las notificaciones en tiempo real		* @param { string } el html que se va ha cargar		* @param { bool } false = restringe el modulo; true = no lo restringe		*/		protected function restrictionNotificaciones( $htmlfil , $bool ){																			$exprex = '/<a(\s+[\w\.\-\=\"\:\(\)\;\'\s]*)href(\s*)=(\s*)"javascript:joms\.notifications\.showWindow\(\);"(\s+)title(\s*)=(\s*)"([a-zA-Z0-9\s])+"(\s*[\w\.\-\=\"\:\(\)\;\'\s]*)>(\s*)<i(\s+[\w\.\-\=\"\:\(\)\;\'\s]*)class(\s*)=(\s*)"(js\-icon\-globe)"(\s*[\w\.\-\=\"\:\(\)\;\'\s]*)>(\s*[a-zA-Z0-9_\s]*)<\/i>(\s*)<\/a>/mi';						/*preg_match_all( $exprex , $htmlfil , $coincidencias );			var_dump($coincidencias);*/						if( !$bool )				$htmlfil = preg_replace( $exprex , '' , $htmlfil);						return $htmlfil;		}				/**		* Metodo restringe el etiquetado de fotos		* @param { string } el html que se va ha cargar		* @param { bool } false = restringe el modulo; true = no lo restringe		*/		protected function restrictionPhotoTag( $htmlfil , $bool ){						/*'<div class="photoTagging visible-desktop"></div>'			*/																			$exprex = '/<div(\s+[\w\.\-\=\"\:\(\)\;\'\s]*)(class(\s*)=(\s*)"[\w\-\s]*photoTagging)/i';						preg_match_all( $exprex , $htmlfil , $coincidencias );			//var_dump($coincidencias); die;						if( !$bool ){				foreach( $coincidencias[0] as $actionvalida ){											/*					* se comprueba si no tiene el atributo y lo añade con su respectivo valor					* de lo contrario añado un valor ya al atributo clase					*/					if( !empty( $actionvalida ) ){						$htmlfil = str_ireplace( $actionvalida , $actionvalida.' quitarelm', $htmlfil);					}				}				unset( $actionvalida );				//$htmlfil = preg_replace( $exprex , '<div class="photoTagging visible-desktop"></div>' , $htmlfil);			}			return $htmlfil;		}				/**		* Metodo restringe la gestion de eventos		* @param { string } el html que se va ha cargar		* @param { bool } false = restringe el modulo; true = no lo restringe		*/		protected function restrictionEvents( $htmlfil , $bool ){						//<a href="/jooinworld_referidos/index.php?option=com_community&amp;view=events																	$exprex = '/((class(\s*)=(\s*)"[\w\-\s]+)"(\s+[\w\.\-\=\"\:\(\)\;\'\s]*))?(href(\s*)=(\s*)"[\/]?jooinworld_referidos\/index\.php\?option=com_community(\&|\&amp;)view=events).*?/'; 						preg_match_all( $exprex , $htmlfil , $coincidencias );			//var_dump($coincidencias);						if( !$bool ){				foreach( $coincidencias[0] as $pos=>$actionvalida ){											/*					* se comprueba si no tiene el atributo y lo añade con su respectivo valor					* de lo contrario añado un valor ya al atributo clase					*/					if( empty( $coincidencias[2][$pos] ) ){						$htmlfil = str_ireplace( $actionvalida , 'class="quitarelm" '.$actionvalida , $htmlfil);					}else{						$htmlfil = str_ireplace( $coincidencias[2][$pos] , $coincidencias[2][$pos]. ' quitarelm' , $htmlfil);					}				}				unset( $actionvalida );				//$htmlfil = preg_replace( $exprex , '<div>Su plan contratado no le proporciona acceso a este modulo</div>' , $htmlfil);			}						return $htmlfil;		}				/**		* Metodo restringe la gestion de grupos		* @param { string } el html que se va ha cargar		* @param { bool } false = restringe el modulo; true = no lo restringe		*/		protected function restrictionGroups( $htmlfil , $bool ){						$exprex = '/((class(\s*)=(\s*)"[\w\-\s]+)"(\s+[\w\.\-\=\"\:\(\)\;\'\s]*))?(href(\s*)=(\s*)"[\/]?jooinworld_referidos\/index\.php\?option=com_community(\&|\&amp;)view=groups).*?/';						preg_match_all( $exprex , $htmlfil , $coincidencias );			//var_dump($coincidencias); exit;			//echo htmlspecialchars($htmlfil); die;						if( !$bool ){				foreach( $coincidencias[0] as $pos=>$actionvalida ){											/*					* se comprueba si no tiene el atributo y lo añade con su respectivo valor					* de lo contrario añado un valor ya al atributo clase					*/					if( empty( $coincidencias[2][$pos] ) ){						$htmlfil = str_ireplace( $actionvalida , 'class="quitarelm" '.$actionvalida , $htmlfil);					}else{						$htmlfil = str_ireplace( $coincidencias[2][$pos] , $coincidencias[2][$pos]. ' quitarelm' , $htmlfil);					}				}				unset( $actionvalida );									//$htmlfil = preg_replace( $exprex , '' , $htmlfil);			}			return $htmlfil;		}				/**		* Metodo restringe la carga de fotos		* @param { string } el html que se va ha cargar		* @param { int } la cantidad de carga de fotos que permite el plan		*/		protected function loadPhotos( $htmlfil , $valor ){						$query = $this->db->getQuery( true );			$query->select( 'COUNT( * ) AS Filas ,  '. $this->db->quoteName( 'creator' ) );			$query->from( $this->db->quoteName( '#__community_photos' ) );			$query->where( $this->db->quoteName( 'creator' ) .' = '.$this->db->quote( $this->_my->id ) );			$query->group( $this->db->quoteName( 'creator' ) );						$this->db->setQuery( $query );						$result = $this->db->loadResultArray();						if ($this->db->getErrorNum()) {                JError::raiseError(500, $this->db->stderr());            }			$query->clear();						if( $result[ 0 ] >= ((int) $valor) ){							$exprex = '/((class(\s*)=(\s*)"[\w\-\s]+)"(\s+[\w\.\-\=\"\:\(\)\;\'\s]*))?((href|onclick)(\s*)=(\s*)"(javascript\:)?joms\.notifications\.showUploadPhoto\()/mi';								preg_match_all( $exprex , $htmlfil , $coincidencias );				//var_dump( $coincidencias ); die;				//recorro todas las coincidencias de los enlaces que abren pop-pap para cargar foto				foreach( $coincidencias[0] as $pos=>$actionvalida ){										/*					* se comprueba si no tiene el atributo y lo añade con su respectivo valor					* de lo contrario añado un valor ya al atributo clase					*/					if( empty( $coincidencias[2][$pos] ) ){						$htmlfil = str_ireplace( $actionvalida , 'class="quitarelm" '.$actionvalida , $htmlfil);					}else{						$htmlfil = str_ireplace( $coincidencias[2][$pos] , $coincidencias[2][$pos]. ' quitarelm' , $htmlfil);					}				}				unset( $actionvalida );							}						return $htmlfil;					}				/**		* Metodo restringe la carga de videos		* @param { string } el html que se va ha cargar		* @param { int } la cantidad de carga de videos que permite el plan		*/		protected function loadVideos( $htmlfil , $valor ){						$query = $this->db->getQuery( true );			$query->select( 'COUNT( * ) AS Filas ,  '. $this->db->quoteName( 'creator' ) );			$query->from( $this->db->quoteName( '#__community_videos' ) );			$query->where( $this->db->quoteName( 'creator' ) .' = '.$this->db->quote( $this->_my->id ) );			$query->group( $this->db->quoteName( 'creator' ) );						$this->db->setQuery( $query );						$result = $this->db->loadResultArray();						if ($this->db->getErrorNum()) {                JError::raiseError(500, $this->db->stderr());            }			$query->clear();						if( $result[ 0 ] >= ((int) $valor) ){								//<a href="javascript: void(0);" onclick="joms.videos.addVideo()">				$exprex = '/((class(\s*)=(\s*)"[\w\-\s]+)"(\s+[\w\.\-\=\"\:\(\)\;\'\s]*))?((href|onclick)(\s*)=(\s*)"(javascript\:)?joms\.videos\.addVideo\()/mi';								preg_match_all( $exprex , $htmlfil , $coincidencias );				//var_dump( $coincidencias ); die;				//recorro todas las coincidencias de los enlaces que abren pop-pap para cargar videos				foreach( $coincidencias[0] as $pos=>$actionvalida ){										/*					* se comprueba si no tiene el atributo y lo añade con su respectivo valor					* de lo contrario añado un valor ya al atributo clase					*/					if( empty( $coincidencias[2][$pos] ) ){						$htmlfil = str_ireplace( $actionvalida , 'class="quitarelm" '.$actionvalida , $htmlfil);					}else{						$htmlfil = str_ireplace( $coincidencias[2][$pos] , $coincidencias[2][$pos]. ' quitarelm' , $htmlfil);					}				}				unset( $actionvalida );							}						return $htmlfil;					}				/**		* Metodo restringe comentar las fotos cargadas		* @param { string } el html que se va ha cargar		* @param { bool } false = restringe el modulo; true = no lo restringe		*/		protected function restrictionPhotoComment( $htmlfil , $bool ){						/*<div id="community-photo-walls" class="cWall-Form">*/						//'/<div(\s+[\w\.\-\=\"\:\(\)\;\'\s]*)(class(\s*)=(\s*)"[\w\-\s]*photoTagging)/i';						$exprex = '/(<div(\s+[\w\.\-\=\"\:\(\)\;\'\s]*)id(\s*)=(\s*)"community\-photo\-walls"(\s+[\w\.\-\=\"\:\(\)\;\'\s]*)class(\s*)=(\s*)"[\w\-\s]*cWall\-Form)/mi';						preg_match_all( $exprex , $htmlfil , $coincidencias );			//var_dump($coincidencias); die;			//echo htmlspecialchars($htmlfil); die;						if( !$bool ){				foreach( $coincidencias[0] as $actionvalida ){											/*					* se comprueba si no tiene el atributo y lo añade con su respectivo valor					* de lo contrario añado un valor ya al atributo clase					*/					if( !empty( $actionvalida ) ){						$htmlfil = str_ireplace( $actionvalida , $actionvalida.' quitarelm' , $htmlfil);					}				}				unset( $actionvalida );				}			return $htmlfil;		}				/**		* Metodo restringe la opcion de control de privacidad		* @param { string } el html que se va ha cargar		* @param { bool } false = restringe el modulo; true = no lo restringe		*/		protected function restrictionPrivacy( $htmlfil , $bool ){						/*<li><a href="/jooinworld_referidos/index.php?option=com_community&amp;view=profile&amp;task=privacy&amp;Itemid=122">																Privacidad</a></li>*/			$exprex = '/<li(\s*[\w\.\-\=\"\:\(\)\;\'\s]*)>(\s)*<a(\s+[\w\.\-\=\"\:\(\)\;\'\s]*)href(\s*)=(\s*)"[\/]?jooinworld_referidos\/index\.php\?option=com_community(\&|\&amp;)view=profile(\&|\&amp;)task=privacy(\&|\&amp;)?(\s*[\w\.\-\=\"\:\(\)\;\&\'\s]*)>[a-zA-Z0-9_áéíóúÁÉÍÓÚñÑ\-\s]+<\/a>(\s)*<\/li>/mi';			/*preg_match_all( $exprex , $htmlfil , $coincidencias );			var_dump($coincidencias);*/						if( !$bool )				$htmlfil = preg_replace( $exprex , '' , $htmlfil);						return $htmlfil;		}				/**		* Metodo restringe la opcion de buscar amigos		* @param { string } el html que se va ha cargar		* @param { bool } false = restringe el modulo; true = no lo restringe		*/		protected function restrictionSearchFriends( $htmlfil , $bool ){						/*<li class=""><a href="/jooinworld_referidos/index.php?option=com_community&amp;view=search&amp;Itemid=122">																Buscar</a></li>*/			$exprex = '/<li(\s*[\w\.\-\=\"\:\(\)\;\'\s]*)>(\s)*<a(\s+[\w\.\-\=\"\:\(\)\;\'\s]*)href(\s*)=(\s*)"[\/]?jooinworld_referidos\/index\.php\?option=com_community(\&|\&amp;)view=search(\&|\&amp;)?(\s*[\w\.\-\=\"\:\(\)\;\&\'\s]*)>[a-zA-Z0-9_áéíóúÁÉÍÓÚñÑ\-\s]+<\/a>(\s)*<\/li>/mi';			/*preg_match_all( $exprex , $htmlfil , $coincidencias );			var_dump($coincidencias);*/						if( !$bool )				$htmlfil = preg_replace( $exprex , '' , $htmlfil);						return $htmlfil;		}				/**		* Metodo restringe la opcion de invitar amigos		* @param { string } el html que se va ha cargar		* @param { bool } false = restringe el modulo; true = no lo restringe		*/		protected function restrictionInvitarAmigos( $htmlfil , $bool ){						/*<li class=""> <a href="/jooinworld_referidos/index.php?option=com_community&amp;view=friends&amp;task=invite&amp;Itemid=122">																Invitar</a></li>>*/			$exprex = '/<li(\s*[\w\.\-\=\"\:\(\)\;\'\s]*)>(\s)*<a(\s+[\w\.\-\=\"\:\(\)\;\'\s]*)href(\s*)=(\s*)"[\/]?jooinworld_referidos\/index\.php\?option=com_community(\&|\&amp;)view=friends(\&|\&amp;)task=invite(\&|\&amp;)?(\s*[\w\.\-\=\"\:\(\)\;\&\'\s]*)>[a-zA-Z0-9_áéíóúÁÉÍÓÚñÑ\-\s]+<\/a>(\s)*<\/li>/mi';			/*preg_match_all( $exprex , $htmlfil , $coincidencias );			var_dump($coincidencias);*/						if( !$bool )				$htmlfil = preg_replace( $exprex , '' , $htmlfil);						return $htmlfil;		}				/**		* Metodo restringe de la bandeja de mensajes		* @param { string } el html que se va ha cargar		* @param { bool } false = restringe el modulo; true = no lo restringe		*/		protected function restrictionBadejaMensajes( $htmlfil , $bool ){						/*<li><a class="menu-icon" href="/jooinworld_referidos/index.php?option=com_community&amp;view=inbox&amp;Itemid=122" onclick="joms.notifications.showInbox();return false;" title="Nuevo Mensaje">			<i class="js-icon-chat"></i>							<span class="js-counter">2</span></a></li>*/						$exprex = '/<li(\s*[\w\.\-\=\"\:\(\)\;\'\s]*)>(\s)*<a(\s+[\w\.\-\=\"\:\(\)\;\'\s]*)href(\s*)=(\s*)"[\/]?jooinworld_referidos\/index\.php\?option=com_community(\&|\&amp;)view=inbox(\s*[áéíóúÁÉÍÓÚñÑ\w\.\-\=\"\:\(\)\;\&\'\s]*)>(\s)*(<i(\s*[\w\.\-\=\"\:\(\)\;\'\s]*)>.*?<\/i>)?(\s)*(<span(\s*[\w\.\-\=\"\:\(\)\;\'\s]*)>[0-9\s]*<\/span>)?(\s)*<\/a>(\s)*<\/li>/mi'; 						//preg_match_all( $exprex , $htmlfil , $coincidencias );			//var_dump($coincidencias); die;			//echo htmlspecialchars( $htmlfil );						if( !$bool )				$htmlfil = preg_replace( $exprex , '' , $htmlfil);						return $htmlfil;		}				/**		* Metodo restringe el modulo de solicitudes de amistad		* @param { string } el html que se va ha cargar		* @param { bool } false = restringe el modulo; true = no lo restringe		*/		protected function restrictionSolicitudesAmistad( $htmlfil , $bool ){						/*<li class="visible-desktop">						<a class="menu-icon" href="/jooinworld_referidos/index.php?option=com_community&amp;view=friends&amp;task=pending&amp;Itemid=122" onclick="joms.notifications.showRequest();return false;" title="Solicitudes de Conexión">							<i class="js-icon-users"></i>													</a>					</li>*/			$exprex = '/<li(\s*[\w\.\-\=\"\:\(\)\;\'\s]*)>(\s)*<a(\s+[\w\.\-\=\"\:\(\)\;\'\s]*)href(\s*)=(\s*)"[\/]?jooinworld_referidos\/index\.php\?option=com_community(\&|\&amp;)view=friends(\&|\&amp;)task=pending(\s*[áéíóúÁÉÍÓÚñÑ\w\.\-\=\"\:\(\)\;\&\'\s]*)>(\s)*(<i(\s*[\w\.\-\=\"\:\(\)\;\'\s]*)>.*?<\/i>)?(\s)*<\/a>(\s)*<\/li>/mi'; 						//preg_match_all( $exprex , $htmlfil , $coincidencias );			//var_dump($coincidencias); die;			//echo htmlspecialchars( $htmlfil );						if( !$bool )				$htmlfil = preg_replace( $exprex , '' , $htmlfil);						return $htmlfil;		}				/**		* Metodo restringe el modulo de los ultimos eventos		* @param { string } el html que se va ha cargar		* @param { bool } false = restringe el modulo; true = no lo restringe		*/		protected function restrictionEventsLast( $htmlfil , $bool ){						//<div class="moduletable app-box eventos">						$exprex = '/(class\s*=\s*"moduletable\s+app\-box\s+eventos[\w\-\s]*)"/i'; 						preg_match_all( $exprex , $htmlfil , $coincidencias );			//var_dump($coincidencias); die;			//echo htmlspecialchars( $htmlfil );						if( !$bool ){				foreach($coincidencias[1] as $actionvalida){										if( !empty($actionvalida) )						$htmlfil = str_ireplace( $actionvalida , $actionvalida.' quitarelm' , $htmlfil);				}				unset($actionvalida);			}						return $htmlfil;		}				/**		* Metodo restringe el modulo de los ultimas fotos		* @param { string } el html que se va ha cargar		* @param { bool } false = restringe el modulo; true = no lo restringe		*/		protected function restrictionPhotoLast( $htmlfil , $bool ){						//<div class="moduletable app-box eventos">						$exprex = '/(class\s*=\s*"moduletable\s+app\-box\s+fotos[\w\-\s]*)"/i'; 						preg_match_all( $exprex , $htmlfil , $coincidencias );			//var_dump($coincidencias); die;			//echo htmlspecialchars( $htmlfil );						if( !$bool ){				foreach($coincidencias[1] as $actionvalida){										if( !empty($actionvalida) )						$htmlfil = str_ireplace( $actionvalida , $actionvalida.' quitarelm' , $htmlfil);				}				unset($actionvalida);			}						return $htmlfil;		}				/**		* Metodo restringe el modulo de los ultimas videos		* @param { string } el html que se va ha cargar		* @param { bool } false = restringe el modulo; true = no lo restringe		*/		protected function restrictionVideosLast( $htmlfil , $bool ){						//<div class="moduletable app-box eventos">						$exprex = '/(class\s*=\s*"moduletable\s+app\-box\s+videos[\w\-\s]*)"/i'; 						preg_match_all( $exprex , $htmlfil , $coincidencias );			//var_dump($coincidencias); die;			//echo htmlspecialchars( $htmlfil );						if( !$bool ){				foreach($coincidencias[1] as $actionvalida){										if( !empty($actionvalida) )						$htmlfil = str_ireplace( $actionvalida , $actionvalida.' quitarelm' , $htmlfil);				}				unset($actionvalida);			}						return $htmlfil;		}				/**		* Metodo restringe el modulo de miembros de la comunidad		* @param { string } el html que se va ha cargar		* @param { bool } false = restringe el modulo; true = no lo restringe		*/		protected function restrictionCommunityMembers( $htmlfil , $bool ){						//<div class="moduletable app-box eventos">						$exprex = '/(class\s*=\s*"moduletable\s+app\-box\s+com\-members[\w\-\s]*)"/i'; 						preg_match_all( $exprex , $htmlfil , $coincidencias );			//var_dump($coincidencias); die;			//echo htmlspecialchars( $htmlfil );						if( !$bool ){				foreach($coincidencias[1] as $actionvalida){										if( !empty($actionvalida) )						$htmlfil = str_ireplace( $actionvalida , $actionvalida.' quitarelm' , $htmlfil);				}				unset($actionvalida);			}						return $htmlfil;		}				/**		* Metodo restringe el modulo de mi perfil		* @param { string } el html que se va ha cargar		* @param { bool } false = restringe el modulo; true = no lo restringe		*/		protected function restrictionMiPerfil( $htmlfil , $bool ){						/*<li class=""><a href="/jooinworld_referidos/index.php?option=com_community&amp;view=profile&amp;task=uploadAvatar&amp;Itemid=122">																Cambiar Foto Perfil</a><ul>sasasa</ul></li>*/																			$exprex = '/<li(\s*[\w\.\-\=\"\:\(\)\;\'\s]*)>(\s)*<a(\s+[\w\.\-\=\"\:\(\)\;\'\s]*)href(\s*)=(\s*)"[\/]?jooinworld_referidos\/index\.php\?option=com_community(\&|\&amp;)view=profile(\&|\&amp;)?(\s*[\w\.\-\=\"\:\(\)\;\&\'\s]*)>[a-zA-Z0-9_áéíóúÁÉÍÓÚñÑ\-\s]+<\/a>(\s)*(<ul(\s*[\w\.\-\=\"\:\(\)\;\'\s]*)>.*?<\/ul>)?(\s)*<\/li>/mi';			/*preg_match_all( $exprex , $htmlfil , $coincidencias );			var_dump($coincidencias);*/						if( !$bool )				$htmlfil = preg_replace( $exprex , '' , $htmlfil);						return $htmlfil;		}				/**		* Metodo restringe la carga de fotos		* @param { string } el html que se va ha cargar		* @param { int } la cantidad de carga de fotos que permite el plan		*/		protected function loadPhotosWall( $htmlfil , $valor ){						$query = $this->db->getQuery( true );			$query->select( 'COUNT( * ) AS Filas ,  '. $this->db->quoteName( 'creator' ) );			$query->from( $this->db->quoteName( '#__community_photos' ) );			$query->where( $this->db->quoteName( 'creator' ) .' = '.$this->db->quote( $this->_my->id ) );			$query->group( $this->db->quoteName( 'creator' ) );						$this->db->setQuery( $query );						$result = $this->db->loadResultArray();						if ($this->db->getErrorNum()) {                JError::raiseError(500, $this->db->stderr());            }			$query->clear();						if( $result[ 0 ] >= ((int) $valor) ){								/*<li class="creator type-photo active" type="photo">*/				$exprex = '/(class(\s*)=(\s*)"[\w\-\s]*type-photo[\w\-\s]*)"/i';								preg_match_all( $exprex , $htmlfil , $coincidencias );				//var_dump( $coincidencias ); die;				//recorro todas las coincidencias de los enlaces que abren pop-pap para cargar foto				foreach( $coincidencias[1] as $actionvalida ){										/*					* se comprueba si no tiene el atributo y lo añade con su respectivo valor					* de lo contrario añado un valor ya al atributo clase					*/					if( !empty( $actionvalida ) )						$htmlfil = str_ireplace( $actionvalida , $actionvalida.' quitarelm' , $htmlfil);				}				unset( $actionvalida );							}						return $htmlfil;		}				/**		* Metodo restringe la carga de videos		* @param { string } el html que se va ha cargar		* @param { int } la cantidad de carga de videos que permite el plan		*/		protected function loadVideosWall( $htmlfil , $valor ){						$query = $this->db->getQuery( true );			$query->select( 'COUNT( * ) AS Filas ,  '. $this->db->quoteName( 'creator' ) );			$query->from( $this->db->quoteName( '#__community_videos' ) );			$query->where( $this->db->quoteName( 'creator' ) .' = '.$this->db->quote( $this->_my->id ) );			$query->group( $this->db->quoteName( 'creator' ) );						$this->db->setQuery( $query );						$result = $this->db->loadResultArray();						if ($this->db->getErrorNum()) {                JError::raiseError(500, $this->db->stderr());            }			$query->clear();						if( $result[ 0 ] >= ((int) $valor) ){								//<li class="creator type-video" type="video">				$exprex = '/(class(\s*)=(\s*)"[\w\-\s]*type-video[\w\-\s]*)"/i';								preg_match_all( $exprex , $htmlfil , $coincidencias );				//var_dump( $coincidencias ); die;				//recorro todas las coincidencias de los enlaces que abren pop-pap para cargar videos				foreach( $coincidencias[1] as $actionvalida ){										/*					* se comprueba si no tiene el atributo y lo añade con su respectivo valor					* de lo contrario añado un valor ya al atributo clase					*/					if( !empty( $actionvalida ) )						$htmlfil = str_ireplace( $actionvalida , $actionvalida.' quitarelm' , $htmlfil);				}				unset( $actionvalida );							}						return $htmlfil;		}		    }}