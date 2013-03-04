define([
	'backbone',
    './couponform',
    '../collections/coupons',
    'text!../templates/coupon.html'
], function(Backbone,
            CouponFormView,
            CouponsCollection,
            CouponRowTmpl){
    var MainView = Backbone.View.extend({
        el: $('#merchandising'),
        events: {
            'click #coupons table a[data-role]': 'couponAction'
        },
        templates: {
            couponRowTmpl: _.template(CouponRowTmpl)
        },
        initialize: function(){
            this.couponFormView = new CouponFormView();
            this.couponFormView.app = this;
            this.couponFormView.render();

            this.coupons = new CouponsCollection();
            this.coupons.on('reset', this.renderCoupons, this);
            this.coupons.on('add', this.renderCoupon, this);
            this.coupons.fetch();
        },
        renderCoupons: function(coupons){
            var tbody = this.$el.find('table tbody');
            tbody.html(coupons.size() ? '' : '<tr><td colspan="'+this.$el.find('table thead th').size()+'">You don&#39;t have any coupon yet.</td></tr>');
            coupons.each(this.renderCoupon, this);
        },
        renderCoupon: function(coupon){
            if (this.coupons.size()){

            }
            this.$el.find('table tbody').append(this.templates.couponRowTmpl({coupon: coupon}));
        },
        couponAction: function(e){
            var self    = this,
                data    = $(e.currentTarget).data();

            if (!_.isEmpty(data)){
                switch (data.role){
                    case 'delete':
                        if (!_.has(data, 'cid')) return false;
                        var model = this.coupons.get(data.cid);
                        if (model) model.destroy();
                        break;
                    case 'edit':
                        break;
                }
            }

        }
    });

    return MainView;
});