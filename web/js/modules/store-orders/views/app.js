define(['backbone',
    '../collections/orders',
    './order',
    'text!../templates/paginator.html',
    'text!../templates/export_dialog.html',
    'text!../templates/tracking_code.html',
    'text!../templates/refund_dialog.html',
    'text!../templates/shipping_labels_dates_dialog.html',
    'text!../templates/refund_shipment_dialog.html',
    'text!../templates/send_payment_request_dialog.html',
    'i18n!../../../nls/'+$('input[name=system-language]').val()+'_ln',
    'moment',
    'accounting',
    'tinyMCE'
], function(Backbone,
        OrdersCollection, OrdersView,
        PaginatorTmpl, ExportTemplate, TrackingCodeTemplate, RefundTemplate, ShippingLabelDates, RefundShipmentTmpl, SendPaymentRequestTemplate,i18n, moment, accounting, tinymce
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
            'change #filter-order-type': 'toggleRecurring',
            'click .generate-shipping-order-label' : 'generateShippingLabel',
            'click .refund-shipping-order-label' : 'refundShippingLabel'
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
        refundShippingLabel: function(e)
        {
            e.preventDefault();

            var orderId = $(e.currentTarget).data('order-id'),
                model = this.orders.get(orderId),
                self = this;

            $.ajax({
                url: $('#website_url').val()+'plugin/shopping/run/getRefundShipmentScreenInfo/',
                type: 'POST',
                dataType: 'json',
                data: {'orderId': orderId, 'secureToken': $('.orders-secure-token').val()}
            }).done(function(response){
                console.log(response);
                if (response.error == '1') {
                    showMessage(response.responseText, true, 5000);
                } else {
                    var shipmentRefundButton  = _.isUndefined(i18n['Refund']) ? 'Refund':i18n['Refund'],
                        assignShipmentRefundButtons = {};

                        assignShipmentRefundButtons[shipmentRefundButton] = function() {
                            $('.ui-button').css('zIndex',"101");
                        };

                    var dialog = _.template(RefundShipmentTmpl, {
                            orderId: orderId,
                            i18n:i18n,
                            accounting: accounting,
                            moneyFormat: self.orders.moneyFormat,
                            shippingTaxRate : self.orders.shippingTaxRate,
                            defaultTaxes: self.orders.defaultTaxes,
                            order: self.orders.get(orderId),
                            shipmentRefundButtonStatus: response.responseText.shipment_refund_button_status,
                            shipmentRefundScreenDescription: response.responseText.shipment_refund_screen_description
                        }),
                        availabilityMonths = [],
                        availableDateAndTime = [];


                    $(dialog).dialog({
                        dialogClass: 'seotoaster',
                        width: '50%',
                        resizable: false,
                        buttons: assignShipmentRefundButtons,
                        open: function (event, ui) {
                            if (response.responseText.shipment_refund_button_status === false) {
                                $(".ui-dialog-buttonset").remove();
                            }
                        },
                        close: function (event, ui) {
                            $(this).dialog('destroy');
                        }
                    });


                }
            });
        },
        generateShippingLabel: function(e)
        {
           var confirmMessage = _.isUndefined(i18n['Do you want to specify shipment date?'])?'Do you want to specify shipment date?':i18n['Do you want to specify shipment date?'],
               confirmMessageLabel = _.isUndefined(i18n['Do you want to create a label?'])?'Do you want to create a label?':i18n['Do you want to create a label?'],
               confirmMessageAvailabilityDate = _.isUndefined(i18n['Do you want to use this date for the shipping label?'])?'Do you want to use this date for the shipping label?':i18n['Do you want to use this date for the shipping label?'],
               orderId = $(e.currentTarget).data('order-id'),
               self = this,
               model = this.orders.get(orderId),
               assignAvailabilityDatesButtons = {},
               availabilityButton  = _.isUndefined(i18n['Create label']) ? 'Create label':i18n['Create label'],
               elRow = $(e.currentTarget).closest('tr');

            assignAvailabilityDatesButtons[availabilityButton] = function() {
                $('.ui-dialog').css('zIndex',"101");
                var availabilityDate = $('#shipment-availability-result-'+orderId).data('availability-date'),
                    availabilityTime = $('#shipment-availability-result-'+orderId).data('availability-time');

                if (!$('#shipment-availability-result-'+orderId).data('availability-date')) {
                    showMessage(_.isUndefined(i18n['Please specify shipment date'])?'Please specify shipment date':i18n['Please specify shipment date'], true, 5000);
                    return false;
                }

                if (!$('#shipment-availability-result-'+orderId).data('availability-time')) {
                    showMessage(_.isUndefined(i18n['Please specify shipment time'])?'Please specify shipment time':i18n['Please specify shipment time'], true, 5000);
                    return false;
                }
                smoke.confirm(confirmMessageAvailabilityDate, function (e) {
                    if (e) {
                        self.generateShippingLabelRequest(orderId, availabilityDate, availabilityTime, elRow);
                    }
                }, {
                    ok: _.isUndefined(i18n['Yes']) ? 'Yes' : i18n['Yes'],
                    cancel: _.isUndefined(i18n['No']) ? 'No' : i18n['No']
                });

            };

           smoke.confirm(confirmMessageLabel, function (e) {
               if(e) {
                   var shippingAvailabilityDays = JSON.parse(model.get('shipping_availability_days'));

                    if (_.isEmpty(shippingAvailabilityDays) || _.isNull(shippingAvailabilityDays)) {
                        showMessage(_.isUndefined(i18n['Shipping service does not support shipment label creation'])?'Shipping service does not support shipment label creation':i18n['Shipping service does not support shipment label creation'], true, 5000);
                        return false;
                    }

                   var dialog = _.template(ShippingLabelDates, {
                        orderId: orderId,
                        i18n:i18n,
                        shippingAvailabilityDays: shippingAvailabilityDays,
                        accounting: accounting,
                        moneyFormat: self.orders.moneyFormat,
                        shippingTaxRate : self.orders.shippingTaxRate,
                        defaultTaxes: self.orders.defaultTaxes,
                        order: self.orders.get(orderId)
                   }),
                       availabilityMonths = [],
                       availableDateAndTime = [];

                   _.each(shippingAvailabilityDays.availabilityDates, function(time, date){
                       if (typeof availabilityMonths[moment(date, 'YYYY-MM-DD').format("M")] === 'undefined') {
                           availabilityMonths[moment(date, 'YYYY-MM-DD').format("M")] = [parseInt(moment(date, 'YYYY-MM-DD').format("D"))];
                       } else {
                           availabilityMonths[moment(date, 'YYYY-MM-DD').format("M")].push(parseInt(moment(date, 'YYYY-MM-DD').format("D")));
                       }
                       if (typeof availableDateAndTime[date] === 'undefined') {
                           availableDateAndTime[date] = [time];
                       } else {
                           availableDateAndTime[date].push(time);
                       }
                   });

                   $(dialog).dialog({
                        dialogClass: 'seotoaster',
                        width: '75%',
                        height: '400',
                        resizable: false,
                        buttons: assignAvailabilityDatesButtons,
                        open: function (event, ui) {
                            $('#availability-days-datepicker').datepicker({
                                beforeShowDay: function (date) {
                                    if (typeof availabilityMonths[date.getMonth() + 1] === 'undefined') {
                                        return [false, ''];
                                    }
                                    if (_.contains(availabilityMonths[date.getMonth() + 1], parseInt(date.getDate()))) {
                                        return [true, ''];
                                    }
                                    return [false, ''];
                                },
                                onSelect: function () {
                                    if (typeof availableDateAndTime[$.datepicker.formatDate("yy-mm-dd", $(this).datepicker('getDate'))] !== 'undefined') {
                                        $('#availability-shipment-time-'+orderId).empty();
                                        $('#shipment-availability-summary-'+orderId).find('.shipment-availability-date-summary').empty().text($.datepicker.formatDate("dd M yy", $(this).datepicker('getDate')));
                                        $('#shipment-availability-summary-'+orderId).find('.shipment-availability-time-summary').empty().text('');
                                        $('#shipment-availability-result-'+orderId).data('availability-date', $.datepicker.formatDate("yy-mm-dd", $(this).datepicker('getDate'))).data('availability-time', '');
                                        _.each(availableDateAndTime[$.datepicker.formatDate("yy-mm-dd", $(this).datepicker('getDate'))], function(time, date) {
                                            _.each(time, function(time){
                                                $('#availability-shipment-time-'+orderId).append('<button class="availability-shipment-time btn">'+time+'</button>');
                                            });
                                        });
                                    }
                                }
                            });

                            $('#availability-shipment-time-'+orderId).on('click', '.availability-shipment-time', function(e){
                                var el = $(e.currentTarget),
                                    switchTimeBlock = el.closest('div');

                                switchTimeBlock.find('.availability-shipment-time').removeClass('current');
                                el.addClass('current');
                                $('#shipment-availability-summary-'+orderId).find('.shipment-availability-time-summary').empty().text(el.text());
                                $('#shipment-availability-result-'+orderId).data('availability-time', el.text());
                            });
                        },
                        close: function (event, ui) {
                            $(this).dialog('destroy');
                        }
                   });

                   checkboxRadioStyle();
                   return false;
               }
           });

        },
        generateShippingLabelRequest: function(orderId, availabilityDate, availabilityTime, elRow, regenerate)
        {
            var self = this,
                model = self.orders.get(orderId);
            $.ajax({
                url: $('#website_url').val()+'plugin/shopping/run/shippingLabel/',
                type: 'POST',
                dataType: 'json',
                data: {'orderId': orderId, 'secureToken': $('.orders-secure-token').val(), 'availabilityDate': availabilityDate, availabilityTime: availabilityTime, 'regenerate' : regenerate}
            }).done(function(response) {
                if (response.error == '1') {
                    if (typeof response.responseText.regenerate !== 'undefined') {
                        smoke.confirm(response.responseText.message, function (e) {
                            if (e) {
                                self.generateShippingLabelRequest(orderId, availabilityDate, availabilityTime, elRow, true);
                            }
                        }, {
                            ok: _.isUndefined(i18n['Yes']) ? 'Yes' : i18n['Yes'],
                            cancel: _.isUndefined(i18n['No']) ? 'No' : i18n['No']
                        });
                    } else {
                        showMessage(response.responseText, true, 5000);
                    }
                } else {
                    showMessage(response.responseText.message, false, 5000);
                    if (response.responseText.shipping_label_link) {
                        elRow.find('.shipping-label-link').removeClass('hidden').val(response.responseText.shipping_label_link);
                        model.set({
                            'shipping_label_link': response.responseText.shipping_label_link
                        });

                    }
                    $('.ui-dialog-titlebar-close').trigger('click');
                }
            });
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
                status = el.data('status'),
                subStatus = el.data('sub-status');


            var model = this.orders.get(id),
                realRefundByDefault = this.orders.realRefundByDefault;

            if (status === 'refunded') {
                confirmMessage = _.isUndefined(i18n['Are you sure you want to refund this payment?'])?'Are you sure you want to refund this payment?':i18n['Are you sure you want to refund this payment?'];

                var refundButton  = _.isUndefined(i18n['Refund']) ? 'Refund':i18n['Refund'],
                    assignRefundButtons = {},
                    dialog = _.template(RefundTemplate, {
                        i18n:i18n,
                        orderId: id,
                        gateway: model.get('gateway'),
                        total: model.get('total'),
                        realRefundByDefault: realRefundByDefault
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
            } else if(status === 'partial') {
                if (subStatus === 'completed') {
                    confirmMessage = _.isUndefined(i18n['Are you sure you want to mark order as paid?']) ? 'Are you sure you want to mark order as paid?' : i18n['Are you sure you want to mark order as paid?'];
                    smoke.confirm(confirmMessage, function (e) {
                        if (e) {
                            $.ajax({
                                url: $('#website_url').val() + 'plugin/shopping/run/changeOrderStatus/',
                                type: 'POST',
                                dataType: 'json',
                                data: {
                                    'orderId': id,
                                    'secureToken': $('.orders-secure-token').val()
                                },
                                success: function (response) {
                                    if (response.error === 1) {
                                        showMessage(response.responseText, true, 5000);
                                    } else {
                                        showMessage(response.responseText, false, 5000);
                                        model.set('status', subStatus);
                                        $('.ui-dialog-titlebar-close').trigger('click');
                                    }
                                }
                            });

                        }

                    }, {
                        ok: _.isUndefined(i18n['Yes']) ? 'Yes' : i18n['Yes'],
                        cancel: _.isUndefined(i18n['No']) ? 'No' : i18n['No']
                    });

                    return '';
                }


                confirmMessage = _.isUndefined(i18n['Are you sure you want to send payment request?']) ? 'Are you sure you want to send payment request?' : i18n['Are you sure you want to send payment request?'];

                var partialButton  = _.isUndefined(i18n['Send payment request']) ? 'Send payment request':i18n['Send payment request'],
                    assignPartialButtons = {},
                    dialog = _.template(SendPaymentRequestTemplate, {
                        i18n:i18n,
                        orderId: id,
                        total: model.get('total')
                    });

                assignPartialButtons[partialButton] = function() {
                    $('.ui-dialog').css('zIndex', "101");
                    smoke.confirm(confirmMessage, function (e) {
                        if (e) {
                            $.ajax({
                                url: $('#website_url').val() + 'plugin/shopping/run/sendPaymentInfoEmail/',
                                type: 'POST',
                                dataType: 'json',
                                data: {
                                    'orderId': $('#send-notification-order-id').val(),
                                    'secureToken': $('.orders-secure-token').val(),
                                    'sendPaymentRequestMessage': tinymce.activeEditor.getContent()
                                },
                                success: function (response) {
                                    if (response.error === 1) {
                                        showMessage(response.responseText, true, 5000);
                                    } else {
                                        showMessage(response.responseText, false, 5000);
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
                    buttons: assignPartialButtons,
                    resizable: false,
                    open: function (event, ui) {
                        tinymce.remove();
                        self.initTiny(self.orders.sendPaymentInfoDefaultText);
                    },
                    close: function (event, ui) {
                        tinymce.remove();
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
                    shippingTrackingCodeId: response.responseText.shippingTrackingCodeId,
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
                                text =  $('#shippingTrackingId').val(),
                                data = {
                                    trackingUrlId: trackingUrlId,
                                    shippingTrackingId: text,
                                    id:id
                                };

                            $.ajax({
                                url: $('#website_url').val()+'plugin/shopping/run/order',
                                data: data,
                                type: 'POST',
                                dataType: 'json',
                                beforeSend: function(){
                                    el.closest('td').find('.tracking-info').hide();
                                    el.closest('td').find('.ajax-loader').show();
                                },
                                success: function(response) {
                                    if (response.hasOwnProperty('error') && !response.error){
                                        showMessage(_.isUndefined(i18n['Saved'])?'Saved':i18n['Saved']);
                                    }
                                    if (response.hasOwnProperty('responseText')){
                                        var trackingCodeText = el.closest('td').find('.tracking-code-text').text();

                                        model.set({
                                            'status': response.responseText.status,
                                            'shipping_tracking_id': response.responseText.shippingTrackingId
                                        });
                                    }

                                    if(trackingCodeText == response.responseText.shippingTrackingId) {
                                        el.closest('td').find('.ajax-loader').hide();
                                        el.closest('td').find('.tracking-info').show();
                                    }
                                    $('#tracking-dialog').dialog('close');
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
        },
        initTiny: function (sendPaymentInfoDefaultText){
            var websiteUrl = $('#website_url').val(), self = this;
            tinymce.init({
                script_url              : websiteUrl+'system/js/external/tinymce/tinymce.gzip.php',
                selector                : "#send-payment-notification-info",
                skin                    : 'seotoaster',
                menubar                 : false,
                browser_spellcheck      : true,
                resize                  : false,
                convert_urls            : false,
                relative_urls           : false,
                statusbar               : false,
                allow_script_urls       : true,
                force_p_newlines        : true,
                forced_root_block       : false,
                entity_encoding         : "raw",
                plugins                 : [
                    "advlist lists link anchor image charmap visualblocks code media table paste textcolor fullscreen autolink"
                ],
                toolbar1                : "leadshortcode bold italic underline alignleft aligncenter alignright alignjustify | bullist numlist forecolor backcolor | link unlink image media hr | formatselect | fontsizeselect | pastetext code | fullscreen | spellcheckbtn",
                fontsize_formats        : "8px 10px 12px 14px 16px 18px 24px 36px",
                block_formats           : "Block=div;Paragraph=p;Block Quote=blockquote;Cite=cite;Address=address;Code=code;Preformatted=pre;H2=h2;H3=h3;H4=h4;H5=h5;H6=h6",
                image_advtab            : true,
                extended_valid_elements : "a[*],input[*],select[*],textarea[*]",
                setup                   : function(ed){
                    var keyTime = null;
                    ed.on('change blur keyup', function(ed, e){
                        self.dispatchEditorKeyup(ed, e, keyTime);
                        this.save();
                    });
                    ed.on('init', function (e) {
                        //this gets executed AFTER TinyMCE is fully initialized
                        ed.setContent(sendPaymentInfoDefaultText);
                    });
                }
            });

            this.tinimce = tinymce;
        },
        dispatchEditorKeyup: function(editor, event, keyTime) {
            var keyTimer = keyTime;
            if(keyTimer === null) {
                keyTimer = setTimeout(function() {
                    keyTimer = null;
                }, 1000)
            }
        },
    });

    return MainView;
});
