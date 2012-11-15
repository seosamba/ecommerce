define([
	'underscore',
	'backbone'
], function(_, Backbone){
	
	var TagModel = Backbone.Model.extend({
		urlRoot: function(){ return $('#website_url').val() + 'api/store/tags/id/' },
		defaults: function(){
			return {
				name: ''
			};
		}		
	});
	
	return TagModel;
});