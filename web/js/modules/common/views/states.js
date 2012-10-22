define([
	'underscore',
	'backbone',
    './listitem'
], function(_, Backbone, ListItemView){

    var statesListView = Backbone.View.extend({
        el: $('#add-state-dialog'),
        events: {
            'change #state-filter': 'filterList'
        },
        render: function(){
            var stateFilter = $('#state-filter');
            var template    = _.template('<% _.each(app.countries.toJSON(), function(item) { %> <option value="<%= item.country %>"><%= item.name %></option> <% }) %>');
            stateFilter.append(template(app.countries.toJSON()));
            this.$el.find('#state-list').empty();
            _.each(this.collection, function(item){
                var view = new ListItemView({
                    model: item
                });
                view.render(view.templates.state, null, 'states').$el.data('country', item.country).appendTo('#state-list');
            });
            return this;
        },
        filterList: function(e){
            var search = e.target.value;
            if (search === '0'){
                $('#state-list > li:hidden').show();
                return;
            }
            _.each($('#state-list > li'), function(li){
                if ($(li).data('country') != search) {
                    $(li).hide();
                } else {
                    $(li).show();
                }
            });
        }
    });

	return statesListView;
});