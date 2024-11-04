tinymce.PluginManager.add('aidescription', function(editor, url) {

    editor.ui.registry.addButton('aidescription', {

        //text: $(document).find('#generate-ai-product-full-description-translation').val(),
        tooltip: $(document).find('#generate-ai-product-full-description-translation').val(),
        icon: 'triangleUp',
        onAction: function() {
            $(document).find('#generate-ai-product-full-description').trigger('click');
        }
    });
});
