define([
	'backbone',
    '../../coupons/views/coupon_form',
    '../../coupons/views/coupons_table',
    '../../groups/views/group_form',
    '../../groups/views/group_table',
    '../../quantity-discount/views/discount-quantity-form',
    '../../quantity-discount/views/discount-quantity-table'
], function(Backbone,
            CouponFormView,
            CouponsTableView,
            GroupFormView,
            GroupsTableView,
            QuantityDiscountForm,
            QuantityDiscountTable){
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

            this.quantityDiscountForm = new QuantityDiscountForm();
            this.quantityDiscountForm.render();

            this.quantityDiscountTable = new QuantityDiscountTable();
            this.quantityDiscountTable.render();

            this.groupForm.$el.on('group:created', _.bind(this.groupsTable.render, this.groupsTable));
            this.couponForm.$el.on('coupon:created', _.bind(this.couponsTable.render, this.couponsTable));
            this.quantityDiscountForm.$el.on('discount:created', _.bind(this.quantityDiscountTable.render, this.quantityDiscountTable));
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