define([
    'backbone',
    '../collections/discount-quantity',
    $('#website_url').val() + 'system/js/external/jquery/plugins/DataTables/jquery.dataTables.min.js'
], function (Backbone,
             DiscountQuantityCollection) {

    var DiscountQuantityTableView = Backbone.View.extend({
        el: $('#quantity-discount-table'),
        events: {
            'click a[data-role=delete]': 'deleteQuantityDiscount',
            'click a[data-role=edit]': 'editQuantityDiscount'
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
            var priceType = $('.discount-currency').val(), status = quantityDiscount.get('status'),
                priceSign = '-';
            if (quantityDiscount.get('priceType') === 'percent') {
                priceType = '%';
            }
            if (quantityDiscount.get('priceSign') === 'plus') {
                priceSign = '+';
            }
            if (status == '') {
                status = 'GLOBAL DISCOUNT';
            }

            if (status === 'disabled') {
                status = 'Global discount disabled';
            }

            if (status === 'enabled') {
                status = 'Product specific';
            }
            var pId = quantityDiscount.get('productId'), quan = parseInt(quantityDiscount.get('quantity')), ps = quantityDiscount.get('priceSign'),
                pt = quantityDiscount.get('priceType'), pa = quantityDiscount.get('amount');
            this.$el.fnAddData([
                '<span class="discount-quantity">' + quantityDiscount.get('quantity') + '</span>',
                '<span>' + priceSign + ' ' + quantityDiscount.get('amount') + ' ' + priceType + '</span>',
                '<span>' + status + '</span>',
                '<a class="ticon-pencil icon14" data-role="edit" data-cid="' + pId + '" data-quantity="' + quan +
                '" data-amount="' + pa + '" data-type="' + pt + '" data-sign="' + ps + '" href="javascript:;"></a>' +
                ' <a class="ticon-remove error icon14" data-role="delete"  data-cid="' + pId + '" data-quantity="' + quan +
                '" data-amount="' + pa + '" data-type="' + pt + '" data-sign="' + ps + '" data-status="' + quantityDiscount.get('status') + '" href="javascript:;"></a>'
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
                    '/priceSign/' + $(e.currentTarget).data('sign') +
                    '/priceType/' + $(e.currentTarget).data('type'),
                    type: 'DELETE',
                    dataType: 'json'
                }).done(function (response) {
                    hideSpinner();
                    self.$el.trigger('discount:deleted');
                })

            })
        },
        editQuantityDiscount: function (e) {

                var status = status;
                if (status === 'disabled') {
                    $('#disc-status').prop('checked', true);
                } else {
                    $('#disc-status').prop('checked', false);
                }
                $('#quantity').val($(e.currentTarget).data('quantity'));
                $('#discount-quantity-price-type').val($(e.currentTarget).data('type')).prop('selected', true);
                $('#discount-quantity-sign').val($(e.currentTarget).data('sign')).prop('selected', true);
                $('#amount').val($(e.currentTarget).data('amount')).focus();
            console.log($(e.currentTarget));

        }
    });

    return DiscountQuantityTableView;
});