define([
	'backbone',
    './couponform',
    '../../coupons/views/coupons_table'
], function(Backbone,
            CouponFormView,
            CouponsTableView){
    var MainView = Backbone.View.extend({
        el: $('#merchandising'),
        events: {},
        templates: {},
        initialize: function(){
            this.couponFormView = new CouponFormView();
            this.couponFormView.app = this;
            this.couponFormView.render();

            this.couponsTable = new CouponsTableView();
        }
    });

    return MainView;
});