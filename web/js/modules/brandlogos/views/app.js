define([
	'Underscore',
	'Backbone',
    'modules/product/collections/brands',
    'modules/brandlogos/views/brandlogo'
], function(_, Backbone, BrandList, BrandView){

    var brandLogosListView = Backbone.View.extend({
        el: $('#manage-logos'),
        events: {

        },
        initialize: function(){
            this.brands = new BrandList();
            this.brands.on('reset', this.render, this);
        },
        render: function(){
            var self = this;
            this.$('ul.brand-list').empty();
            this.brands.each(function(brand, i){
                if (!brand.has('src')){
                    var image = self.images.find(function(img){
                        var brandName = brand.get('name').toLowerCase(),
                            imgName = img.get('name').toLowerCase();
                            regExp = new RegExp('^'+brandName+'\\.(png|jpe?g|gif)$')
                        return !!regExp.test(imgName);
                    });
                    brand.set({src: ( _.isObject(image) ? image.get('src').replace('product', 'small') : $('#website_url').val()+'system/images/noimage.png')});
                }
                var view = new BrandView({model: brand});
                view.render().$el.appendTo('#manage-logos ul.brand-list').addClass(((i+1)%6==0)?'omega':'');
            })
        },
        fetchImages: function() {
            var self = this;
            return $.post(
                $('#website_url').val()+'backend/backend_media/getdirectorycontent',
                {folder: $('#things-select-folder').val()},
                function(response){ self.images = new Backbone.Collection(response.imageList); }
            );
        }
    })
	
	return brandLogosListView;
});