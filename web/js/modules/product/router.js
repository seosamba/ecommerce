define([
	'libs/underscore/underscore', 
	'libs/backbone/backbone',
	'modules/product/views/app',
	'modules/product/models/product',
	'modules/product/collections/productlist',
	'modules/product/views/productlist',
	'modules/product/collections/categories',
	'modules/product/views/category'
], function(_, Backbone, AppView, ProductModel, ProductsCollection, ProductListingView,  CategoryCollection, CategoryView){
	var Router = Backbone.Router.extend({
		app: null,
		routes: {
			'': 'newProduct',
			'new': 'newProduct',
			'edit/:id': 'editProduct',
			'list': 'productListToggle'
		},
		products: null,
		categories: null,
		initialize: function(){
			this.app = new AppView();
			
			this.products = new ProductsCollection();
			this.products.bind('add', this.renderProductView, this);
			this.products.bind('reset', this.loadProducts, this);

			$('#product-list').hide();
			$('#manage-product').show();
			
			this.categories = new CategoryCollection();
			this.categories.bind('add', this.addCategory, this);
			this.categories.bind('reset', this.renderCategories, this);
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
		loadProducts: function(productsCollection){
			$('#product-list').empty();
			productsCollection.each(this.renderProductView);
		},
		renderProductView: function(product){
			var productView = new ProductListingView({model: product});
			$('#product-list').append(productView.render().el);
		},
		addCategory: function(category){
			var view = new CategoryView({model: category});
			$('#product-categories').append(view.render().el);
		},
		renderCategories: function(){
			$('#product-categories').empty();
			this.categories.each(this.addCategory, this);
		},
		productListToggle: function(){
			$('#product-list').show('slide');
		}
	});
	
	var initialize = function(){
		window.appRouter = new Router;
		$.when(
			appRouter.products.fetch(),
			appRouter.categories.fetch()
		).then(function(){
			Backbone.history.start();				
		});
	};
	
	return {
		initialize: initialize
	};
});

