CKEDITOR.editorConfig = function(config) {
    config.toolbar = [
        {name: 'a', items: ['FontSize']},
        {name: 'b', items: ['Bold', 'Italic', 'Underline']},
        {name: 'c', items: ['NumberedList', 'BulletedList']},
        {name: 'd', items: ['BidiLtr', 'BidiRtl']},
        {name: 'e', items: ['Link', 'Unlink']},
        {name: 'f', items: ['JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock', 'Image', 'HorizontalRule']},
        {name: 'g', items: ['RemoveFormat']},
        {name: 'h', items: ['Source']},
        {name: 'i', items: ['Maximize']}
    ];
    config.format_tags = 'p;h1;h2;h3;h4;h5;h6;pre';
    config.extraPlugins = 'justify,bidi,font';
    config.contentsLanguage = locale;
    config.defaultLanguage = locale;
    config.language = locale;
};