define([
	'backbone',
    '../collections/discount-quantity',
    $('#website_url').val()+'system/js/external/jquery/plugins/DataTables/jquery.dataTables.min.js'
], function(Backbone,
            DiscountQuantityCollection
            ){

    var DiscountQuantityTableView = Backbone.View.extend({
        el: $('#quantity-discount-table'),
        events: {
            'click a[data-role=delete]': 'deleteQuantityDiscount',
            'click a[data-role=edit]'  : 'editQuantityDiscount'
        },
        templates: {},
        initialize: function(options){
            var aoColumnDefs = [
                { "bSortable": false, "aTargets": [ -1 ] }
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
            this.quantityDiscounts.on('destroy', this.renderQuantityDiscounts, this);
        },
        render: function(){
            this.quantityDiscounts.pager();
        },
        renderQuantityDiscounts: function(){
            this.$el.fnClearTable();
            this.quantityDiscounts.each(this.renderQuantityDiscount, this);
        },
        renderQuantityDiscount: function(quantityDiscount){
            var discountPriceType = $('.discount-currency').val(),
                priceSign = '-';
            if(quantityDiscount.get('discountPriceType') === 'percent'){
                discountPriceType = '%';
            }
            if(quantityDiscount.get('discountPriceSign') === 'plus'){
                priceSign = '+';
            }
            this.$el.fnAddData([
                '<span class="discount-quantity">'+quantityDiscount.get('discountQuantity')+'</span>',
                '<span>'+priceSign+' '+quantityDiscount.get('discountAmount')+' '+discountPriceType+'</span>',
                '<span>'+quantityDiscount.get('applyScope')+'</span>',
                '<a class="ticon-pencil icon14" data-role="edit" data-cid="'+quantityDiscount.get('id')+'" href="javascript:;"></a> <a class="ticon-remove error icon14" data-role="delete" data-cid="'+quantityDiscount.get('id')+'" href="javascript:;"></a>'
            ]);
        },
        deleteQuantityDiscount: function(e){
            var cid = $(e.currentTarget).data('cid'),
                model = this.quantityDiscounts.get(cid);
            showConfirm('Are you sure?', function(){
                showSpinner();
                if (model){
                    model.destroy();
                }
            });
        },
        editQuantityDiscount: function(e){
            var cid = $(e.currentTarget).data('cid');
            $.ajax({
                url: $('#website_url').val() + 'api/store/discounts/id/',
                data:{id:cid},
                type: 'GET',
                dataType: 'json'

            }).done(function(response) {
                var discountGlobal = response[0].applyScope;
                if (discountGlobal === 'global') {
                    $('#discount-quantity-global').prop('checked', true);
                } else{
                    $('#discount-quantity-global').prop('checked', false);
                }
                $('#discountQuantity').val(response[0].discountQuantity);
                $('#discount-quantity-price-type').val(response[0].discountPriceType).prop('selected',true);
                $('#discount-quantity-sign').val(response[0].discountPriceSign).prop('selected',true);
                $('#discountAmount').val(response[0].discountAmount).focus();
            })
        }
    });

    return DiscountQuantityTableView;
});