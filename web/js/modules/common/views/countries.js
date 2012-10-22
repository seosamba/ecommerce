define([
	'underscore',
	'backbone',
    './listitem'
], function(_, Backbone, ListItemView){

    var countriesListView = Backbone.View.extend({
        el: $('#add-country-dialog'),
        events: {
            'keyup #country-filter': 'filterList'
        },
        render: function(){
            this.$el.find('#country-list').empty();
            _.each(this.collection, function(country){
                var view = new ListItemView({
                    model: country
                });
                view.render(view.templates.country, 'add', 'countries').$el.appendTo('#country-list');
            });
            return this;
        },
        refresh: function(){
            console.log(this);
        },
        filterList: function(e){
            var search = e.target.value.toLowerCase();
            if (search == ''){
                $('#country-list > li:hidden').show();
                return;
            }
            _.each($('#country-list > li'), function(li){
                var text = $(li).text().toLowerCase();

                if (text.search(search) == -1) {
                    $(li).hide();
                } else {
                    $(li).show();
                }
            });
        }
    });

	return countriesListView;
});