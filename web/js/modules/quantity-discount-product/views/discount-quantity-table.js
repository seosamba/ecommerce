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
                "iDisplayLength": 5,
                "bPaginate": true,
                "bAutoWidth": false,
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
            var priceType = $('.discount-currency').val(),
                priceSign = '-';
            if (quantityDiscount.get('priceType') === 'percent') {
                priceType = '%';
            }
            if (quantityDiscount.get('priceSign') === 'plus') {
                priceSign = '+';
            }
            this.$el.fnAddData([
                '<span class="discount-quantity">' + quantityDiscount.get('quantity') + '</span>',
                '<span>' + priceSign + ' ' + quantityDiscount.get('amount') + ' ' + priceType + '</span>',
                '<span>' + quantityDiscount.get('status') + '</span>',
                '<a class="ticon-pencil icon14" data-role="edit" data-cid="' + quantityDiscount.get('productId') + '" data-quantity="' + quantityDiscount.get('quantity') +
                    '" href="javascript:;"></a> <a class="ticon-remove error icon14" data-role="delete"  data-quantity="' + quantityDiscount.get('quantity')+ '"  data-cid="'  +
                    quantityDiscount.get('productId') + '"  href="javascript:;"></a>'
            ]);
        },
        deleteQuantityDiscount: function (e) {
            var cid = $(e.currentTarget).data('cid'), quantity = $(e.currentTarget).data('quantity'),
                self = this;
            showConfirm('Are you sure?', function () {
                showSpinner();
                $.ajax({
                    url: $('#website_url').val() + 'api/store/productdiscounts/id/'+cid+'/quantity/'+quantity,
                    type: 'DELETE',
                    dataType: 'json'
                }).done(function (response) {
                    hideSpinner();
                    self.$el.trigger('discount:deleted');
                })

            })
        },
        editQuantityDiscount: function (e) {
            var cid = $(e.currentTarget).data('cid'), quantity = $(e.currentTarget).data('quantity');
            $.ajax({
                url: $('#website_url').val() + 'api/store/productdiscounts/id/',
                data: {
                    id: cid,
                    quantity: quantity
                },
                type: 'GET',
                dataType: 'json'

            }).done(function (response) {
                var status = response[0].status;
                if (status === 'disabled') {
                    $('#disc-status').prop('checked', true);
                } else {
                    $('#disc-status').prop('checked', false);
                }
                $('#quantity').val(response[0].quantity);
                $('#discount-quantity-price-type').val(response[0].priceType).prop('selected', true);
                $('#discount-quantity-sign').val(response[0].priceSign).prop('selected', true);
                $('#amount').val(response[0].amount).focus();
            })
        }
    });

    return DiscountQuantityTableView;
});