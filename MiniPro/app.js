App({

  onLaunch: function() {
    var that = this;

    // promise引入
    let util = require('/utils/util.js');

    // 更新检测
    const updateManager = wx.getUpdateManager()
    updateManager.onCheckForUpdate(function(res) {
      // 请求完新版本信息的回调
      // console.log(res.hasUpdate)
    })
    updateManager.onUpdateReady(function() {
      util.modalPromisified({
        title: '更新提示',
        content: '新版本已经准备好，请点击确认以更新',
        showCancel: false,
      }).then(res => {
        updateManager.applyUpdate()
      })
    })

    // 用户登陆记录本地存储
    var logs = wx.getStorageSync('logs') || []
    logs.unshift(Date.now())
    wx.setStorageSync('logs', logs)

    that.loadInfo();
  },

  /**
   * 用户登陆
   * 装载系统设置
   */
  loadInfo: function() {
    var that = this;

    let util = require('/utils/util.js');

    // 用户信息请求
    util.loginPromisified().then(res => {
      if (res.code) {
        return util.post('minibase/getUserAccount', {
          openid: wx.getStorageSync('opeind'),
          code: res.code
        }, 100)
      }
    }).then(res => {
      wx.setStorageSync('openid', res.openid);
      that.globalData.uid = res.uid;
      that.globalData.isAuth = res.isAuth;
      that.globalData.isMember = res.isMember;
      that.globalData.points = res.points;
      return util.post('store/getSystemSetting', {
        uid: res.uid
      }, 100)
    }).catch(res => {
      util.modalPromisified({
        title: '系统提示',
        content: '请检查网络连接是否正常',
        confirmText: '重新连接'
      }).then(res => {
        if (res.confirm) {
          that.loadInfo();
        }
      })
    }).then(res => {
      that.globalData.setting = res;
    }).catch(res => {
      util.modalPromisified({
        title: '系统提示',
        content: '请检查网络连接是否正常',
        confirmText: '重新连接'
      }).then(res => {
        if (res.confirm) {
          that.loadInfo();
        }
      })
    }).finally(res => {
      console.log(that.globalData)
    })
  },

  /**
   * 全局变量
   */
  globalData: {
    siteroot: 'https://store.up.maikoo.cn/mini/'
  }

})