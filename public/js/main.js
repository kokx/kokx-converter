function toggleOptions()
{
	var options = document.getElementById('options');
	var link    = document.getElementById('hide');

	if (options.style.display == 'none') {
		options.style.display = 'block';
		link.innerHTML = 'Verberg Opties';
	} else {
		options.style.display = 'none';
		link.innerHTML = 'Toon Opties';
	}
}
