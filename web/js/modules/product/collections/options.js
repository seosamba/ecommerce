define([
	'libs/underscore/underscore',
	'libs/backbone/backbone',
	'modules/product/models/option'
], function(_, Backbone, ProductOption){
	var ProductOptions = Backbone.Collection.extend({
		model: ProductOption
	});
	
	return ProductOptions;
});