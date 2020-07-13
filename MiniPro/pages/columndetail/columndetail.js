var app = getApp();
var util = require('../../utils/util.js');
Page({

  data: {
    columnInfo: [], // 专栏详情
    rateStar: [0, 0, 0, 0, 0], // 构造评分的星级
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function(options) {
    this.getColumnInfo(options.columnid)
  },

  /**
   * 获取当前专栏的详情
   */
  getColumnInfo: function(columnid) {
    var that = this;
    wx.request({
      url: app.globalData.siteroot + 'column/getColumnInfo',
      method: 'POST',
      data: {
        columnid: columnid,
        openid: wx.getStorageSync('openid')
      },
      dataType: 'json',
      success: function(res) {
        if (res.statusCode == 200 && res.data.code == 0) {
          that.setData({
            columnInfo: res.data.data
          })
          // 动态设置专题名称
          wx.setNavigationBarTitle({
            title: res.data.data.name,
          })
        } else {
          wx.showModal({
            title: '系统提示',
            content: '参数错误',
            showCancel: false,
            success: function(res) {
              if (res.confirm) {
                wx.navigateBack({
                  delta: 1
                })
              }
            }
          })
        }
      },
      fail: function() {},
      complete: function() {}
    })
  },

  /**
   * 用户进行收藏和取消收藏的操作
   */
  userFavChange: function(e) {
    var that = this;
    // 这个index是当前点击的小程序在这个专题列表中的索引
    var index = e.currentTarget.dataset.index;
    var columnInfo = that.data.columnInfo;
    // 判断是否新增还是取消
    var url = columnInfo.minis[index].isFav ? 'user/userCancelFav' : 'user/userAddFav';
    wx.showLoading({
      title: '请求中...',
      mask: true
    })
    wx.request({
      url: app.globalData.siteroot + url,
      method: 'POST',
      dataType: 'json',
      data: {
        openid: wx.getStorageSync('openid'),
        favId: columnInfo.minis[index].mini_id,
        appid: columnInfo.minis[index].appid,
        favType: 1,
        idx: columnInfo.minis[index].isFav ? columnInfo.minis[index].favIdx : ''
      },
      success: function(res) {
        if (res.statusCode == 200 && res.data.code == 0) {
          // 更新界面数据
          columnInfo.minis[index].isFav = columnInfo.minis[index].isFav ? !columnInfo.minis[index].isFav : true;
          columnInfo.minis[index].favIdx = res.data.data ? res.data.data : columnInfo.minis[index].favIdx;
          that.setData({
            columnInfo: columnInfo
          })
          var toastTitle = "";
          if (columnInfo.minis[index].isFav) {
            toastTitle = "收藏成功";
          } else {
            toastTitle = "取消收藏成功";
          }
          wx.showToast({
            title: toastTitle,
            duration: 1000
          })
        } else {
          wx.showToast({
            title: '网络错误',
            icon: 'loading',
            duration: 1000
          })
        }
      },
      fail: function() {},
      complete: function() {
        wx.hideLoading();
      }
    })
  },

  /**
   * 统计小程序点击情况
   * 这里是直接跳转到指定小程序 所以isEnter传1
   */
  miniClick: function(e) {
    util.miniClickCount(e.currentTarget.dataset.mini, e.currentTarget.dataset.appid, 1);
  },

  /**
   * 点击进入小程序详情界面
   * 这里是跳转到小程序详情页 所以isEnter传0
   */
  miniDetail: function(e) {
    // util.miniClickCount(e.currentTarget.dataset.mini, e.currentTarget.dataset.appid, 0);
    wx.navigateTo({
      url: '../minidetail/minidetail?miniId=' + e.currentTarget.dataset.mini,
    })
  },


  /**
   * 用户分享
   */
  onShareAppMessage: function() {
    var that = this;
    return {
      title: '这个小程序专题，请你一定不要错过~！',
      path: '/pages/columndetail/columndetail?columnid=' + that.data.columnInfo.idx
    }
  },

})