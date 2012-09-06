define([
	'Underscore',
	'Backbone'
], function(_, Backbone){

    var ruleModel = Backbone.Model.extend({
        urlRoot: $('#website_url').val()+'api/store/taxes/id',
        defaults: {
            isDefault: 0,
            zoneId: null,
            rate1: null,
            rate2: null,
            rate3: null
        }
    })
	
	return ruleModel;
});