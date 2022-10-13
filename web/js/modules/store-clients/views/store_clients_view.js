define([
	'backbone',
    'text!../templates/paginator.html',
    '../collections/customers',
    './customer_row',
    '../../groups/collections/group',
    'text!../../groups/templates/groups_dialog.html',
    'text!../templates/email_service.html',
    'text!../templates/crm_service.html',
    'i18n!../../../nls/'+$('input[name=system-language]').val()+'_ln',
    'tinyMCE',
    ], function(Backbone, PaginatorTmpl, CustomersCollection, CustomerRowView, GroupsCollection, GroupsDialogTmpl, EmailServiceDialogTmpl, CrmServiceDialogTmpl, i18n, tinymce){

    var StoreClientsView = Backbone.View.extend({
        el: $('#clients'),
        events: {
            'click #export-users': 'exportUsers',
            'click td.paginator a.page': 'navigate',
            'click th.sortable': 'sort',
            'click #customer-details div.toolbar a:first': 'toggleDetails',
            'change #clients-check-all': 'toggleAllPeople',
            'change select#mass-action': 'doAction',
            'keyup #clients-search': 'searchClient',
            'change select[name=groups]': 'assignGroup',
            'blur input.customer-attribute': 'changeCustomAttr',
            'blur input.change-user-attribute': 'changeUserAttr',
            'change select.change-user-attribute': 'changeUserAttr',
            'click th.customer-attribute':'deleteCustomAttr',
            'change .mobile-phone-country-code':'changeMobileDesktopMask',
            'click .clients-filter':'clientsFilter',
        },
        templates: {
            paginator: _.template(PaginatorTmpl)
        },
        initialize: function(){
            $('#customer-details').hide();
            this.customers = new CustomersCollection();
            this.customers.on('reset', this.renderCustomers, this);
            this.customers.server_api.clientsFilter = 'clients-only';
            this.customers.pager();
        },
        render: function(){},
        renderCustomer: function(customer){
            customer.set({i18n: i18n});
            var view = new CustomerRowView({model: customer});
            this.$('#customer-list').append(view.render().el);
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
            this.$('#customer-list').empty();
            this.customers.each(this.renderCustomer.bind(this));
            this.customers.info()['i18n'] = i18n;
            this.$('td.paginator').html(this.templates.paginator(this.customers.information));
        },
        toggleDetails: function()
        {
            $('#clients-table,#customer-details, .search-line').toggle();
        },
        exportUsers: function(){
            if ($('#customer-list tr').length > 1) {
                $('#export-users-form').submit();
            } else {
                showMessage(_.isUndefined(i18n['There are no users for export'])?'There are no users for export':i18n['There are no users for export'], true);
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
        showCustomerDetails: function(uid) {
            var self = this;
            tinymce.remove();

            $.get($('#website_url').val()+'plugin/shopping/run/profile/', {id: uid},function(response, status) {
                self.renderCustomerDetails(response, status);
                self.initTinyMce();
            });

        },
        dispatchEditorKeyup(editor, event, keyTime) {
            var keyTimer = keyTime;
            if(keyTimer === null) {
                keyTimer = setTimeout(function() {
                    keyTimer = null;
                }, 1000)
            }
        },
        initTinyMce() {
            var self = this;

            tinymce.init({
                script_url: $('#website_url').val() + 'system/js/external/tinymce/tinymce.gzip.php',
                selector: '#signature',
                skin: 'seotoaster',
                menubar: false,
                resize: false,
                convert_urls: false,
                browser_spellcheck: true,
                relative_urls: false,
                statusbar: false,
                allow_script_urls: true,
                force_p_newlines: true,
                forced_root_block: false,
                entity_encoding: "raw",
                plugins: [
                    "advlist lists link anchor image charmap visualblocks code media table paste textcolor fullscreen"
                ],
                toolbar1: 'link unlink | image | hr | bold italic | fontsizeselect | pastetext | forecolor backcolor | formatselect | code | fullscreen |',
                fontsize_formats: "8px 10px 12px 14px 16px 18px 24px 36px",
                block_formats: "Block=div;Paragraph=p;Block Quote=blockquote;Cite=cite;Address=address;Code=code;Preformatted=pre;H2=h2;H3=h3;H4=h4;H5=h5;H6=h6",
                extended_valid_elements: "a[*],input[*],select[*],textarea[*]",
                image_advtab: true,
                setup: function (ed) {
                    var keyTime = null;
                    ed.on('change blur keyup', function (ed, e) {
                        //@see content.js for this function
                        self.dispatchEditorKeyup(ed, e, keyTime);
                    });
                    ed.on('blur', function (ed, e) {
                        editUserProfileSendAjax('signature', tinymce.activeEditor.getContent());
                    });
                }
            })
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
                customer.set({checked: value}, {silent: true});
            });

            $('.check-td').find('input').prop('checked', value);

            if (typeof _checkboxRadio === "function")  {
                _checkboxRadio();
            }
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
            showConfirmCustom(_.isUndefined(i18n['Are you sure?'])?'Are you sure?':i18n['Are you sure?'], _.isUndefined(i18n['Yes'])?'Yes':i18n['Yes'], _.isUndefined(i18n['No'])?'No':i18n['No'], function(){
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
                                   $('#customer-list input[value='+id+']').closest('tr').remove();
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
                self.customers.server_api.search = term;
                self.applyFilter();
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
        changeUserAttr:function(e){
            var target = e.currentTarget,
                attrName = $(target).data('user-attribute-name'),
                data = {},
                oldValue = $(target).data('old-value'),
                currentValue = $(target).val();

            if (attrName === 'mobilePhone') {
                currentValue = currentValue.replace(/\D/g, '');
            }

            if (oldValue == currentValue) {
                return false;
            }
            data[attrName] = $(target).val();
            $.ajax({
               url: $('#website_url').val() + 'api/toaster/users/id/' + $(target).data('uid'),
               method: 'PUT',
               data: JSON.stringify(data),
               complete: function(xhr, status, response) {
                   if (status === 'error'){
                       showMessage(status, true);
                   } else {
                       $(target).data('old-value', currentValue);
                       showMessage(_.isUndefined(i18n['Saved!'])?'Saved!':i18n['Saved!']);
                   }
               }
            });

        },
        changeMobileDesktopMask: function(e)
        {
            var selectionType = $(e.currentTarget).data('type'),
                value =  $(e.currentTarget).val(),
                customerId = $(e.currentTarget).data('uid'),
                customerModel = this.customers.get(customerId),
                mobileMasks = customerModel.get('mobileMasks'),
                desktopMasks = customerModel.get('desktopMasks');

            if (selectionType === 'mobile') {
                if (typeof mobileMasks[value] !== 'undefined') {
                    $(e.currentTarget).closest('tr').find('.mobile-phone-value').mask(mobileMasks[value].mask_value, {autoclear: false});
                } else {
                    $(e.currentTarget).closest('tr').find('.mobile-phone-value').mask('(999) 999 9999', {autoclear: false});
                }
            }

            if (selectionType === 'desktop') {
                if (typeof desktopMasks[value] !== 'undefined') {
                    $(e.currentTarget).closest('tr').find('.mobile-phone-value').mask(desktopMasks[value].mask_value, {autoclear: false});
                } else {
                    $(e.currentTarget).closest('tr').find('.mobile-phone-value').mask('(999) 999 9999', {autoclear: false});
                }
            }
        },
        deleteCustomAttr:function(){
            $('body').on('click', 'th.customer-attribute span', function(e){
                var attrName = $(this).parent().data('custom');
                showConfirmCustom(_.isUndefined(i18n['Do you really want to delete this column? (Data will be deleted!)'])?'Do you really want to delete this column? (Data will be deleted!)':i18n['Do you really want to delete this column? (Data will be deleted!)'], _.isUndefined(i18n['Yes'])?'Yes':i18n['Yes'], _.isUndefined(i18n['No'])?'No':i18n['No'], function(){
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
        },
        clientsFilter: function (e)
        {
            var filterType = $(e.currentTarget).data('filter-type');
            this.customers.server_api.clientsFilter = filterType;
            this.applyFilter();
        },
        applyFilter: function(e) {
            if(typeof e !== 'undefined'){
                e.preventDefault();
            }
            this.customers.ordersChecked = [];
            this.customers.currentPage = 0;
            this.customers.pager();
        },
    });
	return StoreClientsView;
});
