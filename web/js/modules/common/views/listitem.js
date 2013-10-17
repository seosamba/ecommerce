define([
    'underscore',
    'backbone'
], function (_, Backbone) {
    var listItemView = Backbone.View.extend({
        tagName    : 'li',
        className  : 'entry-row',
        events     : {
            'click .add-item'    : 'addItem',
            'click .remove-item' : 'removeItem'
        },
        templates  : {
            country : '<%= name %>',
            state   : '<%= app.countries.findByCode(country).get("name") %>: <%= name %>'
        },
        render     : function (template, mode, listName) {
            var template = _.template(template);
            this.$el.html(template(this.model));
            if (listName) {
                this.$el.data('listname', listName);
            }
            switch (mode) {
                default:
                case 'add':
                    this.$el.prepend('<a href="javascript:;" class="add-item icon-plus fl-right"></a>');
                    break;
                case 'delete':
                    this.$el.prepend('<a href="javascript:;" class="remove-item icon-close error fl-right"></a>');
                    break
            }
            return this;
        },
        addItem    : function (e) {
            var index = app.view.zoneHolder.tabs('option', 'active'),
                currentZone = app.view.zonesCollection.at(index);
            currentZone.addItem(this.$el.data('listname'), this.model);
            $(e.target).parent('li').hide('slide');
        },
        removeItem : function (e) {
            var index = app.view.zoneHolder.tabs('option', 'active'),
                currentZone = app.view.zonesCollection.at(index);
            currentZone.removeItem(this.$el.data('listname'), this.model);
        }
    });
    return listItemView;
});

