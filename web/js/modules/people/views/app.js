define([
	'Underscore',
	'Backbone',
    'modules/people/collections/customers',
    'modules/people/views/customer_row'
], function(_, Backbone, CustomersCollection, CustomerRowView){

    var AppView = Backbone.View.extend({
        el: $('#people'),
        events: {
            'click #people-previous': 'goPreviousPage',
            'click #people-next': 'goNextPage',
            'click th.sortable': 'sort'
        },
        initialize: function(){
            this.customers = new CustomersCollection();
            this.customers.bind('reset', this.render, this);
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
        }
    });
	
	return AppView;
});