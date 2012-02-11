define([
	'Underscore',
	'Backbone'
], function(_, Backbone){

    var listItemView = Backbone.View.extend({
        tagName: 'li',
        className: 'entry-row ui-state-active padding5px',
        events: {
            'click .add-item': 'addItem',
            'click .remove-item': 'removeItem'
        },
        templates:{
            country: '${name}',
            state: '${app.countries.findByCode(country).get("name")}: ${name}'
        },
        render: function(template, mode, listName){
            this.$el.html($.tmpl(template, this.model));
            if (listName){
                this.$el.data('listname', listName);
            }
            switch (mode){
                default:
                case 'add':
                    this.$el.append('<span class="add-item ui-icon ui-icon-plusthick"></span>');
                    break;
                case 'delete':
                    this.$el.append('<span class="remove-item ui-icon ui-icon-trash"></span>');
                    break
            }
            return this;
        },
        addItem: function(e){
            var index = app.view.zoneHolder.tabs('option', 'selected'),
                currentZone = app.view.zonesCollection.at(index);
                currentZone.addItem(this.$el.data('listname'), this.model);
            $(e.target).parent('li').hide('slide');
        },
        removeItem: function(e){
            var index = app.view.zoneHolder.tabs('option', 'selected'),
                currentZone = app.view.zonesCollection.at(index);
                currentZone.removeItem(this.$el.data('listname'), this.model);
        }
    });

	return listItemView;
});

