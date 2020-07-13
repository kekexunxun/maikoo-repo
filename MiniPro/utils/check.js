var app = getApp();

/**
 * 校验登陆状态
 * 判断app.js中的获取用户信息方法是否已经执行完成
 */
function checkLoginState() {
  var promise = new Promise((resolve, reject) => {
    var i = 0;
    var intval = setInterval(res => {
      if (app.globalData.uid && app.globalData.setting) {
        clearInterval(intval);
        resolve('success');
      } else {
        i++;
        if (i == 30) {
          clearInterval(intval);
          reject('overtime');
        }
      }
    }, 100)
  })
  return promise;
}

module.exports = {
  checkLoginState: checkLoginState
}