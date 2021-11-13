$('#btnEditProfile').click(function(){
    $('#modal-dialog').empty();
    var ipId = $('#ipId').text();
    var ipName = $('#ipName').text();
    var ipPhone = $('#ipPhone').text();
    var ipEmail = $('#ipEmail').text();
    var ipAddress= $('#ipAddress').text();
    var dateArr= $('#ipDateOfBirth').text().split("/")
    var ipDateOfBirth = dateArr[2] + "-" + dateArr[1] + '-' + dateArr[0];

    var divInfo = '<form class="user" method="post" action="./edit-profile.php">\n' +
    '<div class="modal-dialog" role="document">\n' +
        '            <div class="modal-content">\n' +
        '              <div class="modal-header">\n' +
        '                <h5 class="modal-title">Edit infomation</h5>\n' +
        '                <button type="button" class="close" data-dismiss="modal" aria-label="Close">\n' +
        '                  <span aria-hidden="true">&times;</span>\n' +
        '                </button>\n' +
        '              </div>\n' +
        '              <div class="modal-body">\n' +
        '                <div class="form-group">\n' +
        '                    <label for="ipName">Name</label>\n' +
        '                    <input hidden type="text" class="form-control" name="valId" value="'+ ipId +'">\n' +
        '                    <input type="text" class="form-control" name="valName" placeholder="Name" value="'+ ipName +'">\n' +
        '                </div>\n' +
        '                <div class="form-group">\n' +
        '                    <label for="ipPhone">Phone</label>\n' +
        '                    <input type="text" class="form-control" name="valPhone" value="'+ ipPhone +'">\n' +
        '                </div>\n' +
        '                <div class="form-group">\n' +
        '                    <label for="ipEmail">Email address</label>\n' +
        '                    <input type="email" class="form-control" name="valEmail" aria-describedby="emailHelp" placeholder="Enter email" value="'+ ipEmail +'">\n' +
        '                </div>\n' +
        '                <div class="form-group">\n' +
        '                    <label for="ipAddress">Address</label>\n' +
        '                    <input type="text" class="form-control" name="valAddress" placeholder="Address" value="'+ ipAddress +'">\n' +
        '                </div>\n' +
        '                <div class="form-group">\n' +
        '                    <label for="ipDateOfBirth">Date of birth</label>\n' +
        '                    <input type="date" class="form-control" name="valDateOfBirth" placeholder="Date of birth" value="'+ipDateOfBirth+'"/>\n' +
        '                </div>\n' +
        '              </div>\n' +
        '              <div class="modal-footer">\n' +
        '                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>\n' +
        '                <button id="btnSaveProfile" type="submit" class="btn btn-primary">Save changes</button>\n' +
        '              </div>\n' +
        '            </div>\n' +
        '          </div>\n' +
        '       </form>';
    $('#modal-dialog').append(divInfo);
    $('#modal-dialog').modal('show');
});

$(document).on('change', '#btnFileInfo', function(){
    var file_data = $('#btnFileInfo').prop('files')[0];
    var form_data = new FormData();                  
    form_data.append('file', file_data);                          
    $.ajax({
        url: './uploadFileAvatar.php',
        cache: false,
        contentType: false,
        processData: false,
        data: form_data,                         
        type: 'post',
        success: function(msg){
            if (msg.indexOf('error') >= 0)
            {
                alert(msg);
            }
            else
            {
                window.location.href = './profile.php?userId=' + msg;
            }
        },
        error: function(err) {
            alert(err);
        }
     });
});

try{
    var userId = $("#accountId").attr('data-account');

    if (userId == undefined)
    {
        userId = $('#accountId').val();
    }
    
    if (userId.length > 0)
    {
        var host = "ws://" + window.location.hostname + ':1998?userId=' + userId;
        var socket = new WebSocket(host);
            
        socket.onopen = function(){
            console.log('connected');
        }

        socket.onclose = function(){
            console.log('disconnected');
        }

        socket.onmessage = function(msg){
            //xử lý dữ liệu trả về
            //0 lời mời kb, 1 chấp nhận kb, 2 không chấp nhận, 3 hủy kb, 4 tin nhắn
            //người gửi reload page, người nhận thực hiện công việc cụ thể
            var data = JSON.parse(msg.data);
            if (parseInt(data['type']) == 0)
            {
                //gửi kb
                if (userId == data['user_send'])
                {
                    reloadPage();
                }
                else
                {
                    if (location.href.indexOf('list-user.php?type=users') >= 0 || location.href.indexOf('list-user.php?type=friendreq') >= 0
                    || location.href.indexOf('profile.php?userId=' + data['user_send']) >= 0)
                    {
                        reloadPage();
                    }
                    else
                    {
                        queryUiNotification();
                        queryNumberOfFriendReq();
                    }
                }
            }
            else if (parseInt(data['type']) == 1)
            {
                queryUiAddFriends(data);
            }
            else if (parseInt(data['type']) == 2)
            {
                queryUiAddFriends(data);
            }
            else if (parseInt(data['type']) == 3)
            {
                //hủy kb
                if (location.href.indexOf('list-user.php?type=friends') >= 0 || location.href.indexOf('list-user.php?type=users') >= 0
                    || location.href.indexOf('profile.php?userId=' + data['user_send']) >= 0)
                {
                    reloadPage();
                }
                else
                {
                    queryNumberOfFriend();
                }
            }
            else if (parseInt(data['type']) == 4)
            {
                if (location.href.indexOf('message.php?userId=' + data['user_send']) >= 0)
                {
                    //Thêm tin nhắn vào hộp thoại
                    addMessageReceive(data['message']);
                }
                else if (location.href.indexOf('message.php') >= 0)
                {
                    //thêm người dùng vào danh sách nhắn tin
                    //chưa có thì thêm vào danh sách, có rồi thì hiển thị tên đỏ
                    var hasItem = false;
                    $(".user-message").each(function(liHtml){
                        if ($(liHtml).attr('data-id') == data['user_receive'])
                        {
                            hasItem = true;
                        }
                    });

                    if (!hasItem)
                    {
                        var strHtml = "<li class='user-message' data-id='"+data["user-send"]+"'>";
                        strHtml += "<div class='d-flex bd-highlight'>";
						strHtml += "<div class='img_cont'>";
                        strHtml += "<img src='"+(data["avatar"] == '' ? './uploads/no-image.png' : ('./uploads/' + data["avatar"]))+"' class='rounded-circle user_img'>";
						//strHtml += "<span class='online_icon offline'></span>";
						strHtml += "</div>";
						strHtml += "<div class='user_info'>";
                        strHtml += "<span>"+data["name"]+"</span>";
                        //strHtml += "<p>Kalid is online</p>";
                        strHtml += "<div class='user_info'>";
                        strHtml += "</div>";
                        strHtml += "</div>";
                        strHtml += "</li>";
                        $('.contacts').append(strHtml);
                    }
                }
                else
                {
                    //trang khac thi
                    queryUiMessage();                    
                }
            }
        }
    }
}
catch
{
    console.log('Error socket');
}

function reloadPage()
{
    setTimeout(() => {
        location.reload();
    }, 100);
}

function queryUiAddFriends(data)
{
    if (userId == data['user_send'])
    {
        reloadPage();
    }
    else
    {
        if (location.href.indexOf('list-user.php?type=users') >= 0 || location.href.indexOf('list-user.php?type=friends') >= 0
        || location.href.indexOf('profile.php?userId=' + data['user_send']) >= 0)
        {
            location.reload();
        }
        else
        {
            queryUiNotification();
            queryNumberOfFriend();
        }
    }
}

function queryNumberOfFriendReq()
{
    $.get( "./func.php?f=numberOfFriendReq", function(strData) {
        var data = JSON.parse(strData);
        $('#countRequest').text(data["data-count"]);
    });
}

function queryNumberOfFriend()
{
    $.get( "./func.php?f=numberOfFriend", function(strData) {
        var data = JSON.parse(strData);
        $('#countFriend').text(data["data-count"]);
    });
}

function queryUiNotification()
{
    $.get( "./func.php?f=notificationCenter", function(strData) {
        var data = JSON.parse(strData);
        $('#countNotification').text(data["data-count"]);
        //bind lại danh sách thông báo
        $('#notificationCenter').empty();
        var arr = data['data-arr'];
        var htmlContent = '';
        arr.forEach(element => {
            htmlContent += '<a class="dropdown-item d-flex align-items-center" href="./profile.php?userId='+element["DACCOUNTID_SEND"]+'&readed='+element["ID"]+'">';
            htmlContent += '    <div class="dropdown-list-image mr-3">';
            htmlContent += '        <img class="rounded-circle" src="./uploads/'+(element["AVATAR"] == null ? 'no-image.png' : element["AVATAR"])+'" alt="">';
            //htmlContent += '        <div class="status-indicator bg-success"></div>';
            htmlContent += '    </div>';
            htmlContent += '    <div>';
            htmlContent += '        <div class="small text-gray-500">'+element["TIMECREATED"]+'</div>';
            htmlContent += (element['WATCHED']) == 0 ? '        <span class="font-weight-bold">' : '';
            htmlContent += element["MESSAGE"];
            htmlContent += (element['WATCHED']) == 0 ? '        </span>' : '';
            htmlContent += '    </div>';
            htmlContent += '</a>';
        });
        $('#notificationCenter').append(htmlContent);
    });
}

function queryUiMessage()
{
    $.get( "./func.php?f=messageCenter", function(strData) {
        var data = JSON.parse(strData);
        $('#countMessage').text(data["data-count"]);
        //bind lại danh sách thông báo
        $('#messageCenter').empty();
        var arr = data['data-arr'];
        var htmlContent = '';
        arr.forEach(element => {
            htmlContent += '<a class="dropdown-item d-flex align-items-center" href="./message.php?userId='+element["DACCOUNTID_SEND"]+'">';
            htmlContent += '    <div class="dropdown-list-image mr-3">';
            htmlContent += '        <img class="rounded-circle" src="./uploads/'+(element["AVATAR"] == null ? 'no-image.png' : element["AVATAR"])+'" alt="">';
            //htmlContent += '        <div class="status-indicator bg-success"></div>';
            htmlContent += '    </div>';
            htmlContent += '    <div>';
            htmlContent += '        <div class="small text-gray-500">'+element["TIMECREATED"]+'</div>';
            htmlContent += (element['WATCHED']) == 0 ? '        <span class="font-weight-bold">' : '';
            htmlContent += element["MESSAGE"];
            htmlContent += (element['WATCHED']) == 0 ? '        </span>' : '';
            htmlContent += '    </div>';
            htmlContent += '</a>';

        });
        $('#messageCenter').append(htmlContent);
    });
}

// public $type;
// public $user_send;
// public $user_reveice;
// public $message;
//0 lời mời kb, 1 chấp nhận kb, 2 không chấp nhận, 3 hủy kb, 4 tin nhắn
//click add friend
$('.addFriend').click(function(){
    var userId = $("#accountId").attr('data-account');
    var dataId = $(this).attr('data-id');

    var data = {};
    data['type'] = 0;
    data['user_send'] = userId;
    data['user_reveice'] = dataId;

    $.post("./func.php?f=sendFriendRequest", {data: JSON.stringify(data)}, function(e){
        processData(e, data);
    });
});

$('.confirm-f').click(function(){
    var userId = $("#accountId").attr('data-account');
    var dataId = $(this).attr('data-id');
    var data = {};
    data['type'] = 1;
    data['user_send'] = userId;
    data['user_reveice'] = dataId;

    $.post("./func.php?f=okFriendRequest", {data: JSON.stringify(data)}, function(e){
        processData(e, data);
    });
});

$('.cancel-confirm-f').click(function(){
    var userId = $("#accountId").attr('data-account');
    var dataId = $(this).attr('data-id');
    var data = {};
    data['type'] = 2;
    data['user_send'] = userId;
    data['user_reveice'] = dataId;
    
    $.post("./func.php?f=cancelFriendRequest", {data: JSON.stringify(data)}, function(e){
        processData(e, data);
    });
});

$('.remove-f').click(function(){
    var userId = $("#accountId").attr('data-account');
    var dataId = $(this).attr('data-id');
    var data = {};
    data['type'] = 3;
    data['user_send'] = userId;
    data['user_reveice'] = dataId;
    
    $.post("./func.php?f=removeFriend", {data: JSON.stringify(data)}, function(e){
        processData(e, data);
    });
});

function processData(e, dataSocket)
{
    var data = JSON.parse(e);
    if (data['success'])
    {
        try
        {
            if (socket != undefined) socket.send(JSON.stringify(dataSocket));
        }
        catch
        {}
        reloadPage();
    }
    else
    {
        alert(data["error"]);
    }
}

$('.user-message').click(function(){
    $('.user-message').removeClass("active");
    $(this).addClass("active");
});

$('.input-group-append').click(function(){
    saveMessage();
});

$('#txtMessage').keyup(function(e){
    if (e.keyCode == 13)
    {
        saveMessage();
    }
});


function scrollToEnd()
{
    var d = $('.msg_card_body');
    d.scrollTop(d.prop("scrollHeight"));
}

function saveMessage()
{
    //Lấy thông tin và tin nhắn gửi
    if ($.trim($('#currentId').val()).length == 0 || $.trim($('#accountId').val()).length == 0 || $.trim($('#txtMessage').val()).length == 0)
    {
        $('#txtMessage').val('');
        return;
    }

    var data = {};
    data['user_send'] = $.trim($('#accountId').val());
    data['user_reveice'] = $.trim($('#currentId').val());
    data['type'] = 4;
    data['message'] = $.trim($('#txtMessage').val());
    data['avatar'] = $.trim($('#account_avatar').val());
    data['name'] = $.trim($('#account_name').val());
    //lưu tin nhắn
    $.post('./func.php?f=saveMessage', {data: JSON.stringify(data)}, function(dataStr, status, xhr){
        if (status == 'success')
        {
            var dataObj = JSON.parse(dataStr);
            if (dataObj['success'])
            {
                //hiển thị tin vừa gửi
                addMessageSend($('#txtMessage').val());
                try
                {
                    if (socket != undefined) socket.send(JSON.stringify(data));
                }
                catch
                {}
                scrollToEnd();
            }
            else{
                alert(dataObj['msg']);
            }
        }
        else
        {
            alert(JSON.stringify(dataObj));
        }
    });
}

function addMessageSend(data){
    var avatar = $('#userAvatar').val();
    var html = '';
    html += '<div class="d-flex justify-content-end mb-4">';
    html += '   <div class="msg_cotainer_send">';
    html += data;
    //html += '<span class="msg_time_send">9:10 AM, Today</span>';
    html += '   </div>';
    html += '   <div class="img_cont_msg">';
    html += '       <img src="'+avatar+'" class="rounded-circle user_img_msg">';
    html += '   </div>';
    html += '</div>';
    $('.msg_card_body').append(html);
    $('#txtMessage').val("");
}

function addMessageReceive(data){
    var avatar = $('#currentAvatar').attr('src');
    var html = '';
    html += '<div class="d-flex justify-content-start mb-4">';
    html += '   <div class="img_cont_msg">';
    html += '       <img src="'+avatar+'" class="rounded-circle user_img_msg">';
    html += '   </div>';
    html += '   <div class="msg_cotainer">';
    html += data;
    //html += '<span class="msg_time_send">9:10 AM, Today</span>';
    html += '   </div>';
    html += '</div>';
    $('.msg_card_body').append(html);
    scrollToEnd();
}

$(document).on("click", ".user-message", function(){
    //đổi thông tin, fill lại thông tin tin nhắn
    $('#currentAvatar').attr("src", $(this).find(".user_img").attr("src").trim());
    $('#currentId').attr("value", $(this).attr("data-id"));
    $('.user_info').find("span").text($(this).find(".user_info").text().trim());
    $('.msg_card_body').empty();

    var data = {};
    data['user_send'] = $.trim($('#accountId').val());
    data['user_reveice'] = $.trim($(this).attr("data-id"));

    $.post('./func.php?f=getAllMessage', {data: JSON.stringify(data)}, function(dataStr, status, xhr){
        if (status == 'success')
        {
            var dataObj = JSON.parse(dataStr);
            if (dataObj['success'])
            {
                //Hiển thị tin nhắn
                var arr = JSON.parse(dataObj["msg"]);
                arr.forEach((value, index)=>{
                    if (value["LOAI"] == 0)
                    {
                        addMessageSend(value["MESSAGE"]);
                    }
                    else
                    {
                        addMessageReceive(value["MESSAGE"]);
                    }
                    scrollToEnd();
                });
            }
            else{
                alert(dataObj['msg']);
            }
        }
        else
        {
            alert(JSON.stringify(dataObj));
        }
    });
});

$(document).ready(function(){
    scrollToEnd();
});