define([
	'Underscore',
	'Backbone',
	'modules/people/views/app'
], function(_, Backbone, AppView){
	var Router = Backbone.Router.extend({
		app: null,
		routes: {
			'': 'indexAction',
			'new': 'newAction',
            'edit/:id': 'editAction'
		},
        initialize: function() {
            this.app = new AppView();
        },
        indexAction: function(){
        },
        newAction: function(){

        },
        editAction: function(userid){

        }
	});

	var initialize = function(){
		window.appRouter = new Router;

		Backbone.history.start();
	};

	return {
		initialize: initialize
	};
});

