define([
	'underscore',
	'backbone'
], function(_, Backbone){
	
	var SupplierModel = Backbone.Model.extend({
        urlRoot: function(){
            return $('#website_url').val() + 'api/store/suppliers'
        },
		defaults: {
            name: ''
		}
	});
	
	return SupplierModel;
});