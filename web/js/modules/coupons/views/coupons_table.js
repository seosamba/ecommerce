define([
	'backbone',
    '../collections/coupons',
    './coupon_row'
], function(Backbone,
            CouponsCollection,
            CouponRowView){

    var CouponTableView = Backbone.View.extend({
        el: $('#coupon-table'),
        events: {},
        templates: {},
        initialize: function(){
            this.coupons = new CouponsCollection();
            this.coupons.on('reset', this.renderCoupons, this);
            this.coupons.on('add', this.renderCoupon, this);
        },
        render: function(){
            this.coupons.pager();
        },
        renderCoupons: function(coupons){
            var tbody = this.$el.find('tbody');
            tbody.html(coupons.size() ? '' : '<tr><td colspan="'+this.$el.find('thead th').size()+'">You don&#39;t have any coupon yet.</td></tr>');
            coupons.each(this.renderCoupon, this);
        },
        renderCoupon: function(coupon){
            if (this.coupons.size()){

            }
            var view = new CouponRowView({model: coupon});
            this.$el.find('tbody').append(view.render().$el);
        }
    });

    return CouponTableView;
});