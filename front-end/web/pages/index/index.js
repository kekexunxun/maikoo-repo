const app = getApp();
const util = require('../../utils/util.js');
const tool = require('../../utils/tool.js');

Page({
  data: {
    gIndex: 0,
    imgCtnHeight: 0, // 图片所占区域高度
    group: ['同乡群', '校友群', '同班群', '社区群'],
    groupImgArr: ["/images/tx_index.jpg", "/images/xy_index.png", "/images/tb_index.jpg", "/images/sq_index.jpg"], // 群切换时 对应的群首页图片
    gTypeArr: ["COUNTRY", "SCHOOL", "CLASS", "COMMUNITY"], // 群类别
  },

  onLoad: function(options) {
    // 计算图片所在区域应占当前屏幕的高度
    var that = this;
    let sysInfo = wx.getSystemInfoSync();
    let imgCtnHeight = sysInfo.windowHeight - 222 * (sysInfo.screenWidth / 750);
    that.setData({
      imgCtnHeight: imgCtnHeight
    })
    // 获取小程序相关设置
    that.getMiniSetting();
    // 判断小程序是否需要跳转
    if (options.action && options.action == "redirect") {
      if (options.page == "approve") {
        wx.navigateTo({
          url: '/pages/approve/approve'
        })
      } else if (options.page == "apply") {
        wx.navigateTo({
          url: '/pages/memdetail/memdetail?action=apply&gtype=' + that.data.gTypeArr.indexOf(options.gtype) + '&applyid=' + options.applyid
        })
      } else if (options.page == 'gdetail') {
        wx.navigateTo({
          url: '/pages/gdetail/gdetail?gtype=' + that.data.gTypeArr.indexOf(options.gtype) + '&gid=' + options.gid
        })
      }
    }
    // 当小程序是通过认证重新进入的index
    if (app.globalData.share && app.globalData.share != "") {
      let share = app.globalData.share.split('=');
      app.globalData.share = "";
      wx.navigateTo({
        url: '/pages/gdetails/gdetails?gtype=' + share[0] + '&gid=' + share[1]
      })
    }
  },

  /**
   * 获取小程序相关设置
   */
  getMiniSetting: function() {
    var that = this;
    util.post('/api/setting', {}, that, 300).then(res => {
      app.globalData.shareText = res.data.share_text;
      if (res.data.mini_name) {
        wx.setNavigationBarTitle({
          title: res.data.mini_name
        })
      }
    }).catch(error => {})
  },

  /**
   * 变更群类别
   */
  groupChange: function(evt) {
    var that = this;
    that.setData({
      gIndex: evt.currentTarget.dataset.idx
    })
  },

  /**
   * 群轮播图滑动监听
   */
  swiperChange: function(evt) {
    this.setData({
      gIndex: evt.detail.current
    })
  },

  /**
   * 创建群
   */
  createGroup: function(evt) {
    tool.storeFormId(evt.detail.formId);
    wx.navigateTo({
      url: '/pages/gcreate/gcreate?gIdx=' + this.data.gIndex,
    })
  },

  /**
   * 用户分享
   */
  onShareAppMessage: function() {
    return {
      title: app.globalData.shareText || '「探迹」你想找的人都在这里',
      path: '/pages/index/index'
    }
  }

})