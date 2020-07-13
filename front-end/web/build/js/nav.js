// 检测当前用户是否已登录 如何没有 登陆则跳转至登陆界面
var webroot = "http://www.supermc.vip";
if (!YDUI.util.sessionStorage.get('token')) {
    YDUI.dialog.alert('请先登录', function () {
        location.href = webroot + "/login.html"
    });
}

// 界面刷新方法
function pageReload() {
    setTimeout(res => {
        location.reload();
    }, 1300);
}
// 返回上一页
function pageBack(timeOut = 1300) {
    setTimeout(res => {
        history.back();
    }, timeOut);
}
// 登陆成功后跳转到首页
function navToIndex(timeOut = 1300) {
    setTimeout(res => {
        location.href = webroot + '/index.html';
    }, timeOut);
}
// 跳转到登陆界面
function navToLogin(timeOut = 1300) {
    setTimeout(res => {
        location.href = webroot + '/login.html';
    }, timeOut);
}
// 点击跳转到支付宝绑定界面
function navToBindAlipay() {
    location.href = './html/my/alipay.html';
}
// 点击跳转到银行卡绑定界面
function navToBindBank() {
    location.href = './html/my/bankcard.html';
}
// 点击跳转到钱包地址绑定
function navToBindWAdress() {
    location.href = '../address.html';
}
// 轮播图点击跳转到新闻公告详情
function navToNoticeDetail(obj) {
    if ($(obj).data('navtype') == 1) {
        location.href = './html/news/detail.html?id=' + $(obj).data('navid');
    }
}
// 新闻列表点击跳转到新闻公告详情
function navToNewsDetail(obj) {
    location.href = './detail.html?id=' + $(obj).data('id');
}
// 跳转到我的矿机详情
function navToPoolDetail(obj) {
    window.location.href = "./html/pool/detail.html?sn=" + $(obj).data('sn');
}
// 跳转到购买矿机界面
function navToMachineDetail(obj) {
    location.href = './buy.html?sn=' + $(obj).data('sn');
}
// 跳转到SMC交易记录详情
function navToSMCRecordDetail(obj) {
    let type = $(obj).data('type');
    let url = "";

    if (type == 0) {
        url = './record/buyin.detail.html?sn=';
    } else if (type == 1) {
        url = './record/sell.detail.html?sn=';
    } else if (type == 2) {
        url = './record/extract.detail.html?sn=';
    } else {
        url = './record/lp.detail.html?sn=';
    }
    location.href = url + $(obj).data('sn');
}
// 跳转到ETH交易记录详情
function navToETHRecordDetail(obj) {
    // 分type进行跳转
    let type = $(obj).data('type');
    let url = "";
    if (type == 0) {
        url = './record/buyin.detail.html?sn=';
    } else if (type == 1) {
        url = './record/sell.detail.html?sn=';
    } else if (type == 2) {
        url = './record/extract.detail.html?sn=';
    } else {
        url = './record/exchange.detail.html?sn=';
    }
    location.href = url + $(obj).data('sn');
}
// footer点击
$('#index').click(function () {
    location.href = './index.html';
})
$('#pool').click(function () {
    location.href = './pool.html';
})
$('#wallet').click(function () {
    location.href = './wallet.html';
})
$('#my').click(function () {
    location.href = './my.html';
})
// nav点击
$(function () {
    $('.back-ico').click(function () {
        history.back();
    })
})
// 首页
$('#navToNoticeDetail').click(function () {
    location.href = './html/news/detail.html?id=' + $(this).data('navid');
})
// 我的钱包界面
$('#navToMyAddress').click(function () {
    location.href = './html/wallet/address.html';
})
$('#navToSMCExtract').click(function () {
    location.href = './html/wallet/smc/extract.html';
})
// 我的界面
$('#navToAuth').click(function () {
    location.href = './html/my/auth.html';
})
$('#navToChangePass').click(function () {
    location.href = './html/my/cpass.html';
})
$('#navToChangeTPass').click(function () {
    location.href = './html/my/tpass.html';
})
$('#navToAliAccount').click(function () {
    location.href = './html/my/alipay.html';
})
$('#navToBankcard').click(function () {
    location.href = './html/my/bankcard.html';
})
$('#navToNotice').click(function () {
    location.href = './html/news/list.html';
})
$('#navToSMCTrade').click(function () {
    location.href = './html/wallet/smc/type.html';
})
$('#navToMachine').click(function () {
    location.href = './html/wallet/machine/list.html';
})
$('#navToSMCLock').click(function () {
    location.href = './html/wallet/smc/lp.html';
})
$('#navToETH').click(function () {
    location.href = './html/wallet/eth/index.html';
})
// SMC交易
$('#navToSMCBuyIn').click(function () {
    location.href = './buyin.html';
})
$('#navToSMCSell').click(function () {
    location.href = './sell.html';
})
$('#navToSMCRecord').click(function () {
    location.href = './record.html';
})
$('#navToSMCExtractRecord').click(function () {
    location.href = './extract.record.html';
})
$('#navToSMCLpRecord').click(function () {
    location.href = './lp.record.html';
})
// ETH交易
$('#navToETHBuyIn').click(function () {
    location.href = './buyin.html';
})
$('#navToETHSell').click(function () {
    location.href = './sell.html';
})
$('#navToETHExtract').click(function () {
    location.href = './extract.html';
})
$('#navToETHExchange').click(function () {
    location.href = './exchange.html';
})
$('#navToETHRecord').click(function () {
    location.href = './record.html?etype=' + $(this).data('type');
})
$('#navToETHRecordDetail').click(function () {
    location.href = './record.html?etype=' + $(this).data('type');
})
$('#navToETHExchangeRecord').click(function () {
    location.href = './exchange.record.html';
})