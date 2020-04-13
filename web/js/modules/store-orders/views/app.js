define(['backbone',
    '../collections/orders',
    './order',
    'text!../templates/paginator.html',
    'text!../templates/export_dialog.html',
    'text!../templates/tracking_code.html',
    'text!../templates/refund_dialog.html',
    'i18n!../../../nls/'+$('input[name=system-language]').val()+'_ln'
], function(Backbone,
        OrdersCollection, OrdersView,
        PaginatorTmpl, ExportTemplate, TrackingCodeTemplate, RefundTemplate, i18n
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
            'click .sendInvoice': 'sendInvoice',
            'click #orders-filter-reset-btn': 'resetFilter',
            'change select[name="order-mass-action"]': 'massAction',
            'change input[name="check-order[]"]': 'toggleOrder',
            'change #filter-order-type': 'toggleRecurring'
        },
        templates: {
            paginator: _.template(PaginatorTmpl)
        },
        initialize: function(){
            this.orders = new OrdersCollection;
            this.orders.ordersChecked = [];

            var options = this.getParams();
            if (!_.isEmpty(options)) {
                if (typeof options.filter !== 'undefined') {
                    console.log(options);
                    var withDetailedFilters = false;
                    if (typeof options.filter_from_date !== 'undefined') {
                        $('#orders-filter-fromdate').val(options.filter_from_date);
                        withDetailedFilters = true;
                    }

                    if (typeof options.filter_to_date !== 'undefined') {
                        $('#orders-filter-todate').val(options.filter_to_date);
                        withDetailedFilters = true;
                    }

                    if (typeof options.filter_status !== 'undefined') {
                        $('#filter-status').val(options.filter_status.split(',')).trigger('chosen:updated');
                        withDetailedFilters = true;
                    }

                    if (typeof options.filter_by_coupon_code !== 'undefined') {
                        $('#filter-by-coupon-code').val(options.filter_by_coupon_code);
                        withDetailedFilters = true;
                    }

                    if (typeof options.filter_from_amount !== 'undefined') {
                        $('input[name=filter-from-amount]', '#store-orders form.filters').val(options.filter_from_amount);
                        withDetailedFilters = true;
                    }

                    if (typeof options.filter_from_amount !== 'undefined') {
                        $('input[name=filter-to-amount]', '#store-orders form.filters').val(options.filter_from_to);
                        withDetailedFilters = true;
                    }

                    if (typeof options.filter_order_type !== 'undefined') {
                        $('#filter-order-type').val(options.filter_order_type.split(',')).trigger('chosen:updated');
                        withDetailedFilters = true;
                    }

                    if (typeof options.filter_country !== 'undefined') {
                        $('#filter-country').val(options.filter_country.split(',')).trigger('chosen:updated');
                        withDetailedFilters = true;
                    }

                    if (typeof options.filter_state !== 'undefined') {
                        $('#filter-state').val(options.filter_state.split(',')).trigger('chosen:updated');
                        withDetailedFilters = true;
                    }

                    if (typeof options.filter_carrier !== 'undefined') {
                        $('#filter-carrier').val(options.filter_carrier.split(',')).trigger('chosen:updated');
                        withDetailedFilters = true;
                    }

                    if (typeof options.exclude_quotes !== 'undefined') {
                        if (options.exclude_quotes === '1') {
                            $('#exclude-quotes-from-search').prop('checked', true);
                            withDetailedFilters = true;
                        }
                    }

                    if (typeof options.is_gift !== 'undefined') {
                        if (options.is_gift === '1') {
                            $('#is-a-gift').prop('checked', true);
                            withDetailedFilters = true;
                        }
                    }

                    if (typeof options.filter_by_order_id !== 'undefined') {
                        $('input[name=search]').val(options.filter_by_order_id);
                    }

                    if (typeof options.filter_product_key!== 'undefined') {
                        $('input[name=filter-product-key]').val(options.filter_product_key);
                    }

                    if (typeof options.filter_by_user_name !== 'undefined') {
                        $('input[name=user-name]').val(options.filter_by_user_name);
                    }

                    if (withDetailedFilters === true) {
                        $('#extra-filters').slideToggle();
                    }
                }
            }

            this.orders.server_api = _.extend(this.orders.server_api, {
                'id': function() { return $('input[name=search]').val(); },
                'filter': function() {
                    return {
                        'product-key': $('input[name=filter-product-key]', '#store-orders form.filters').val(),
                        'status': $('#filter-status', '#store-orders form.filters').val(),
                        'country': $('select[name=filter-country]', '#store-orders form.filters').val(),
                        'state': $('select[name=filter-state]', '#store-orders form.filters').val(),
                        'carrier': $('select[name=filter-carrier]', '#store-orders form.filters').val(),
                        'date-from': $('input[name=filter-from-date]', '#store-orders form.filters').val(),
                        'date-to': $('input[name=filter-to-date]', '#store-orders form.filters').val(),
                        'amount-from': $('input[name=filter-from-amount]', '#store-orders form.filters').val(),
                        'amount-to': $('input[name=filter-to-amount]', '#store-orders form.filters').val(),
                        'user': $('input[name=user-name]', '#store-orders form.filters').val(),
                        'filter-order-type': $('select[name=filter-order-type]', '#store-orders form.filters').val(),
                        'filter-recurring-order-type': $('select[name=filter-recurring-order-type]', '#store-orders form.filters').val(),
                        'filter-by-coupon': $('input[name=filter-by-coupon-code]', '#store-orders form.filters').val(),
                        'filter-exclude-quotes': function() { if($('#exclude-quotes-from-search').is(':checked')){ return '1' } else { return '0'}; },
                        'is_gift': function() { if($('#is-a-gift').is(':checked')){ return '1' } else { return '0'}; }
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
            if (typeof _checkboxRadio === "function")  {
                _checkboxRadio();
            }
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
            if (typeof _checkboxRadio === "function")  {
                _checkboxRadio();
            }
        },
        exportOrdersAction: function(){
            var orders = this.orders.ordersChecked.join(',');
            var filters = this.orders.server_api.filter();
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
                    ordersIds: orders,
                    filters: $.param(filters),
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
            this.orders.ordersChecked = [];
            this.orders.currentPage = 0;
            this.orders.pager();
        },
        resetFilter: function(e){
            e.preventDefault();
            var $form = $(e.currentTarget).closest('form');
            $form.find('input:text').val('').end()
                 .find('select.filter').val('0').trigger('chosen:updated');

            $('#exclude-quotes-from-search').prop('checked', false);
            $('#is-a-gift').prop('checked', false);
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
                id          = parseInt(el.closest('div').data('order-id')),
                confirmMessage = _.isUndefined(i18n['Are you sure you want to change status for this order?'])?'Are you sure you want to change status for this order?':i18n['Are you sure you want to change status for this order?'],
                status = el.data('status');

            var model = this.orders.get(id);

            if (status === 'refunded') {
                confirmMessage = _.isUndefined(i18n['Are you sure you want to refund this payment?'])?'Are you sure you want to refund this payment?':i18n['Are you sure you want to refund this payment?'];

                var refundButton  = _.isUndefined(i18n['Refund']) ? 'Refund':i18n['Refund'],
                    assignRefundButtons = {},
                    dialog = _.template(RefundTemplate, {
                        i18n:i18n,
                        orderId: id,
                        gateway: model.get('gateway'),
                        total: model.get('total')
                    });

                assignRefundButtons[refundButton] = function() {
                    $('.ui-dialog').css('zIndex',"101");
                    smoke.confirm(confirmMessage, function (e) {
                        if (e) {
                            $.ajax({
                                url: $('#website_url').val() + 'plugin/shopping/run/refundPayment/orderId/',
                                type: 'POST',
                                dataType: 'json',
                                data: {
                                    'orderId': $('#refund-order-id').val(),
                                    'refundAmount': $('.partial-refund-amount').val(),
                                    'refundInfo': $('.refund-info').val(),
                                    'secureToken': $('.orders-secure-token').val(),
                                    'paymentGateway': model.get('gateway'),
                                    'refundUsingPaymentGateway': $('input.use-refund-payment-gateway').is(':checked') ? 1 : 0,
                                    'refundTax': $('.refund-tax').val()
                                },
                                success: function (response) {
                                    if (response.error === 1) {
                                        showMessage(response.responseText, true, 5000);
                                    } else {
                                        showMessage(response.responseText.message, false, 5000);
                                        model.set('status', status);
                                        model.set('total', response.responseText.total);
                                        $('.ui-dialog-titlebar-close').trigger('click');
                                    }
                                }
                            });

                        }

                    }, {
                        ok: _.isUndefined(i18n['Yes']) ? 'Yes' : i18n['Yes'],
                        cancel: _.isUndefined(i18n['No']) ? 'No' : i18n['No']
                    });

                };

                $(dialog).dialog({
                    dialogClass: 'seotoaster',
                    width: '35%',
                    height: '450',
                    buttons: assignRefundButtons,
                    resizable: false,
                    open: function (event, ui) {

                    },
                    close: function (event, ui) {
                        $(this).dialog('destroy');
                    }
                });

            } else {
                smoke.confirm(confirmMessage, function (e) {
                    if (e) {
                        $.ajax({
                            url: $('#website_url').val() + 'plugin/shopping/run/order?id=' + id,
                            data: {status: status},
                            type: 'POST',
                            dataType: 'json',
                            beforeSend: function () {
                                el.closest('td').html('<img src="' + $('#website_url').val() + 'system/images/ajax-loader-small.gif" style="margin: 20px auto; display: block;">');
                            },
                            success: function (response) {
                                showMessage(_.isUndefined(i18n['Saved']) ? 'Saved' : i18n['Saved'], response.hasOwnProperty('error') && response.error);
                                if (!response.error && response.hasOwnProperty('responseText')) {
                                    model.set('status', response.responseText.status);
                                }
                            }
                        });
                    }
                }, {
                    ok: _.isUndefined(i18n['Yes']) ? 'Yes' : i18n['Yes'],
                    cancel: _.isUndefined(i18n['No']) ? 'No' : i18n['No']
                });
            }
        },
        changeTracking: function(e){
            var self    = this,
                el      = $(e.currentTarget),
                id      = parseInt(el.closest('tr').find('td.order-id').text()),
                model = this.orders.get(id);

            $.ajax({
                url: $('#website_url').val()+'plugin/shopping/run/fetchShippingUrlNames/orderId/' + id,
                type: 'GET',
                dataType: 'json'

            }).done(function(response) {
                var dialog = _.template(TrackingCodeTemplate, {
                    data:response.responseText.data,
                    defaultSelection: response.responseText.defaultSelection,
                    trackingId: response.responseText.trackingId,
                    trackingName: response.responseText.trackingName,
                    orderId: id,
                    i18n:i18n
                });

                $(dialog).dialog({
                    width: 600,
                    dialogClass: 'seotoaster',
                    resizable:false,
                    open: function(event, ui) {
                        $('.save-data').on('click', function(e){
                            e.preventDefault();
                            var  trackingUrlId =  $('#marketing-services').val(),
                                text =  $('#shippingTrackingCode').val(),
                                data = {
                                    trackingUrlId: trackingUrlId,
                                    shippingTrackingCode: text,
                                    id:id
                                };

                            $.ajax({
                                url: $('#website_url').val()+'plugin/shopping/run/order',
                                data: data,
                                type: 'POST',
                                dataType: 'json',
                                beforeSend: function(){
                                    el.closest('td').html('<img src="'+$('#website_url').val()+'system/images/ajax-loader-small.gif" style="margin: 20px auto; display: block;">');
                                },
                                success: function(response) {
                                    if (response.hasOwnProperty('error') && !response.error){
                                        showMessage(_.isUndefined(i18n['Saved'])?'Saved':i18n['Saved']);
                                    }
                                    if (response.hasOwnProperty('responseText')){
                                        model.set({
                                            'status': response.responseText.status,
                                            'shipping_tracking_code': response.responseText.shippingTrackingCode
                                        });
                                    }
                                }
                            });
                        });

                    },
                    close: function(event, ui){
                        $(this).dialog('close').remove();
                    }
                });
            });
        },
        sendInvoice: function(event){
            var el = $(event.currentTarget),
                id = parseInt(el.closest('tr').find('td.order-id').text()),
                tdElement  = el.closest('td'),
                tdContent =  tdElement.html();
            $.ajax({
                url: $('#website_url').val()+'plugin/invoicetopdf/run/sendInvoiceToUser/',
                data: {
                    'cartId': id,
                    'dwn': 0
                },
                type: 'POST',
                dataType: 'json',
                beforeSend: function(){
                    tdElement.html('<img src="'+$('#website_url').val()+'system/images/ajax-loader-small.gif" style="margin: 20px auto; display: block;">');
                },
                success: function(response) {
                    if (response.hasOwnProperty('error')) {
                        if (!response.error) {
                            showMessage(_.isUndefined(i18n['Invoice has been sent']) ? 'Invoice has been sent' : i18n['Invoice has been sent']);
                        } else {
                            showMessage(response.responseText, false, 5000);
                        }
                    }
                    tdElement.html(tdContent);
                }
            });
        },
        toggleRecurring : function(e){
            var currentType = $(e.currentTarget).val();
            if (currentType === 'recurring_id') {
                $('.recurring-filters').removeClass('hidden');
            } else {
                $('.recurring-filters').addClass('hidden');
            }
        },
        getParams:  function () {
            var result = {},
                tmpData = [];
            location.search
                .substr(1)
                .split("&")
                .forEach(function (item) {
                    tmpData = item.split("=");
                    result[decodeURIComponent(tmpData[0])] = decodeURIComponent(tmpData[1]);
                });
            return result;
        }
    });

    return MainView;
});
