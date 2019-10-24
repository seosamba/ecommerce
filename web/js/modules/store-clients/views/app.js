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
                script_url: $('#website_url').val() + 'system/js/external/tinymce/tinymce.gzip.php',
                selector: '#signature',
                skin: 'seotoaster',
                menubar: false,
                resize: false,
                convert_urls: false,
                browser_spellcheck: true,
                relative_urls: false,
                statusbar: false,
                allow_script_urls: true,
                force_p_newlines: true,
                forced_root_block: false,
                entity_encoding: "raw",
                plugins: [
                    "advlist lists link anchor image charmap visualblocks code media table paste textcolor fullscreen"
                ],
                toolbar1: 'link unlink | image | hr | bold italic | fontsizeselect | pastetext | forecolor backcolor | formatselect | code | fullscreen |',
                fontsize_formats: "8px 10px 12px 14px 16px 18px 24px 36px",
                block_formats: "Block=div;Paragraph=p;Block Quote=blockquote;Cite=cite;Address=address;Code=code;Preformatted=pre;H2=h2;H3=h3;H4=h4;H5=h5;H6=h6",
                extended_valid_elements: "a[*],input[*],select[*],textarea[*]",
                image_advtab: true,
                setup: function (ed) {
                    var keyTime = null;
                    ed.on('change keyup', function (ed, e) {
                        //@see content.js for this function
                        self.dispatchEditorKeyup(ed, e, keyTime);
                        this.save();
                    });
                    ed.on('blur', function (ed, e) {
                        editUserProfileSendAjax('signature', tinymce.activeEditor.getContent());
                    });
                }
            })
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