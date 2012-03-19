define([
	'Underscore',
	'Backbone',
    'modules/people/models/customer_row'
], function(_, Backbone, CustomerRowModel){

    var CustomersCollection = Backbone.Collection.extend({
        model: CustomerRowModel,
        paginator: {
            limit: 30,
            offset: 0,
            last: false
        },
        order: {
            by: null,
            asc: true
        },
        searchTerm: '',
        cached: {}, //@todo add caching mecanism
        initialize: function(){
            this.bind('reset', this.updatePaginator, this);
        },
        url: function(){
            var url = $('#website_url').val()+'plugin/shopping/run/getdata/type/customer/for/dashboard/',
                order = '';
            url += '?'+'limit='+this.paginator.limit+'&offset='+this.paginator.offset;
            if (this.order.by) {
                url += '&order=' + this.order.by + ' ' + (this.order.asc ? 'asc' : 'desc');
            }
            if (this.searchTerm) {
                url += '&search='+this.searchTerm;
            }
            return url + '&id=';
        },
        next: function(callback) {
            if (!this.paginator.last) {
                this.paginator.offset += this.paginator.limit;
                return this.fetch().done(callback);
            }
            console.log('Last reached');
        },
        previous: function(callback) {
            if (this.paginator.offset >= this.paginator.limit){
                this.paginator.offset -= this.paginator.limit;
                return this.fetch().done(callback);
            }
            console.log('First reached');
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