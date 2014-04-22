define([
	'backbone',
    './pickup_location_form',
    './pickup_location_table',
    '../collections/pickup-location'
], function(Backbone,
            PickupLocationFormView, PickupLocationTableView, PickupLocationCollection){
    var MainView = Backbone.View.extend({
        el: $('#manage-pickup-locations'),
        events: {
            'click #new-pickup-location-btn': 'addCategory',
            'click .ui-state-default': 'activeTab',
            'click #delete-pickup-location-category': 'deleteCategory',
            'blur .change-category-label': 'changeCategoryName'
        },
        templates: {},
        initialize: function(){
            showSpinner();
            this.PickupLocationForm = new PickupLocationFormView();
            this.PickupLocationForm.render();

            this.pickupLocationTable = new PickupLocationTableView();
            this.pickupLocationTable.render();

            this.PickupLocationForm.$el.on('pickupLocation:created', _.bind(this.pickupLocationTable.render, this.pickupLocationTable));
        },
        activeTab: function(){
            $('.location-table').removeClass('hidden');
            $('#edit-pickup-location').removeClass('hidden');
            $('.delete-selected-category').removeClass('hidden');
            $('.change-category-label').removeClass('hidden');
            $('.category-label').removeClass('hidden');
            $('#location-edit-id').val('');
            $('#edit-pickup-location').attr('method', 'POST');
            showSpinner();
            this.pickupLocation = new PickupLocationCollection();
            this.pickupLocation.on('reset', this.render, this);
            this.pickupLocationTable.render();
        },
        changeCategoryName: function(){
            var currentCategoryId = $(".ui-state-active").find('a').data('category-id');
            var categoryName = $('.change-category-label').val();
            $.ajax({
                url: $('#website_url').val()+'api/store/pickuplocationcategories/id/'+currentCategoryId+'/categoryName/'+categoryName,
                type: 'PUT',
                dataType: 'json',
                success: function(response) {
                    $(".ui-state-active").find('a').text(response.name);
                    $('#manage-pickup-locations').tabs("refresh");
                }
            });
        },
        deleteCategory: function(){
            var currentCategoryId = $(".ui-state-active").find('a').data('category-id');
            var index = $('#manage-pickup-locations').tabs('option', 'active');
            var self = this;
            showConfirm('Are you sure?', function(){
                $.ajax({
                    url: $('#website_url').val()+'api/store/pickuplocationcategories/id/'+currentCategoryId+'/',
                    type: 'DELETE',
                    dataType: 'json',
                    success: function(id) {
                        var tab = $('#manage-pickup-locations').find('.ui-tabs-nav li:eq('+index+')').remove();
                        var panelId = tab.attr( "aria-controls" );
                        $( "#" + panelId ).remove();
                        $('#manage-pickup-locations').tabs("refresh");
                        $('#edit-pickup-location').trigger('pickupLocation:created');
                        if(_.isNull($(".ui-state-active").find('a').data('category-id'))){
                            self.hideConfig();
                        }
                    }
                });
            });
        },
        addCategory: function() {
            var name = 'name';
            var self = this;
            $.ajax({
                url: $('#website_url').val()+'api/store/pickuplocationcategories',
                type: 'POST',
                data:{name:name},
                dataType: 'json',
                success: function(id) {
                    self.$el.find('.ui-tabs-nav .add-new-pickup-location').before('<li><a data-category-id="'+id+'" href="#pickup-category-'+id+'">'+name+'</a></li>');
                    self.$el.find('.header').after('<div id="pickup-category-'+id+'"></div>');
                    self.$el.tabs('refresh');
                }
            });
        },
        hideConfig: function(){
            $('.location-table').addClass('hidden');
            $('#edit-pickup-location').addClass('hidden');
            $('.delete-selected-category').addClass('hidden');
            $('.change-category-label').addClass('hidden');
            $('#location-edit-id').val('');
            $('.category-label').addClass('hidden');
            $('#edit-pickup-location').attr('method', 'POST');
        }
    });

    return MainView;
});