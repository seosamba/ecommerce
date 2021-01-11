define([
	'backbone',
    '../models/supplier'
], function(Backbone, SupplierModel){

    var SupplierList = Backbone.Collection.extend({
        model: SupplierModel,
        url: function(){
            return $('#website_url').val() + 'api/store/suppliers/';
        }
    });
	return SupplierList;
});