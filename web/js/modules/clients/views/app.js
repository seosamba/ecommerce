define([
	'Underscore',
	'Backbone',
    'modules/clients/collections/customers',
    'modules/clients/views/customer_row'
], function(_, Backbone, CustomersCollection, CustomerRowView){

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
            'keyup #clients-search': 'searchClient'
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
            var $el = $(e.target)
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
                var ids = _(checked).pluck('id');
                console.log(ids);
                Backbone.sync('delete', null, {
                    url: app.customers.urlRoot,
                    data: JSON.stringify({ids: ids}),
                    success: function(response){
                        app.customers.fetch().done(function(){
                            var msg = '';
                            _(response).each(function(status, id){
                               if (status === false) {
                                   var model = app.customers.get(id);
                                   if (model) {
                                       msg += (msg.length ? ', ' : '') + model.get('full_name');
                                       model.set('checked', true);
                                   }
                               }
                            });
                            if (msg.length) {
                                showMessage('Unable to remove following users: '+msg, true);
                            }
                        });
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
        }
    });
	
	return AppView;
});