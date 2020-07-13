var app = getApp();

Page({

  data: {
    showSendCode: true,       //是否展示发送验证码按钮
    isCanClick: true,
    userName: '',     
    identID: '',       //为userName和identID赋初值 避免图片展示规则失效
    telNum: '',             //用户手机号
    isLogin: false,
    isValidate: false
  },

  onLoad: function (options) {
    // 如果用户是认证用户 那么此界面为信息修改界面
    var that = this;
    var isAuth = app.globalData.isAuth;
    if(isAuth){
      that.setData({
        isAuth: isAuth,
        userAuthName: app.globalData.userName,
        userAuthIdentID: app.globalData.identID,
        userAuthTelNum: app.globaData.telNum,
        telNum: app.globalData.telNum || "",
        identID: app.globalData.identID || "",
        userName: app.globalData.userName || ""
      })
      // 动态设置NavigationBarTitle
      wx.setNavigationBarTitle({
        title: '信息修改',
      })
    }
    // 判断用户是否用微信登陆
    that.setData({
      isLogin: app.globalData.userInfo ? true : false
    })

  },

  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function () {
    var that = this;
    var count = app.globalData.telCodeTimeCount;
    if (count){
      that.setData({
        refreshCodeTime: count - 1
      })
      that.getTelCode();
    }
  },

  /**
   * 输入用户身份证号
   */
  inputIdentID: function(e){
    this.setData({
      identID: e.detail.value
    })
  },
  /**
   * 输入手机号
   */
  inputTel: function(e){
    this.setData({
      telNum: e.detail.value
    })
  },
  /**
   * 输入姓名
   */
  inputName: function(e){
    this.setData({
      userName: e.detail.value
    })
  },
  /**
   * 输入验证码
   */
  inputValidateCode: function(e){
    var that = this;
    var validateCode = e.detail.value;
    if (validateCode.length == 6){
      if (validateCode == that.data.validateCode){
        wx.showToast({
          title: '验证成功',
        })
        that.setData({
          isValidate: true
        })
      }else{
        wx.showToast({
          title: '验证码错误',
          icon: 'none'
        })
      }
    }
  },

  /**
   * 获取手机验证码
   */
  getTelCode: function (){
    var that = this;
    // 如果已经认证 但是没有更换手机号码也是不能获取验证码的
    if (that.data.isAuth){
      if (that.data.userAuthTelNum == that.data.telNum){
        wx.showToast({
          title: '手机号码未变更',
          icon: 'none'
        })
        return;
      }
    }
    if (!that.data.telNum){
      wx.showToast({
        title: '请输入手机号码',
        icon: 'none'
      })
    }else if(that.data.telNum.length != 11){
      wx.showToast({
        title: '手机号格式错误',
        icon: 'none'
      })
    }else{
      var count = that.data.refreshCodeTime || 180;
      that.setData({
        refreshCodeTime: count,
        showSendCode: false,
        isTelInputDis: true
      })
      that.refreshCode();
      // 后台请求验证码
      wx.request({
        url: app.globalData.siteroot + 'sms/sendSingleSms',
        method: 'POST',
        dataType: 'json',
        data: { telNum: that.data.telNum, openid: wx.getStorageSync('openid') },
        success: function(res){
          if(res.data.code == "200"){
            wx.showToast({
              title: '验证码发送成功',
            })
            that.setData({
              validateCode: res.data.validateCode,
              showValidateInput: true
            })
          }else{
            wx.showToast({
              title: '发送失败',
              icon: 'loading'
            })
            that.setData({
              isTelInputDis: false
            })
          }
        },
        fail: function(){
          that.setData({
            isTelInputDis: false
          })
        },
        complete: function(){}
      })
    }
  },
  // 设置手机验证码的定时器 180s后自动关闭
  refreshCode: function(){
    var that = this;
    var intval = setInterval(function(){
      var refreshCodeTime = that.data.refreshCodeTime;
      refreshCodeTime--;
      if(refreshCodeTime == 0){
        clearInterval(intval);
        that.setData({
          showSendCode: true,
          refreshCodeTime: 180
        })
      }else{
        that.setData({
          refreshCodeTime: refreshCodeTime
        })
      }
    }, 1000);
  },
  /**
   * 勾选相关条款按钮
   */
  bindAgreeChange: function(){
    var that = this;
    var isAgree = that.data.isAgree ? !that.data.isAgree : true;
    that.setData({
      isAgree: isAgree
    })
  },

  /**
   * 获取用户信息
   */
  getUserInfo: function (e) {
    var that = this;
    wx.getSetting({
      success: res => {
        if (res.authSetting['scope.userInfo']) {
          wx.showToast({
            title: '授权成功'
          })
          wx.request({
            url: app.globalData.siteroot + 'mini/setUserInfo',
            method: 'POST',
            dataType: 'json',
            data: { openid: wx.getStorageSync('openid'), userInfo: e.detail.userInfo },
            success: function (res) {
              // 系统重新调用获取用户信息接口以更新当前用户信息
              app.globalData.userInfo = e.detail.userInfo;
              app.globalData.isAuth = true;
              that.setData({
                isLogin: true
              })
            },
            fail: function () { },
            complete: function () { }
          })
        } else {
          wx.showToast({
            title: '授权失败',
            icon: 'loading'
          })
        }
      }
    })
  },

  /**
   * 用户认证的按钮
   */
  userAuthBtn: function(e){
    var that = this;
    // 如果已经认证，判断信息是否有变更
    if (that.data.isAuth){
      if (that.data.userName == that.data.userAuthName && that.data.identID == that.data.userAuthIdentID && that.data.telNum == that.data.userAuthTelNum){
        wx.showToast({
          title: '信息未变更',
          icon: 'none'
        })
        return;
      }
    }
    // 姓名是否输入
    if (!that.data.userName || that.data.userName == "userName"){
      wx.showToast({
        title: '请输入姓名',
        icon: 'none'
      })
      return;
    }
    // 身份证验证
    if (!that.data.identID || that.data.identID.length != 18 || that.data.identID == '123456789012345678'){
      wx.showToast({
        title: '身份证格式错误',
        icon: 'none'
      })
      return;
    }
    if (!that.data.isValidate) {
      wx.showToast({
        title: '请先验证手机号',
        icon: 'none'
      })
      return;
    }
    if (!that.data.isAgree) {
      wx.showToast({
        title: '请阅读并同意用户条款',
        icon: 'none'
      })
      return;
    }
    // 向后台数据请求并验证
    wx.showLoading({
      title: '验证中...',
      mask: 'true'
    })
    wx.request({
      url: app.globalData.siteroot + 'fangte/authUser',
      method: 'POST',
      dataType: 'json',
      data: {
        name: that.data.userName,
        identID: that.data.identID,
        telNum: that.data.telNum,
        openid: wx.getStorageSync('openid')
      },
      success: function(res){
        wx.hideLoading();
        if(res.data.code == "200"){
          if(that.data.isAuth){
            wx.showToast({
              title: '信息修改成功',
            })
            // 修改globalData
            app.globaData.identID = that.data.identID;
            app.globaData.telNum = that.data.telNum;
            app.globaData.userName = that.data.name;
          }else{
            wx.showToast({
              title: '信息验证成功',
            })
          }
          // 更新全局变量
          app.globalData.isAuth = true;
          // 让当前界面关闭时不保存计数值
          that.setData({
            isAuth: true
          })
          // 返回上一页
          setTimeout(function(){
            wx.navigateBack({
              delta: 1
            })
          }, 1000)
        }else{
          wx.showToast({
            title: '网络错误',
            icon: 'loading'
          })
        }
      },
      fail: function () { wx.hideLoading();},
      complete: function(){}
    })
    
  },

  /**
   * 生命周期函数--监听页面卸载
   * 当用户获取完验证码并返回时，将可获取验证码有效期保存
   * 当用户验证成功后则不必保存
   */
  onUnload: function () {
    if(!this.data.isAuth){
      app.globalData.telCodeTimeCount = this.data.refreshCodeTime
    }
  },

  
})