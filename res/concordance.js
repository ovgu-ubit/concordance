$(document).ready(function() {

	$('a.jump_link#hide').ready(function(event) {
		var link = document.getElementById('hide');
		var isHidden = link.getAttribute('status') === 'hide';
		link.innerHTML = (isHidden) ? link.getAttribute('hidecontent') : link.getAttribute('showcontent');
		link.title = (isHidden) ? link.getAttribute('hidetitle') : link.getAttribute('showtitle');
		document.getElementById('debug_frame').style.display = (isHidden) ? 'none' : 'block';
	});

	$('a.jump_link#hide').click(function(event) {
		var link = document.getElementById('hide');
		var isHidden = link.getAttribute('status') === 'hide';
		link.setAttribute('status', ((isHidden) ? 'show' : 'hide'));
		link.innerHTML = (isHidden) ? link.getAttribute('showcontent') : link.getAttribute('hidecontent');
		link.title = (isHidden) ? link.getAttribute('showtitle') : link.getAttribute('hidetitle');
		document.getElementById('debug_frame').style.display = (isHidden) ? 'block' : 'none';
	});
});
