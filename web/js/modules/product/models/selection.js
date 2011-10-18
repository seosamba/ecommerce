define([
	'libs/underscore/underscore',
	'libs/backbone/backbone'
], function(_, Backbone){
	var Selection = Backbone.Model.extend({
		defaults: function(){
			return {
				title: '',
				priceModifierSign: '+',
				priceModifierType: 'percent',
				priceModifierValue: null,
				weightModifierSign: '+',
				weightModifierValue: null,
				isDefault: 0
			}
		}
	});
	return Selection;
});