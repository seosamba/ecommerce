define([
	'backbone',
	'../models/tag',
    'backbone.paginator'
], function(Backbone, TagModel){
	
    var TagsCollection = Backbone.Paginator.requestPager.extend({
        model: TagModel,
        paginator_core: {
            type: 'GET',
            dataType: 'json',
            url: '/api/store/tags/'
        },
        paginator_ui: {
            firstPage: 0,
            currentPage: 0,
            perPage: 40
        },
        server_api: {
            'limit': function(){ return this.perPage; },
            'offset': function(){ return this.currentPage * this.perPage; }
        }
    })
	
	return TagsCollection;
});