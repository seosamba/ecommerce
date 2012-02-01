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
        productListHolder: $('#product-list-holder'),
		categories: null,
        brands: null,
		initialize: function(){
			this.app = new AppView();

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
			this.productListHolder.append(productView.render().el);
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
            $('#product-brand').html('<option value="-1" disabled>Select a brand</option>');
            _(this.brands.sortBy(function(brand){ return brand.get('name').toLowerCase();})).each(this.addBrand, this);
            $('#product-brand > option:first').attr('disabled', true);
        },
		productListToggle: function(){
            var callback = function(){ $('#product-list').show('slide').find('#product-list-holder').trigger('scroll'); }
            if (this.products === null) {
                this.initProductlist().load().done([
                    callback,
                    appRouter.waypointCallback
                ]);
            } else {
                callback();
            }
		},
        initProductlist: function() {
            if (this.products === null) {
                this.products = new ProductsCollection();
                this.products.bind('add', this.renderProductView, this);
                this.products.bind('reset', this.loadProducts, this);
            }

            return this.products;
        },
        waypointCallback: function(){
            var list = appRouter.products;
            $('.productlisting:last', appRouter.productListHolder ).waypoint(function(){
                $(this).waypoint('remove');
//                if (!list.paginator.last){
//                    list.load().done([
//                        appRouter.waypointCallback,
//                        function(){ $('#product-list-search').trigger('keyup'); }
//                    ]);
//                }
                list.load([ function(){$('#product-list-search').trigger('keyup');}, appRouter.waypointCallback ]);
            }, {context: appRouter.productListHolder, offset: 'bottom-in-view' } );
        }
	});

	var initialize = function(){
		window.appRouter = new Router;
		$.when(
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

