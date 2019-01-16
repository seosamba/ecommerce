define([
	'backbone',
    '../collections/coupons',
    'i18n!../../../nls/'+$('input[name=system-language]').val()+'_ln',
    $('#website_url').val()+'system/js/external/jquery/plugins/DataTables/jquery.dataTables.min.js'
], function(Backbone, CouponsCollection, i18n){

    var CouponTableView = Backbone.View.extend({
        el: $('#coupon-table'),
        events: {
            'click a[data-role=delete]': 'deleteCoupon',
            'mouseover a[data-role=loadProductPage]': 'loadProductPage',
            'click .coupon-code-dashboard': 'setCouponForDashboard'
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
                "iDisplayLength": 7,
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
            var usageInfo = 'unlimited';
            if(coupon.get('scope') === 'client') {
                usageInfo = 'one per client';
            } else if (coupon.get('oneTimeUse') === '1') {
                usageInfo = 'one time';
            }

            this.$el.fnAddData([
                coupon.get('id'),
                (coupon.get('type') === 'freeshipping' ? 'free shipping' : coupon.get('type') ),
                '<a class="coupon-code-dashboard" data-coupon-code-dashboard="'+coupon.get('code')+'" href="'+$('#website_url').val()+'dashboard/orders/" target="_blank">'+coupon.get('code')+'</a>',
                coupon.get('startDate'),
                coupon.get('endDate'),
                coupon.get('allowCombination') === '1' ? 'yes' : 'no',
                usageInfo,
                _.isEmpty(coupon.get('products')) ? 'cart' : _.reduce(coupon.get('products'), function(memo, p){
                    return memo + '<a href="javascript:;" data-role="loadProductPage" data-pid="'+p+'" title="Click to open product page">'+p+'</a>';
                }, ''),
                '<a href="javascript:;" class="tpopup" data-url="'+$('#website_url').val()+'plugin/shopping/run/zones/">'+coupon.get('zoneName')+'</acl>',
                coupon.get('action'),
                '<a class="ticon-remove error icon14" data-role="delete" data-cid="'+coupon.get('id')+'" href="javascript:;"></a>'
            ]);
        },
        deleteCoupon: function(e){
            var cid = $(e.currentTarget).data('cid'),
                couponName = $(e.currentTarget).closest('tr').find('.coupon-code-dashboard').data('coupon-code-dashboard'),
                model = this.coupons.get(cid);

            if (model){
                $.ajax({
                    'url': $('#website_url').val() + 'plugin/shopping/run/checkUseCoupon',
                    'type':'GET',
                    'dataType':'json',
                    'data': {cid: cid}
                }).done(function(response){
                    if (response.error == 1) {
                        showMessage(_.isUndefined(i18n['Can\'t delete coupon!']) ? 'Can\'t delete coupon!':i18n['Can\'t delete coupon!'], true, 5000);
                    } else {
                        if(typeof response.responseText.used !== 'undefined') {
                            showConfirm(couponName + ' ' + response.responseText.used + '. ' + (_.isUndefined(i18n['Are you sure to delete?']) ? 'Are you sure to delete?':i18n['Are you sure to delete?']), function(){
                                model.destroy();
                            });
                        } else {
                            showConfirm(_.isUndefined(i18n['Are you sure to delete?']) ? 'Are you sure to delete?':i18n['Are you sure to delete?'], function(){
                                model.destroy();
                            });
                        }
                    }
                });
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
        },
        setCouponForDashboard: function(e){
            var couponCode = $(e.currentTarget).data('coupon-code-dashboard');
            localStorage.setItem('couponCode', couponCode);
        }
    });

    return CouponTableView;
});