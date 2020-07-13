const app = getApp();
const util = require('../../utils/util.js');
const tool = require('../../utils/tool.js');

Page({

  data: {
    isAuth: false, // 用户是否认证
    toastHidden: true,
    menu: [{
      idx: 1,
      icon: "/images/icon_fav.png",
      text: "收藏记录",
      spot: false,
      tap: "navToFav"
    }, {
      idx: 2,
      icon: "/images/icon_approve.png",
      text: "我的审核",
      spot: false,
      tap: "navToApprove"
    }, {
      idx: 3,
      icon: "/images/icon_apply.png",
      text: "我的申请",
      spot: false,
      tap: "navToApply"
    }, {
      idx: 4,
      icon: "/images/icon_message.png",
      text: "我的消息",
      spot: false,
      tap: "navToMsg"
    }, {
      idx: 5,
      icon: "/images/btn_feedback.png",
      text: "用户反馈",
      spot: false,
      tap: "navToFB"
    }]
  },

  /**
   * 判断用户的认证情况
   */
  onShow: function() {
    if (app.globalData.isAuth) {
      this.setData({
        avatarUrl: app.globalData.avatarUrl,
        nickname: app.globalData.nickname,
        isAuth: true
      })
    }
    this.checkFlag();
  },

  onLoad: function() {},

  /**
   * 跳转到我的收藏界面
   */
  navToFav: function() {
    if (!this.checkAuth()) return;
    wx.navigateTo({
      url: '/pages/fav/fav'
    })
  },

  /**
   * 跳转到我的审核界面
   */
  navToApprove: function() {
    if (!this.checkAuth()) return;
    wx.navigateTo({
      url: '/pages/approve/approve'
    })
  },

  /**
   * 跳转到我的申请界面
   */
  navToApply: function() {
    if (!this.checkAuth()) return;
    wx.navigateTo({
      url: '/pages/apply/apply'
    })
  },

  /**
   * 跳转到我的消息界面
   */
  navToMsg: function() {
    if (!this.checkAuth()) return;
    // 将msg的spot去掉
    let menu = this.data.menu;
    menu[3].spot = false;
    this.setData({
      menu: menu
    })
    wx.navigateTo({
      url: '/pages/msg/msg'
    })
  },

  /**
   * 跳转到用户反馈
   */
  navToFB: function() {
    if (!this.checkAuth()) return;
    wx.navigateTo({
      url: '/pages/feedback/feedback'
    })
  },

  /**
   * 检测用户是否有申请 审核 消息记录
   */
  checkFlag: function() {
    var that = this;
    let menu = that.data.menu;
    util.post('/api/flag/message', {}, that, 100).then(res => {
      menu[3].spot = res.data.has_new_msg;
      return util.post('/api/flag/apply', {}, that, 100, false);
    }).then(res => {
      if (res.data.sq_has_unread || res.data.tx_has_unread || res.data.tb_has_unread || res.data.xy_has_unread) {
        menu[2].spot = true;
      } else {
        menu[2].spot = false;
      }
      return util.post('/api/flag/review', {}, that, 100, false);
    }).then(res => {
      if (res.data.sq_has_unread || res.data.tx_has_unread || res.data.tb_has_unread || res.data.xy_has_unread) {
        menu[1].spot = true;
      } else {
        menu[1].spot = false;
      }
      that.setData({
        menu: menu
      })
    }).catch(error => {
      that.setData({
        toastHidden: false,
        toastTitle: "FLAG获取失败",
        toastAction: ""
      })
    })
  },

  /**
   * 用户点击登录按钮
   */
  login: function(evt) {
    tool.storeFormId(evt.detail.formId);
    wx.navigateTo({
      url: '/pages/auth/auth'
    })
  },

  /**
   * 判断用户是否认证
   */
  checkAuth: function() {
    if (!app.globalData.isAuth) {
      this.setData({
        toastHidden: false,
        toastTitle: "请登陆后再进行后续操作",
        toastAction: "auth"
      })
    }
    return app.globalData.isAuth;
  },

  toastConfirm: function() {
    if (this.data.toastAction == "auth") {
      wx.navigateTo({
        url: '/pages/auth/auth'
      })
    }
  },

  /**
   * 用户下拉刷新 获取新消息
   */
  onPullDownRefresh: function() {
    this.checkFlag();
  }

})