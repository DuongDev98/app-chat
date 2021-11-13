//dialog
function ShowDialogYesNo(message, callBack) {
    $('#modal-dialog').empty();
    $('#modal-dialog').append('<div class="modal-dialog" role="document">\n' +
        '    <div class="modal-content">\n' +
        '      <div class="modal-header">\n' +
        '        <h5 class="modal-title">Lựa chọn</h5>\n' +
        '        <button type="button" class="close" data-dismiss="modal" aria-label="Close">\n' +
        '          <span aria-hidden="true">&times;</span>\n' +
        '        </button>\n' +
        '      </div>\n' +
        '      <div class="modal-body">\n' +
        '      <p>'+message+'</p>' +
        '      </div>\n' +
        '      <div class="modal-footer">\n' +
        '        <div id="btnYes" class="btn btn-primary">Yes</div>\n' +
        '        <button type="button" class="btn btn-danger" data-dismiss="modal">No</button>\n' +
        '      </div>\n' +
        '    </div>\n' +
        '</div>');

    $('#modal-dialog').modal('show');

    $('#modal-dialog').find("#btnYes").on("click", function () {
        callBack();
    });
}
//Thong tin
function ShowDialogInfo(message) {
    $('#modal-dialog').empty();
    $('#modal-dialog').append('<div class="modal-dialog" role="document">\n' +
        '    <div class="modal-content">\n' +
        '      <div class="modal-header">\n' +
        '        <h5 class="modal-title">Thông báo</h5>\n' +
        '        <button type="button" class="close" data-dismiss="modal" aria-label="Close">\n' +
        '          <span aria-hidden="true">&times;</span>\n' +
        '        </button>\n' +
        '      </div>\n' +
        '      <div class="modal-body">\n' +
        '      <p>'+message+'</p>' +
        '      </div>\n' +
        '      <div class="modal-footer">\n' +
        '        <button type="button" class="btn btn-primary" data-dismiss="modal">OK</button>\n' +
        '      </div>\n' +
        '    </div>\n' +
        '</div>');

    $('#modal-dialog').modal('show');
}
//ajax post data
function SendDataToServer(url, method, data, callBackSuccess) {
    $.ajax({
        url : window.location.origin + url,
        type : method,
        data : {"data" : JSON.stringify(data)},
        success : function (data){
            var result = JSON.parse(data);
            var msg = result["msg"] + "";
            if (msg == "success") {
                callBackSuccess();
            } else {
                ShowDialogInfo(msg);
            }
        }, error : function (error) {
            ShowDialogInfo("Có lỗi trong quá trình xử lý, vui lòng liên hệ nhà cung cấp!\r\n" + JSON.stringify(error));
        }
    });
};