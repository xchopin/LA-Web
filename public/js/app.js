
$(function () {
    $('.message .close').on('click', function () {
        $(this).closest('.message').transition('fade')
    })

    $('.ui.sidebar').sidebar('attach events', '.toc.item')
    $('.ui.dropdown').dropdown()
    $('.menu .item').tab()

    $('#open-menu').click(function (){
        $('#hamburger-menu').toggleClass('open')
        $('#menu').transition('fade left', '50ms')
    })

    $('.item h3').click(function () {
        console.log($(this).closest('i').find('.down'))
        if ($(this).children('i').find('.down').length === 0 ) {
            $(this).children('i').find('.down').addClass('up').removeClass('down')

        } else {
            $(this).children('i').find('.up').addClass('down').removeClass('up')
        }

        $(this).closest('div').find('.collapsible').transition('fade down')
    })

})
