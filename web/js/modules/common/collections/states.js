define([
	'underscore',
	'backbone',
    '../models/state'
], function(_, Backbone, StateModel){
	var statesCollection = Backbone.Collection.extend({
        url: function(){ return $('#website_url').val()+'plugin/shopping/run/getdata/type/states/'; },
        model: StateModel
    })

	return statesCollection;
});