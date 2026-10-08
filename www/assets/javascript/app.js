/**
 * AJAX for the whole site: Naja (loaded before this file) handles every link, form and button
 * with class="ajax" and redraws the snippets named in the response.
 * This file only initializes it and shows the spinner (styled by #ajax-spinner in the CSS).
 */
document.addEventListener('DOMContentLoaded', function () {
	var spinner = document.createElement('div');
	spinner.id = 'ajax-spinner';
	spinner.style.position = 'fixed';
	spinner.style.left = '50%';
	spinner.style.top = '50%';
	document.body.appendChild(spinner);

	naja.addEventListener('start', function () {
		spinner.style.display = 'block';
	});
	naja.addEventListener('complete', function () {
		spinner.style.display = 'none';
	});

	naja.initialize();
});
