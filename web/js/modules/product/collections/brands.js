define([
	'libs/underscore/underscore',
	'libs/backbone/backbone',
    'modules/product/models/brand'
], function(_, Backbone, BrandModel){

    var BrandList = Backbone.Collection.extend({
        model: BrandModel,
        url: $('#websiteUrl').val() + 'plugin/shopping/run/getdata/type/brands'
    });
	return BrandList;
});