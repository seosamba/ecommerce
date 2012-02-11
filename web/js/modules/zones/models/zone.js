define([
	'Underscore',
	'Backbone'
], function(_, Backbone){

    var zoneModel = Backbone.Model.extend({
        defaults: {
            name: 'New zone',
            countries: [],
            states: [],
            zip: []
        },
        addItem: function(property, itemModel){
            if (!_.any(this.get(property), function(item){
                if (item.hasOwnProperty('id') && itemModel.hasOwnProperty('id')){
                    return item.id == itemModel.id;
                } else {
                    return _.isEqual(item,itemModel);
                }
                return false;
            })){
                this.get(property).push(_.extend({},itemModel));
                this.trigger('change');
            }
            return this;
        },
        removeItem: function(property, itemModel){
            var attr = _.reject(this.get(property), function(item){
                if (item.hasOwnProperty('id') && itemModel.hasOwnProperty('id')){
                    return item.id == itemModel.id;
                } else {
                    return _.isEqual(item, itemModel);
                }
                return false;
            });

            this.set(property, attr);

            return this;
        }
    });

	return zoneModel;
});