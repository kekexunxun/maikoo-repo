function check() {
  if (!$('#account').val()) {
    layer.msg('用户名不能为空!', { icon: 2, time: 1000 });
  } else if (!$('#pwd').val()) {
    layer.msg('密码不能为空!', { icon: 2, time: 1000 });
  } else {
    var username = $('#account').val();
    var password = md5($('#pwd').val());
    $.ajax({
      url: 'checkLogin',
      type: "POST",
      dataType: "json",
      data: {
        username: username,
        password: password
      },
      success: function (res) {
        // alert(res.code);
        if (res.code == "0") {
          layer.msg('登陆成功!', { icon: 1, time: 1000 });
          setTimeout("window.location.href='http://xnps.up.maikoo.cn/index'", 1000);
        } else if (res.code == "100") {
          layer.msg('账号不存在!', { icon: 2, time: 1000 });
        } else if (res.code == "300") {
          layer.msg('密码错误!', { icon: 2, time: 1000 });
        } else if (res.code == "400") {
          layer.msg('账号未启用，请联系最高管理员启用!', { icon: 2, time: 1000 });
        }
      }
    });
  }
}
// 键盘事件
function keyDown() {
  if (event.keyCode == 13) {  //回车键的键值为13
    $('#loginbtn').click(); //调用登录按钮的登录事件
  }
}
$(document).ready(
  function () {
    $("#gotoPage").keydown(function (event) {
      if (event.keyCode == 13) {
        $('#loginbtn').click();
      }
    })
  }
);