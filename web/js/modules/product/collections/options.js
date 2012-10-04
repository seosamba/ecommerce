define([
	'backbone',
	'../models/option'
], function(Backbone, ProductOption){
	var ProductOptions = Backbone.Collection.extend({
		model: ProductOption,
        url: '/api/store/options/'
	});
	
	return ProductOptions;
});