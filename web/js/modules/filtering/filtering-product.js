/*jslint nomen: true*/
/*global $, jQuery, _, Backbone */
$(function () {
    var $widgets = $('form.plugin-filtering-widget');
    $widgets.on('change', 'input.mass-change:checkbox', function (e) {
        var $subList = $(e.currentTarget).closest('li').find('ul input:checkbox');
        console.log($subList);
        $subList.prop('checked', e.currentTarget.checked);
    });
    $widgets.find('div.slider').each(function () {
        var min = Math.floor(parseFloat($(this).data('min'))),
            max = Math.ceil(parseFloat($(this).data('max')));
        $(this).slider({
            range: true,
            min: min,
            max: max,
            values: [ min, max ]
        });
    });
});