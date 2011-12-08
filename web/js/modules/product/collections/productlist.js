define([
	'libs/underscore/underscore',
	'libs/backbone/backbone',
	'modules/product/models/product'
], function(_, Backbone, ProductModel){
	var ProductList = Backbone.Collection.extend({
		model: ProductModel,
		url: '/plugin/shopping/run/getdata/type/product/',
		initialize: function(){
		},
        search: function(search) {
            search = search.toLowerCase();
            return this.filter(function(prod){
                if (prod.get('name').toLowerCase().indexOf(search) != -1 ||
                    prod.get('sku').toLowerCase().indexOf(search) != -1 ||
                    prod.get('mpn').toLowerCase().indexOf(search) != -1 ) {
                    return true;
                } else {
                    return false;
                }
            });
        }
	});
	return ProductList;
});