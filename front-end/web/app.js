App({

  onLaunch: function() {
    var that = this;
    const util = require('/utils/util.js');
    // 更新检测
    const updateManager = wx.getUpdateManager()
    updateManager.onCheckForUpdate(function(res) {
      // 请求完新版本信息的回调
    })
    updateManager.onUpdateReady(function() {
      util.modalPromisified({
        title: '更新提示',
        content: '新版本已经准备好，请点击确认以更新',
        showCancel: false,
      }).then(res => {
        updateManager.applyUpdate();
      }).catch(error => {
        console.log(error);
      })
    })
    // 用户登录 用户信息获取
    that.userLogin();
  },

  /**
   * 用户登陆 获取accessToken
   */
  userLogin: function() {
    var that = this;
    const util = require('/utils/util.js');
    util.loginPromisified().then(res => {
      if (res.code) {
        // 获取openid
        return util.post('/api/user/login', {
          code: res.code
        }, that, 100, false);
      } else {
        this.setData({
          toastHidden: false,
          toastTitle: "网络错误，请检查网络后重试",
          toastAction: "",
          toastCancel: 0
        })
      }
    }).then(res => {
      wx.setStorageSync('token', res.data.access_token);
      // 获取用户基本信息
      return util.post('/api/user/information', {}, that, 200, false);
    }).catch(error => {}).then(res => {
      if (res.msg == "user auth fail") {
        that.globalData.isAuth = false;
      } else {
        that.globalData.nickname = res.data.nickname;
        that.globalData.avatarUrl = res.data.avatar_url;
        that.globalData.isAuth = true;
      }
    }).catch(error => {})
  },

  /**
   * 全局变量
   */
  globalData: {
    siteroot: 'https://minglu.maikoo.cn'
  },

  /**
   * 将收集到的用户openid发向后台
   */
  uploadFormId: function() {
    var formIdArr = wx.getStorageSync('formIdArr') || [];
    if (formIdArr.length == 0) {
      return;
    }
    const util = require('/utils/util.js');
    util.post('/api/form-id/insert', JSON.stringify(formIdArr), this, 100, false).then(res => {
      // 成功上传之后将对应缓存移除
      wx.removeStorageSync('formIdArr');
    })
  },

  /**
   * 小程序隐藏时触发
   */
  onHide: function() {
    this.uploadFormId();
  }

})