const app = getApp();
Page({
  data: {
    userInfo: [], //用户信息
    menuList: [{
      icon: '../../images/my-fav.png',
      text: '我的收藏',
      tap: 'myFavourite',
      isadmin: false
    }, {
      icon: '../../images/setting.png',
      text: '个人信息',
      tap: 'myInfo',
      isadmin: false
    }],
  },

  onLoad: function(options) {
    // 判断用户是否初次登陆 这里的登陆实际是获取用户信息
    // 判断用户是否有授权
  },

  /**
   * 在这个钩子判断用户是否授权
   */
  onShow: function(){
    this.setData({
      isAuth: app.globalData.isAuth,
      userInfo: app.globalData.userInfo
    })
  },

  navToAuth: function(){
    wx.showModal({
      title: '系统提示',
      content: '完成授权后方可正常使用',
      success: function (res) {
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
        }
      }
    })
  },

  myInfo: function(res){
    wx.showToast({
      title: '敬请期待',
      icon: 'loading',
      duration: 1200
    })
  },

  /**
   * 查看我的推介
   */
  myFavourite: function() {
    var that = this;
    if (!that.data.isAuth){
      that.navToAuth();
    }else{
      wx.navigateTo({
        url: '../favourite/favourite',
      })
    }
  },

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function() {
    return {
      title: '这里有很多好玩的~快来看看吧~',
      path: '/pages/index/index'
    }
  },

})