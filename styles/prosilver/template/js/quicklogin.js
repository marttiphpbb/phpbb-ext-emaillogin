;(function($, window, document) {
	$('document').ready(function(){
		var $span = $('#marttiphpbb_emaillogin');
		$('fieldset.quick-login>label[for="username"]>span').filter('span').text($span.text());
		if ($span.data('auth-method') === 'db_email'){
			$('#username').attr('type', 'email');
		}
	});
})(jQuery, window, document);
