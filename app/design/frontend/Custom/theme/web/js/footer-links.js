require(['jquery'], function ($) {
    $(function () {        
        $(".footer.content a[href='" + window.location.href + "']").addClass("active-page");
    });
});