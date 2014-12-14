(function($){

	if( typeof $ === 'undefined' )
		return false;


	var phoenix_uploader;
	$(document).on( 'click', '.phoenix-form-add-media', function(e){

		e.preventDefault();

		//If the uploader object has already been created, reopen the dialog
		if (phoenix_uploader) {
			phoenix_uploader.open();
			return;
		}

		//Extend the wp.media object
		phoenix_uploader = wp.media.frames.file_frame = wp.media({
			title: 'Choose Image',
			button: {
				text: 'Choose Image'
			},
			multiple: false
		});

		//When a file is selected, grab the URL and set it as the text field's value
		phoenix_uploader.on('select', function() {
			var attachment = phoenix_uploader.state().get( 'selection' ).first().toJSON();
			console.log( attachment );
		});

		//Open the uploader dialog
		phoenix_uploader.open();

		return false;
	} );

})(jQuery);