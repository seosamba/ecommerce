define([
	'underscore',
	'backbone'
], function(_, Backbone){
	
	var BrandModel = Backbone.Model.extend({
        urlRoot: 'plugin/shopping/run/getdata/type/brands',
		defaults: {
            name: ''
		}
	});
	
	return BrandModel;
});