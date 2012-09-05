/**
 * .
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */
define([
	'backbone',
	'./views/app'
], function(Backbone, AppView){
	var Router = Backbone.Router.extend({
		app: null,
		routes: {
			'': 'indexAction',
			'search/:type/:id': 'searchAction'
		},
		initialize: function(){
			this.app = new AppView();
		},
		indexAction: function(){
		},
		searchAction: function(type, id){

            switch (type){
                case 'order':
                    this.app.$('input[name=search]').val(id);
                    break;
                case 'product':
                    this.app.orders.server_api.productid = id;
                    break;
            }
            this.navigate('/');
            this.app.orders.pager();
		}
	});

	return {
		initialize: function(){
            var appRouter = new Router;
            Backbone.history.start();
            return appRouter;
        }
	};
});

