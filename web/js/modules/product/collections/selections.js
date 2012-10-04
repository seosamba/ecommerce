define([
	'underscore',
	'backbone',
	'../models/selection'
], function(_, Backbone, Selection){
	var Selections = Backbone.Collection.extend({
		model: Selection,
		initialize: function(){
			this.bind('change:_deleted', this.checkDefault, this);
			this.bind('remove', this.checkDefault, this);
		},
		hasDefault: function(){
			var list = this.find(function(selection){
				if (selection.has('_deleted')) return false;
				if (selection.get('isDefault') == '1') return true;
				return false;
			});
			return (list !== undefined);
		},
		checkDefault: function(){
			if (!this.hasDefault()){
				var firstAvailable = this.find(function(selection){
					return !selection.has('_deleted');
				});
				firstAvailable && firstAvailable.set({isDefault: '1'});
			}
		}
	});
	return Selections;
});