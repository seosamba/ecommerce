define(['backbone',
    '../collections/orders',
    './order',
    'text!../templates/paginator.html'
], function(Backbone,
        OrdersCollection, OrdersView,
        PaginatorTmpl
    ){
    var MainView = Backbone.View.extend({
        el: $('#store-orders'),
        events: {
            'click #extra-filters-switch': function(){ $('#extra-filters', this.el).slideToggle(); } ,
            'change input.filter': 'applyFilter',
            'click #orders-filter-apply-btn': 'applyFilter',
            'click td.paginator a.page': 'navigate'
        },
        templates: {
            paginator: _.template(PaginatorTmpl)
        },
        initialize: function(){
            this.orders = new OrdersCollection;
            this.orders.server_api = _.extend(this.orders.server_api, {
                'id': function() { return $('input[name=search]').val(); },
                'filter': function() {
                    return {
                        'product-key': $('input[name=filter-product-key]', '#store-orders form.filters').val(),
                        'country': $('select[name=filter-country]', '#store-orders form.filters').val(),
                        'state': $('select[name=filter-state]', '#store-orders form.filters').val(),
                        'carrier': $('select[name=filter-carrier]', '#store-orders form.filters').val(),
                        'date-from': $('input[name=filter-from-date]', '#store-orders form.filters').val(),
                        'date-to': $('input[name=filter-to-date]', '#store-orders form.filters').val(),
                        'amount-from': $('input[name=filter-from-amount]', '#store-orders form.filters').val(),
                        'amount-to': $('input[name=filter-to-amount]', '#store-orders form.filters').val()
                    };
                }
            });
            this.orders.on('reset', this.renderOrders, this);
            this.orders.pager();
        },
        resetFilters: function(){
            this.$('form.filters > :input').val('');
        },
        render: function(){},
        renderOrder: function(order){
            order.set({useInvoice: $('#invoiceEnable').val()});
            var view = new OrdersView({model: order});
            this.$('#orders-list').append(view.render().el);
        },
        renderOrders: function(){
            this.$('#orders-list').empty();
            this.orders.each(this.renderOrder.bind(this));
            this.$('td.paginator').html(this.templates.paginator(this.orders.info()));
        },
        applyFilter: function(){
            this.orders.pager();
        },
        navigate: function(e){
            e.preventDefault();

            var page = $(e.currentTarget).data('page');
            if ($.isNumeric(page)){
                this.orders.goTo(page);
            } else {
                switch(page){
                    case 'first':
                        this.orders.goTo(this.orders.firstPage);
                        break;
                    case 'last':
                        this.orders.goTo(this.orders.totalPages);
                        break;
                    case 'prev':
                        this.orders.requestPreviousPage();
                        break;
                    case 'next':
                        this.orders.requestNextPage();
                        break;
                }
            }
        }
    });

    return MainView;
});