if (navigator.userAgent.match(/IEMobile\/10\.0/)) {
    var msViewportStyle = document.createElement("style");
    msViewportStyle.appendChild(document.createTextNode("@-ms-viewport{width:auto!important}"));
    document.getElementsByTagName("head")[0].appendChild(msViewportStyle);
}

function jobseeker(option) {
    function boot(option) {
        switch (option) {
            case "initMenu":
                initMenu();
                activeNav2Style();
                break;
            case "initLanguage":
                initLanguage()
                break;
            default:
                break;
        }
    }
    function initMenu() {
        $("#content_left ul.list-group").first().show().siblings().hide();
        $("#nav-level-1 li a").each(function(index) {
            $(this).click(function() {
                $(this).parent().addClass('active').siblings().removeClass('active');
                var level2 = "nav-level-2-" + $(this).attr('id').substring($(this).attr('id').lastIndexOf("-") + 1);
                $("#" + level2).show().siblings().hide();
                var firstChild = $("#" + level2 + " a").first();
                firstChild.addClass('active').siblings().removeClass('active');
                $("#container").attr('src', firstChild.attr('href'));
            });
        });
    }
    function activeNav2Style() {
        $("a.list-group-item").each(function() {
            $(this).click(function() {
                $(this).addClass('active').parent().siblings().children("a.list-group-item").removeClass('active');
            });
        });
    }
    function initLanguage() {
        $("#change-locale li a").click(function() {
            var date = new Date();
            var navItem = null;
            date.setUTCDate(date.getUTCDate() + 365);
            document.cookie = "LANG=" + $(this).attr("name") + "; domain=" + window.location.hostname + "; expires=" + date.toUTCString() + "; path=/admin";
            $("#container").attr("src", window.container.location.href);
        });
    }
    boot(option);
}

jQuery(document).ready(function() {
    if (window.container == undefined && self.location.href == parent.location.href) {
        var path = window.location.pathname;
        var reg = path.match(/\/admin(.*)/);
        if (reg[1] == "") {
            window.location.href = "/admin/";
        }
    }
    jobseeker("initMenu");
    jobseeker("initLanguage");
});