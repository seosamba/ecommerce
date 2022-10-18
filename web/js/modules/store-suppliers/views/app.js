define([
	'backbone',
    'text!../templates/paginator.html',
    '../../store-clients/collections/customers',
    './supplier_row',
    '../../companies/collections/company',
    'text!../../groups/templates/groups_dialog.html',
    'text!../../companies/templates/company_dialog.html',
    'text!../../store-clients/templates/email_service.html',
    'text!../../store-clients/templates/crm_service.html',
    'i18n!../../../nls/'+$('input[name=system-language]').val()+'_ln'
    ], function(Backbone, PaginatorTmpl, CustomersCollection, SupplierRowView, CompaniesCollection, GroupsDialogTmpl, CompanyDialogTmpl, EmailServiceDialogTmpl, CrmServiceDialogTmpl, i18n){

    var AppView = Backbone.View.extend({
        el: $('#suppliers'),
        events: {
            'click #export-users': 'exportUsers',
            'click th.sortable': 'sort',
            'click #supplier-details div.toolbar a:first': function() {$('#suppliers-table,#supplier-details, .search-line').toggle()},
            'change #clients-check-all': 'toggleAllPeople',
            'change select#mass-action': 'doAction',
            'keyup #suppliers-search': 'searchSupplier',
            'change .companies-assignment': 'assignSingleCompany',
            'blur input.mobile-number': 'changeMobileNumber',
            'click td.paginator a.page': 'navigate',
        },
        templates: {
            paginator: _.template(PaginatorTmpl)
        },
        initialize: function(){
            $('#supplier-details').hide();
            this.customers = new CustomersCollection();
            this.customers.on('reset', this.renderCustomers, this);
            this.customers.server_api.roleId = 'supplier';
            this.customers.pager();
        },
        render: function(){},
        renderCustomer: function(customer){
            customer.set({i18n: i18n});
            var view = new SupplierRowView({model: customer});
            this.$('#supplier-list').append(view.render().el);
        },
        renderCustomers: function(){
            var clientsFilter = this.customers.server_api.clientsFilter;
            $.each($('.clients-filter'), function(index, value) {
                var filterType = $(value).data('filter-type');
                if(filterType == clientsFilter) {
                    $(value).addClass('current');
                } else {
                    $(value).removeClass('current');
                }
            });
            this.$('#supplier-list').empty();
            this.customers.each(this.renderCustomer.bind(this));
            this.customers.info()['i18n'] = i18n;
            this.$('td.paginator').html(this.templates.paginator(this.customers.information));
        },
        exportUsers: function(){
            if ($('#supplier-list tr').length > 1) {
                $('#export-users-form').submit();
            } else {
                showMessage(_.isUndefined(i18n['There are no users for export'])?'There are no users for export':i18n['There are no users for export'], true);
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
                this.customers.server_api.order = key;
                this.customers.pager();
            }
        },
        navigate: function(e){
            e.preventDefault();

            var page = $(e.currentTarget).data('page');
            if ($.isNumeric(page)){
                this.customers.goTo(page);
            } else {
                switch(page){
                    case 'first':
                        this.customers.goTo(this.customers.firstPage);
                        break;
                    case 'last':
                        this.customers.goTo(this.customers.totalPages);
                        break;
                    case 'prev':
                        this.customers.requestPreviousPage();
                        break;
                    case 'next':
                        this.customers.requestNextPage();
                        break;
                }
            }
        },
        showSupplierDetails: function(uid) {
            $.get($('#website_url').val()+'plugin/shopping/run/profile/', {id: uid, userRole:'supplier'}, this.renderCustomerDetails);
        },
        renderCustomerDetails: function(response, status) {
            if (status === "success") {
                $('#suppliers-table, .search-line').hide();
                $('#supplier-details').find('#profile').html(response.responseText).end().show();
            }
        },
        toggleAllPeople: function(e) {
            var value = e.target.checked;
            this.customers.each(function(customer){
                customer.set({checked: value});
            });
        },
        doAction: function(e){
            var method = $(e.target).val();

            method = this[method];
            if (_.isFunction(method)){
                method.call(this);
            }
            $(e.target).val(0);
        },
        deleteSelected: function(){
            var checked = this.customers.checked();
            if (_.isEmpty(checked)){
                return false;
            }
            showConfirm('Are you sure?', function(){
                var self = this,
                    ids = _(checked).pluck('id');
                Backbone.sync('delete', null, {
                    url: $('#website_url').val()+'api/store/customers/',
                    data: JSON.stringify({ids: ids}),
                    success: function(response){
                            var msg = '';
                            _(response).each(function(status, id){
                               if (status === false) {
                                   var model = self.customers.get(id);
                                   if (model) {
                                       msg += (msg.length ? ', ' : '') + model.get('full_name');
                                       model.set('checked', true);
                                   }
                               }else{
                                   $('#supplier-list input[value='+id+']').closest('td').remove();
                               }
                            });
                            if (msg.length) {
                                showMessage(_.isUndefined(i18n['Unable to remove following suppliers'])?'Unable to remove following suppliers':i18n['Unable to remove following suppliers']+': '+msg, true);
                            }else{
                                showMessage(_.isUndefined(i18n['Suppliers deleted'])?'Suppliers deleted':i18n['Suppliers deleted']);
                            }
                    },
                    error: function(xhr, error, msg){
                        if (xhr.status === 404) {
                            showMessage(xhr.responseText, true);
                        }
                    }
                });
            });

        },
        searchSupplier: function(e){
            var term = e.target.value,
                self = this;

            clearTimeout(self.searching);
            self.searching = setTimeout(function(){
                self.customers.server_api.roleId = 'supplier';
                self.customers.server_api.search = term;
                self.applyFilter();
            }, 600);
        },
        applyFilter: function(e) {
            if(typeof e !== 'undefined'){
                e.preventDefault();
            }
            this.customers.ordersChecked = [];
            this.customers.currentPage = 0;
            this.customers.pager();
        },
        assignSingleCompany: function(e){
            var currentTarget = $(e.target),
                companyId = currentTarget.val(),
                userId = currentTarget.closest('tr').find('input[name="select[]"]').val();

            if (companyId == '0') {
                $.ajax({
                    url: $('#website_url').val()+'api/store/companysuppliers/supplierId/'+userId,
                    type: 'DELETE',
                    dataType: 'json',
                    success: function(response){
                        showMessage(_.isUndefined(i18n['Company saved'])?'Company saved':i18n['Company saved']);
                    }
                });
            } else {
                $.ajax({
                    url: $('#website_url').val() + 'api/store/companysuppliers/',
                    data: {
                        id: companyId,
                        suppliersIds: userId.split(','),
                        secureToken: $('.clientsSecureToken').val()
                    },
                    type: 'POST',
                    dataType: 'json',
                    success: function (response) {
                        showMessage(_.isUndefined(i18n['Company saved']) ? 'Company saved' : i18n['Company saved']);
                    }
                });
            }
        },
        assignPassword: function(e){
            var checkedSuppliers = this.customers.checked();
            var customerIds = '';

            if(checkedSuppliers.length == 0){
                return false;
            }
            $.each(checkedSuppliers, function(index, value) {
                customerIds += value.id+',';
            });
            customerIds = customerIds.substring(0, customerIds.length - 1);

            smoke.confirm(_.isUndefined(i18n['Change password for suppliers?'])?'Change password for suppliers?':i18n['Change password for suppliers?'], function(e) {
                if(e) {
                    showSpinner();
                    $.ajax({
                        url: $('#website_url').val()+'api/store/customers/customerIds/'+customerIds+'/changePassword/1/',
                        type: 'PUT',
                        dataType: 'json'

                    }).done(function(response) {
                        hideSpinner();
                        showMessage(_.isUndefined(i18n['Changed'])?'Changed':i18n['Changed']);

                    });
                } else {

                }
            }, {classname:"error", 'ok':'Yes', 'cancel':'No'});


        },
        assignCompany: function(e) {
            var self = this;

            if (!this.companies){
                this.companies = new CompaniesCollection();
                this.companies.perPage = 1000;
                this.companies.fetch({async: false});
            }

            var applyButton  = _.isUndefined(i18n['Apply']) ? 'Apply':i18n['Apply'];
            var supplierButtons = {};

            supplierButtons[applyButton] = function() {

                var companyName = $('input[name=newCompany]').val();

                var checked = self.customers.where({checked: true}),
                    userIds     = _.pluck(checked, 'id');

                if (!userIds.length){
                    return false;
                }
                $.ajax({
                    url: $('#website_url').val() + 'api/store/companysuppliers/',
                    type: 'POST',
                    context: $(this),
                    data: {'companyName': companyName, 'suppliersIds':userIds, 'id':$('#company-list').val()},
                    dataType: 'json',
                    success: function(response){
                        if (response.error == '1') {
                            showMessage(response.responseText, true, 5000);
                            return false;
                        } else {
                            showMessage(_.isUndefined(i18n['Saved. To see the changes please reload the screen.']) ? 'Saved. To see the changes please reload the screen.':i18n['Saved. To see the changes please reload the screen.'], false, 5000);
                            $(this).dialog('close');
                        }

                    }
                });
            };

            var dialog = _.template(CompanyDialogTmpl, {
                companies: this.companies.toJSON(),
                totalProducts: this.customers.totalRecords,
                i18n:i18n
            });
            $(dialog).dialog({
                dialogClass: 'seotoaster',
                buttons: supplierButtons,
                close: function (event, ui) {
                    $(this).dialog('destroy');
                }
            });
            return false;

        },
        emailMarketing: function(e){
            var checkedSuppliers = this.customers.checked();
            var customerIds = [];

            if(checkedSuppliers.length == 0){
                return false;
            }

            $.each(checkedSuppliers, function(index, value) {
                customerIds.push(value.id);
            });

            $.ajax({
                url: $('#website_url').val()+'plugin/apps/run/getEnabledServicesDashboard/serviceType/email/customers/'+customerIds,
                type: 'GET',
                dataType: 'json'

            }).done(function(response) {
                    if(response.error == 1){
                        showMessage(_.isUndefined(i18n['No available services'])?'No available services':i18n['No available services']);
                        return false;
                    }else{
                        var applyButton  = _.isUndefined(i18n['Apply']) ? 'Apply':i18n['Apply'];
                        var assignEmailService = {};
                        var customerIds = response.responseText.clients;
                        var customerIds = customerIds.split(',');


                        var enabledServices = response.responseText.enabledServices,
                            enabledServicesLabels = response.responseText.enabledServicesLabels;

                        assignEmailService[applyButton] = function() {

                            if($("#marketing-services option:selected").val() == 'select'){
                                showMessage(_.isUndefined(i18n['Please choose service'])?'Please choose service':i18n['Please choose service']);
                                return false;
                            }

                            if($("input:checkbox[name=list]:checked").length == 0){
                                showMessage(_.isUndefined(i18n['Please choose list'])?'Please choose list':i18n['Please choose list']);
                                return false;
                            }

                            var lists = [];
                            $("input:checkbox[name=list]:checked").each(function() {
                                lists.push($(this).val());
                            });

                            $.ajax({
                                url: $('#website_url').val()+'plugin/apps/run/sendServicesDashboard/customers/'+customerIds+'/service/'+$("#marketing-services option:selected").val()+'/lists/'+lists,
                                type: 'POST',
                                dataType: 'json'
                                }).done(function(response) {
                                    if(response.error == 0){
                                        showMessage(_.isUndefined(i18n['Emails added'])?'Emails added':i18n['Emails added']);
                                    }else{
                                        showMessage(_.isUndefined(i18n['Something went wrong'])?'Something went wrong':i18n['Something went wrong']);
                                    }
                                })
                        };

                        var dialog = _.template(EmailServiceDialogTmpl, {
                            enabledServices:enabledServices,
                            customerIds:customerIds,
                            i18n:i18n,
                            enabledServicesLabels:enabledServicesLabels
                        });

                        $(dialog).dialog({
                            width: 600,
                            dialogClass: 'seotoaster',
                            resizable:false,
                            buttons: assignEmailService,
                            open: function(event, ui) {
                                $('#marketing-services').on('change',  function(){
                                    if($("#marketing-services option:selected").val() != 'select'){
                                        $.ajax({
                                            url: $('#website_url').val()+'plugin/apps/run/getService/serviceName/'+$("#marketing-services option:selected").val(),
                                            type: 'GET',
                                            dataType: 'json'

                                        }).done(function(response) {
                                            if(response.error == 1){
                                                showMessage(_.isUndefined(i18n['No available lists'])?'No available lists':i18n['No available lists']);
                                                $('#subscribe-list').remove();
                                                return false;
                                            }else{
                                                $('#subscribe-list').remove();
                                                var subscribeList = '<div class="mt10px" id="subscribe-list">';
                                                $.each(response.responseText.list, function(value, listName){
                                                    subscribeList += '<label class="fl-left mr30px pointer">'+listName+' <input type="checkbox" name="list" value="'+value+'"/></label>'
                                                });
                                                subscribeList += '</div>';
                                                $('#marketing-services').after(subscribeList);

                                            }

                                        });
                                    }else{
                                        $('#subscribe-list').remove();
                                    }
                                })

                            },
                            close: function(event, ui){
                                $(this).dialog('close').remove();
                            }
                        });
                        return false;
                    }

            });
        },
        crmMarketing: function(e){
            var checkedSuppliers = this.customers.checked();
            var customerIds = [];

            if(checkedSuppliers.length == 0){
                return false;
            }

            $.each(checkedSuppliers, function(index, value) {
                customerIds.push(value.id);
            });

            $.ajax({
                url: $('#website_url').val()+'plugin/apps/run/getEnabledServicesDashboard/serviceType/crm/customers/'+customerIds,
                type: 'GET',
                dataType: 'json'

            }).done(function(response) {
                if(response.error == 1){
                    showMessage(_.isUndefined(i18n['No available services'])?'No available services':i18n['No available services']);
                    return false;
                }else{
                    var applyButton  = _.isUndefined(i18n['Apply']) ? 'Apply':i18n['Apply'],
                        assignCrmService = {},
                        customerIds = response.responseText.clients,
                        customerIds = customerIds.split(',');


                    var enabledServices = response.responseText.enabledServices,
                        enabledServicesLabels = response.responseText.enabledServicesLabels;

                    assignCrmService[applyButton] = function() {

                        if($("#crm-services option:selected").val() == 'select'){
                            showMessage(_.isUndefined(i18n['Please choose service'])?'Please choose service':i18n['Please choose service']);
                            return false;
                        }

                        if($("input:checkbox[name=list]:checked").length == 0){
                            showMessage(_.isUndefined(i18n['Please choose list'])?'Please choose list':i18n['Please choose list']);
                            return false;
                        }

                        var lists = [];
                        $("input:checkbox[name=list]:checked").each(function() {
                            lists.push($(this).val());
                        });

                        $.ajax({
                            url: $('#website_url').val()+'plugin/apps/run/sendServicesDashboard/customers/'+customerIds+'/service/'+$("#crm-services option:selected").val()+'/lists/'+lists,
                            type: 'POST',
                            dataType: 'json'
                        }).done(function(response) {
                            if(response.error == 0){
                                showMessage(_.isUndefined(i18n['Added'])?'Added':i18n['Added']);
                            }else{
                                showMessage(_.isUndefined(i18n[response.responseText])?response.responseText:i18n[response.responseText], false, 5000);
                            }
                        })
                    };

                    var dialog = _.template(CrmServiceDialogTmpl, {
                        enabledServices:enabledServices,
                        customerIds:customerIds,
                        i18n:i18n,
                        enabledServicesLabels:enabledServicesLabels
                    });

                    $(dialog).dialog({
                        width: 600,
                        dialogClass: 'seotoaster',
                        resizable:false,
                        buttons: assignCrmService,
                        open: function(event, ui) {
                            $('#crm-services').on('change',  function(){
                                if($("#crm-services option:selected").val() != 'select'){
                                    $.ajax({
                                        url: $('#website_url').val()+'plugin/apps/run/getServiceDashboard/serviceName/'+$("#crm-services option:selected").val(),
                                        type: 'GET',
                                        dataType: 'json'

                                    }).done(function(response) {
                                        if(response.error == 1){
                                            showMessage(_.isUndefined(i18n['No available lists'])?'No available lists':i18n['No available lists']);
                                            $('#subscribe-list').remove();
                                            return false;
                                        }else{
                                            $('#subscribe-list').remove();
                                            var subscribeList = '<div class="mt10px" id="subscribe-list">';
                                            $.each(response.responseText.list, function(value, listName){
                                                subscribeList += '<label class="fl-left mr30px pointer">'+listName+' <input type="checkbox" name="list" value="'+value+'"/></label>'
                                            });
                                            subscribeList += '</div>';
                                            $('#crm-services').after(subscribeList);

                                        }

                                    });
                                }else{
                                    $('#subscribe-list').remove();
                                }
                            })

                        },
                        close: function(event, ui){
                            $(this).dialog('close').remove();
                        }
                    });
                    return false;
                }

            });
        },
        changeMobileNumber:function(e){
            var target = e.currentTarget,
                data = {};
            data['mobilePhone'] = $(target).val();
            $.ajax({
               url: $('#website_url').val() + 'api/toaster/users/id/' + $(target).data('uid'),
               method: 'PUT',
               data: JSON.stringify(data),
               complete: function(xhr, status, response) {
                   if (status === 'error'){
                       showMessage(status, true);
                   } else {
                       showMessage(_.isUndefined(i18n['Number saved!'])?'Number saved!':i18n['Number saved!']);
                   }
               }
            });

        }
    });
	
	return AppView;
});