define([
	'backbone',
    '../models/company_products'
], function(Backbone, CompanyProductsModel){

    var CompanyProductsList = Backbone.Collection.extend({
        model: CompanyProductsModel,
        url: function(){
            return $('#website_url').val() + 'api/store/companyproducts/';
        }
    });
	return CompanyProductsList;
});