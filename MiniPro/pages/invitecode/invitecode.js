var app = getApp();
Page({

  data: {
    code: "",
    disable: false
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {

  },

  inputCode: function(e){
    this.setData({
      code: e.detail.value
    })
  },

  confirm: function(e){
    var that = this;
    // 将确认按钮设置为不可点击
    that.setData({
      disable: true
    })
    var formId = e.detail.formId;
    if(that.data.code == "" || !that.data.code){
      wx.showToast({
        title: '邀请码不能为空',
        icon: 'loading',
        duration: 1500
      })
      setTimeout(function () {
        that.setData({
          disable: false
        })
      }, 1000)
      return;
    }
    wx.showLoading({
      title: '系统验证中',
      mask: 'true'
    })
    wx.request({
      url: app.globalData.siteroot + 'mini/checkInviteCode',
      method: 'POST',
      dataType: 'json',
      data: { openid: wx.getStorageSync('openid'), code: that.data.code },
      success: function(res){
        wx.hideLoading();
        if(res.data.code == "200"){
          // 将该Code写入全局变量
          app.globalData.inviteCode = that.data.code;
          wx.showToast({
            title: '验证成功',
            duration: 1200
          })
          // 再次获取用户信息
          setTimeout(function () {
            wx.showModal({
              title: '系统提示',
              content: '部分系统功能已被禁用，请完善个人信息后重试',
              showCancel: false,
              success: function (res) {
                if (res.confirm) {
                  wx.switchTab({
                    url: '/pages/index/index'
                  })
                }
              }
            })
          }, 1000)
        }else if(res.data.code == "201"){
          wx.showToast({
            title: '邀请码已失效',
            icon: 'loading',
            duration: 1200
          })
          that.setData({
            disable: false
          })
        }else{
          wx.showToast({
            title: '邀请码错误',
            icon: 'loading',
            duration: 1200
          })
          that.setData({
            disable: false
          })
        }
      },
      fail: function(){},
      complete: function(){wx.hideLoading()}
    })
  }

})