define([
	'Underscore',
	'Backbone',
    'modules/common/models/state'
], function(_, Backbone, StateModel){
	var statesCollection = Backbone.Collection.extend({
        url: $('#websiteUrl').val()+'/plugin/shopping/run/getdata/type/states/',
        model: StateModel
    })

	return statesCollection;
});