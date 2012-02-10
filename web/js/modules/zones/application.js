define([
	'Underscore',
	'Backbone',
    'modules/zones/views/app',
    'modules/common/collections/states',
    'modules/common/collections/countries',
    'modules/common/views/countries',
    'modules/common/views/states'
], function(_, Backbone, AppView, StatesCollection, CountriesCollection, CountryListView, StateListView){
    if (!window.console) {
        window.console = {
        log: function(){
                return false;
            }
        };
    }

    var initialize = function(){

        window.app = {
            view: new AppView,
            states: new StatesCollection,
            countries: new CountriesCollection
        };

        $.when(
            app.countries.fetch(),
            app.states.fetch()
        ).then(function(){
            app.view.zonesCollection.fetch();
        }).then(function(){
            $('#add-country-dialog').dialog({
                modal: true,
                autoOpen: false,
                resizable: false,
                height: 300,
                width: 500,
                create: function(){
                    var list = _(app.countries.toJSON()).sortBy(function(c){
                        return c.name.toLowerCase()
                    });
                    var view = new CountryListView({
                       collection: list
                    });
                    view.render();
                },
                close: function(event, ui){
                    $('#country-filter').val('');
                    $('#country-list > li').show();
                }
           });

            $('#add-state-dialog').dialog({
                modal: true,
                autoOpen: false,
                resizable: false,
                height: 410,
                width: 600,
                create: function(){
                    var view = new StateListView({
                       collection: app.states.toJSON()
                    });
                    view.render()
                }
            });

            $('#ajax_msg').hide();
        });
    };

	return {
        initialize: initialize
    };
});