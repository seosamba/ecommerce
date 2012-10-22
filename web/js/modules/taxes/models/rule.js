define([
	'underscore',
	'backbone'
], function(_, Backbone){

    var ruleModel = Backbone.Model.extend({
        urlRoot: $('#website_url').val()+'api/store/taxes/id',
        defaults: {
            isDefault: 0,
            zoneId: null,
            rate1: 0,
            rate2: 0,
            rate3: 0
        }
    })
	
	return ruleModel;
});