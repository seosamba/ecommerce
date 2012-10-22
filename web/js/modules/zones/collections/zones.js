define([
	'underscore',
	'backbone',
    '../models/zone'
], function(_, Backbone, ZoneModel){

    var zoneCollection = Backbone.Collection.extend({
        model: ZoneModel,
        url: $('#website_url').val()+'api/store/zones/id'
    });

	return zoneCollection;
});