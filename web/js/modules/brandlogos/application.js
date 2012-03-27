define([
    'Underscore',
    'Backbone',
    'modules/brandlogos/views/app',
], function(_, Backbone, AppView){
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
            filename: ''
        };
        $.when(
            app.view.fetchImages()
        ).done(function(){
            app.view.brands.fetch();
        });
	}

	return { 
		initialize: initialize
	};
});