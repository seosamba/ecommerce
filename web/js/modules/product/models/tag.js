define([
	'libs/underscore/underscore',
	'libs/backbone/backbone'
], function(_, Backbone){
	
	var TagModel = Backbone.Model.extend({
		urlRoot:  $('#websiteUrl').val() + '/plugin/shopping/run/getdata/type/tags/id/',
		defaults: function(){
			return {
				name: ''
			};
		}		
	});
	
	return TagModel;
});