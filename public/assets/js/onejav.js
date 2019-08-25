/*
 * Copyright (c) 2019 JOOservices Ltd
 * @author Viet Vu <jooservices@gmail.com>
 * @package XGallery
 * @license GPL
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */
jQuery(document).ready(function () {
    jQuery.ajax({
        url: "/onejav/ajax",
        method: "GET",
        data: {
            action: 'getFeaturedItems'
        }
    })
        .done(function (data) {
            jQuery('.featured-items').append(data);
            if (lazyLoadInstance) {
                lazyLoadInstance.update();
            }
        })
        .fail(function () {

        })
        .always(function () {

        });


    jQuery.ajax({
        url: "/onejav/ajax",
        method: "GET",
        data: {
            action: 'getTags'
        }
    })
        .done(function (data) {
            jQuery.each(data, function (index, value) {
                jQuery('.tags').append('<span class="badge badge-info mr-1">' + value + '</span>');
            })
        })
        .fail(function () {

        })
        .always(function () {

        });

});
jQuery('button.load-more').on('click', function (el) {
    jQuery.ajax({
        url: "/onejav/ajax",
        method: "GET",
        data: {
            action: 'getDailyItems',
            date: jQuery('.daily-items .items').last().data('date')
        }
    })
        .done(function (data) {
            jQuery('.daily-items .items').after(data);
            if (lazyLoadInstance) {
                lazyLoadInstance.update();
            }
        })
        .fail(function () {

        })
        .always(function () {

        });
});