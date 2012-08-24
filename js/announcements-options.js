var farbtastic;
var farbtastic2;

(function($){
	// change the colour of the sample square and change the colour picker's selected colour for ticker color
	var tickerPickColor = function(a) {
		farbtastic.setColor(a);
		$('#ticker-color').val(a);
		$('#ticker-color-example').css('background-color', a);
		$('#ticker-wrapper-sample').css('background-color', a);
		$('#ticker-sample').css('background-color', a);
		$('#ticker-content-sample').css('background-color', a);
	};

	// change the colour of the sample square and change the colour picker's selected colour for text color
	var textPickColor = function(a) {
		farbtastic2.setColor(a);
		$('#text-color').val(a);
		$('#text-color-example').css('background-color', a);
		$('#ticker-content-sample').css('color', a);
		$('#ticker-content-sample a').css('color', a);
	};

	//change the height of the sample ticker
	var tickerChangeHeight = function(a) {
		$('#ticker-height').val(a);
		$('#ticker-wrapper-sample').css('height', a);
	}

	$(document).ready( function() {
		//turn the default colour <span> below the textbox into a link
		$('#ticker-default-color').wrapInner('<a href="#" />');
		$('#text-default-color').wrapInner('<a href="#" />');
		$('#default-height').wrapInner('<a href="#" />');
		$('#default-max-chars').wrapInner('<a href="#" />');


		farbtastic = $.farbtastic('#tickerColorPickerDiv', tickerPickColor);
		farbtastic2 = $.farbtastic('#textColorPickerDiv', textPickColor);

		//initialise colour picker and sample square values
		tickerPickColor( $('#ticker-color').val() );
		textPickColor( $('#text-color').val() );
		tickerChangeHeight( $('#ticker-height').val() );

		//show colour picker when sample colour square or button is clicked for ticker
		$('.tickerpickcolor').click( function(e) {
			$('#tickerColorPickerDiv').show();
			e.preventDefault();
		});

		//show colour picker when sample colour square or button is clicked for text
		$('.textpickcolor').click( function(e) {
			$('#textColorPickerDiv').show();
			e.preventDefault();
		});

		//call *pickColor functions to change the sample square and selected colour
		$('#ticker-color').keyup( function() {
			var a = $('#ticker-color').val(),
				b = a;

			a = a.replace(/[^a-fA-F0-9]/, '');
			if ( '#' + a !== b )
				$('#ticker-color').val(a);
			if ( a.length === 3 || a.length === 6 )
				tickerPickColor( '#' + a );
		});

		$('#text-color').keyup( function() {
			var a = $('#text-color').val(),
				b = a;

			a = a.replace(/[^a-fA-F0-9]/, '');
			if ( '#' + a !== b )
				$('#text-color').val(a);
			if ( a.length === 3 || a.length === 6 )
				textPickColor( '#' + a );
		});

		$('#ticker-height').keyup( function() {
			var a = $('#ticker-height').val();

			if ( a.match(/[0-9]+px/) ) {
				tickerChangeHeight( a );
			}
			else if( a.match(/[0-9]/) )
				tickerChangeHeight( a + 'px' );
		});


		//hide colour pickers when click is outside of them
		$(document).mousedown( function() {
			$('#tickerColorPickerDiv').hide();
			$('#textColorPickerDiv').hide();
		});

		//click event handler for the Default colour links
		$('#ticker-default-color a').click( function(e) {
			tickerPickColor( '#' + this.innerHTML.replace(/[^a-fA-F0-9]/, '') );
			e.preventDefault();
		});
		$('#text-default-color a').click( function(e) {
			textPickColor( '#' + this.innerHTML.replace(/[^a-fA-F0-9]/, '') );
			e.preventDefault();
		});

		//click event handler for Default height link
		$('#default-height a').click( function(e) {
			tickerChangeHeight( this.innerHTML );
			e.preventDefault();
		});

		//click event handler for Default max chars link
		$('#default-max-chars a').click( function(e) {
			$('#max-chars').val(this.innerHTML);
			e.preventDefault();
		});

	});
})(jQuery);
