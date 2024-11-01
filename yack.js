$ = jQuery;
$(document).ready(function () {
    $.TruncateAtWord = function (Input, Length, AddDots) {
        if (Input === null)
            return Input;

        if (Input.length <= Length)
            return Input;

        var iLastSpace = Input.lastIndexOf(' ', Length),
        dots = "";

        if (AddDots == true) {
            dots = "...";
        }

        if (iLastSpace == -1)
            return Input.substr(0, Length) + dots;
        else
            return Input.substr(0, iLastSpace) + dots;
    };

    function yackRender(response) {
        if (response === null || response.Data === null || response.Data.length === 0) {
            if ($("#yackstar-stream > ul >li").length === 0) {
                $("#yackstar-stream").html('<div class="loading">' + yackLocal["noRecentActivities"] + '</div>');
            }
            return;
        }
        $("#yackstar-stream div.loading").remove();
        
        if ($("#yackstar-stream").find(">ul").length === 0) {
            $("#yackstar-stream").append("<ul id='yackul'></ul>");
        }

        var newElem = $("<ul></ul>");
        $(response.Data).each(function (index, item) {
            if ($("#yackstar-stream>ul>li[aid='" + item.ActivityID + "_" + item.NotificationActionID + "_" + item.ActivityTypeID +"_"+item.CreatedDate+ "']").length === 0) {
                var activity =
                    '<li aid="' + item.ActivityID + "_" + item.NotificationActionID + "_" + item.ActivityTypeID +"_"+item.CreatedDate +'" class="activity"><div><img src="' + response.Site + '/photo/user/' + item.CreatedByUserID + '/small" width="30" height="40" alt="" /></div>'
                        + '<div>'
                            + '<a class="yp" target="_blank" href="' + response.Site + '/users/profile/' + item.CreatedByUserID + '">' + $.TruncateAtWord(item.CreatedByDisplayName, 30, false) + '</a>'
                                + ' ' + GetActionName(item.NotificationActionID, item.ActivityTypeID)
                                    + ' ' + FormatAction(item.ProfileID, item.ProfileType, item.ProfileName, item.NotificationActionID, item.Title, item.ApplicationID, item.PermaLink, item.PageNumber, item.ActivityTypeID, item.ObjectID, item.Category, item.CreatedByUserID, response.Site)
                                        + '</li>';
                newElem.append(activity);
            }
        });
        var anim=$(newElem.html());
        anim.hide();
        $("#yackstar-stream>ul").prepend(anim);
        anim.slideDown('normal',function(){
        	$("#yackstar-stream").niceScroll({ zindex: 999 }).doScroll(0);
        });
    	$("#yackstar-stream").getNiceScroll().show();
    }

    function GetActionName(NActionID, ActivityTypeID) {
        if (ActivityTypeID === 3) {
            return yackLocal["hasTagged"];
        }
        var res = "";
        try {
            res = yackLocal["Action_" + NActionID];
        }
        catch (r) {
        }

        if (res == "") {
            res = " ";
        }
        return res;
    }

    function GetAppName(AppID, Title, ActivityTypeID, ActionID) {
        if ((ActivityTypeID == 2) && (ActionID != 10 && ActionID != 11)) {
            return yackLocal["comment"];
        }

        try {
            Title = yackLocal["App_" + AppID];
        }
        catch (r) {
        }

        if (Title == "") {
            Title = yackLocal["item"];
        }

        return Title;
    }

    function FormatAction(ProfileID, ProfileTypeID, ProfileName, ActionID, Title, AppID, PermaLink, PageNumber, ActivityTypeID, ObjectID, Category, CreatedByUserID, Site) {

        var profileAct = "", FileName = Title;

        Title = GetAppName(AppID, Title, ActivityTypeID, ActionID, CreatedByUserID, ProfileID, ProfileTypeID);

        switch (ActionID) {
            case 19:
                try {
                    Category = jQuery.parseJSON(Category).Name;
                } catch (e) {
                }
                profileAct = ' <a class="yp" target="_blank" href="' + Site + '/users/profile/' + ObjectID + '">' + $.TruncateAtWord(Category, 20, true) + '</a> ';
                break;
            case 23:
                try {
                    Category = jQuery.parseJSON(Category).Name;
                } catch (e) {
                }
                profileAct = ' <a class="yp" target="_blank" href="' + Site + '/tags/profile/' + ObjectID + '">' + $.TruncateAtWord(Category, 20, true) + '</a> ';
                break;
            case 11:
            case 10:
            case 16:
                if (ProfileID == CreatedByUserID) {
                    profileAct = yackLocal["his"];
                } else {
                    profileAct = ' <a class="yp" target="_blank" href="' + Site + '/users/profile/' + ProfileID + '">' + $.TruncateAtWord(ProfileName, 25, false) + '\'s</a> ';
                }
                break;
        }

        Title = $.TruncateAtWord(Title, 35, true);
        FileName = $.TruncateAtWord(FileName, 30, true);
        switch (ActionID) {
            case 9:
            case 19:
            case 21:
            case 22:
            case 23:
                if ((AppID == 58 || AppID == 62 || AppID == 63 || AppID == 64 || AppID == 7 || AppID == 29) && FileName != "") {
                    Title = FileName;
                }
                if (ActivityTypeID != 3) {
                    profileAct += yackLocal["inside"];
                }
                if ((AppID == 58 || AppID == 62) && PageNumber != null) {
                    profileAct += ' <a class="breakword" target="_blank" href="' + Site + PermaLink + '#' + PageNumber + '">' + FileName + '</a> ';
                } else {
                    profileAct += ' <a class="breakword" target="_blank" href="' + Site + PermaLink + '">' + Title + '</a> ';

                }
                if (ActivityTypeID == 3) {
                    profileAct += yackLocal["withTopic"] + ' <a class="yp" target="_blank" href="' + Site + '/tags/profile/' + ObjectID + '">' + $.TruncateAtWord(Category, 20, true) + '</a> ';
                }
                break;
            case 16:
            case 11:
            case 10:
                if ((AppID == 58 || AppID == 62 || AppID == 63 || AppID == 64) && FileName != "") {
                    profileAct += Title + ' <a class="breakword" target="_blank" href="' + Site + PermaLink + '">' + FileName + '</a> ';
                } else {
                    profileAct += ' <a class="breakword" target="_blank" href="' + Site + PermaLink + '">' + Title + '</a> ';
                }
                break;
        }

        return profileAct;

    }

    function yack_GetStream() {
        $.ajax({
            type: 'GET',
            contentType: "application/json;charset=utf-8",
            url: "?yackstream=1",
            cache: false,
            dataType: 'json',
            success: function (result) {
                if (result.Success == true) {
                    yackRender(result);
                }
            },
            error: function (e) {
                switch (e.status) {
                    case 401:
                    	if ($("#yackstar-stream").find(">ul").length === 0) {
                    		$("#yackstar-stream").html('<div class="loading">' + yackLocal["noAuth"] + '</div>');
                    	}
                        break;
                    case 500:
                    	if ($("#yackstar-stream").find(">ul").length === 0) {
                    		$("#yackstar-stream").html('<div class="loading">' + yackLocal["serverError"] + '</div>');
                    	}
                        break;
                    default:
                        break;
                }
            }
        });
    }

    window.setInterval(yack_GetStream, 30000);
    yack_GetStream();
});