define([
	'Underscore',
	'Backbone',
    'modules/zones/models/zone'
], function(_, Backbone, ZoneModel){

    var zoneCollection = Backbone.Collection.extend({
        model: ZoneModel,
        url: $('#website_url').val()+'plugin/shopping/run/getdata/type/zones/id'
    });

	return zoneCollection;
});