var farbtastic;

(function($){
	var pickColor = function(a) {
		farbtastic.setColor(a);
		$('#ticker-color').val(a);
		$('#ticker-color-example').css('background-color', a);
	};

	$(document).ready( function() {
		$('#ticker-default-color').wrapInner('<a href="#" />');

		farbtastic = $.farbtastic('#tickerColorPickerDiv', pickColor);

		pickColor( $('#ticker-color').val() );

		$('.pickcolor').click( function(e) {
			$('#tickerColorPickerDiv').show();
			e.preventDefault();
		});

		$('#ticker-color').keyup( function() {
			var a = $('#ticker-color').val(),
				b = a;

			a = a.replace(/[^a-fA-F0-9]/, '');
			if ( '#' + a !== b )
				$('#ticker-color').val(a);
			if ( a.length === 3 || a.length === 6 )
				pickColor( '#' + a );
		});

		$(document).mousedown( function() {
			$('#tickerColorPickerDiv').hide();
		});

		$('#ticker-default-color a').click( function(e) {
			pickColor( '#' + this.innerHTML.replace(/[^a-fA-F0-9]/, '') );
			e.preventDefault();
		});

	});
})(jQuery);
