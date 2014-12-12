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
            'click a[data-role=delete]': 'deleteQuantityDiscount'
        },
        templates: {},
        initialize: function(options){
            var aoColumnDefs = [
                { "bSortable": false, "aTargets": [ -1 ] }
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
            var discountPriceType = $('.discount-currency').val();
            if(quantityDiscount.get('discountPriceType') === 'percent'){
                discountPriceType = '%';
            }
            this.$el.fnAddData([
                '<span class="discount-quantity">'+quantityDiscount.get('discountQuantity')+'</span>',
                '<span>- '+quantityDiscount.get('discountAmount')+' '+discountPriceType+'</span>',
                '<span>'+quantityDiscount.get('applyScope')+'</span>',
                '<a class="ticon-remove error icon14 block centered" data-role="delete" data-cid="'+quantityDiscount.get('id')+'" href="javascript:;"></a>'
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
        }
    });

    return DiscountQuantityTableView;
});