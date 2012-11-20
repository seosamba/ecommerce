define([
	'underscore',
	'backbone'
], function(_, Backbone){
	
	var BrandModel = Backbone.Model.extend({
        urlRoot: function(){
            return $('#website_url').val() + 'api/store/brands'
        },
		defaults: {
            name: ''
		}
	});
	
	return BrandModel;
});