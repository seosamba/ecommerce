define([
	'backbone',
	'../models/option'
], function(Backbone, ProductOption){
	var ProductOptions = Backbone.Collection.extend({
		model: ProductOption,
        url: function(){
            return $('#website_url').val() + 'api/store/options/';
        }
	});
	
	return ProductOptions;
});