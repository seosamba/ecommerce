define([
	'Underscore',
	'Backbone'
], function(_, Backbone){

    var ruleModel = Backbone.Model.extend({
        urlRoot: $('#websiteUrl').val()+'/plugin/shopping/run/getdata/type/taxrules',
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