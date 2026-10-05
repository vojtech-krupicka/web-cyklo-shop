var HassaMceConfigFull = {
    // Location of TinyMCE script
    script_url: baseUrl + '/assets/javascript/tiny_mce/tiny_mce.js',
    language: 'cs',
    width: 695,

    skin: "o2k7",
    skin_variant: "black",

    // General options
    theme: "advanced",
    plugins: "pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template",

    // Theme options
    theme_advanced_buttons1: "styleselect,formatselect,fontselect,fontsizeselect",
    theme_advanced_buttons2: "save,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,cut,copy,paste,pastetext,pasteword,|,search,replace,|,undo,redo,|,cleanup,attribs,styleprops,code,fullscreen",
    theme_advanced_buttons3: "bullist,numlist,|,link,unlink,anchor,image,media,|,forecolor,backcolor,|,cite,abbr,acronym,sub,sup,|,hr,advhr,removeformat,visualaid,|,charmap,emotions,|,visualchars,nonbreaking",
    theme_advanced_buttons4: "tablecontrols,|,insertlayer,moveforward,movebackward,absolute",

    theme_advanced_toolbar_location: "top",
    theme_advanced_toolbar_align: "left",
    theme_advanced_statusbar_location: "bottom",
    theme_advanced_resizing: true,
    theme_advanced_resize_horizontal: false,

    relative_urls: false,
    document_base_url: baseUrl,
    external_image_list_url: '/javascript/tinymce.imagelist.js',
    external_link_list_url: '/javascript/tinymce.filelist.js',

    // Example content CSS (should be your site CSS)
    content_css: '/css/style.css',
    body_class: "wysiwyg"
};
