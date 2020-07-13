// 图形验证码
// canvas画验证码方法
function drawVerifyCode(vCode) {
    var canvas_width = $('#verify-canvas').width();
    var canvas_height = $('#verify-canvas').height();
    var canvas = document.getElementById("verify-canvas");//获取到canvas的对象，演员
    var context = canvas.getContext("2d");//获取到canvas画图的环境，演员表演的舞台
    canvas.width = canvas_width;
    canvas.height = canvas_height;
    var sCode = "A,B,C,E,F,G,H,J,K,L,M,N,P,Q,R,S,T,W,X,Y,Z,1,2,3,4,5,6,7,8,9,0";
    var aCode = sCode.split(",");
    var aLength = aCode.length;//获取到数组的长度

    for (var i = 0; i <= 3; i++) {
        var j = Math.floor(Math.random() * aLength);//获取到随机的索引值
        var deg = Math.random() * 30 * Math.PI / 180;//产生0~30之间的随机弧度
        var txt = aCode[j];//得到随机的一个内容
        vCode[i] = txt.toLowerCase();
        var x = 10 + i * 20;//文字在canvas上的x坐标
        var y = 20 + Math.random() * 8;//文字在canvas上的y坐标
        context.font = "bold 23px 微软雅黑";

        context.translate(x, y);
        context.rotate(deg);

        context.fillStyle = randomColor();
        context.fillText(txt, 0, 0);

        context.rotate(-deg);
        context.translate(-x, -y);
    }
    for (var i = 0; i <= 5; i++) { //验证码上显示线条
        context.strokeStyle = randomColor();
        context.beginPath();
        context.moveTo(Math.random() * canvas_width, Math.random() * canvas_height);
        context.lineTo(Math.random() * canvas_width, Math.random() * canvas_height);
        context.stroke();
    }
    for (var i = 0; i <= 30; i++) { //验证码上显示小点
        context.strokeStyle = randomColor();
        context.beginPath();
        var x = Math.random() * canvas_width;
        var y = Math.random() * canvas_height;
        context.moveTo(x, y);
        context.lineTo(x + 1, y + 1);
        context.stroke();
    }
}

function randomColor() {//得到随机的颜色值
    var r = Math.floor(Math.random() * 256);
    var g = Math.floor(Math.random() * 256);
    var b = Math.floor(Math.random() * 256);
    return "rgb(" + r + "," + g + "," + b + ")";
}

// 手机验证码
var $getCode = $('#J_GetCode');
/* 定义参数 */
$getCode.sendCode({
    disClass: 'btn-disabled',
    secs: 60,
    run: false,
    runStr: '{%s}秒后重新获取',
    resetStr: '重发验证码'
});
// 手机验证码点击发送
$('#J_GetCode').click(function () {
    /* ajax 成功发送验证码后调用【start】 */
    var userMobile = $.trim($('#user-mobile').val());
    let mobileReg = /^[1][3,4,5,7,8][0-9]{9}$/;
    if (userMobile == "" || userMobile.length != 11 || !mobileReg.test(userMobile)) {
        YDUI.dialog.toast('手机号码格式错误', 'error', 1300);
    } else {
        YDUI.dialog.loading.open('验证码发送中');
        request('/api/captcha/send', { mobile: userMobile }, 400).then(res => {
            $getCode.sendCode('start');
            $('#isSendCode').val(1)
            YDUI.dialog.toast('验证码已发送', 'success', 1500);
        }).catch(error => {
            console.log(error);
            YDUI.dialog.toast(error.toString(), 'error', 1300);
        })
    }
})