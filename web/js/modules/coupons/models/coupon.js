define([
    'backbone'
], function (Backbone) {
    var CouponModel = Backbone.Model.extend({
        urlRoot: function(){
            return $('#website_url').val() + 'api/store/coupons/id/';
        }
    });

    return CouponModel;
});