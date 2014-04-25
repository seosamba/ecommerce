define([
	'backbone',
    '../collections/pickup-location-cat'
], function(Backbone, PickupLocationCategoriesCollection){
    var PickupLocationCatView = Backbone.View.extend({
        el: $('#manage-pickup-locations'),
        events: {
            'click .uploader-category-logo': 'triggerUpload'
        },
        templates: {

        },
        initialize: function(){
            this.categories = new PickupLocationCategoriesCollection();
            this.categories.fetch();
        },
        render: function(){
             return this;
        },
        triggerUpload: function() {
            $('#pickup-logo-uploader-pickfiles').trigger('click');
        }
    });

    return  PickupLocationCatView;
});
