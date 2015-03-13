define([
	'backbone',
    '../collections/pickup-location-cat'
], function(Backbone, PickupLocationCategoriesCollection){
    var PickupLocationCatView = Backbone.View.extend({
        el: $('#manage-pickup-locations'),
        events: {
            'click .uploader-category-logo': 'triggerUpload',
            'blur .change-category-label': 'changeCategoryName',
            'click #delete-pickup-location-category': 'deleteCategory',
            'click #new-pickup-location-btn': 'addCategory'
        },
        templates: {

        },
        initialize: function(){
            this.categories = new PickupLocationCategoriesCollection();
            this.categories.on('change', this.render, this);
            this.categories.on('reset', this.renderCategory, this);
            this.categories.on('add', this.renderCategory, this);
        },
        render: function(){
            this.categories.fetch();
        },
        triggerUpload: function() {
            $('#pickup-logo-uploader-pickfiles').trigger('click');
        },
        renderCategory: function(){

            var currentCategoryId = $('.ui-state-active').find('a').data('category-id');

            // Set img
            if(!_.isNull(currentCategoryId)){
                var currentCategory   = this.categories.get(currentCategoryId),
                    websiteUrl        = $('#website_url').val(),
                    src               = websiteUrl+'system/images/noimage.png',
                    timestamp = new Date().getTime(),
                    externalCategory = currentCategory.get('externalCategory');
                if (!_.isNull(externalCategory)) {
                    $('.imported-category').text(externalCategory);
                }
                if (!_.isNull(currentCategory.get('img'))) {
                    src = websiteUrl+'media/'+$('#things-select-folder').val()+'/small/'+currentCategory.get('img');
                }
                $('.uploader-category-logo img').attr('src', src+'?'+timestamp);
            }
        },
        changeCategoryName: function(e) {
            var currentCategoryId = $(".ui-state-active").find('a').data('category-id');
            var categoryName = $(e.currentTarget).val();
            var currentCategory = this.categories.get(currentCategoryId);
            var self = this;

            currentCategory.set('name', categoryName);
            currentCategory.save(currentCategory, {success:function(model, response) {
                $(".ui-state-active").find('a').text(categoryName);
                $('#manage-pickup-locations').tabs("refresh");
                self.categories.get(currentCategoryId).set('name', categoryName);
            }});

        },
        addCategory: function() {
            var name = 'name';
            var self = this;
            $.ajax({
                url: $('#website_url').val()+'api/store/pickuplocationcategories',
                type: 'POST',
                data:{name:name, secureToken:$('.secure-token-pickup-cat').val()},
                dataType: 'json',
                success: function(id) {
                    self.$el.find('.ui-tabs-nav .add-new-pickup-location').before('<li><a data-category-id="'+id+'" href="#pickup-category-'+id+'">'+name+'</a></li>');
                    self.$el.find('.header').after('<div id="pickup-category-'+id+'"></div>');
                    self.$el.tabs('refresh');
                }
            });
        },
        deleteCategory: function(){
            var currentCategoryId = $(".ui-state-active").find('a').data('category-id');
            var index = $('#manage-pickup-locations').tabs('option', 'active');

            var self = this;
            var model = this.categories.get(currentCategoryId);
            showConfirm('Are you sure?', function(){
                if (model){
                    showSpinner();
                    model.destroy({success:function(){
                        var tab = $('#manage-pickup-locations').find('.ui-tabs-nav li:eq('+index+')').remove();
                        var panelId = tab.attr( "aria-controls" );
                        $( "#" + panelId ).remove();
                        $('#manage-pickup-locations').tabs("refresh");
                        $('#edit-pickup-location').trigger('pickupLocation:created');
                        $('#edit-pickup-location').trigger('pickupLocation:deleted');
                        if(_.isNull($(".ui-state-active").find('a').data('category-id'))){
                            self.hideConfig();
                        }
                    }});
                }
            });
        },
        hideConfig: function(){
            $('#pickup-location-config').addClass('hidden');
            $('#edit-pickup-location').attr('method', 'POST');
        }
    });

    return  PickupLocationCatView;
});
