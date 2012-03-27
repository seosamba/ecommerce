define([
	'Underscore',
	'Backbone',
    'modules/product/models/brand'
], function(_, Backbone, BrandModel){

    var BrandList = Backbone.Collection.extend({
        model: BrandModel,
        url: $('#website_url').val() + 'plugin/shopping/run/getdata/type/brands'
    });
	return BrandList;
});