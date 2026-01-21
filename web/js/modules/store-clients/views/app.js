define([
	'backbone',
    'tinyMCE',
    './store_clients_view'
    ], function(Backbone, tinymce, StoreClientsView){
    var StoreClientsRouter = Backbone.Router.extend({
        routes: {
            ''         : 'index',
            'client/:id' : 'clientDetails'
        },
        index: function ()
        {
            if (!window.Toastr){
                window.Toastr = {}
            }
            this.ClientsView = new StoreClientsView();
            Toastr.StoreClientsWidget = this.ClientsView;
            this.ClientsView.render();
            $('#clients-table').removeClass('hidden');
            $('.search-line').removeClass('hidden');

        },
        clientDetails: function(clientId)
        {
            if (!clientId) {
                return false;
            }
            if (window.location.hash !== '') {
                $('#customer-details').find('.link').attr('href', $('#website_url').val()+'dashboard/clients/');
            }

            var self = this;
            tinymce.remove();

            $.get($('#website_url').val()+'plugin/shopping/run/profile/', {id: clientId},function(response, status) {
                if (response.error == "1") {
                    window.location.href = $('#website_url').val()+'dashboard/clients/';
                } else {
                    $('#clients-table, .search-line').hide();
                    $('#customer-details').find('#profile').html(response).end().show();
                    self.initTinyMce();
                }
            });

        },
        dispatchEditorKeyup(editor, event, keyTime) {
            var keyTimer = keyTime;
            if(keyTimer === null) {
                keyTimer = setTimeout(function() {
                    keyTimer = null;
                }, 1000)
            }
        },
        initTinyMce() {
            var self = this;

            tinymce.init({
                selector: '#signature',
                skin: 'oxide',
                promotion: false,
                branding: false,
                statusbar: false,
                menubar: false,
                resize: false,

                forced_root_block: 'p',
                browser_spellcheck: true,
                convert_urls: false,
                paste_as_text: true, // forces plain-text paste
                entity_encoding: "raw",

                plugins: [
                    'advlist autolink link image lists charmap preview code fullscreen hr paste'
                ],

                toolbar: [
                    'link unlink | image | hr | bold italic | fontsizeselect', // row 1
                    'forecolor backcolor | styleselect | pastetext | code | fullscreen' // row 2
                ],

                formats: {
                    cite: { block: 'cite', wrapper: false },
                    address: { block: 'address', wrapper: false },
                    codeblock: { block: 'code', wrapper: false }
                },

                style_formats: [
                    { title: 'Paragraph', block: 'p' },
                    { title: 'Heading 2', block: 'h2' },
                    { title: 'Heading 3', block: 'h3' },
                    { title: 'Block Quote', block: 'blockquote' },
                    { title: 'Cite', format: 'cite' },
                    { title: 'Address', format: 'address' },
                    { title: 'Code Block', format: 'codeblock' }
                ],

                setup: function (ed) {
                    var keyTime = null;
                    ed.on('change blur keyup', function (ed, e) {
                        self.dispatchEditorKeyup(ed, e, keyTime);
                    });
                    ed.on('blur', function () {
                        editUserProfileSendAjax('signature', tinymce.activeEditor.getContent());
                    });
                }
            });
        }
    });

    var initializeStoreClientsRouter = function() {
        window.appStoreClientsRouter = new StoreClientsRouter;
        Backbone.history.start();
    };

    return {
        initializeStoreClientsRouter: initializeStoreClientsRouter
    };
});