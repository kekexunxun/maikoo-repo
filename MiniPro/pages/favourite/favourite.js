var app = getApp();
var util = require('../../utils/util.js');
var sliderWidth = 96; // 需要设置slider的宽度，用于计算中间位置
Page({

  data: {
    tabs: ["小程序", "专题"],
    activeIndex: 0,
    sliderOffset: 0,
    sliderLeft: 0,
    miniList: [], //收藏的小程序列表
    columnList: [], // 收藏的专题列表
    miniPageNum: 0, // 当前页码
    columnPageNum: 0, // 当前页码
    isHaveMoreMini: true, // 收藏中是否有更多可展示的小程序
    isHaveMoreColumn: true, // 收藏中是否有更多可展示的专题
    isHaveColumn: true, // 是否有收藏的专题
    isHaveMini: true, // 是否有收藏的小程序
  },

  onLoad: function() {
    var that = this;
    wx.getSystemInfo({
      success: function(res) {
        that.setData({
          sliderLeft: (res.windowWidth / that.data.tabs.length - sliderWidth) / 2,
          sliderOffset: res.windowWidth / that.data.tabs.length * that.data.activeIndex
        });
      }
    });
    // 默认先获取小程序的收藏
    that.getUserFav(1, 0);
  },

  /**
   * 获取用户的收藏列表
   * 
   * favType 收藏的类别 1 小程序 2 专题
   * pageNum 所需获取的页码
   */
  getUserFav: function(favType, pageNum) {
    var that = this;
    var isHaveMore = favType == 1 ? that.data.isHaveMoreMini : that.data.isHaveMoreColumn;
    if (!isHaveMore) {
      wx.showToast({
        title: '没有更多啦',
        icon: 'loading',
        duration: 1000
      })
      wx.stopPullDownRefresh();
      return;
    }
    var dataList = [];
    // 如果有数据，那么向后台进行数据获取
    wx.showLoading({
      title: '加载中...',
      mask: true
    })
    wx.request({
      url: app.globalData.siteroot + 'user/getUserFav',
      method: 'POST',
      dataType: 'json',
      data: {
        openid: wx.getStorageSync('openid'),
        // openid: 'o3ep65c2yivD-P0TZTjClNkLiIGc',
        favType: favType,
        pageNum: pageNum
      },
      success: function(res) {
        if (res.statusCode == 200 && res.data.code == 0) {
          if (!res.data.data || res.data.data.length < 10) {
            isHaveMore = false;
          }
          if (!res.data.data && pageNum == 0) {
            if (favType == 1) {
              that.setData({
                isHaveMini: false
              })
            } else {
              that.setData({
                isHaveColumn: false
              })
            }
          }
          dataList = res.data.data;
        } else {
          isHaveMore = false;
          wx.showToast({
            title: '网络错误',
            icon: 'none'
          })
        }
      },
      fail: function() {},
      complete: function() {
        // 数据更新
        if (favType == 1) {
          that.setData({
            isHaveMoreMini: isHaveMore,
            miniList: that.data.miniList.concat(dataList),
            miniPageNum: pageNum + 1
          })
        } else if (favType == 2) {
          that.setData({
            isHaveMoreColumn: isHaveMore,
            columnList: that.data.columnList.concat(dataList),
            columnPageNum: pageNum + 1
          })
        }
        wx.hideLoading();
        wx.stopPullDownRefresh();
      }
    })
  },

  /**
   * 用户取消收藏
   */
  cancelUserFav: function(evt) {
    var that = this;
    // 这个idx是收藏表中的主键idx
    var idx = evt.currentTarget.dataset.idx;
    var favId = evt.currentTarget.dataset.favid;
    // 这个index是当前删除的程序在数组中的位置
    var index = evt.currentTarget.dataset.index;
    wx.showModal({
      title: '系统提示',
      content: '确定要取消收藏吗?',
      success: function(res) {
        if (res.confirm) {
          wx.showLoading({
            title: '取消收藏中...',
            mask: true
          })
          var favType = that.data.activeIndex == 0 ? 1 : 2;
          wx.request({
            url: app.globalData.siteroot + 'user/userCancelFav',
            method: 'POST',
            dataType: 'json',
            data: {
              openid: wx.getStorageSync('openid'),
              idx: idx,
              favId: favId,
              favType: favType
            },
            success: function(res) {
              if (res.statusCode == 200 && res.data.code == 0) {
                wx.showToast({
                  title: '取消收藏成功',
                  duration: 1000
                })
                // 更新当前数据
                var dataList = favType == 1 ? that.data.miniList : that.data.columnList;
                dataList.splice(index, 1);
                if (favType == 1) {
                  that.setData({
                    miniList: dataList
                  })
                } else if (favType == 2) {
                  that.setData({
                    columnList: dataList
                  })
                }
              }
            },
            fail: function() {},
            complete: function() {
              wx.hideLoading();
            }
          })
        }
      }
    })
  },

  // TAB点击事件
  tabClick: function(evt) {
    var that = this;
    that.setData({
      sliderOffset: evt.currentTarget.offsetLeft,
      activeIndex: evt.currentTarget.id
    });
    if (evt.currentTarget.id == 1 && that.data.columnPageNum == 0) {
      that.getUserFav(2, 0);
    }
  },

  /**
   * 跳转到小程序详情页面
   * isEnter传0
   */
  navToMiniDetail: function(evt) {
    // 这个index是当前点击的小程序在列表中的位置索引
    var that = this;
    var index = evt.currentTarget.dataset.index;
    var miniInfo = that.data.miniList[index];
    // util.miniClickCount(miniInfo.mini.mini_id, miniInfo.mini.appid, 0);
    wx.navigateTo({
      url: '../minidetail/minidetail?miniId=' + miniInfo.fav_id,
    })
  },

  /**
   * 小程序点击统计
   * 这里是直接跳转 所以isEnter传 1
   */
  miniClick: function(evt) {
    // console.log('miniClick')
    var that = this;
    // 这个index是当前点击的小程序在列表中的位置索引
    var index = evt.currentTarget.dataset.index;
    var miniInfo = that.data.miniList[index];
    util.miniClickCount(miniInfo.mini.mini_id, miniInfo.mini.appid, 1);
  },

  /**
   * 页面相关事件处理函数--监听用户下拉动作
   */
  onPullDownRefresh: function() {
    var that = this;
    var favType = that.data.activeIndex == 0 ? 1 : 2;
    that.getUserFav(favType, 0);
  },

  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom: function() {
    var that = this;
    var favType = that.data.activeIndex == 0 ? 1 : 2;
    var pageNum = that.data.activeIndex == 0 ? that.data.miniPageNum : that.data.columnPageNum;
    that.getUserFav(favType, pageNum);
  },

  /**
   * 用户分享
   */
  onShareAppMessage: function () {
    return {
      title: '这里有很多好玩的~快来看看吧~',
      path: '/pages/index/index'
    }
  },

})