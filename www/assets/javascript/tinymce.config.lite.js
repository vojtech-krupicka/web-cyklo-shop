var HassaMceConfigLite = {
	// Location of TinyMCE script
	script_url : baseUrl + '/javascript/tiny_mce/tiny_mce.js',
	language: 'cs',
	width: 460,
	
	skin : "o2k7",
	
	// General options
	theme : "advanced",
	plugins : "pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template",

	// Theme options
	theme_advanced_buttons1 : "fontselect,fontsizeselect,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,undo,redo,|,table",
	theme_advanced_buttons2 : "bullist,numlist,|,link,unlink,anchor,|,forecolor,backcolor,|,cite,abbr,acronym,sub,sup,|,hr,advhr,removeformat,visualaid,|,charmap,emotions",
				
	theme_advanced_toolbar_location : "top",
	theme_advanced_toolbar_align : "left",
	theme_advanced_statusbar_location : "bottom",
	theme_advanced_resizing : true,
	theme_advanced_resize_horizontal : false,
	theme_advanced_statusbar_location : false,

	
	external_image_list_url : "/javascript/tinymce.imagelist.js",

	relative_urls : false,
	document_base_url : baseUrl	
};