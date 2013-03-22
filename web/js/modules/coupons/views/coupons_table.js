define([
	'backbone',
    '../collections/coupons',
    $('#website_url').val()+'system/js/external/jquery/plugins/DataTables/jquery.dataTables.min.js'
], function(Backbone,
            CouponsCollection
            ){

    var CouponTableView = Backbone.View.extend({
        el: $('#coupon-table'),
        events: {
            'click a[data-role=delete]': 'deleteCoupon'
        },
        templates: {},
        initialize: function(){
            this.$el.dataTable({
                'sDom': 'tp',
                "bPaginate": true,
                "iDisplayLength": 7,
                "bAutoWidth": false
            });
            this.coupons = new CouponsCollection();
            this.coupons.on('reset', this.renderCoupons, this);
            this.coupons.on('add', this.renderCoupon, this);
            this.coupons.on('destroy', this.renderCoupons, this);
        },
        render: function(){
            this.coupons.pager();
        },
        renderCoupons: function(){
            this.$el.fnClearTable();
            this.coupons.each(this.renderCoupon, this);
        },
        renderCoupon: function(coupon){
            this.$el.fnAddData([
                coupon.get('id'),
                coupon.get('type'),
                coupon.get('code'),
                coupon.get('startDate'),
                coupon.get('endDate'),
                coupon.get('allowCombination') === '1' ? 'yes' : 'no',
                coupon.get('scope'),
                coupon.get('action'),
                '<a data-role="delete" data-cid="'+coupon.get('id')+'" href="javascript:;">[x]</a>'
            ]);
        },
        deleteCoupon: function(e){
            var cid = $(e.currentTarget).data('cid');
            console.log(cid);
            var model = this.coupons.get(cid);
            console.log(model);
            if (model){
                model.destroy();
            }
        }
    });

    return CouponTableView;
});