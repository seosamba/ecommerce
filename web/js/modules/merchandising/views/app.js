define([
	'backbone',
    '../../coupons/views/coupon_form',
    '../../coupons/views/coupons_table'
], function(Backbone,
            CouponFormView,
            CouponsTableView){
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