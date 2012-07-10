define([
	'Underscore',
	'Backbone'
], function(_, Backbone){
    var templateModel = Backbone.Model.extend({});

	var tempaltesCollellection = Backbone.Collection.extend({
        url: $('#website_url').val()+'storeapi/v1/templates/',
        model: templateModel
    });

	return tempaltesCollellection;
});