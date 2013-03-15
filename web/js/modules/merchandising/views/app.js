define([
	'backbone',
    '../../coupons/views/coupon_form',
    '../../coupons/views/coupons_table'
], function(Backbone,
            CouponFormView,
            CouponsTableView){
    var MainView = Backbone.View.extend({
        el: $('#merchandising'),
        events: {},
        templates: {},
        initialize: function(){
            this.couponForm = new CouponFormView();
            this.couponForm.render();

            this.couponsTable = new CouponsTableView();
            this.couponsTable.render();

            this.couponForm.$el.on('coupon:created', _.bind(this.couponsTable.render, this.couponsTable));
        }
    });

    return MainView;
});