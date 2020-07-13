//获取应用实例
const app = getApp()
var util = require('../../utils/util.js');

Page({
  data: {

  },

  onLoad: function() {
    // 获取当前小程序首页界面信息
    var that = this;
    wx.request({
      url: app.globalData.siteroot + 'shop/getShopInfo',
      method: 'GET',
      success: function(res) {
        if (res.statusCode == 200) {
          // 对当前banner进行构造
          var bannerList = [];
          var banner = res.data.banner;
          for (var i = 0; i < banner.length; i++) {
            bannerList.push(banner[i].pic);
          }
          that.setData({
            bannerList: bannerList, // 纯banner地址
            banner: res.data.banner, // 小程序Banner的所有信息
            column: res.data.column, // 专题推荐
            catagory: res.data.catagory // 分类展示
          })
        }
      }
    })
  },

  /**
   * Banner跳转
   */
  bannerNavigate: function(e) {
    var that = this;
    var idx = e.currentTarget.dataset.idx;
    var navigate = that.data.banner[idx].navigate;
    var navigateId = that.data.banner[idx].navigate_id;

    // 判断banner的跳转类型做处理 看是跳转到哪个位置
    if (navigate == 1) {
      wx.navigateTo({
        url: '../minidetail/minidetail?miniId=' + navigateId,
      })
    } else if (navigate == 2) {
      wx.navigateTo({
        url: '../columndetail/columndetail?columnid=' + navigateId,
      })
    } else if (navigate == 3) {
      wx.navigateTo({
        url: '../rank/rank',
      })
    }
  },


  /**
   * 用户分享
   */
  onShareAppMessage: function() {
    return {
      title: '这里有很多好玩的~快来看看吧~',
      path: '/pages/index/index'
    }
  },

  /**
   * 统计小程序点击情况
   * 首页 -> 小程序详情页 所以isEnter 传 0
   */
  miniClick: function(e) {
    // util.miniClickCount(e.currentTarget.dataset.mini, e.currentTarget.dataset.miniappid, 0);
  },

  /**
   * 跳转到小程序分类界面
   */
  navToCat: function(e) {
    var catId = e.currentTarget.dataset.catid;
    // 记录用户跳转的分类ID
    util.miniCatagoryCount(catId);
    wx.navigateTo({
      url: '../catagory/catagory?catId=' + catId,
    })
  },

  /**
   * 点击全部专题按钮
   */
  navToColumn: function() {
    wx.navigateTo({
      url: '../column/column',
    })
  },

  /**
   * 点击具体的专题
   */
  navToColumnDetail: function(e) {
    var columnId = e.currentTarget.dataset.idx;
    // 数据记录
    util.miniColumnCount(columnId);
    wx.navigateTo({
      url: '../columndetail/columndetail?columnid=' + columnId,
    })
  }


})