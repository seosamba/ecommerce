define([
	'libs/underscore/underscore',
	'libs/backbone/backbone'
], function(_, Backbone){
	
	var CategoryModel = Backbone.Model.extend({
		urlRoot:  $('#websiteUrl').val() + '/plugin/shopping/run/getdata/type/categories/id/',
		defaults: function(){
			return {
				name: ''
			};
		}		
	});
	
	return CategoryModel;
});