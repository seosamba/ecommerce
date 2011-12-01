define([
	'libs/underscore/underscore', 
	'libs/backbone/backbone',
	'modules/product/views/app',
	'modules/product/models/product',
	'modules/product/collections/productlist',
	'modules/product/views/productlist',
	'modules/product/collections/categories',
	'modules/product/views/category',
    'modules/product/collections/brands'
], function(_, Backbone, AppView, ProductModel, ProductsCollection, ProductListingView,
            CategoryCollection, CategoryView, BrandsCollection){
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
        brands: null,
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

            this.brands = new BrandsCollection();
            this.brands.bind('all', this.renderBrands, this);
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
        addBrand: function(brand) {
            $.tmpl("<option value='${name}' {{if url}}data-url='${url}'{{/if}}>${name}</option>", brand.toJSON()).appendTo('#product-brand');
        },
        renderBrands: function(){
            $('#product-brand').empty();
            this.brands.each(this.addBrand, this);
        },
		productListToggle: function(){
			$('#product-list').show('slide');
		}
	});
	
	var initialize = function(){
		window.appRouter = new Router;
		$.when(
			appRouter.products.fetch(),
			appRouter.categories.fetch(),
            appRouter.brands.fetch()
		).then(function(){
			Backbone.history.start();				
		});
	};
	
	return {
		initialize: initialize
	};
});

