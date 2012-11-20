define([
	'underscore',
	'backbone',
    '../models/state'
], function(_, Backbone, StateModel){
	var statesCollection = Backbone.Collection.extend({
        url: function(){ return $('#website_url').val()+'api/store/geo/type/state/'; },
        model: StateModel
    })

	return statesCollection;
});