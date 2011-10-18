define([
	'libs/underscore/underscore',
	'libs/backbone/backbone',
	'modules/product/models/selection'
], function(_, Backbone, Selection){
	var Selections = Backbone.Collection.extend({
		model: Selection
	});
	return Selections;
});