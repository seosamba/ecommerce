define([
	'libs/underscore/underscore', 
	'libs/backbone/backbone',
	'modules/product/views/app',
	'modules/product/models/product',
	'modules/product/collections/productlist',
	'modules/product/views/productlist',
	'modules/product/collections/tags',
	'modules/product/views/tag',
    'modules/product/collections/brands'
], function(_, Backbone, AppView, ProductModel, ProductsCollection, ProductListingView,
            TagsCollection, TagView, BrandsCollection){
	var Router = Backbone.Router.extend({
		app: null,
		routes: {
			'': 'newProduct',
			'new': 'newProduct',
            'edit/:id': 'editProduct'
		},
		products: null,
        productListHolder: $('#product-list-holder'),
		tags: null,
        brands: null,
		initialize: function(){
			this.app = new AppView();

			$('#product-list').hide();
			$('#manage-product').show();

			this.tags = new TagsCollection();
			this.tags.bind('add', this.addTag, this);
			this.tags.bind('reset', this.renderTags, this);

            this.brands = new BrandsCollection();
            this.brands.bind('all', this.renderBrands, this);
		},
		newProduct: function(){
			$('#product-list:visible').hide('slide');
			this.app.setModel(new ProductModel());
		},
		editProduct: function(productId){
			$('#product-list:visible').hide('slide');
            if (this.products === null) {
                var product = new ProductModel();
                product.fetch({data: {id: productId}});
                this.app.setModel(product);
            } else {
                this.app.setModel(this.products.get(productId));
            }
		},
		loadProducts: function(productsCollection){
			this.productListHolder.empty();
			productsCollection.each(this.renderProductView, this);
		},
		renderProductView: function(product){
			var productView = new ProductListingView({model: product});
			this.productListHolder.append(productView.render().el).trigger('scroll');
		},
		addTag: function(tag, index){
            var view = new TagView({model: tag});
                view.render();
            if (index instanceof Backbone.Collection){
                $('#product-tags').prepend(view.$el);
            } else {
                $('#product-tags').append(view.$el);
            }
        },
		renderTags: function(){
			$('#product-tags').empty();
			this.tags.each(this.addTag, this);
        },
        addBrand: function(brand) {
            $.tmpl("<option value='${name}' {{if url}}data-url='${url}'{{/if}}>${name}</option>", brand.toJSON()).appendTo('#product-brand');
        },
        renderBrands: function(){
            $('#product-brand').html('<option value="-1" disabled>Select a brand</option>');
            _(this.brands.sortBy(function(brand){ return brand.get('name').toLowerCase();})).each(this.addBrand, this);
            $('#product-brand > option:first').attr('disabled', true);
        },
        initProductlist: function() {
            if (this.products === null) {
                this.products = new ProductsCollection();
                this.products.bind('add', this.renderProductView, this);
                this.products.bind('reset', this.loadProducts, this);
            }

            return this.products;
        }
	});

	var initialize = function(){
		window.appRouter = new Router;
		$.when(
			appRouter.tags.fetch(),
            appRouter.brands.fetch()
		).then(function(){
			Backbone.history.start();
		});
	};

	return {
		initialize: initialize
	};
});

