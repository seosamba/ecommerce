define([
	'backbone',
	'./views/app'
], function(Backbone, AppView){

	var Router = Backbone.Router.extend({
		app: null,
		routes: {
			'': 'newProduct',
			'new': 'newProduct',
            'edit/:id': 'editProduct'
		},
		initialize: function(){
			this.app = new AppView();
		},
		newProduct: function(){
			$('#product-list:visible').hide('slide');
			this.app.setProduct(null);
		},
		editProduct: function(productId){
			$('#product-list:visible').hide('slide');
            this.app.setProduct(productId);
		}
	});

    return Router;
});

