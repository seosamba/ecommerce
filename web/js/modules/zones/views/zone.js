define([
	'Underscore',
	'Backbone',
    'modules/common/views/listitem'
], function(_, Backbone, ListItemView){

    var zoneTabView = Backbone.View.extend({
        template: $('#zoneTemplate').template(),
        events: {
            'click .clearprop': 'clearProperty',
            'change .zone-name': 'setName',
            'change .zone-zip': 'setZip'
        },
        initialize: function(){
            this.model.view = this;
            this.model.on('change', this.render, this);
            this.model.on('destroy', this.remove, this);
        },
        render: function(){
            $(this.el).html($.tmpl(this.template, this.model.toJSON()));
            //rendering list of countries for zone
            var countriesList = this.$el.find('.zone-countries');
            _.each(this.model.get('countries'), function(country){
                var view = new ListItemView({
                    model: country
                });
                countriesList.append(view.render(view.templates.country, 'delete', 'countries').$el);
            });
            //rendering list of states for zone
            var statesList = this.$el.find('.zone-states');
            _.each(this.model.get('states'), function(state){
                var view = new ListItemView({
                    model: state
                });
                statesList.append(view.render(view.templates.state, 'delete', 'states').$el);
            });
            this.$el.find('.delete-zone').button();
            return this;
        },
        clearProperty: function(e){
            var propName = $(e.target).data('property');
            if (this.model.has(propName)){
                var data = {};
                data[propName] = this.model.defaults[propName];
                this.model.set(data);
            }
            console.log(this.model.toJSON());
        },
        setName: function(e){
            this.model.set('name', e.target.value);
            $('ul.ui-tabs-nav a[href=#zone-'+ this.model.cid +'] span').text(this.model.get('name'));
        },
        setZip: function(e){
            this.model.set('zip', e.target.value.match(/^[0-9*?]{2,7}$/gm));
        }
    })
	
	return zoneTabView;
});