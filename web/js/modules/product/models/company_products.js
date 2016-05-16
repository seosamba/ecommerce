define([
	'underscore',
	'backbone'
], function(_, Backbone){
	
	var CompanyProductsModel = Backbone.Model.extend({
        urlRoot: function(){
            return $('#website_url').val() + 'api/store/companyproducts'
        },
		defaults: {
            name: ''
		}
	});
	
	return CompanyProductsModel;
});