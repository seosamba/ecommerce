define([
	'Underscore',
	'Backbone',
    'modules/taxes/models/rule'
], function(_, Backbone, RuleModel){

    var rulesCollection = Backbone.Collection.extend({
        model: RuleModel,
        url: $('#websiteUrl').val()+'plugin/shopping/run/getdata/type/taxrules/id'
    });

	return rulesCollection;
});