define([
	'backbone',
    '../models/brand'
], function(Backbone, BrandModel){

    var BrandList = Backbone.Collection.extend({
        model: BrandModel,
        url: function(){
            return $('#website_url').val() + 'api/store/brands/';
        }
    });
	return BrandList;
});