define([
	'libs/underscore/underscore',
	'libs/backbone/backbone'
], function(_, Backbone){
	
	var CategoryModel = Backbone.Model.extend({
		defaults: function(){
			return {
				name: ''
			};
		}		
	});
	
	return CategoryModel;
});