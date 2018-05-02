;(function($, window, document) {
	$('document').ready(function(){
		$('ul > a.showphpbbevents, ol > a.showphpbbevents').wrap('<li></li>');
		$('select > option[data-marttiphpbb-showphpbbevents-link]')
			.each(function(){
				$('<a></a>')
					.attr('href', $(this).data('marttiphpbb-showphpbbevents-link'))
					.attr('title', $(this).data('marttiphpbb-showphpbbevents-title'))
					.addClass('showphpbbevents-option')
					.text($(this).data('marttiphpbb-showphpbbevents-name'))
					.insertAfter($(this).parent());
			});
		$option.parent().append();
	});
})(jQuery, window, document);
