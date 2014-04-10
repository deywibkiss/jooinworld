/** * Utilities for seg component *  *  */( function( $, window, document ){		var Misc = function( a ){				// attributes or global vars here			};		Misc.prototype = {						/**			 * Inializes the functions when DOM ready			 */						initialize: function(){								this.closeModalWindow();							}						/**			 *  Serialize form into json format			 *  			 *  @param { string } name class or id of the html element to embed the loader			 *  @return { object } form into json			 *  			 */		,	formToJson: function( selector ){							var o = {};			    var a = $( selector ).serializeArray();			    			    $.each( a, function() {			        if ( o[ this.name ] !== undefined ) {			            if ( ! o[this.name].push ) {			                o[ this.name ] = [ o[ this.name ] ];			            }			            			            o[ this.name ].push( this.value || '' );			            			        } else {			            o[ this.name ] = this.value || '';			        }			    });			    			    return o;								}			       /**	         * Helps in the process of making a ajax requests	         *	         * @param { object } Options for configuring the ajax request	         * @param { object } data object to be sent	         */		,	ajaxHandler: function( options, data ) {		             var result	             ,   defaults = {	                     type: 'post'	                 ,   url: 'index.php'	                 ,   data: data	                 ,   async: false	                 ,   success: function( data ) {	                             result = data;	                     }		                 ,   error: function ( XMLHttpRequest, textStatus, errorThrown ) {	                             console.log( "error :" + XMLHttpRequest.responseText );	                     }	                 }		             // Merge defaults and options	             options = $.extend( {}, defaults, options );		             // Do the ajax request	             $.ajax( options );		             // Return the response object	             return result;		        }	        /**            * Given an array of required fields, this function            * checks whether the second argument have them            */        ,   validateEmptyFields: function( required, objectData, errors ) {                $.each( required, function( key, value ) {                    if ( objectData[ value ] == null || objectData[ value ] == "" ) {                        errors.push( value );                    }                });                return errors;            }            /**			* Given an array of required fields, this function			* checks whether the second argument have them			*/		,   validateEmptyObjectAttrs: function( _object, errors ) {				$.each( _object, function( key, value ) {					if ( value == null || value == "" ) {						errors.push( key );					}				});				return errors;			}            /**			*			* Validate only numbers			* @param { string } the string to validate			* 			*/		,	justNumbers: function( value ){				var pattern = /^\d+$/				, 	exp = new RegExp( pattern );				if( typeof value == 'undefined' )					return false;				return exp.test( value );			}			/**			*			* Validate only letters			* @param { string } the string to validate			* 			*/		,	justLetters: function( value ){				var pattern = /^[ñA-Za-z _]*[ñA-Za-z][ñA-Za-z _]*$/				, 	exp = new RegExp( pattern );				if( typeof value == 'undefined' )					return false;				return exp.test( value );			}			/**			* Converts latin string to anglo			*			* @param { string } the string to be sanitized			* @param { bool } numbers are allowed or not			* @param { bool } special characters are allowed or not			* @param { bool } blank spaces are allowed or not			* @param { string } blank spaces are allowed or not			* @param { string } blank spaces are replaced to			* @return { string } the string sanitized			*			*/		,	latinToAnglo: function( value, allowNumbers, allowSpecial, allowSpaces, replace ){				if( typeof replace == 'undefined' || replace == null )					replace = '';				value = value.replace(/[ÀÁÂÃÄÅ]/,"A");			    value = value.replace(/[àáâãäå]/,"a");			    value = value.replace(/[ÈÉÊË]/,"E");			    value = value.replace(/[èéêë]/,"e");			    value = value.replace(/[íìîï]/,"i");			    value = value.replace(/[ÍÌÎÏ]/,"I");			    value = value.replace(/[óòôö]/,"o");			    value = value.replace(/[ÒÓÔÖ]/,"O");			    value = value.replace(/[úùûü]/,"u");			    value = value.replace(/[ÚÙÛÜ]/,"U");			    value = value.replace(/[çÇ]/,"c");			    value = value.replace(/[ñ]/,"n");			    value = value.replace(/[Ñ]/,"N");			    if( ! allowNumbers )			    	value = value.replace(/[1234567890]/g, '');			    if( ! allowSpecial )			    	value = value.replace(/[&\/\\#,+()$~%.'":*?<>{}]/g, '' );			    if( ! allowSpaces )			    	value = value.replace( /[  ]/g, replace);						return value;			}        	/**            * Check whether an string is a correct email            * @param { str } String to test            * @return { bool }            */        ,   isEmail: function( string ) {                var emailExpression = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;                return emailExpression.test( string );            }            /**            * Sets a countdown            *            * @param { object } arguments to set: count, limit, selector, callback            * @return { function } the callback passed, return otherwise            *              */        ,	setCountdown: function( args ){				var counter=setInterval( reverse, 1000); //1000 will  run it every 1 second				function reverse(){										args.count = args.count - 1;					if ( args.count <= args.limit	 ) {												clearInterval( counter);						if ( args.callback !== null ) {                            args.callback.call();                        }						return;					}					$( args.selector ).text( args.count );				}        	}        	/**        	* Sets a countdown a display the text in a HTML Object, triggers a function callback when reaches the limit        	*        	* @param { object } args with data {										duration: ( int ) duration in milliseconds					,	interval: ( int ) interval of the count down in milliseconds					,	limit: ( int ) limit until callback executes					,	selector: ( string ) jQuery selector string					,	callback: ( function ) function callback        		}        	*        	* @return { function } Callback function        	*        	*/        ,	humanCountDown: function( args ){        		var counter = setInterval( humanReverse, 1000)				,	_this = this;				function humanReverse(){					args.duration = args.duration - args.interval;					var mom = moment( args.duration ).format( 'mm:ss' );					if ( args.duration < args.limit ) {												clearInterval( counter);						if ( args.callback !== null ) {                            args.callback.call();                        }						return;					}        			$( args.selector ).text( mom );				}				        	}    	,	showNotification: function( type, message, time, callback ){								    		$( '.global-notification' ).removeClass( 'error, success, warning, info' );	    		$( '.global-notification' ).addClass( type );	    		$( '.global-notification' ).text( message );	    		$( '.global-notification' ).fadeIn();	    		this.closeOnClickOut( '.global-notification' );								if( time != 0 ){					if( typeof callback != 'undefined' ){						setTimeout( function(){ 							$( '.global-notification' ).fadeOut();							callback.call();						}, time )						return;					}					setTimeout( function(){ $( '.global-notification' ).fadeOut(); }, time )					return;	    							}    		}    		/**        	* validates if object is empty        	*        	*/        ,	isEmptyObject: function( obj ){        		// Speed up calls to hasOwnProperty				var hasOwnProperty = Object.prototype.hasOwnProperty;			    // null and undefined are "empty"			    if (obj == null) return true;			    // Assume if it has a length property with a non-zero value			    // that that property is correct.			    if (obj.length && obj.length > 0)    return false;			    if (obj.length === 0)  return true;			    // Otherwise, does it have any properties of its own?			    // Note that this doesn't handle			    // toString and toValue enumeration bugs in IE < 9			    for (var key in obj) {			        if (hasOwnProperty.call(obj, key)) return false;			    }			    return true;        	}			/**			* Hides an element when it is clicked outiside			* @param { string } string for the jQuery selector like: ".my-class"			* @return { null }			*/		,   closeOnClickOut: function( selector, callback ) {				$( document ).mouseup( function( e ) {					if ( ! $( selector ).is( ":visible" ) ) {						return;					}					if ( $( selector ).has( e.target ).length === 0 ) {						$( selector ).hide();						if ( callback != null ) {							callback.call();						}					}				});			}			/**			* Sets dots in numbers			*			* @param { numeric } the number to be changed			* @param { numeric } how many decimals do you want to display for? e.g 1.000.00 ( two decimals )			* @param { string } decimals character separator			* @param { string } thousands character separator			* @return { string } the number formatted			*			*/		,	numberDots: function( num, decimals, decimalSeparator, thousandSeparator ){				num = parseInt( num );				var number = new String( num );				var result = '';				if( typeof decimals == 'undefined' )					decimals = 2;				if( typeof decimalSeparator == 'undefined' )					decimalSeparator = '.';				if( typeof thousandSeparator == 'undefined' )					thousandSeparator = '.';				while( number.length > 3 ){				 	result = thousandSeparator + number.substr(number.length - 3) + result;				 	number = number.substring(0, number.length - 3);				}				result = number + result;				if( decimals != 0 ){					result += decimalSeparator					for( var i = 0; i < decimals; i++ ){						result += '0';					}				}				return result;			}			/**			* Clear all window intervals started previously			*			*/		,	clearAllIntervals: function(){				var interval_id = window.setInterval("", 9999);				// Get a reference to the last interval +1				for (var i = 1; i < interval_id; i++)				    window.clearInterval( i );			}			/**			* Show modal window and render a template, runs a callback function too			*			* @param { object } configuration object {								  @param { string } modal header title				, @param { string } html content				, @param { numeric } width				, @param { numeric } height				, @param { function } a callback function to be called after show			}			*			*/		,	showModalWindow: function( config ){				$( '.celu-modal-box .header .modal-title' ).text( config.title );				$( '.celu-modal-box .body' ).html( config.content );				// set dimensions				if( typeof config.width != 'undefined' ){					$( '.celu-modal-box' ).width( config.width );					$( '.celu-modal-box' ).css( 'margin-left', '-' + ( config.width / 2 ) + 'px' );				}				if( typeof config.height != 'undefined' ){					$( '.celu-modal-box' ).height( config.height );					$( '.celu-modal-box' ).css( 'min-height', config.height );					$( '.celu-modal-box' ).css( 'margin-top', '-' + ( config.height / 2 ) + 'px' );				}else{					$( '.celu-modal-box' ).css( 'height', 'auto' );					$( '.celu-modal-box' ).css( 'min-height', 'auto' );					var height = $( '.celu-modal-box' ).height();										$( '.celu-modal-box' ).css( 'margin-top', '-' + ( height / 2 ) + 'px' );				}				$( '.celu-over-screen' ).fadeIn();				$( '.celu-modal-box' ).fadeIn();				// runs a callback after show				if( typeof callback != 'undefined' ){					callback.call();				}			}			/**			* Close modal window 			*			*/		,	closeModalWindow: function(){				$( document ).ready( function(){					$( 'body' ).delegate( '.close-modal-button', 'click', function( e ){						e.preventDefault();						$( '.celu-modal-box .body' ).html( '' );						$( '.celu-over-screen' ).fadeOut();						$( '.celu-modal-box' ).fadeOut();					});					$( '.celu-over-screen' ).click( function( e ){						$( '.celu-modal-box .body' ).html( '' );						$( '.celu-over-screen' ).fadeOut();						$( '.celu-modal-box' ).fadeOut();					});				});								// Esc key press				$( document ).keyup( function( e ){					var keyCode = e.which || e.keyCode;					if( keyCode == 27 ){						$( '.celu-modal-box .body' ).html( '' );						$( '.celu-over-screen' ).fadeOut();						$( '.celu-modal-box' ).fadeOut();					}				});			}			/**			* Close modal window 			*			*/		,	_closeModalWindow: function(){				$( '.celu-modal-box .body' ).html( '' );				$( '.celu-over-screen' ).fadeOut();				$( '.celu-modal-box' ).fadeOut();			}			/**			*			*			*/		,	htmlEntities: function( str ) {			    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');			}			/**			* Parse a json string to JS format			*			*/		,	jsonToObject: function( text ){				text = text.replace(/'/g, '"');				var object = JSON.parse( text, function (key, value) {					    var type;				    if (value && typeof value === 'object') {				        type = value.type;				        if (typeof type === 'string' && typeof window[type] === 'function') {				            return new (window[type])(value);				        }				    }				    return value;				});				return object;			}			/**			* Gets any elements from array in random and withot repeat			*			* @param { array } array of elements			* @param { numeric } number of items to be get			*			* 			*/		,	randomFrom: function ( array, n ) {			    var at = 0;			    var tmp, current, top = array.length;			    if(top) while(--top && at++ < n) {			        current = Math.floor(Math.random() * (top - 1));			        tmp = array[current];			        array[current] = array[top];			        array[top] = tmp;			    }			    return array.slice(-n);			}			/**			* Customize some functions from Date			*			*/		,	customizeDate: function(){				Date.prototype.getHoursTwoDigits = function(){				    var retval = this.getHours();				    if (retval < 10){				        return ("0" + retval.toString());				    }else {				        return retval.toString();				    }				}				Date.prototype.getMinutesTwoDigits = function(){				    var retval = this.getMinutes();				    if (retval < 10){				        return ("0" + retval.toString());				    }else {				        return retval.toString();				    }				}			}			/**			* Redirect to an specific url or refresh the page			* @param { string } the url to be redirect to			*			*/		,	redirect: function( url ){				if( typeof url != '' ){					window.location.reload();				}				window.location = url;			}			/**			* Parses string formatted as YYYY-MM-DD to a Date object.			* If the supplied string does not match the format, an 			* invalid Date (value NaN) is returned.			* @param {string} dateStringInRange format YYYY-MM-DD, with year in			* range of 0000-9999, inclusive.			* @return {Date} Date object representing the string.			*/		,	parseISO8601: function ( dateStringInRange ) {				var isoExp = /^\s*(\d{4})-(\d\d)-(\d\d)\s*$/,				date = new Date(NaN), month,				parts = isoExp.exec(dateStringInRange);				if(parts) {					month = +parts[2];					date.setFullYear(parts[1], month - 1, parts[3]);										if(month != date.getMonth() + 1) {						date.setTime(NaN);					}				}				return date;			}						/**			* random array			*			* @param { Array } the number to be changed			* @return { Array } the number with dots			*			*/		,	shuffle: function(array){			  var j, temp;			  for(var i = array.length - 1; i > 0; i--){				j = Math.floor(Math.random() * (i + 1));				temp = array[i];				array[i] = array[j];				array[j] = temp;			  }			  return array;			}		};			window.Misc = new Misc();	window.Misc.initialize();		})( jQuery, this, this.document, undefined );