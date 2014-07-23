define([
	'underscore',
	'backbone',
    'i18n!../../../nls/'+$('input[name=system-language]').val()+'_ln'
], function(_, Backbone, i18n){

    var listItemView = Backbone.View.extend({
        tagName    : 'li',
        className  : 'entry-row pr2 pl2',
        events     : {
            'click .add-item'    : 'addItem',
            'click .remove-item' : 'removeItem'
        },
        templates  : {
            country : '<%= name %>',
            state   : '<%= app.countries.findByCode(country).get("name") %>: <%= name %>'
        },
        render: function(template, mode, listName){
            var template = _.template(template);
            this.$el.html(template(this.model));
            if (listName){
                this.$el.data('listname', listName);
            }
            switch (mode){
                default:
                case 'add':
                    if(_.isUndefined(this.model.state)){
                        this.$el.append('<span data-element-country="'+this.model.country+'" class="add-item ticon-plus success fl-right pointer"></span>');
                    }else{
                        this.$el.append('<span data-element-state="'+this.model.country+'-'+this.model.state+'" class="add-item ticon-plus success fl-right pointer"></span>');
                    }
                    break;
                case 'delete':
                    if(_.isUndefined(this.model.state)){
                        this.$el.append('<span data-element-country="'+this.model.country+'" class="remove-item ticon-close error fl-right pointer"></span>');
                    }else{
                        this.$el.append('<span data-element-state="'+this.model.country+'-'+this.model.state+'" class="remove-item ticon-close error fl-right pointer"></span>');
                    }
                    break;
            }
            return this;
        },
        addItem: function(e){
            var currentModel = this.model;
            var notAddExistingZone = false;
            var currentTarget = $(e.target).parent('li');
            if(app.view.zonesCollection.length > 0){
                $.each(app.view.zonesCollection.models, function(index, countrys){
                    if(countrys.get('countries').length > 0){
                        $.each(countrys.get('countries'), function(index, country){
                           if(country.country == currentModel.country){
                               notAddExistingZone = true;
                           }
                        });
                    }
                });
            }
            var index = app.view.zoneHolder.tabs('option', 'active'),
                currentZone = app.view.zonesCollection.at(index);
            var currentListName = this.$el.data('listname');
            if(notAddExistingZone){
                smoke.confirm((_.isUndefined(i18n['Wait a minute! this country is already part of another zone... Add anyway?'])?'Wait a minute! this country is already part of another zone... Add anyway?':i18n['Wait a minute! this country is already part of another zone... Add anyway?']), function(e) {
                    if(e) {
                        currentZone.addItem(currentListName, currentModel);
                        currentTarget.hide('slide');
                    }else{
                        return false;
                    }
                }, {classname : 'errors', ok : _.isUndefined(i18n['Add'])?'Add':i18n['Add'], cancel : _.isUndefined(i18n['No'])?'No':i18n['No']});
            }
            if(!notAddExistingZone){
                currentZone.addItem(currentListName, currentModel);
                currentTarget.hide('slide');
            }
        },
        removeItem: function(e){
            var index = app.view.zoneHolder.tabs('option', 'active'),
                currentZone = app.view.zonesCollection.at(index);
                currentZone.removeItem(this.$el.data('listname'), this.model);
        }
    });

	return listItemView;
});

