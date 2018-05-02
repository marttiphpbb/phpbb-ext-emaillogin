;(function($, window, document) {
	$('document').ready(function(){
		$('ul > a.emaillogin, ol > a.emaillogin').wrap('<li></li>');
		$('select > option[data-marttiphpbb-emaillogin-link]')
			.each(function(){
				$('<a></a>')
					.attr('href', $(this).data('marttiphpbb-emaillogin-link'))
					.attr('title', $(this).data('marttiphpbb-emaillogin-title'))
					.addClass('emaillogin-option')
					.text($(this).data('marttiphpbb-emaillogin-name'))
					.insertAfter($(this).parent());
			});
		$option.parent().append();
	});
})(jQuery, window, document);
