define([
	'libs/underscore/underscore',
	'libs/backbone/backbone',
	'modules/product/models/product'
], function(_, Backbone, ProductModel){
	var ProductList = Backbone.Collection.extend({
		model: ProductModel,
		url: '/plugin/shopping/run/getdata/type/product/',
		initialize: function(){
		}
	});
	return ProductList;
});