/*!
 * jQuery address chain plugin
 * Author: Pavlo Kovalov <pavlo.kovalyov@gmail.com>
 * Licensed under the MIT license
 */

;(function ( $, window, document, undefined ) {

    $.fn.addressChain = function ( options ) {
        var defaultValues = options && options.hasOwnProperty('defaults') ?
            $.extend({country: null, state: null}, options.defaults) :
            null ;

        options = $.extend( {}, $.fn.addressChain.options, options );

        return this.each(function () {
            var elem = $(this),
                countrySelect = (options.countrySelector instanceof $ ? options.countrySelector : $(options.countrySelector, this)),
                stateSelect   = (options.stateSelector instanceof $ ? options.stateSelector : $(options.stateSelector, this)),
                stateLabel    = stateSelect.attr('id') && $('label[for='+stateSelect.attr('id')+']', this);

            //hiding state element if it's empty
            if (options.toogleStateVisibility && stateSelect.find('option').length === 0){
                stateSelect.hide();
                stateLabel && stateLabel.hide();
            }

            countrySelect.on('change', function(){
                var countryCode = $(this).val();

                //doing AJAX-request
                return $.ajax({
                    url: options.url,
                    dataType: 'json',
                    data: { country: countryCode },
                    success: function(response) {
                        if (response) {

                            var html = '';

                            $.each(response, function(key, item){
                               //handling if states returned as key-value pairs or as set of objects
                                if (typeof item === 'string') {
                                    html += '<option value="' + key + '" label="' + item +
                                        '" data-country="' + countryCode + '">' + item + '</option>';
                                } else if (typeof item === 'object') {
                                    html += '<option value="' + this.id + '" label="' + this.name +
                                        '" data-country="' + this.country + '">' + this.name + '</option>';
                                } else {
                                    $.error('Wrong response data format from server');
                                }
                            });
                            stateSelect.html(html).trigger('addressChain:updated');

                            if (options.toogleStateVisibility){
                                if (html.length) {
                                    stateSelect.show();
                                    stateLabel && stateLabel.show();
                                } else {
                                    stateSelect.hide();
                                    stateLabel && stateLabel.hide();
                                }
                            }

                            if (defaultValues && stateSelect.children().size()){
                                stateSelect.val(defaultValues.state);
                            }
                        }
                    }
                });
            });

            if (defaultValues && countrySelect.find("[selected]").size()){
                countrySelect.val(defaultValues.country).change();
            } else {
                if (stateSelect.children().size() === 0) {
                    countrySelect.trigger('change');
                }
            }

        });
    };

    // Globally overriding options
    // Here are our publicly accessible default plugin options
    // that are available in case the user doesn't pass in all
    // of the values expected. The user is given a default
    // experience but can also override the values as necessary.
    // eg. $fn.pluginName.key ='otherval';


    $.fn.addressChain.options = {
        countrySelector: "select[name$=country]",
        stateSelector: "select[name$=state]",
        countryUrl: null,
        url: '',
        cache: false, /* TODO implement chaching */
        toogleStateVisibility: true
    };

})( jQuery, window, document );
