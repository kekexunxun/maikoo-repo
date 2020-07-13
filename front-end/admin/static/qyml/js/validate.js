$(function () {
    // 小程序设置表格
    $("#form-system-setting").validate({
        rules: {
            mini_name: {
                required: true,
                maxlength: 10
            },
            share_text: {
                required: true,
                maxlength: 20
            }
        }
    });
})