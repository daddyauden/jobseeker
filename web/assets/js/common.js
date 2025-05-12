if (navigator.userAgent.match(/IEMobile\/10\.0/)) {
    var msViewportStyle = document.createElement("style");
    msViewportStyle.appendChild(document.createTextNode("@-ms-viewport{width:auto!important}"));
    document.getElementsByTagName("head")[0].appendChild(msViewportStyle);
}

var brand_queue = function (content) {
    window.setTimeout(function () {
        var introLength = content.length;
        var queue = 0;
        $("#brand_intro p:first").html(content[queue]);
        queue++;
        function play() {
            $("#brand_intro p:first").fadeOut(1500, "linear");
            $("#brand_intro p:last").html(content[queue]).fadeIn(3000, "linear");
            if (queue < introLength - 1) {
                queue++;
            } else {
                queue = 0;
            }
            $("#brand_intro p:first").appendTo($("#brand_intro"));
        }
        window.setInterval(play, 5000);
    }, 0);
};

var now = $.now();

var initJobIndustry = function (industry, choose, isMultiple, defaultValue) {
    if (isMultiple === undefined || isMultiple === "") {
        isMultiple = true;
    }

    var industryStr = '<select id="industry" name="industry" id="industry" class="form-control"><option value="">' + choose + '</option>';

    var industrySubArr = [];

    var industryVal = [];

    if (isMultiple === true) {
        for (var industryId in industry) {
            industryId = parseInt(industryId);
            if (defaultValue && defaultValue[0] && defaultValue[0] === industryId) {
                industryStr += '<option selected="selected" value="' + industryId + '">' + industry[industryId]['name'];
            } else {
                industryStr += '<option value="' + industryId + '">' + industry[industryId]['name'];
            }

            if (industry[industryId]['sub']) {
                industrySubArr[industryId] = [];
                for (var industrySubId in industry[industryId]['sub']) {
                    industrySubId = parseInt(industrySubId);
                    if (defaultValue && defaultValue[1] && -1 !== $.inArray(industrySubId, defaultValue[1])) {
                        industrySubArr[industryId].push('<label><input type="checkbox" checked="checked" name="subindustry" id="subindustry" multiple="true" value="' + industrySubId + '" />' + industry[industryId]['sub'][industrySubId] + '</label>');
                    } else {
                        industrySubArr[industryId].push('<label><input type="checkbox" name="subindustry" id="subindustry" multiple="true" value="' + industrySubId + '" />' + industry[industryId]['sub'][industrySubId] + '</label>');
                    }
                }
            }
        }

        industryStr += '</select>';

        $("#job_industry_level_1").append(industryStr);

        $("#job_industry_level_2 input:checkbox").each(function () {

            if ($(this).is(":checked") === true) {
                industryVal.push($(this).val());
                $("#form_industry").val(industryVal.join(","));
            }

            $(this).click(function () {
                if ($(this).is(":checked") === true) {
                    industryVal.push($(this).val());
                    $("#form_industry").val(industryVal.join(","));
                }

                if ($(this).is(":checked") === false) {
                    var removeItem = parseInt($(this).val());
                    industryVal = $.grep(industryVal, function (industryValue) {
                        return industryValue != removeItem;
                    });
                    $("#form_industry").val(industryVal.join(","));
                }
            });
        });

        function a() {
            industryVal = [];
            var industryid = $("#form_industry").val();
            var id = $("#industry option:selected").val();

            if (id) {
                $("#job_industry_level_2").html(industrySubArr[id].join("")).show();
            } else {
                $("#job_industry_level_2").html("").hide();
            }

            $("#job_industry_level_2 input:checkbox").each(function () {
                if (industryid) {
                    for (var i = 0, len = industryid.length; i < len; i++) {
                        if ($(this).val() == industryid[i]) {
                            $(this).attr("checked", "checked");
                        }
                    }
                }

                $(this).click(function () {
                    if ($(this).is(":checked") === true) {
                        industryVal.push($(this).val());
                        $("#form_industry").val(industryVal.join(","));
                    }

                    if ($(this).is(":checked") === false) {
                        var removeItem = parseInt($(this).val());
                        industryVal = $.grep(industryVal, function (industryValue) {
                            return industryValue != removeItem;
                        });
                        $("#form_industry").val(industryVal.join(","));
                    }
                });
            });
        }

        $("#industry").change(a);
        $("#industry").click(a);
    } else {
        for (var industryId in industry) {
            industryId = parseInt(industryId);
            if (defaultValue && defaultValue[0] && defaultValue[0] === industryId) {
                industryStr += '<option selected="selected" value="' + industryId + '">' + industry[industryId]['name'];
            } else {
                industryStr += '<option value="' + industryId + '">' + industry[industryId]['name'];
            }

            if (industry[industryId]['sub']) {
                industrySubArr[industryId] = [];
                for (var industrySubId in industry[industryId]['sub']) {
                    industrySubId = parseInt(industrySubId);
                    if (defaultValue && defaultValue[1] && -1 !== $.inArray(industrySubId, defaultValue[1])) {
                        industrySubArr[industryId].push('<label><input type="radio" checked="checked" name="subindustry" id="subindustry" value="' + industrySubId + '" />' + industry[industryId]['sub'][industrySubId] + '</label>');
                    } else {
                        industrySubArr[industryId].push('<label><input type="radio" name="subindustry" id="subindustry" value="' + industrySubId + '" />' + industry[industryId]['sub'][industrySubId] + '</label>');
                    }
                }
            }
        }

        industryStr += '</select>';

        $("#job_industry_level_1").append(industryStr);

        $("#job_industry_level_2 input:radio").each(function () {

            if ($(this).is(":checked") === true) {
                $("#form_industry").val($(this).val());
            }

            $(this).click(function () {
                if ($(this).is(":checked") === true) {
                    $("#form_industry").val($(this).val());
                }
            });
        });

        function a() {
            industryVal = [];
            var industryid = $("#form_industry").val();
            var id = $("#industry option:selected").val();

            if (id) {
                $("#job_industry_level_2").html(industrySubArr[id].join("")).show();
            } else {
                $("#job_industry_level_2").html("").hide();
            }

            $("#job_industry_level_2 input:radio").each(function () {
                if (industryid && industryid == $(this).val()) {
                    $(this).attr("checked", "checked");
                }

                $(this).click(function () {
                    $("#form_industry").val($(this).val());
                });
            });
        }
        ;

        $("#industry").change(a);
        $("#industry").click(a);
    }
};

var initJobArea = function (area, choose, isMultiple, defaultValue) {
    if (isMultiple === undefined || isMultiple === "") {
        isMultiple = true;
    }

    var areaStr = '<select id="area" name="area" class="form-control"><option value="">' + choose + '</option>';

    var areaSubArr = [];

    var areaVal = [];

    if (isMultiple === true) {

        for (var areaId in area) {
            areaId = parseInt(areaId);
            if (defaultValue && defaultValue[0] && defaultValue[0] === areaId) {
                areaStr += '<option selected="selected" value="' + areaId + '">' + area[areaId]['name'];
            } else {
                areaStr += '<option value="' + areaId + '">' + area[areaId]['name'];
            }

            if (area[areaId]['sub']) {
                areaSubArr[areaId] = [];
                for (var areaSubId in area[areaId]['sub']) {
                    areaSubId = parseInt(areaSubId);
                    if (defaultValue && defaultValue[1] && -1 !== $.inArray(areaSubId, defaultValue[1])) {
                        areaSubArr[areaId].push('<label><input type="checkbox" checked="checked" name="subarea" id="subarea" multiple="true" value="' + areaSubId + '" />' + area[areaId]['sub'][areaSubId] + '</label>');
                    } else {
                        areaSubArr[areaId].push('<label><input type="checkbox" name="subarea" id="subarea" multiple="true" value="' + areaSubId + '" />' + area[areaId]['sub'][areaSubId] + '</label>');
                    }
                }
            }
        }

        areaStr += '</select>';

        $("#job_area_level_1").append(areaStr);

        $("#job_area_level_2 input:checkbox").each(function () {

            if ($(this).is(":checked") === true) {
                areaVal.push($(this).val());
                $("#form_area").val(areaVal.join(","));
            }

            $(this).click(function () {
                if ($(this).is(":checked") === true) {
                    areaVal.push($(this).val());
                    $("#form_area").val(areaVal.join(","));
                }

                if ($(this).is(":checked") === false) {
                    var removeItem = parseInt($(this).val());
                    areaVal = $.grep(areaVal, function (areaValue) {
                        return areaValue != removeItem;
                    });
                    $("#form_area").val(areaVal.join(","));
                }
            });
        });

        function b() {
            areaVal = [];
            $("#form_area").val("");
            var id = $("#area option:selected").val();

            if (id) {
                $("#job_area_level_2").html(areaSubArr[id].join("")).show();
            } else {
                $("#job_area_level_2").html("").hide();
            }

            $("#job_area_level_2 input:checkbox").each(function () {
                $(this).attr("checked", "checked");
                areaVal.push($(this).val());
                $("#form_area").val(areaVal.join(","));
                $(this).click(function () {
                    if ($(this).is(":checked") === true) {
                        areaVal.push($(this).val());
                        $("#form_area").val(areaVal.join(","));
                    }

                    if ($(this).is(":checked") === false) {
                        var removeItem = parseInt($(this).val());
                        areaVal = $.grep(areaVal, function (areaValue) {
                            return areaValue != removeItem;
                        });
                        $("#form_area").val(areaVal.join(","));
                    }
                });
            });
        }

        $("#area").change(b);
        $("#area").click(b);
    } else {
        for (var areaId in area) {
            areaId = parseInt(areaId);
            if (defaultValue && defaultValue[0] && defaultValue[0] === areaId) {
                areaStr += '<option selected="selected" value="' + areaId + '">' + area[areaId]['name'];
            } else {
                areaStr += '<option value="' + areaId + '">' + area[areaId]['name'];
            }

            if (area[areaId]['sub']) {
                areaSubArr[areaId] = [];
                for (var areaSubId in area[areaId]['sub']) {
                    areaSubId = parseInt(areaSubId);
                    if (defaultValue && defaultValue[1] && -1 !== $.inArray(areaSubId, defaultValue[1])) {
                        areaSubArr[areaId].push('<label><input type="radio" checked="checked" name="subarea" id="subarea" multiple="true" value="' + areaSubId + '" />' + area[areaId]['sub'][areaSubId] + '</label>');
                    } else {
                        areaSubArr[areaId].push('<label><input type="radio" name="subarea" id="subarea" multiple="true" value="' + areaSubId + '" />' + area[areaId]['sub'][areaSubId] + '</label>');
                    }
                }
            }
        }

        areaStr += '</select>';

        $("#job_area_level_1").append(areaStr);

        $("#job_area_level_2 input:radio").each(function () {

            if ($(this).is(":checked") === true) {
                $("#form_area").val($(this).val());
            }

            $(this).click(function () {
                if ($(this).is(":checked") === true) {
                    $("#form_area").val($(this).val());
                }
            });
        });

        function b() {
            areaVal = [];
            var areaid = $("#form_area").val();
            var id = $("#area option:selected").val();

            if (id) {
                $("#job_area_level_2").html(areaSubArr[id].join("")).show();
            } else {
                $("#job_area_level_2").html("").hide();
            }

            $("#job_area_level_2 input:radio").each(function () {
                if (areaid && areaid == $(this).val()) {
                    $(this).attr("checked", "checked");
                }

                $(this).click(function () {
                    $("#form_area").val($(this).val());
                });
            });
        }
        ;

        $("#area").change(b);
        $("#area").click(b);
    }
};

var initJobProduct = function (product, isMultiple, defaultValue) {
    if (isMultiple === undefined || isMultiple === "") {
        isMultiple = true;
    }
    var productStr = "";
    var productVal = [];
    if (isMultiple === true) {
        for (var productId in product) {
            if (defaultValue && -1 !== $.inArray(parseInt(productId), defaultValue)) {
                productStr += '<label><input type="checkbox" checked="checked" name="product" id="product" multiple="true" value="' + productId + '" />' + product[productId] + '</label>';
            } else {
                productStr += '<label><input type="checkbox" name="type" multiple="product" id="product" value="' + productId + '" />' + product[productId] + '</label>';
            }
        }

        $("#job_product").append(productStr);

        $("#job_product input:checkbox").each(function () {

            if ($(this).is(":checked") === true) {
                productVal.push($(this).val());
                $("#form_product").val(productVal.join(","));
            }

            $(this).click(function () {
                if ($(this).is(":checked") === true) {
                    productVal.push($(this).val());
                    $("#form_product").val(productVal.join(","));
                }
                if ($(this).is(":checked") === false) {
                    var removeItem = parseInt($(this).val());
                    productVal = $.grep(productVal, function (productValue) {
                        return productValue != removeItem;
                    });
                    $("#form_product").val(productVal.join(","));
                }
            });

        });
    } else {
        for (var productId in product) {
            if (defaultValue && -1 !== $.inArray(parseInt(productId), defaultValue)) {
                productStr += '<label><input type="radio" checked="checked" name="product" id="product" value="' + productId + '" />' + product[productId] + '</label>';
            } else {
                productStr += '<label><input type="radio" name="product" id="product" value="' + productId + '" />' + product[productId] + '</label>';
            }
        }

        $("#job_product").append(productStr);

        $("#job_product input:radio").each(function () {

            if ($(this).is(":checked") === true) {
                $("#form_product").val($(this).val());
            }

            $(this).click(function () {
                if ($(this).is(":checked") === true) {
                    $("#form_product").val($(this).val());
                }
            });

        });

        $("#job_product").hide();
    }
};

var initJobType = function (defaultValue) {
    $("#form_type option").each(function () {
        if (defaultValue && $(this).val() == defaultValue) {
            $(this).attr('selected', 'selected');
        }
    });
};


var initJobTypeForEdit = function (type, choose, isMultiple, defaultValue) {
    if (isMultiple === undefined || isMultiple === "") {
        isMultiple = true;
    }

    if (isMultiple === true) {
        var typeStr = '<select id="type" name="type" class="form-control" multiple="multiple"><option value="">' + choose + '</option>';

        for (var typename in type) {
            typeId = parseInt(type[typename]);
            if (defaultValue && defaultValue[0] && defaultValue[0] === typeId) {
                typeStr += '<option selected="selected" value="' + typeId + '">' + type[typeId] + '</option>';
            } else {
                typeStr += '<option value="' + typeId + '">' + type[typeId] + '</option>';
            }
        }

        typeStr += '</select>';

        $("#job_type").append(typeStr);

        function b() {
            var id = $("#type option:selected").val();
            $("#form_type").val(id);
        }

        $("#type").change(b);
    } else {
        var typeStr = '<select id="type" name="type" class="form-control"><option value="">' + choose + '</option>';
        for (var typename in type) {
            typeId = parseInt(type[typename]);
            if (defaultValue && defaultValue[0] && defaultValue[0] === typeId) {
                typeStr += '<option selected="selected" value="' + typeId + '">' + typename + '</option>';
            } else {
                typeStr += '<option value="' + typeId + '">' + typename + '</option>';
            }
        }

        typeStr += '</select>';

        $("#job_type").append(typeStr);

        function b() {
            var id = $("#type option:selected").val();
            $("#form_type").val(id);
        }

        $("#type").change(b);
    }
};

var initJobSalary = function (defaultValue) {
    $("#form_salary option").each(function () {
        if (defaultValue && $(this).val() === defaultValue) {
            $(this).attr('selected', 'selected');
        }
    });
};

var initJobBegintime = function (defaultValue) {
    $("#form_begintime option").each(function () {
        if (defaultValue && $(this).val() === defaultValue) {
            $(this).attr('selected', 'selected');
        }
    });
};

var edit = function (edit, url, comment) {
    var data = {};
    var inputs = $(edit).parent().parent().find("[name^=form]");
    for (var i = 0, len = inputs.length; i < len; i++) {
        if ($(inputs[i]).attr("required") == "required" && !$(inputs[i]).val()) {
            $(inputs[i]).attr("placeholder", comment.novalue);
            $(inputs[i]).addClass("animated shake");
            return;
        }
        var id = $(inputs[i]).attr("id");
        data[id.substring(id.lastIndexOf("_") + 1)] = $(inputs[i]).val();
    }
    $.ajax({
        type: "POST",
        url: url,
        data: $.toJSON(data)
    }).done(function (message) {
        if (message == "same") {
            $("#message_alert").fadeIn(500).html(comment.nochange).delay(2000).fadeOut(500);
        } else if (message == "success") {
            $("#message_alert").fadeIn(500).html(comment.success).delay(2000).fadeOut(500);
            window.location.reload();
        } else if (message == "error") {
            $("#message_alert").fadeIn(500).html(comment.faid).delay(2000).fadeOut(500);
        } else {
            $("#message_alert").fadeIn(500).html(comment.invalid).delay(2000).fadeOut(500);
        }
    });
};

var del = function (del, url, comment) {
    var pp = $(del).parent().parent();
    var id = pp.find("[name='form[id]']").val();
    $.ajax({
        type: "POST",
        url: url,
        data: id
    }).done(function (message) {
        if (message == "success") {
            $("#message_alert").fadeIn(500).html(comment.success).delay(2000).fadeOut(500);
            window.location.reload();
        } else if (message == "error") {
            $("#message_alert").fadeIn(500).html(comment.fail).delay(2000).fadeOut(500);
        } else {
            $("#message_alert").fadeIn(500).html(comment.invalid).delay(2000).fadeOut(500);
        }
    });
};

var delivery = function (data, url, comment) {
    $("#delivery_status").html(comment.deliverying);
    $.ajax({
        type: "POST",
        url: url,
        data: data
    }).done(function (message) {
        if (message == "success") {
            $("#delivery_status").html(comment.success);
        } else if (message == "fail") {
            $("#delivery_status").html(comment.fail);
        } else if (message == "repeat") {
            $("#delivery_status").html(comment.repeat);
        } else if (message == "nologin") {
            $("#delivery_status").html(comment.nologin);
        } else if (message == "same") {
            $("#delivery_status").html(comment.same);
        } else if (message == "more") {
            $("#delivery_status").html(comment.more);
        } else if (message == "less") {
            $("#delivery_status").html(comment.less);
        } else {
            $("#delivery_status").html(comment.invalid);
        }
    });
};

var reserve = function (data, url, comment) {
    $("#reserve_status").html(comment.deliverying);
    if ($(".tagsinput span.tag").length === 0) {
        $("#reserve_status").html(comment.less);
        $("#reserve_dialog_inner .col-4").addClass("animated shake");
        return 0;
    } else if ($(".tagsinput span.tag").length > 5) {
        $("#reserve_status").html(comment.more);
        return 0;
    } else {
        var dating = $("#dating").val();
        if (!dating) {
            $("#reserve_status").html(comment.less);
            return 0;
        }
        $.ajax({
            type: "POST",
            url: url,
            data: $.toJSON({"data": data, "dating": dating.split(","), "dmessage": $("#dmessage").val()})
        }).done(function (message) {
            if (message == "success") {
                $("#reserve_status").html(comment.success);
                $("#reserve_dialog").fadeOut(2000, "linear");
            } else if (message == "fail") {
                $("#reserve_status").html(comment.fail);
            } else if (message == "repeat") {
                $("#reserve_status").html(comment.repeat);
            } else if (message == "nologin") {
                $("#reserve_status").html(comment.nologin);
            } else if (message == "more") {
                $("#reserve_status").html(comment.more);
            } else if (message == "less") {
                $("#reserve_status").html(comment.less);
            } else {
                $("#reserve_status").html(comment.invalid);
            }
        });
    }
};

var schedule = function (data, url, comment) {
    $("#schedule_status").html(comment.deliverying);
    var schedule_date = $("input[name=schedule_date]:checked").val();
    if (schedule_date === undefined) {
        $("#schedule_status").html(comment.less);
        $("#reserve_dialog_inner .col-5").addClass("animated shake");
        return 0;
    } else {
        $.ajax({
            type: "POST",
            url: url,
            data: $.toJSON({"data": data, "schedule": schedule_date})
        }).done(function (message) {
            if (message == "success") {
                $("#schedule_status").html(comment.success);
                $("#reserve_dialog").fadeOut(2000, "linear");
            } else if (message == "fail") {
                $("#schedule_status").html(comment.fail);
            } else if (message == "repeat") {
                $("#schedule_status").html(comment.repeat);
            } else if (message == "nologin") {
                $("#schedule_status").html(comment.nologin);
            } else if (message == "less") {
                $("#schedule_status").html(comment.less);
            } else {
                $("#schedule_status").html(message);
            }
        });
    }
};

$('#dating').tagsinput({maxTags: 5});

$('.datetime').datetimepicker({
    language: locale,
    autoclose: true,
    showMeridian: true,
    startView: "year",
    minView: "month",
    minuteStep: 10,
    todayBtn: true,
    todayHighlight: true
});
$('.datetime').datetimepicker("setStartDate", $('.datetime').first().attr("startdate"));
$('.datetime').datetimepicker("setEndDate", $('.datetime').first().attr("enddate"));

$('.job_post_datetime').datetimepicker({
    language: locale,
    autoclose: true,
    showMeridian: true,
    startView: "year",
    minView: "hour",
    minuteStep: 10,
    todayBtn: true,
    todayHighlight: true
});
$('.job_post_datetime').datetimepicker("setStartDate", $('.job_post_datetime').first().attr("startdate"));
$('.job_post_datetime').datetimepicker("setEndDate", $('.job_post_datetime').first().attr("enddate"));

$('.delivery_dating').datetimepicker({
    language: locale,
    autoclose: true,
    showMeridian: true,
    startView: "year",
    minView: "hour",
    minuteStep: 10,
    todayBtn: true,
    todayHighlight: true
});
var date = new Date(now + 1000 * 60 * 60 * 3);
$('.delivery_dating').datetimepicker("setStartDate", date.getFullYear() + "-" + (date.getMonth() + 1) + "-" + date.getDate() + " " + date.getHours() + ":00");

$('.delivery_dating').on('change', function () {
    if ($(this).val()) {
        $("#dating").tagsinput('add', $(this).val());
    } else {
        $("#dating").tagsinput('refresh');
    }
});

$('.delivery_dating').on('focus', function () {
    $("#dating").tagsinput('removeAll');
    $('.delivery_dating').each(function () {
        var text = $(this).val();
        if (text) {
            $("#dating").tagsinput("add", text);
        }
    });
});

var clear_delivery_dating = function (text) {
    $('.delivery_dating').each(function () {
        if (text === $(this).val()) {
            $(this).val("");
        }
    });
};

$(document).ready(function () {
    var controller = function (event) {
        if (Object.prototype.toString.call(event.target) === Object.prototype.toString.call(this) || event.which === 27) {
            var country = $("#other_country").css("display");
            var locale = $("#other_locale").css("display");
            var job_industry_level_2 = $("#job_industry_level_2").css("display");
            var job_area_level_2 = $("#job_area_level_2").css("display");
            if (country !== "none") {
                $("#other_country").hide();
            }
            if (locale !== "none") {
                $("#other_locale").hide();
            }
            if (job_industry_level_2 !== "none") {
                $("#job_industry_level_2").hide();
            }
            if (job_area_level_2 !== "none") {
                $("#job_area_level_2").hide();
            }
        }
    };

    $(document).keyup(controller);

    $("#main").click(controller);

    $("#change_country").click(function (event) {
        $("#other_country").toggle();
        $("#other_locale").hide();
        event.stopPropagation();
    });

    $("#change_locale").click(function (event) {
        $("#other_locale").toggle();
        $("#other_country").hide();
        event.stopPropagation();
    });

    $("#other_locale li a").click(function () {
        var date = new Date();
        var navItem = null;
        date.setUTCDate(date.getUTCDate() + 365);
        document.cookie = "LANG=" + $(this).attr("name") + "; domain=" + domain + "; expires=" + date.toUTCString() + "; path=/";
        window.location.reload();
    });

    $("#signin").click(function () {
        $("#jobseeker_signin").fadeIn();
        $("#plugin_signin").fadeIn();
        $("#jobseeker_signup").fadeOut();
    });

    $("#signup").click(function () {
        $("#jobseeker_signup").fadeIn();
        $("#plugin_signin").fadeIn();
        $("#jobseeker_signin").fadeOut();
    });

    $(".nav li a").click(function () {
        var parent = $(this).parent();
        parent.addClass("actived");
        parent.siblings().removeClass("actived");
    });

    $("#close-btn").click(function () {
        $(this).parent().fadeOut(300, "linear");
    });

    $("#job_list_left ul.nav li a").click(function () {
        $("#job_list_right").fadeIn(300, "linear");
    });

    $("#delivery_left ul.nav li a").click(function () {
        $("#delivery_right").fadeIn(300, "linear");
    });

    $(".alert .close").click(function () {
        $(this).parent().slideUp();
    });

    $(".avator").hover(function () {
        $(".jobseeker_home").show();
    }, function () {
        $(".jobseeker_home").hide();
    });

    $(".nav a").click(function () {
        $(this).addClass("actived").siblings().removeClass("actived");
    });

    $("#toggle-dialog").click(function () {
        $("#reserve_dialog").show();
    });

    $(".select-type, .select-salary, .select-begintime").click(function () {
        $("#job_industry_level_2").hide();
        $("#job_area_level_2").hide();
    });

    $("#area").click(function () {
        $("#job_industry_level_2").hide();
    });

    $("#industry").click(function () {
        $("#job_area_level_2").hide();
    });
});