var app = getApp();
var util = require('../../utils/util.js');
var WxParse = require('../../utils/wxParse/wxParse.js');
Page({

  data: {
    activityInfo: [], //活动详情
    isJoin: false, //用户是否参与当前活动
    posterModalShow: false, // 是否显示poster modal
  },

  onLoad: function(options) {
    console.log(options)
    var that = this;
    if (!options.activityid && !options.scene) {
      wx.showModal({
        title: '系统提示',
        content: '界面参数缺失',
        showCancel: false,
        success: function(res) {
          wx.switchTab({
            url: '../index/index',
          })
        }
      })
      return;
    } else if (options.scene) {
      let scene = decodeURIComponent(options.scene)
      that.setData({
        activityID: scene
      })
    } else {
      that.setData({
        activityID: options.activityid
      })
    }
    var timeCount = 0;
    var intval = setInterval(res => {
      if (app.globalData.userID) {
        clearInterval(intval);
        that.getActivityInfo();
      } else {
        timeCount++;
        if (timeCount == 30) {
          clearInterval(intval);
          wx.showModal({
            title: '系统提示',
            content: '网络错误，请检查网络是否有效',
            showCancel: false
          })
        }
      }
    }, 100)
  },

  getActivityInfo: function() {
    var that = this;
    // 判断当前用户是否有认证
    if (!app.globalData.inviteCode) {
      wx.redirectTo({
        url: '/pages/invitecode/invitecode',
      })
      return;
    }
    wx.showLoading({
      title: '加载中...',
      mask: true
    })
    setTimeout(function() {
      wx.request({
        url: app.globalData.siteroot + 'fangte/getActivityById',
        method: 'POST',
        dataType: 'json',
        data: {
          userid: app.globalData.userID,
          activityID: that.data.activityID
        },
        success: function(res) {
          // code == "200" 表明当前活动正常
          if (res.data.code == "400" || res.data.code == "401" || res.data.code == "201") {
            wx.hideLoading();
            wx.showModal({
              title: '系统提示',
              content: '当前活动不存在或已过期',
              showCancel: false,
              success: function(res) {
                if (res.confirm) {
                  wx.switchTab({
                    url: '../my/my',
                  })
                }
              }
            })
          } else if (res.data.code == "200") {
            // 首先要判断当前活动是否已经结束
            var countDown = [];
            countDown.push({
              oriTime: res.data.activity.countDown - 3,
              time: util.getTimeStr(res.data.activity.countDown),
              isActive: true
            })
            that.setData({
              activityInfo: res.data.activity,
              countDown: countDown[0]
            })
            // 对活动详情进行解码
            WxParse.wxParse('content', 'html', res.data.activity.detail, that, 5);
          }
          // 判断当前活动是否需要倒计时
          that.checkActivity();
        },
        fail: function() {},
        complete: function() {
          wx.hideLoading();
          wx.stopPullDownRefresh();
          wx.hideNavigationBarLoading();
        }
      })
    }, 600)
  },

  // 判断当前活动是否需要倒计时
  checkActivity: function() {
    var that = this;
    var activity = that.data.activityInfo;
    if (activity.is_active && activity.countDown) {
      var countDownIntval = setInterval(function() {
        var countDown = that.data.countDown;
        countDown.oriTime = --countDown.oriTime;
        countDown.isActive = countDown.oriTime == 0 ? false : true;
        if (!countDown.isActive) {
          clearInterval(countDownIntval);
          var activityInfo = that.data.activityInfo;
          activityInfo.is_active = 0;
          wx.showToast({
            title: '活动已结束',
            icon: 'none'
          })
          taht.setData({
            activityInfo: activityInfo
          })
        }
        countDown.time = util.getTimeStr(countDown.oriTime);
        that.setData({
          countDown: countDown
        })
      }, 1000);
    }
  },

  /**
   * 用户报名
   */
  signUp: function(e) {
    var that = this;
    // 首先要判断用户是否实名认证
    if (app.globalData.isAuth) {
      wx.showModal({
        title: '系统提示',
        content: '您确定要报名参加当前活动吗？',
        success: function(res) {
          if (res.confirm) {
            if (!that.data.activityInfo.is_active) {
              wx.showToast({
                title: '活动已结束',
                icon: 'none',
                mask: true
              })
              return;
            }
            wx.showLoading({
              title: '请稍后...',
              mask: true
            })
            setTimeout(function() {
              wx.request({
                url: app.globalData.siteroot + 'fangte/activitySingUp',
                method: 'POST',
                dataType: 'json',
                data: {
                  activityID: that.data.activityInfo.activity_id,
                  openid: wx.getStorageSync('openid'),
                  userid: app.globalData.userID,
                  name: app.globalData.userInfo.nickName,
                  pic: app.globalData.userInfo.avatarUrl
                },
                success: function(res) {
                  if (res.data.code == "200") {
                    wx.showToast({
                      title: '参与成功'
                    })
                    var activityInfo = that.data.activityInfo;
                    activityInfo.isJoin = true;
                    that.setData({
                      activityInfo: activityInfo
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
            }, 600)
          }
        }
      })
    } else {
      wx.showModal({
        title: '系统提示',
        content: '您还未实名认证，不能参加当前活动，请点击确认以完成实名认证',
        success: function(res) {
          if (res.confirm) {
            wx.navigateTo({
              url: '../userinfo/userinfo',
            })
          } else if (res.cancel) {
            wx.showToast({
              title: '认证取消',
              icon: 'loading'
            })
          }
        }
      })
    }
  },

  /**
   * 展示poster的modal
   */
  showPosterModal: function() {
    var that = this;
    // 如果有poster 就展示
    if (that.data.activityInfo.activity_poster) {
      this.setData({
        posterModalShow: true
      })
    } else {
      wx.showModal({
        title: '系统提示',
        content: '请及时联系管理员生成活动海报',
        showCancel: false
      })
    }
  },

  /**
   * 遮罩层放置手指乱移动
   */
  preventTouchMove: function() {},

  /**
   * 保存海报到手机
   */
  savePoster: function() {
    var that = this;
    wx.downloadFile({
      url: that.data.activityInfo.activity_poster,
      success: function(res) {
        wx.saveImageToPhotosAlbum({
          filePath: res.tempFilePath,
          success: function() {
            wx.showToast({
              title: '保存成功',
            })
          },
          fail: function() {
            wx.showModal({
              title: '系统提示',
              content: '授权失败，无法保存',
              showCancel: false
            })
          },
          complete: function() {
            that.setData({
              posterModalShow: false
            })
          }
        })
      },
      fail: function() {
        wx.showModal({
          title: '系统提示',
          content: '网络错误，请稍后重试',
          showCancel: false
        })
        that.setData({
          posterModalShow: false
        })
      }
    })
  },

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function() {
    var that = this;
    return {
      title: that.data.activityInfo.name || '我发现了一个好玩的活动，快来看看吧~',
      path: '/pages/activitydetail/activitydetail?activityid=' + that.data.activityID
    }
  }

})