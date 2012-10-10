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
            perPage: 36
        },
        server_api: {
            count: true,
            limit: function(){ return this.perPage; },
            offset: function(){ return this.currentPage * this.perPage; }
        },
        parse: function(response, xhr){
            this.totalCount = _.has(response, 'totalCount') ? response.totalCount : response.length;
            this.totalPages = Math.floor(this.totalCount / this.perPage);
            return _.has(response, 'data') ? response.data : response;
        }
    })
	
	return TagsCollection;
});