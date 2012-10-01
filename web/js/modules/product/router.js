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
            console.log('ololo');
			$('#product-list:visible').hide('slide');
			this.app.setProduct(null);
            console.log('asd');
		},
		editProduct: function(productId){
			$('#product-list:visible').hide('slide');
            this.app.setProduct(productId);
		},
		loadProducts: function(productsCollection){
			this.productListHolder.empty();
			productsCollection.each(this.renderProductView, this);
		},
		renderProductView: function(product){
			var productView = new ProductListingView({model: product});
			this.productListHolder.append(productView.render().el);
            if (this.productListHolder.children().size() === this.products.size()){
                this.productListHolder.find('img.lazy').lazyload({
                    container: this.productListHolder,
                    effect: 'fadeIn'
                }).removeClass('lazy');
            }
		}
	});

    return Router;
});

