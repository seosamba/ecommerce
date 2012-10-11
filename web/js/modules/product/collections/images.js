/**
 * images.js
 * @author Pavel Kovalyov <pavlo.kovalyov@gmail.com>
 */

define(['backbone', 'backbone.paginator'], function(Backbone){
    var ImagesCollection = Backbone.Paginator.clientPager.extend({
        paginator_core: {
            'type': 'POST',
            'dataType': 'json',
            'url': '/backend/backend_media/getdirectorycontent'
        },
        paginator_ui: {
            firstPage: 1,
            currentPage: 1,
            totalPages: 10,
            perPage: 40
        },
        server_api: {
            folder: null
        },
        flush: function(){
            this.models = [];
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