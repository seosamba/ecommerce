define([
	'libs/underscore/underscore', 
	'libs/backbone/backbone',
	'modules/product/views/app',
	'modules/product/models/product'
], function(_, Backbone, AppView, ProductModel){
	var Router = Backbone.Router.extend({
		routes: {
			'': 'newProduct',
			'new': 'newProduct',
			'edit/:id': 'editProduct'
		},
		newProduct: function(){
			var app = new AppView({model: new ProductModel});
			app.render();
		},
		editProduct: function(productId){
			alert('edit product #'+productId); 
//			AppView.model.fetch({data: {id: productId}});
		}
	});
	
	var initialize = function(){
		var appRouter = new Router;
		Backbone.history.start();
	};
	
	return {
		initialize: initialize
	};
});

