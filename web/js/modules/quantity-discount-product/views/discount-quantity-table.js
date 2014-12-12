define([
    'backbone',
    '../collections/discount-quantity',
    $('#website_url').val() + 'system/js/external/jquery/plugins/DataTables/jquery.dataTables.min.js'
], function (Backbone,
             DiscountQuantityCollection) {

    var DiscountQuantityTableView = Backbone.View.extend({
        el: $('#quantity-discount-table'),
        events: {
            'click a[data-role=delete]': 'deleteQuantityDiscount'
        },
        templates: {},
        initialize: function (options) {
            var aoColumnDefs = [
                {"bSortable": false, "aTargets": [-1]}
            ];

            this.$el.dataTable({
                'sDom': 't<"clearfix"p>',
                "iDisplayLength": 12,
                "bPaginate": true,
                "bAutoWidth": false,
                "aaSorting": [],
                "aoColumnDefs": aoColumnDefs
            });
            this.quantityDiscounts = new DiscountQuantityCollection();

            this.quantityDiscounts.on('reset', this.renderQuantityDiscounts, this);
            this.quantityDiscounts.on('add', this.renderQuantityDiscounts, this);
        },
        render: function () {
            this.quantityDiscounts.pager();
        },
        renderQuantityDiscounts: function () {
            this.$el.fnClearTable();
            this.quantityDiscounts.each(this.renderQuantityDiscount, this);
        },
        renderQuantityDiscount: function (quantityDiscount) {
            var priceType = $('.discount-currency').val(), status = quantityDiscount.get('status');
            if (quantityDiscount.get('priceType') === 'percent') {
                priceType = '%';
            }
            if (status == '') {
                status = 'GLOBAL DISCOUNT';
            }
            if (status === 'disabled') {
                status = 'Disabled discount';
            }

            if (status === 'enabled') {
                status = 'Product specific discount';
            }
            var pId = quantityDiscount.get('productId'), quan = parseInt(quantityDiscount.get('quantity')),
                pt = quantityDiscount.get('priceType'), pa = quantityDiscount.get('amount');
          /*  if (status == 'GLOBAL DISCOUNT') {
            }*/
            this.$el.fnAddData([
                '<span class="discount-quantity">' + quantityDiscount.get('quantity') + '</span>',
                '<span>- ' + quantityDiscount.get('amount') + ' ' + priceType + '</span>',
                '<span>' + status + '</span>',
                '<a class="ticon-remove error icon14 block centered" data-role="delete"  data-cid="' + pId + '" data-quantity="' + quan +
                '" data-amount="' + pa + '" data-type="' + pt + '" data-sign="minus" data-status="' + status + '" href="javascript:;"></a>'
            ]);
        },
        deleteQuantityDiscount: function (e) {
            var self = this,
                messageStatus = 'Are you sure?';
            if(!$(e.currentTarget).data('status')){
                messageStatus = 'We won\'t be applying this global discount offer to this product';
            }

            showConfirm(messageStatus, function () {
                showSpinner();
                $.ajax({
                    url: $('#website_url').val() + 'api/store/productdiscounts/id/' +
                    $(e.currentTarget).data('cid') +
                    '/quantity/' + $(e.currentTarget).data('quantity') +
                    '/amount/' + $(e.currentTarget).data('amount') +
                    '/priceSign/minus' +
                    '/priceType/' + $(e.currentTarget).data('type'),
                    type: 'DELETE',
                    dataType: 'json'
                }).done(function (response) {
                    hideSpinner();
                    self.$el.trigger('discount:deleted');
                })

            })
        }
    });

    return DiscountQuantityTableView;
});