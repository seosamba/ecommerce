define([
	'backbone'
], function(Backbone){
	var Selection = Backbone.Model.extend({
		defaults: function(){
			return {
				title: '',
				priceSign: '+',
				priceType: 'percent',
				priceValue: null,
				weightSign: '+',
				weightValue: null,
				isDefault: '0'
			}
		}
	});
	return Selection;
});