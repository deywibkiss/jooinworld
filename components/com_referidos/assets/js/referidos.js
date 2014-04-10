/**
*
* UserModel for { Referidos }
*
*/

( function( $, window, document, Utilities ){

	var ReferidosModel = function( a ){


	};

	ReferidosModel.prototype = {


			/**
			 * Funcion que obtiene los referidos y los muestra en forma de organigrama
			 *
			 * @param {}
			 * @return {}
			 */
			showViewArbol: function( success, _data ){

				var aOptions = {
						dataType: "json"
					,	async: true
					,   success: success
				}
				
				,   aData = {
						option: "com_referidos"
					,	task: "referido.loadItems"
					,   data: _data
				}

				// Pass the params to ajaxHandlerUpdated which will do the ajax request
				return Utilities.ajaxHandler( aOptions, aData );
				
			}

	};

	// Expose to global scope
	window.ReferidosModel = new ReferidosModel();

})( jQuery, this, this.document, this.Misc, undefined );