/**
 * images.js
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */

define(['backbone', 'backbone.paginator'], function(Backbone){
    var ImagesCollection = Backbone.Paginator.clientPager.extend({
        paginator_core: {
            'type': 'POST',
            'dataType': 'json',
            'url': function(){
                return $('#website_url').val() + 'backend/backend_media/getdirectorycontent';
            }
        },
        paginator_ui: {
            firstPage: 1,
            currentPage: 1,
            totalPages: 3,
            perPage: 50
        },
        server_api: {
            folder: null
        },
        flush: function(){
            this.models = [];
            this.currentPage = this.firstPage;
            if (_.has(this, 'origModels')){
                this.origModels = undefined;
            }

            return this;
        },
        parse: function(response, xhr){
            if (_.has(response, 'imageList') && response.imageList.length) {
                return response.imageList
            }
            return [];
        }
    }) ;

    return ImagesCollection;
});