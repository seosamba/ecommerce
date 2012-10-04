define([
	'backbone',
    '../models/brand'
], function(Backbone, BrandModel){

    var BrandList = Backbone.Collection.extend({
        model: BrandModel,
        url: '/api/store/brands/'
    });
	return BrandList;
});