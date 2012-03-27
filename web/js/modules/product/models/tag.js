define([
	'Underscore',
	'Backbone'
], function(_, Backbone){
	
	var TagModel = Backbone.Model.extend({
		urlRoot:  $('#website_url').val() + '/plugin/shopping/run/getdata/type/tags/id/',
		defaults: function(){
			return {
				name: ''
			};
		}		
	});
	
	return TagModel;
});