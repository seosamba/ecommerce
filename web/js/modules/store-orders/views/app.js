define(['backbone',
    '../collections/orders',
    './order',
    'text!../templates/paginator.html',
    'text!../templates/export_dialog.html',
    'i18n!../../../nls/'+$('input[name=system-language]').val()+'_ln'
], function(Backbone,
        OrdersCollection, OrdersView,
        PaginatorTmpl, ExportTemplate, i18n
    ){
    var MainView = Backbone.View.extend({
        el: $('#store-orders'),
        events: {
            'click #extra-filters-switch': function(){ $('#extra-filters', this.el).slideToggle(); } ,
            'change input.filter': 'applyFilter',
            'change #orders-check-all': 'checkAllOrders',
            'click #orders-filter-apply-btn': 'applyFilter',
            'click td.paginator a.page': 'navigate',
            'click th.sortable': 'sort',
            'click button.change-status': 'changeStatus',
            'click td.shipping-service .setTracking': 'changeTracking',
            'click #orders-filter-reset-btn': 'resetFilter',
            'change select[name="order-mass-action"]': 'massAction',
            'change input[name="check-order[]"]': 'toggleOrder'
        },
        templates: {
            paginator: _.template(PaginatorTmpl)
        },
        initialize: function(){
            this.orders = new OrdersCollection;
            this.orders.ordersChecked = [];
            this.orders.server_api = _.extend(this.orders.server_api, {
                'id': function() { return $('input[name=search]').val(); },
                'filter': function() {
                    return {
                        'product-key': $('input[name=filter-product-key]', '#store-orders form.filters').val(),
                        'status': $('select[name=filter-status]', '#store-orders form.filters').val(),
                        'country': $('select[name=filter-country]', '#store-orders form.filters').val(),
                        'state': $('select[name=filter-state]', '#store-orders form.filters').val(),
                        'carrier': $('select[name=filter-carrier]', '#store-orders form.filters').val(),
                        'date-from': $('input[name=filter-from-date]', '#store-orders form.filters').val(),
                        'date-to': $('input[name=filter-to-date]', '#store-orders form.filters').val(),
                        'amount-from': $('input[name=filter-from-amount]', '#store-orders form.filters').val(),
                        'amount-to': $('input[name=filter-to-amount]', '#store-orders form.filters').val(),
                        'user': $('input[name=user-name]', '#store-orders form.filters').val()
                    };
                }
            });
            this.orders.on('reset', this.renderOrders, this);
            this.orders.pager();
        },
        massAction: function(e){
            var func = $(e.currentTarget).val()+'Action';

            if (_.isFunction(this[func])){
                var orders = this.orders;
                if (orders.length){
                    var self = this;
                    this[func].call(self, orders);
                }
            }
            $(e.currentTarget).val(0);
        },
        checkAllOrders: function(e) {
            var ordersIds = this.orders.ordersChecked;
            var orderIdsExclude = [];
            if(e.target.checked){
                this.orders.each(function(order){
                    ordersIds = _.union(ordersIds, [order.id]);
                    order.set({checked: true});
                });
            }else{
                this.orders.each(function(order){
                    orderIdsExclude = _.union(orderIdsExclude, [order.id]);
                    order.set({checked: false});
                });
                ordersIds = _.difference(ordersIds, orderIdsExclude);
            }
            this.orders.ordersChecked = ordersIds;
        },
        toggleOrder: function(e) {
            var orderId = $(e.target).val();
            if(e.target.checked){
                this.orders.ordersChecked = _.union(this.orders.ordersChecked, [orderId]);
            }else{
                var filtered = _.filter(this.orders.ordersChecked, function(item) {
                    return item !== orderId
                });
                this.orders.ordersChecked = filtered;
            }
        },
        exportOrdersAction: function(){
            if(this.orders.ordersChecked.length){
                var checkedOrders = this.orders.ordersChecked.join(',');
                var exportOrderButton  = _.isUndefined(i18n['Export']) ? 'Export':i18n['Export'];
                var exportOrderButtons = {};
                exportOrderButtons[exportOrderButton] = function() {
                    $(this).dialog('close');
                };
                $.ajax({
                    url: $('#website_url').val()+'plugin/shopping/run/getOrderExportConfig/',
                    type: 'GET',
                    dataType: 'json'

                }).done(function(response) {
                    var dialog = _.template(ExportTemplate, {
                        ordersIds: checkedOrders,
                        i18n:i18n,
                        'defaultConfig': response.responseText.defaultConfig,
                        'exportConfig': response.responseText.export_config
                    });
                    $(dialog).dialog({
                        dialogClass: 'seotoaster',
                        width: '75%',
                        height: '750',
                        resizable: false
                    });
                    return false;
                });

            }
        },
        resetFilters: function(){
            this.$('form.filters > :input').val('');
        },
        render: function(){},
        renderOrder: function(order){
            order.set({useInvoice: $('#invoiceEnable').val()});
            order.set({i18n: i18n});
            var view = new OrdersView({model: order});
            this.$('#orders-list').append(view.render().el);
        },
        renderOrders: function(){
            this.$('#orders-list').empty();
            this.orders.each(this.renderOrder.bind(this));
            this.orders.info()['i18n'] = i18n;
            this.$('td.paginator').html(this.templates.paginator(this.orders.information));
        },
        applyFilter: function(e) {
            if(typeof e !== 'undefined'){
                e.preventDefault();
            }
            this.orders.currentPage = 0;
            this.orders.pager();
        },
        resetFilter: function(e){
            e.preventDefault();
            var $form = $(e.currentTarget).closest('form');
            $form.find('input:text').val('').end()
                 .find('select.filter').val('0').trigger('liszt:updated');
            this.applyFilter();
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
        },
        sort: function(e){
            var $el = $(e.currentTarget),
                key = $el.data('sortkey');

            $el.siblings('.sortable').removeClass('sortUp').removeClass('sortDown');

            if (!!key) {
                if (!$el.hasClass('sortUp') && !$el.hasClass('sortDown')){
                    $el.addClass('sortUp');
                    key += ' ASC';
                } else {
                    if ($el.hasClass('sortUp')){
                        key += ' DESC';
                    }
                    if ($el.hasClass('sortDown')){
                        key += ' ASC';
                    }
                    $el.toggleClass('sortUp').toggleClass('sortDown');
                }
                this.orders.server_api.order = key;
                this.orders.pager();
            }
        },
        changeStatus: function(event){
            var self        = this,
                el          = $(event.currentTarget),
                id          = parseInt(el.closest('div').data('order-id'));

            var model = this.orders.get(id);

            $.ajax({
                url: $('#website_url').val()+'plugin/shopping/run/order?id='+id,
                data: {status: el.data('status')},
                type: 'POST',
                dataType: 'json',
                beforeSend: function(){
                    el.closest('td').html('<img src="'+$('#website_url').val()+'system/images/ajax-loader-small.gif" style="margin: 20px auto; display: block;">');
                },
                success: function(response) {
                    showMessage(_.isUndefined(i18n['Saved'])?'Saved':i18n['Saved'], response.hasOwnProperty('error') && response.error);
                    if (!response.error && response.hasOwnProperty('responseText')){
                        model.set('status', response.responseText.status);
                    }
                }
            });
        },
        changeTracking: function(event){
            var self    = this,
                el      = $(event.currentTarget),
                id      = parseInt(el.closest('tr').find('td.order-id').text());
            var model = this.orders.get(id);

            if (!model) {
                return false;
            }
            smoke.prompt(_.isUndefined(i18n['Insert tracking code for this order'])?'Insert tracking code for this order':i18n['Insert tracking code for this order'], function(value){
                if (value === false) {
                    return value;
                }
                value = $.trim(value);
                if (model.get('shipping_tracking_id') !== value) {
                    $.ajax({
                        url: $('#website_url').val()+'plugin/shopping/run/order?id='+id,
                        data: {shippingTrackingId: value},
                        type: 'POST',
                        dataType: 'json',
                        beforeSend: function(){
                            el.closest('td').html('<img src="'+$('#website_url').val()+'system/images/ajax-loader-small.gif" style="margin: 20px auto; display: block;">');
                        },
                        success: function(response) {
                            console.log(model.toJSON());
                            if (response.hasOwnProperty('error') && !response.error){
                                showMessage(_.isUndefined(i18n['Saved'])?'Saved':i18n['Saved']);
                            }
                            if (response.hasOwnProperty('responseText')){
                                model.set({
                                    'status': response.responseText.status,
                                    'shipping_tracking_id': response.responseText.shippingTrackingId
                                });
                            }
                        }
                    });
                }
            }, {value: model.get('shipping_tracking_id'),
                ok: _.isUndefined(i18n['OK'])?'OK':i18n['OK'],
                cancel: _.isUndefined(i18n['Cancel'])?'Cancel':i18n['Cancel']
            });
        }
    });

    return MainView;
});