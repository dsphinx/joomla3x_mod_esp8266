jQuery(document).ready(function(){
	var $ = jQuery;
	
	function run() {
		var $f = $('.mod-temperature .row:visible:first');
		
		if ($f.length) {
			$f.fadeOut(1000, function(){ run();});
		} else {
			alert('--');
		}
	}
	
	setTimeout(function() {run();},1000);
})
