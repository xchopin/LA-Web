
$(function () {
    $('.message .close').on('click', function () {
        $(this).closest('.message').transition('fade')
    })

    $('.ui.sidebar').sidebar('attach events', '.toc.item')
    $('.ui.dropdown').dropdown()
    $('.menu .item').tab()

    $('#open-menu').click(function(){
        $('#hamburger-menu').toggleClass('open')
        $('#menu').transition('fade left', '50ms')
    })

});
