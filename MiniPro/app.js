//app.js
App({
  onLaunch: function(options) {
    //调用API从本地缓存中获取数据
    var that = this;

    // 判断是否有更新
    const updateManager = wx.getUpdateManager()
    updateManager.onCheckForUpdate(function(res) {
      // 请求完新版本信息的回调
      // console.log(res.hasUpdate)
    })
    updateManager.onUpdateReady(function() {
      wx.showModal({
        title: '更新提示',
        content: '新版本已经准备好，请点击确认以更新',
        showCancel: false,
        success: function(res) {
          if (res.confirm) {
            // 新的版本已经下载好，调用 applyUpdate 应用新版本并重启
            updateManager.applyUpdate()
          }
        }
      })
    })

    var logs = wx.getStorageSync('logs') || [];
    var currentTime = Date.now();
    logs.unshift(currentTime);
    wx.setStorageSync('logs', logs);

    wx.login({
      success: function(res) {
        // console.log('login')
        //获取用户openid
        if (res.code) {
          that.getUserAccountInfo(res.code);
          that.getSystemSetting();
        }
      }
    })

  },

  /**
   * 获取用户信息
   */
  getUserAccountInfo: function(code) {
    var that = this;
    // 获取用户信息
    wx.request({
      url: that.globalData.siteroot + 'mini/getUserAccountState',
      method: 'POST',
      dataType: 'json',
      data: {
        code: code,
        openid: wx.getStorageSync('openid')
      },
      success: function(res) {
        console.log(res)
        // 将用户相关信息入库，openid和登录态有效期
        let user = res.data.data;
        wx.setStorageSync('openid', user.user_openid);
        that.globalData.isAuth = user.isAuth;
        that.globalData.isAdmin = user.isAdmin || false;
        that.globalData.userInfo = user.userInfo;
        that.globalData.rebate = user.rebate || 0.00;
        that.globalData.inviteCode = user.inviteCode || 0;
        that.globalData.userID = user.userID;
        that.globalData.identID = user.identID;
        that.globalData.telNum = user.telNum;
        that.globalData.userName = user.userName;
      },
      fail: function() {
        wx.showToast({
          title: '网络错误',
          icon: 'loading'
        })
      }
    })
  },

  /**
   * 获取系统设置
   */
  getSystemSetting: function() {
    var that = this;
    wx.request({
      url: that.globalData.siteroot + 'mini/getSystemSetting',
      method: 'GET',
      dataType: 'json',
      success: function(res) {
        that.globalData.logi_fee = res.data.setting.logi_fee;
        that.globalData.logi_free_fee = res.data.setting.logi_free_fee;
        that.globalData.mini_color = res.data.setting.mini_color;
        that.globalData.mini_name = res.data.setting.mini_name;
        that.globalData.user_rebate_min = res.data.setting.user_rebate_min;
        that.globalData.share_text = res.data.setting.share_text;
      },
      fail: function() {
        wx.showToast({
          title: '网络错误',
          icon: 'loading'
        })
      }
    })
  },

  globalData: {
    userInfo: null,
    siteroot: "https://ft.up.maikoo.cn/",
    rebate: 0
  }
})