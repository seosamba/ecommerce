define([
	'underscore',
	'backbone',
    '../../common/views/listitem',
    'text!../templates/zones.html',
    'i18n!../../../nls/'+$('input[name=system-language]').val()+'_ln'
], function(_, Backbone, ListItemView, ZonesTmpl, i18n){

    var zoneTabView = Backbone.View.extend({
        templates: {},
        tagName: 'div',
        className: 'content-footer',
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
            this.$el.html( _.template(ZonesTmpl, {'zonesModel':this.model.toJSON(), 'i18n':i18n}));
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

            this.el.id = this.model.cid;
            return this;
        },
        clearProperty: function(e){
            var propName = $(e.target).data('property');
            if (this.model.has(propName)){
                _.isArray(this.model.get(propName)) && this.model.set(propName, []);
                _.isString(this.model.get(propName)) && this.model.set(propName, '');
            }
        },
        setName: function(e){
            this.model.set('name', e.target.value);
            $('ul.ui-tabs-nav a[href=#'+ this.model.cid +']').text(this.model.get('name'));
        },
        setZip: function(e){
            this.model.set('zip', e.target.value.match(/^[\-0-9A-z*?]{2,10}$/gm));
        }
    });
	
	return zoneTabView;
});
