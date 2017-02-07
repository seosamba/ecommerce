define([
	'backbone',
    './store_clients_view'
    ], function(Backbone, StoreClientsView){
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

            $.get($('#website_url').val()+'plugin/shopping/run/profile/', {id: clientId},function(response, status) {
                if (response.error == "1") {
                    window.location.href = $('#website_url').val()+'dashboard/clients/';
                } else {
                    $('#clients-table, .search-line').hide();
                    $('#customer-details').find('#profile').html(response).end().show();
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