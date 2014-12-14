(function( $ ){

	if( typeof $ == 'undefined' )
		throw new Error( "jQuery is not loaded." );


	$.fn.PhoenixDotDotDot = function( arg ){

		if( arg === 'stop' ){
			clearInterval(
				$( this ).text(
					$( this ).data( '_phoenixdotdotdot_on_', 0 ).css( 'width', 'inherit' ).data( '_phoenixdotdotdot_default_text_' )
				).data( '_phoenixdotdotdot_interval_' )
			);
			return this;
		}

		var options = $.extend(
			{
				maxDots: 4,
				speed  : 400,
				text   : ''
			},
			typeof args === 'object' ? args : {}
		);

		return this.each( function(){

			var $this = $( this );

			if( $this.data( '_phoenixdotdotdot_on_' ) == 1 )
				return this;

			$this.data( '_phoenixdotdotdot_default_text_', $this.text() );
			$this.data( '_phoenixdotdotdot_on_', 1 );
			$this.width( $this.width() );

			$this.text( options.text + '.' );
			var dots = 1;
			$this.data( '_phoenixdotdotdot_interval_', setInterval( function(){

				if( ++dots > options.maxDots ){
					$this.text( '.' );
					dots = 1;
				} else{
					$this.append( '.' );
				}

			}, options.speed ) );


		} );

	};


	phoenix.form = {};


	phoenix.form.openMediaFrame = function( target ){
		var mediaFrame;
		(function(){
			if( mediaFrame ){
				mediaFrame.open();
				return;
			}
			mediaFrame = wp.media.frames.file_frame = wp.media( {
				title   : 'Choose Image',
				button  : {
					text: 'Choose Image'
				},
				multiple: false
			} );
			mediaFrame.on( 'select', function(){
				var attachment = mediaFrame.state().get( 'selection' ).first().toJSON();
				$( target ).val( attachment.url );
			} );
			mediaFrame.open();
		})();
	};


})( jQuery );

jQuery( function( $ ){


	var multiSelect = function(){
		var
			$container = $( '.phoenix-form-group.multiselect' );
		$container.on( 'click', '.item', function(){
			var
				$this = $( this ),
				$container = $this.parent(),
				$isMulti = $container.hasClass( 'multi-select' ),
				$checkbox = $this.find( 'input' ),
				$checked = !$this.hasClass( 'checked' );
			if( !$isMulti ){
				$container.find( '.checked' ).removeClass( 'checked' );
				$container.find( ':checkbox' ).val( 'unchecked' );
			}
			if( $checked ){
				$this.addClass( 'checked' );
			}
			else{
				$this.removeClass( 'checked' );
			}
			$checkbox.val( $checked ? 'checked' : 'unchecked' );

		} );
		$container.find( '.items.sortable' ).sortable();
	};
	multiSelect();


} );