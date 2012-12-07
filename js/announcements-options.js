//handles for the sliders
var heightSlider;
var sizeSlider;

(function($){
	// change the sample ticker color
	function tickerPickColor(color) {
		$('#ticker-wrapper-sample').css('background-color', color);
		$('#ticker-sample').css('background-color', color);
		$('#ticker-content-sample').css('background-color', color);
	};

	// change the sample ticker text color
	function textPickColor(color) {
		$('#ticker-content-sample').css('color', color);
		$('#ticker-content-sample a').css('color', color);
	}

	//change the height of the sample ticker
	var tickerChangeHeight = function(a) {
		$('#ticker-wrapper-sample').css('height', a);
	}

	//change the font-size of the sample ticker
	var tickerChangeSize = function(a) {
		$('#ticker-content-sample').css('font-size', a);
	}

	$(document).ready( function() {
		//get a handle to the heightSlider and initialize the dataset value
		//inspired from http://www.htmlfivecan.com/#23 
		heightSlider = document.querySelector( '#ticker-height' );
		heightSlider.dataset.value = heightSlider.value;

		//get a handle to the heightSlider and initialize the dataset value
		//inspired from http://www.htmlfivecan.com/#23 
		sizeSlider = document.querySelector( '#text-size' );
		sizeSlider.dataset.value = sizeSlider.value;

		//turn the default <span> below the textbox into a link
		$('#default-text-size').wrapInner('<a href="#" />');
		$('#default-height').wrapInner('<a href="#" />');
		$('#default-max-chars').wrapInner('<a href="#" />');


		// initialise color pickers and pass in an optional callback function to change the sample ticker colors
		$('#ticker-color').wpColorPicker({
			change: function( event, ui ) {
				tickerPickColor( $('#ticker-color').wpColorPicker('color') );
			}
		});

		$('#text-color').wpColorPicker({
			change: function( event, ui ) {
				textPickColor( $('#text-color').wpColorPicker('color') );
			}
		});

		//initialise sample ticker color and height values
		tickerPickColor( $('#ticker-color').wpColorPicker('color') );
		textPickColor( $('#text-color').wpColorPicker('color') );
		tickerChangeHeight( $('#ticker-height').val() );

	
		//update sample ticker height and font size when the sliders are changed
		$('#ticker-height').change( function() {
			heightSlider.dataset.value = $('#ticker-height').val();
			tickerChangeHeight( $('#ticker-height').val() + 'px' );
		});

		$('#text-size').change( function() {
			sizeSlider.dataset.value = $('#text-size').val();
			tickerChangeSize( $('#text-size').val() + 'px' );
		});

		//click event handler for Default sample text size link
		$('#default-text-size a').click( function(e) {
			$('#text-size').val(this.innerHTML.slice(0, -2) );
			sizeSlider.dataset.value = $('#text-size').val();
			tickerChangeSize( this.innerHTML );
			e.preventDefault();
		});

		//click event handler for Default height link
		$('#default-height a').click( function(e) {
			$('#ticker-height').val( this.innerHTML.slice(0,-2) );
			heightSlider.dataset.value = $('#ticker-height').val();
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
