define([
	'Underscore',
	'Backbone',
    'modules/people/models/customer'
], function(_, Backbone, CustomerModel){

    var CustomersCollection = Backbone.Collection.extend({
        model: CustomerModel,
        paginator: {
            limit: 30,
            offset: 0,
            last: false
        },
        order: {
            by: null,
            asc: true
        },
        initialize: function(){
            this.bind('reset', this.updatePaginator, this);
        },
        url: function(){
            var url = $('#websiteUrl').val()+'plugin/shopping/run/getdata/type/customer/for/dashboard/',
                order = '';
            if (this.order.by) {
                order = '&order=' + this.order.by + ' ' + (this.order.asc ? 'asc' : 'desc');
            }
            return url+'?'+'limit='+this.paginator.limit+'&offset='+this.paginator.offset + order + '&id=';
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
        }
    });

	return CustomersCollection;
});