define([
	'Underscore',
	'Backbone',
	'modules/product/models/option'
], function(_, Backbone, ProductOption){
	var ProductOptions = Backbone.Collection.extend({
		model: ProductOption
	});
	
	return ProductOptions;
});