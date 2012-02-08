define([
    'Underscore',
    'Backbone',
    'modules/taxes/views/app',
    'modules/zones/collections/zones'
], function(_, Backbone, AppView, ZonesCollection ){
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
            zones: new ZonesCollection
        };

        app.view.render();

        $.when(
            app.zones.fetch()
        ).done(function(){
            app.view.rulesCollection.fetch()
        });
	}

	return { 
		initialize: initialize
	};
});