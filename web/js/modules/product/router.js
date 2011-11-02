define([
	'libs/underscore/underscore', 
	'libs/backbone/backbone',
	'modules/product/views/app',
	'modules/product/models/product',
	'modules/product/collections/productlist',
	'modules/product/views/productlist'
], function(_, Backbone, AppView, ProductModel, ProductsCollection, ProductListingView){
	var Router = Backbone.Router.extend({
		app: null,
		routes: {
			'': 'newProduct',
			'new': 'newProduct',
			'edit/:id': 'editProduct',
			'list': 'productListToggle'
		},
		products: null,
		initialize: function(){
			this.app = new AppView();
			
			this.products = new ProductsCollection();
			this.products.bind('reset', this.loadProducts, this);
			this.products.fetch();
			$('#product-list:visible').hide('slide');
		},
		newProduct: function(){
			$('#product-list:visible').hide('slide');
			this.app.setModel(new ProductModel());
		},
		editProduct: function(productId){
			$('#product-list:visible').hide('slide');
			var product = new ProductModel();
			product.fetch({data: {id: productId}});
			this.app.setModel(product);
		},
		loadProducts: function(){
			$('#product-list').empty();
			this.products.each(function(product){
				var productView = new ProductListingView({model: product});
				$('#product-list').append(productView.render().el);
			});
		},
		productListToggle: function(){
			$('#product-list').show('slide');
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

