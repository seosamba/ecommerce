define([
	'backbone',
    '../collections/customers',
    './customer_row',
    '../../groups/collections/group',
    'text!../../groups/templates/groups_dialog.html',
    'text!../templates/email_service.html',
    'text!../templates/crm_service.html',
    'i18n!../../../nls/'+$('input[name=system-language]').val()+'_ln'
    ], function(Backbone, CustomersCollection, CustomerRowView, GroupsCollection, GroupsDialogTmpl, EmailServiceDialogTmpl, CrmServiceDialogTmpl, i18n){

    var AppView = Backbone.View.extend({
        el: $('#clients'),
        events: {
            'click #export-users': 'exportUsers',
            'click #clients-previous': 'goPreviousPage',
            'click #clients-next': 'goNextPage',
            'click th.sortable': 'sort',
            'click #customer-details div.toolbar a:first': function() {$('#clients-table,#customer-details, .search-line').toggle()},
            'change #clients-check-all': 'toggleAllPeople',
            'change select#mass-action': 'doAction',
            'keyup #clients-search': 'searchClient',
            'change select[name=groups]': 'assignGroup',
            'blur input.customer-attribute': 'changeCustomAttr',
            'blur input.mobile-number': 'changeMobileNumber',
            'click th.customer-attribute':'deleteCustomAttr'
        },
        initialize: function(){
            $('#customer-details').hide();
            this.customers = new CustomersCollection();
            this.customers.on('reset', this.render, this);
            this.customers.fetch();
        },
        render: function(){
            $('#customer-list').empty();
            this.customers.each(function(customer){
                var view = new CustomerRowView({model: customer});
                view.render().$el.appendTo('#customer-list');
            });
        },
        exportUsers: function(){
            if ($('#customer-list tr').length > 1) {
                $('#export-users-form').submit();
            } else {
                showMessage(_.isUndefined(i18n['There are no users for export'])?'There are no users for export':i18n['There are no users for export'], true);
            }
        },
        goPreviousPage: function() {
            this.customers.previous();
            return false;
        },
        goNextPage: function() {
            this.customers.next();
            return false;
        },
        sort: function(e) {
            var $el = $(e.target),
                key = $el.data('sortkey');

            $el.siblings('.sortable').removeClass('sortUp').removeClass('sortDown');

            if (!!key) {
                this.customers.order.by = key;
                if (!$el.hasClass('sortUp') && !$el.hasClass('sortDown')){
                    $el.addClass('sortUp');
                    this.customers.order.asc = true;
                } else  {
                    $el.toggleClass('sortUp').toggleClass('sortDown');
                    this.customers.order.asc = !this.customers.order.asc;
                }
                this.customers.fetch()
            }
        },
        showCustomerDetails: function(uid) {
            $.get($('#website_url').val()+'plugin/shopping/run/profile/', {id: uid}, this.renderCustomerDetails);
        },
        renderCustomerDetails: function(response, status) {
            if (status === "success") {
                $('#clients-table, .search-line').hide();
                $('#customer-details').find('#profile').html(response).end().show();
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

            method = this[method]
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
                        //self.customers.fetch().done(function(){
                            var msg = '';
                            _(response).each(function(status, id){
                               if (status === false) {
                                   var model = self.customers.get(id);
                                   if (model) {
                                       msg += (msg.length ? ', ' : '') + model.get('full_name');
                                       model.set('checked', true);
                                   }
                               }else{
                                   $('#customer-list input[value='+id+']').parent().parent().remove();
                               }
                            });
                            if (msg.length) {
                                showMessage(_.isUndefined(i18n['Unable to remove following users'])?'Unable to remove following users':i18n['Unable to remove following users']+': '+msg, true);
                            }else{
                                showMessage(_.isUndefined(i18n['Users deleted'])?'Users deleted':i18n['Users deleted']);
                            }
                        //});
                    },
                    error: function(xhr, error, msg){
                        if (xhr.status === 404) {
                            showMessage(xhr.responseText, true);
                        }
                    }
                });
            });

        },
        searchClient: function(e){
            var term = e.target.value,
                self = this;

            clearTimeout(self.searching);
            self.searching = setTimeout(function(){
                self.customers.search(term);
            }, 600);
        },
        assignGroup: function(e){
            var groupId = $(e.target).val();
            var userId = $(e.target).closest('tr').find('input[name="select[]"]').val();
            $.ajax({
                url: $('#website_url').val()+'api/store/customers/',
                data: {
                    groupId:groupId,
                    userId:userId,
                    secureToken: $('.clientsSecureToken').val()
                },
                type: 'POST',
                dataType: 'json',
                success: function(response){
                    showMessage(_.isUndefined(i18n['Group saved'])?'Group saved':i18n['Group saved']);
                }
            });
        },
        assignGroups: function(){
            var self = this;

            var checkedCustomers = this.customers.checked();
            var allCustomers = this.customers;

            if(checkedCustomers.length == 0){
                return false;
            }

            if (!this.groups){
                this.groups = new GroupsCollection();
                this.groups.fetch({async: false});
            }

            var applyButton  = _.isUndefined(i18n['Apply']) ? 'Apply':i18n['Apply'];
            var assignGroupsButtons = {};

            assignGroupsButtons[applyButton] = function() {
                var groupId = $(this).find($("select option:selected")).val();
                if(groupId == -1){
                    return false;
                }
                var allGroups = 0;
                var customerIds = '';
                if($(this).find('input[name="applyToAll"]').attr('checked')){
                    allGroups = 1;
                    checkedCustomers = allCustomers.models;
                }

                $.each(checkedCustomers, function(index, value) {
                    customerIds += value.id+',';
                });
                customerIds = customerIds.substring(0, customerIds.length - 1);

                $.ajax({
                    url: $('#website_url').val()+'api/store/customers/groupId/'+groupId+'/customerIds/'+customerIds+'/allGroups/'+allGroups,
                    type: 'PUT',
                    dataType: 'json',
                    success: function(response){
                        top.location.reload();
                    }
                });
                $(this).dialog('close');
            };

            var dialog = _.template(GroupsDialogTmpl, {
                groups: this.groups.toJSON(),
                totalCustomers: this.customers.length,
                i18n:i18n
            });
            $(dialog).dialog({
                dialogClass: 'seotoaster',
                buttons: assignGroupsButtons,
                resizable : false
            });
            return false;
        },
        assignPassword: function(e){
            var checkedCustomers = this.customers.checked();
            var customerIds = '';

            if(checkedCustomers.length == 0){
                return false;
            }
            $.each(checkedCustomers, function(index, value) {
                customerIds += value.id+',';
            });
            customerIds = customerIds.substring(0, customerIds.length - 1);

            smoke.confirm(_.isUndefined(i18n['Change password for clients?'])?'Change password for clients?':i18n['Change password for clients?'], function(e) {
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
        emailMarketing: function(e){
            var checkedCustomers = this.customers.checked();
            var customerIds = [];

            if(checkedCustomers.length == 0){
                return false;
            }

            $.each(checkedCustomers, function(index, value) {
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


                        var enabledServices = response.responseText.enabledServices;
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
                            i18n:i18n
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
                                                })
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
            var checkedCustomers = this.customers.checked();
            var customerIds = [];

            if(checkedCustomers.length == 0){
                return false;
            }

            $.each(checkedCustomers, function(index, value) {
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


                    var enabledServices = response.responseText.enabledServices;
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
                        i18n:i18n
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
                                            })
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
        changeCustomAttr:function(){
            $('input.customer-attribute').on('blur', function(e){
                var data = {};
                data[$(this).data('attribute')] = $(this).val();

                $.ajax({
                    url: $('#website_url').val() + 'api/store/customer/id/' + $(this).data('uid'),
                    method: 'PUT',
                    data: JSON.stringify(data),
                    complete: function(xhr, status, response) {
                        if (status === 'error'){
                            showMessage(status, true);
                        } else {
                            showMessage('Attribute saved!');
                        }
                    }
                })
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

        },
        deleteCustomAttr:function(){
            $('body').on('click', 'th.customer-attribute span', function(e){
                var attrName = $(this).parent().data('custom');
                showConfirm('Do you really want to delete this column? (Data will be deleted!)', function(){
                    $.ajax({
                        url: $('#website_url').val() + 'api/store/customer/attr/' + attrName,
                        method: 'DELETE',
                        data: JSON.stringify({attrName: attrName}),
                        complete: function(xhr, status, response) {
                            if (status === 'error'){
                                showMessage(status, true);
                            } else {
                                window.location.reload();
                            }
                        }
                    })
                })
            });
        }
    });
	
	return AppView;
});