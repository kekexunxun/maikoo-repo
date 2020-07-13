const app = getApp()
var util = require('../../utils/util.js');
Page({

  /**
   * 页面的初始数据
   */
  data: {
    miniInfo: [], // 当前小程序详情
    rateStar: [0, 0, 0, 0, 0], // 对当前小程序的评分的平均值
    userRate: 0, // 用户的评分
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function(options) {
    var that = this;
    var miniId = options.miniId;
    if (!miniId) {
      wx.showToast({
        title: '参数错误',
        icon: 'loaidng',
        duration: 1000
      });
      setTimeout(function() {
        wx.redirectTo({
          url: '../index/index',
        })
      }, 1000);
      return;
    }

    that.getMiniDetail(miniId);
  },

  /**
   * 用户授权判断
   */
  onShow: function() {
    // 判断用户是否有授权
    if (!app.globalData.isAuth) {
      wx.showModal({
        title: '系统提示',
        content: '完成授权后方可正常使用',
        success: function(res) {
          if (res.confirm) {
            wx.navigateTo({
              url: '../userauth/userauth',
            })
          } else if (res.cancel) {
            wx.showToast({
              title: '授权取消',
              icon: 'none',
              duration: 1000
            })
            setTimeout(function() {
              wx.navigateBack({
                delta: 1
              })
            }, 1000)
          }
        }
      })
    }
  },


  /**
   * 获取指定小程序的详细信息
   */
  getMiniDetail: function(miniId) {
    var that = this;
    if (!miniId) {
      return;
    }
    wx.request({
      url: app.globalData.siteroot + 'mini/getMini',
      method: 'POST',
      dataType: 'json',
      data: {
        miniId: miniId,
        openid: wx.getStorageSync('openid')
      },
      success: function(res) {
        if (res.statusCode == 200 && res.data.code == 0) {
          // 设置当前界面title
          wx.setNavigationBarTitle({
            title: res.data.data.name + ' - ' + '应用详情',
          })
          // 用户点击记录
          util.miniClickCount(miniId, res.data.data.appid, 0);
          that.setData({
            miniInfo: res.data.data,
            miniId: miniId,
          })
        } else {
          wx.showToast({
            title: '网络错误',
            icon: 'loading'
          })
          setTimeout(function() {
            wx.navigateBack({
              delta: 1
            })
          }, 1000)
        }
      },
      fail: function() {},
      complete: function() {}
    })
  },

  /**
   * 点击查看大图
   */
  showPreviewImage: function(e) {
    var that = this;
    wx.previewImage({
      urls: that.data.miniInfo.pics,
      current: that.data.miniInfo.pics[e.currentTarget.dataset.idx]
    })
  },

  /**
   * 将制定小程序添加到用户的收藏列表
   */
  addUserFav: function(e) {
    var that = this;
    var miniInfo = that.data.miniInfo;
    // 判断是否新增还是取消
    var url = miniInfo.isFav ? 'user/userCancelFav' : 'user/userAddFav';
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
        favId: that.data.miniId,
        appid: miniInfo.appid,
        favType: 1,
        idx: miniInfo.isFav ? miniInfo.favIdx : ''
      },
      success: function(res) {
        wx.hideLoading();
        if (res.statusCode == 200 && res.data.code == 0) {
          // 更新界面数据
          miniInfo.isFav = !miniInfo.isFav;
          miniInfo.favIdx = res.data.data ? res.data.data : miniInfo.favIdx;
          that.setData({
            miniInfo: miniInfo
          })
          var toastTitle = "";
          if (miniInfo.isFav) {
            toastTitle = "收藏成功";
          } else {
            toastTitle = "取消收藏成功";
          }
          wx.showToast({
            title: toastTitle,
          })
        } else {
          wx.showToast({
            title: '网络错误',
            icon: 'loading'
          })
        }
      },
      fail: function() {
        wx.hideLoading();
      },
      complete: function() {}
    })
  },

  /**
   * 统计小程序点击情况
   * 这里是直接跳转 isEnter 1
   */
  miniClick: function(e) {
    var that = this;
    util.miniClickCount(that.data.miniId, that.data.miniInfo.appid, 1);
  },

  /**
   * 用户分享
   */
  onShareAppMessage: function() {
    var that = this;
    var name = that.data.miniInfo.name;
    return {
      title: "“" + name + '” 在这里等你，赶快来看看吧~',
      path: '/pages/minidetail/minidetail?miniId=' + that.data.miniId
    }
  },

  /**
   * 用户对当前小程序进行评分操作
   */
  userRate: function(evt) {
    var that = this;
    var rateStar = [0, 0, 0, 0, 0];
    var index = evt.currentTarget.dataset.idx;
    for (var i = 0; i < index + 1; i++) {
      rateStar[i] = 1;
    }
    that.setData({
      rateStar: rateStar,
      userRate: index + 1
    })
  },

  /**
   * 用户进行评价提交
   */
  submitRate: function() {
    var that = this;
    // 判断是否能够提交
    if (that.data.userRate == 0) {
      wx.showToast({
        title: '请先评分',
        icon: 'loading'
      })
      return;
    }
    wx.showModal({
      title: '系统提示',
      content: '您确定要提交评分吗?',
      success: function(res) {
        if (res.confirm) {
          wx.showLoading({
            title: '提交中...',
            mask: 'true'
          })
          wx.request({
            url: app.globalData.siteroot + 'user/submitRate',
            method: 'POST',
            dataType: 'json',
            data: {
              miniId: that.data.miniId,
              appid: that.data.miniInfo.appid,
              openid: wx.getStorageSync('openid'),
              rate: that.data.userRate
            },
            success: function(res) {
              if (res.statusCode == 200 && res.data.code == 0) {
                wx.showToast({
                  title: '提交成功',
                })
                // 修改当前数据
                var miniInfo = that.data.miniInfo;
                miniInfo.user_rate = that.data.userRate;
                that.setData({
                  miniInfo: miniInfo
                })
              } else {
                wx.showToast({
                  title: '网络错误',
                  icon: 'loading'
                })
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
  }
})