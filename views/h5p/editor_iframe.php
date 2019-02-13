<!doctype html>
<html>
<head>
    <link rel="stylesheet" href="/wordpress/wp-content/plugins/h5p/h5p-php-library/styles/h5p.css?ver=1.11.3">
    <link rel="stylesheet"
          href="/wordpress/wp-content/plugins/h5p/h5p-php-library/styles/h5p-confirmation-dialog.css?ver=1.11.3">
    <link rel="stylesheet"
          href="/wordpress/wp-content/plugins/h5p/h5p-php-library/styles/h5p-core-button.css?ver=1.11.3">
    <link rel="stylesheet" href="/wordpress/wp-content/plugins/h5p/h5p-editor-php-library/libs/darkroom.css?ver=1.11.3">
    <link rel="stylesheet"
          href="/wordpress/wp-content/plugins/h5p/h5p-editor-php-library/styles/css/h5p-hub-client.css?ver=1.11.3">
    <link rel="stylesheet"
          href="/wordpress/wp-content/plugins/h5p/h5p-editor-php-library/styles/css/fonts.css?ver=1.11.3">
    <link rel="stylesheet"
          href="/wordpress/wp-content/plugins/h5p/h5p-editor-php-library/styles/css/application.css?ver=1.11.3">
    <link rel="stylesheet"
          href="/wordpress/wp-content/plugins/h5p/h5p-editor-php-library/styles/css/libs/zebra_datepicker.min.css?ver=1.11.3">
    <script src="/wordpress/wp-content/plugins/h5p/h5p-php-library/js/jquery.js?ver=1.11.3"></script>
    <script src="/wordpress/wp-content/plugins/h5p/h5p-php-library/js/h5p.js?ver=1.11.3"></script>
    <script src="/wordpress/wp-content/plugins/h5p/h5p-php-library/js/h5p-event-dispatcher.js?ver=1.11.3"></script>
    <script src="/wordpress/wp-content/plugins/h5p/h5p-php-library/js/h5p-x-api-event.js?ver=1.11.3"></script>
    <script src="/wordpress/wp-content/plugins/h5p/h5p-php-library/js/h5p-x-api.js?ver=1.11.3"></script>
    <script src="/wordpress/wp-content/plugins/h5p/h5p-php-library/js/h5p-content-type.js?ver=1.11.3"></script>
    <script src="/wordpress/wp-content/plugins/h5p/h5p-php-library/js/h5p-confirmation-dialog.js?ver=1.11.3"></script>
    <script src="/wordpress/wp-content/plugins/h5p/h5p-php-library/js/h5p-action-bar.js?ver=1.11.3"></script>
    <script src="/wordpress/wp-content/plugins/h5p/h5p-editor-php-library/scripts/h5p-hub-client.js?ver=1.11.3"></script>
    <script src="/wordpress/wp-content/plugins/h5p/h5p-editor-php-library/scripts/h5peditor.js?ver=1.11.3"></script>
    <script src="/wordpress/wp-content/plugins/h5p/h5p-editor-php-library/scripts/h5peditor-semantic-structure.js?ver=1.11.3"></script>
    <script src="/wordpress/wp-content/plugins/h5p/h5p-editor-php-library/scripts/h5peditor-library-selector.js?ver=1.11.3"></script>
    <script src="/wordpress/wp-content/plugins/h5p/h5p-editor-php-library/scripts/h5peditor-form.js?ver=1.11.3"></script>
    <script src="/wordpress/wp-content/plugins/h5p/h5p-editor-php-library/scripts/h5peditor-text.js?ver=1.11.3"></script>
    <script src="/wordpress/wp-content/plugins/h5p/h5p-editor-php-library/scripts/h5peditor-html.js?ver=1.11.3"></script>
    <script src="/wordpress/wp-content/plugins/h5p/h5p-editor-php-library/scripts/h5peditor-number.js?ver=1.11.3"></script>
    <script src="/wordpress/wp-content/plugins/h5p/h5p-editor-php-library/scripts/h5peditor-textarea.js?ver=1.11.3"></script>
    <script src="/wordpress/wp-content/plugins/h5p/h5p-editor-php-library/scripts/h5peditor-file-uploader.js?ver=1.11.3"></script>
    <script src="/wordpress/wp-content/plugins/h5p/h5p-editor-php-library/scripts/h5peditor-file.js?ver=1.11.3"></script>
    <script src="/wordpress/wp-content/plugins/h5p/h5p-editor-php-library/scripts/h5peditor-image.js?ver=1.11.3"></script>
    <script src="/wordpress/wp-content/plugins/h5p/h5p-editor-php-library/scripts/h5peditor-image-popup.js?ver=1.11.3"></script>
    <script src="/wordpress/wp-content/plugins/h5p/h5p-editor-php-library/scripts/h5peditor-av.js?ver=1.11.3"></script>
    <script src="/wordpress/wp-content/plugins/h5p/h5p-editor-php-library/scripts/h5peditor-group.js?ver=1.11.3"></script>
    <script src="/wordpress/wp-content/plugins/h5p/h5p-editor-php-library/scripts/h5peditor-boolean.js?ver=1.11.3"></script>
    <script src="/wordpress/wp-content/plugins/h5p/h5p-editor-php-library/scripts/h5peditor-list.js?ver=1.11.3"></script>
    <script src="/wordpress/wp-content/plugins/h5p/h5p-editor-php-library/scripts/h5peditor-list-editor.js?ver=1.11.3"></script>
    <script src="/wordpress/wp-content/plugins/h5p/h5p-editor-php-library/scripts/h5peditor-library.js?ver=1.11.3"></script>
    <script src="/wordpress/wp-content/plugins/h5p/h5p-editor-php-library/scripts/h5peditor-library-list-cache.js?ver=1.11.3"></script>
    <script src="/wordpress/wp-content/plugins/h5p/h5p-editor-php-library/scripts/h5peditor-select.js?ver=1.11.3"></script>
    <script src="/wordpress/wp-content/plugins/h5p/h5p-editor-php-library/scripts/h5peditor-selector-hub.js?ver=1.11.3"></script>
    <script src="/wordpress/wp-content/plugins/h5p/h5p-editor-php-library/scripts/h5peditor-selector-legacy.js?ver=1.11.3"></script>
    <script src="/wordpress/wp-content/plugins/h5p/h5p-editor-php-library/scripts/h5peditor-dimensions.js?ver=1.11.3"></script>
    <script src="/wordpress/wp-content/plugins/h5p/h5p-editor-php-library/scripts/h5peditor-coordinates.js?ver=1.11.3"></script>
    <script src="/wordpress/wp-content/plugins/h5p/h5p-editor-php-library/scripts/h5peditor-none.js?ver=1.11.3"></script>
    <script src="/wordpress/wp-content/plugins/h5p/h5p-editor-php-library/scripts/h5peditor-metadata.js?ver=1.11.3"></script>
    <script src="/wordpress/wp-content/plugins/h5p/h5p-editor-php-library/scripts/h5peditor-metadata-author-widget.js?ver=1.11.3"></script>
    <script src="/wordpress/wp-content/plugins/h5p/h5p-editor-php-library/scripts/h5peditor-metadata-changelog-widget.js?ver=1.11.3"></script>
    <script src="/wordpress/wp-content/plugins/h5p/h5p-editor-php-library/scripts/h5peditor-pre-save.js?ver=1.11.3"></script>
    <script src="/wordpress/wp-content/plugins/h5p/h5p-editor-php-library/ckeditor/ckeditor.js?ver=1.11.3"></script>
</head>
<body>
<div class="h5p-editor h5peditor">Lade, bitte warten...</div>
</body>
</html>