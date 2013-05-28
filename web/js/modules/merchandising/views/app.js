define([
	'backbone',
    '../../coupons/views/coupon_form',
    '../../coupons/views/coupons_table',
    '../../groups/views/group_form',
    '../../groups/views/group_table'
], function(Backbone,
            CouponFormView,
            CouponsTableView,
            GroupFormView,
            GroupsTableView){
    var MainView = Backbone.View.extend({
        el: $('#merchandising'),
        events: {
            'submit form.binded-plugin': 'formSubmit'
        },
        templates: {},
        initialize: function(){
            this.couponForm = new CouponFormView();
            this.couponForm.render();

            this.couponsTable = new CouponsTableView();
            this.couponsTable.render();

            this.groupForm = new GroupFormView();
            this.groupForm.render();

            this.groupsTable = new GroupsTableView();
            this.groupsTable.render();

            this.groupForm.$el.on('group:created', _.bind(this.groupsTable.render, this.groupsTable));
            this.couponForm.$el.on('coupon:created', _.bind(this.couponsTable.render, this.couponsTable));
        },
        formSubmit: function(e) {
            var $form = $(e.target);
            $.ajax({
                url: $form.attr('action'),
                type: $form.attr('method'),
                data: $form.serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.hasOwnProperty('responseText')) {
                        showMessage(response.responseText, response.error);
                    }
                }
            });
            return false;
        }
    });

    return MainView;
});