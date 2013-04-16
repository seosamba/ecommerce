define([
	'backbone',
    '../models/customer_row'
], function(Backbone, CustomerRowModel){

    var CustomersCollection = Backbone.Collection.extend({
        model: CustomerRowModel,
        urlRoot: 'api/store/customers/',
        paginator: {
            limit: 30,
            offset: 0,
            last: false
        },
        order: {
            by: 'reg_date',
            asc: false
        },
        searchTerm: '',
        cached: {}, //@todo add caching mecanism
        initialize: function(){
            this.bind('reset', this.updatePaginator, this);
        },
        url: function(){
            var url = this.urlRoot + 'for/dashboard/',
                order = '';
            url += '?'+'limit='+this.paginator.limit+'&offset='+this.paginator.offset;
            if (this.order.by) {
                url += '&order=' + this.order.by + ' ' + (this.order.asc ? 'asc' : 'desc');
            }
            if (this.searchTerm) {
                url += '&search='+this.searchTerm;
            }
            return $('#website_url').val() + url + '&id=';
        },
        next: function(callback) {
            if (!this.paginator.last) {
                this.paginator.offset += this.paginator.limit;
                return this.fetch().done(callback);
            }
        },
        previous: function(callback) {
            if (this.paginator.offset >= this.paginator.limit){
                this.paginator.offset -= this.paginator.limit;
                return this.fetch().done(callback);
            }
        },
        updatePaginator: function(collection) {
            if (this.length === 0){
                this.previous();
            } else {
                this.paginator.last = (this.length < this.paginator.limit);
            }
        },
        checked: function(){
            return this.filter(function(customer){ return customer.has('checked') && customer.get('checked'); });
        },
        search: function(term){
            if (term !== this.searchTerm){
                this.searchTerm = escape(term);
                this.paginator.offset = 0;
                this.paginator.last = false;
                this.paginator.order = {by: null,asc: true};
                return this.fetch();
            }
        }
    });

	return CustomersCollection;
});