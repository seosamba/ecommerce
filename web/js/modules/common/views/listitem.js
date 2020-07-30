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

            var elementCountry = $(e.target).data('element-country');
            var elementState = $(e.target).data('element-state');
            var attributeType = '';

            if(app.view.zonesCollection.length > 0){
                $.each(app.view.zonesCollection.models, function(index, dataTypes){
                    if(typeof elementCountry !== 'undefined') {
                        if(dataTypes.get('countries').length > 0){
                            $.each(dataTypes.get('countries'), function(index, country){
                                if(country.country == currentModel.country){
                                    attributeType = 'country';
                                    notAddExistingZone = true;
                                }
                            });
                        }
                    } else if(typeof elementState !== 'undefined') {
                        if(dataTypes.get('states').length > 0){
                            $.each(dataTypes.get('states'), function(index, state){
                                if(state.state == currentModel.state){
                                    attributeType = 'state';
                                    notAddExistingZone = true;
                                }
                            });
                        }
                    }
                });
            }
            var index = app.view.zoneHolder.tabs('option', 'active'),
                currentZone = app.view.zonesCollection.at(index);
            var currentListName = this.$el.data('listname');
            if(notAddExistingZone){
                smoke.confirm((_.isUndefined(i18n['Wait a minute! this '+ attributeType +' is already part of another zone... Add anyway?'])?'Wait a minute! this '+ attributeType +' is already part of another zone... Add anyway?':i18n['Wait a minute! this '+ attributeType +' is already part of another zone... Add anyway?']), function(e) {
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

