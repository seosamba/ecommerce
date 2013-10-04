define([
	'backbone',
    '../collections/customers',
    './customer_row',
    '../../groups/collections/group',
    'text!../../groups/templates/groups_dialog.html'
    ], function(Backbone, CustomersCollection, CustomerRowView, GroupsCollection, GroupsDialogTmpl){

    var AppView = Backbone.View.extend({
        el: $('#clients'),
        events: {
            'click #export-users': function(){ $('#export-users-form').submit(); },
            'click #clients-previous': 'goPreviousPage',
            'click #clients-next': 'goNextPage',
            'click th.sortable': 'sort',
            'click #customer-details div.toolbar a:first': function() {$('#clients-table,#customer-details').toggle()},
            'change #clients-check-all': 'toggleAllPeople',
            'change select#mass-action': 'doAction',
            'keyup #clients-search': 'searchClient',
            'change select[name=groups]': 'assignGroup'
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
                $('#clients-table').hide();
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
                                showMessage('Unable to remove following users: '+msg, true);
                            }else{
                                showMessage('Users deleted');
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
                    userId:userId
                },
                type: 'POST',
                dataType: 'json',
                success: function(response){
                    showMessage('Group saved');
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

            var dialog = _.template(GroupsDialogTmpl, {
                groups: this.groups.toJSON(),
                totalCustomers: this.customers.length
            });
            $(dialog).dialog({
                dialogClass: 'seotoaster',
                buttons: {
                    "Apply": function(){
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

                    }
                }
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

            smoke.confirm('Change password for clients?', function(e) {
                if(e) {
                    showSpinner();
                    $.ajax({
                        url: $('#website_url').val()+'api/store/customers/customerIds/'+customerIds+'/changePassword/1/',
                        type: 'PUT',
                        dataType: 'json'

                    }).done(function(response) {
                        hideSpinner();
                        showMessage('Changed');

                    });
                } else {

                }
            }, {classname:"error", 'ok':'Yes', 'cancel':'No'});


        }
    });
	
	return AppView;
});