define([
	'underscore',
	'backbone'
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
                })
            ){
                var p = _.union(this.get(property), [_.extend({}, itemModel)]);
                this.set(property, p);
//                this.trigger('change');
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