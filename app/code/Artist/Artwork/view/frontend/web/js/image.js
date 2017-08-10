var artworkAjax = null;
function getExtension(path) {
    var basename = path.split(/[\\/]/).pop(), // extract file name from full path ...
            // (supports `\\` and `/` separators)
            pos = basename.lastIndexOf("."); // get last position of `.`

    if (basename === "" || pos < 1)            // if file name is empty or ...
        return ""; //  `.` not found (-1) or comes first (0)

    return basename.slice(pos + 1); // extract extension ignoring `.`
}

function readURL(input) {
    var image = document.getElementById("add_work").value;
    var ext = getExtension(image);
    if (input.files && input.files[0])
    {
        var reader = new FileReader();
        reader.onload = function (e)
        {
//            document.getElementById("upld_img").src = e.target.result;
            var base64Image = e.target.result;
            //console.log(e.target.result);
            require(["jquery"], function ($)
            {
                var getUrl = window.location;
                //alert(getUrl);
                var baseUrl = getUrl.protocol + "//" + getUrl.host + "/" + getUrl.pathname.split('/')[1];
                if (artworkAjax)
                    artworkAjax.abort();
                //$('#loading').show();
                artworkajax = $.ajax({
                    url: baseUrl + '/aphro/artwork/account/upload',
//                    url: baseUrl + '/artwork/account/upload',
                    type: 'post',
                    data: "img_data=" + e.target.result + "&ext=" + ext,
                    beforeSend: function (xhr) {
                        $(".field.add_work").hide();
                        $('#loading').show();
                    },
                    success: function (res)
                    {
                        if (res == 'sucess')
                        {

                            $.ajax({
                                url: baseUrl + '/aphro/artwork/account/editart',
//                                url: baseUrl + '/artwork/account/editart',
                                type: "post",
                                success: function (res1)
                                {
                                    document.getElementById("upld_img").src = base64Image;
                                    $('#loading').hide();
                                    $(".art-remove-action").show();
                                    $("#work_result").html(res1);
                                    jQuery(".artwork-head").addClass("image-uploaded");
                                    if ($("#existing-artwork-id").length) {
                                        $("#artwork-remove-id").val($("#existing-artwork-id").val());
                                    }
                                    jQuery(".upload-to-all-text-container").hide();
                                }
                            });
                            //window.location.href = baseUrl+'/aphro/artwork/account/editart';
                        }
                    }
                });
            });
            //document.getElementById("work_url").value=e.target.result;
        }
//console.log(file_result);
        console.log(reader.readAsDataURL(input.files[0]));
    }
}
require(['jquery'], function ($) {
    $(document).on("keydown", "#tag_art", function (event) {
        var keycodes = [32, 9, 188], response = true;
        $(".art-tags-error").hide();
        if (event.keyCode == 9 && $.trim($(this).val()))
        {
            response = false;
        }
        if (keycodes.indexOf(event.keyCode) != -1 && $.trim($(this).val())) {
            var currentValue = $.trim($(this).val());
            var tags = $("#tags-list").val(), tagsList = tags.split(","), tagElement = null;
            if (currentValue && tags && tagsList.indexOf(currentValue) != -1) {
                $(".art-tags-error").show().text("Tags must be unique.");
                return false;
            }
            response=false;
            $(this).val("");
            if (tags) {
                tags = tags + "," + currentValue;
            } else {
                tags = currentValue;
            }
            $("#tags-list").val(tags);
            $(".show-tags-container").append($(".tag-element-container").html());
            tagElement = $(".show-tags-container .tag-element:last-child");
            tagElement.find(".tag-element-text").text(currentValue);
        }
        return response;
    });
    $(document).on("click", ".remove-tags", function () {
        var currentElement = $(this).closest(".tag-element"), tagValue = currentElement.find(".tag-element-text").text(),
                tags = $("#tags-list").val(), tagsList = tags.split(","), position = tagsList.indexOf(tagValue);
        if (position != -1) {
            tagsList.splice(position, 1);
            tags = tagsList.join(",");
            $("#tags-list").val(tags);
        }
        currentElement.remove();
    });
});

