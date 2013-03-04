define([
    'backbone',
    '../models/coupon'
], function (Backbone, CouponModel) {

    var CouponsCollection  = Backbone.Collection.extend({
        model: CouponModel,
        url: function(){
            return $('#website_url').val() + 'api/store/coupons/id/';
        }
    });

    return CouponsCollection;
});
