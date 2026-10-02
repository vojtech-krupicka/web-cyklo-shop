/**
 * AJAX Nette Framwork plugin for jQuery
 *
 * @copyright   Copyright (c) 2009 Jan Marek
 * @license     MIT
 * @link        http://nettephp.com/cs/extras/jquery-ajax
 * @version     0.2
 */

jQuery.extend({
	nette: {
		updateSnippet: function (id, html) {
			$("#" + id).html(html);
		},

		success: function (payload) {
			// redirect
			if (payload.redirect) {
				window.location.href = payload.redirect;
				return;
			}

			// snippets
			if (payload.snippets) {
				for (var i in payload.snippets) {
					jQuery.nette.updateSnippet(i, payload.snippets[i]);
				}
			}
		}
	}
});

jQuery.ajaxSetup({
	success: jQuery.nette.success,
	dataType: "json"
});


/**
 * Funkce pro pridani spinneru pro ajax po nacteni stranky 
 */
$(function () {
	// vhodne nastylovany div se vlozi nakonec HTML kodu po nacteni stranky
	$('<div id="ajax-spinner"></div>').appendTo("body").ajaxStop(function () {
		// Pri udalosti ajaxStop se spinner schova a nastavi se mu puvodni pozice
		$(this).hide().css({
			position: "fixed",
			left: "50%",
			top: "50%"
		});
	}).hide();
});


/**
 * Efekt pri aktualizaci snippetu 
 */
jQuery.nette.updateSnippet = function (id, html) {
	$("#" + id).fadeTo("fast", 0.01, function () {
		$(this).html(html).fadeTo("fast", 1);
	});
};


/**
 * Dovazi funkcionalitu AJAXu na vsechny odkazy, ktere maji tridu nastavenu na
 * 'ajax'. Po kliknuti na dany odkaz spusti AJAXovy pozadavek a zobrazi spinner. 
 */
$(document).ready(function(){
	$("a.ajax").live("click", function (event) {
		event.preventDefault();
		$.get(this.href);
		
		// zobrazení spinneru a nastavení jeho pozice
		$("#ajax-spinner").show().css({
			position: "absolute",
			left: event.pageX + 20,
			top: event.pageY + 40
		});
		

	});
	
	$('form.ajax :submit').live('click', function (event) {
		// zobrazení spinneru a nastavení jeho pozice
		$("#ajax-spinner").show().css({
			position: "absolute",
			left: event.pageX + 20,
			top: event.pageY + 40
		});
		
		$(this).ajaxSubmit();
		return false;
	});
	
	$('select.ajax').livequery('change', function (event) {
		// zobrazení spinneru a nastavení jeho pozice
		$("#ajax-spinner").show().css({
			position: "absolute",
			left: event.pageX + 20,
			top: event.pageY + 40
		});
		
		$(this).next().ajaxSubmit();
		return false;
	});
	
	//ajaxová změna počtu řádků na stránku datagridů pomocí změny hodnoty selectboxu
	$("form.ajax").find('select').livequery("change", function (event) {
		$(this).ajaxSubmit();
	});

});
