define([
	'underscore',
	'backbone'
], function(_, Backbone){
	
	var BrandModel = Backbone.Model.extend({
        urlRoot: function(){
            return $('#website_url').val() + 'plugin/shopping/run/getdata/type/brands'
        },
		defaults: {
            name: ''
		}
	});
	
	return BrandModel;
});