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
            'click a[data-role=delete]': 'deleteCoupon',
            'mouseover a[data-role=loadProductPage]': 'loadProductPage'
        },
        templates: {},
        initialize: function(options){
            var aoColumnDefs = [
                { "bSortable": false, "aTargets": [ -1 ] }
            ];

            if (_.isObject(options)){
                if (_.has(options, 'hideProductColumn')){
                    aoColumnDefs.push({ "bVisible": false, "aTargets": [ 7 ] });
                }
            }

            this.$el.dataTable({
                'sDom': 't<"clearfix"p>',
                "bPaginate": true,
                "iDisplayLength": 8,
                "bAutoWidth": false,
                "aoColumnDefs": aoColumnDefs
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
                (coupon.get('type') === 'freeshipping' ? 'free shipping' : coupon.get('type') ),
                coupon.get('code'),
                coupon.get('startDate'),
                coupon.get('endDate'),
                coupon.get('allowCombination') === '1' ? 'yes' : 'no',
                coupon.get('scope') === 'client' ? 'yes' : '-',
                _.isEmpty(coupon.get('products')) ? 'cart' : _.reduce(coupon.get('products'), function(memo, p){
                    return memo + '<a href="javascript:;" data-role="loadProductPage" data-pid="'+p+'" title="Click to open product page">'+p+'</a>';
                }, ''),
                coupon.get('action'),
                '<a class="icon-remove error icon14" data-role="delete" data-cid="'+coupon.get('id')+'" href="javascript:;"></a>'
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
        },
        loadProductPage: function(e){
            var self = e.currentTarget,
                pid = parseInt($(self).data('pid'));
            if (pid){
                $.getJSON($('#website_url').val()+'api/store/products', {id: pid}, function(response){
                    if ($(self).attr('href') === 'javascript:;'){
                        $('a[data-role=loadProductPage][data-pid='+pid+']', $(self).closest('table'))
                            .removeAttr('data-role')
                            .attr({
                                'href': $('#website_url').val()+response.page.url,
                                'target': '_blank'
                            });
                    }
                });
            }
        }
    });

    return CouponTableView;
});