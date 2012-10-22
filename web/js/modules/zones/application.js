define([
	'underscore',
	'backbone',
    './views/app',
    '../common/collections/states',
    '../common/collections/countries',
    '../common/views/countries',
    '../common/views/states'
], function(_, Backbone, AppView, StatesCollection, CountriesCollection, CountryListView, StateListView){
    if (!window.console) {
        window.console = {
        log: function(){
                return false;
            }
        };
    }

    window.app = {
        view      : new AppView,
        states    : new StatesCollection,
        countries : new CountriesCollection,
        views     : {
            countryList : new CountryListView,
            statesList  : new StateListView
        }
    };

    $(function() {
        $(document).trigger('zones:loaded');
    })
});