define([
	'underscore',
	'backbone',
    '../models/rule'
], function(_, Backbone, RuleModel){

    var rulesCollection = Backbone.Collection.extend({
        model: RuleModel,
        url: $('#website_url').val()+'api/store/taxes/id'
    });

	return rulesCollection;
});