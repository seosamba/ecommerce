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
            url: function(){
                return $('#website_url').val() + 'api/store/tags/'
            }
        },
        paginator_ui: {
            firstPage: 1,
            currentPage: 1,
            perPage: 55,
            nameTag: ''

        },
        server_api: {
            count : true,
            limit : function(){ return this.perPage; },
            offset: function(){ return (this.currentPage - this.firstPage) * this.perPage; },
            name  : function(){ return this.nameTag; }
        },
        parse: function(response, xhr){
            this.totalCount = _.has(response, 'totalCount') ? response.totalCount : response.length;
            this.totalPages = Math.ceil(this.totalCount / this.perPage);
            return _.has(response, 'data') ? response.data : response;
        }
    })
	
	return TagsCollection;
});