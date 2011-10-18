define([
	'libs/underscore/underscore',
	'libs/backbone/backbone'
], function(_, Backbone){
	
	var ProductOption = Backbone.Model.extend({
		defaults: function(){
			return {
				title: '',
				type: 'dropdown'
			}
		}
	});
	
	return ProductOption;
});