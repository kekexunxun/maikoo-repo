const app = getApp();
const util = require('../../utils/util.js');
const tool = require('../../utils/tool.js');

Page({

  data: {
    modalHeight: "", // 设置遮罩层的高度
    formHeight: "", // 设置form的高度
    gTypeArr: ["COUNTRY", "SCHOOL", "CLASS", "COMMUNITY"], // 群组类别
    toastHidden: true,
    hasApplied: false, // 是否有该群正在处理中的申请
  },

  /**
   * 隐藏右上角的转发
   */
  onReady: function() {
    wx.hideShareMenu({
      success: function(res) {
        console.log(res)
      }
    })
  },

  /**
   * 获取传递过来的群名称和群ID
   */
  onLoad: function(options) {
    if (options.scene) {
      var scene = decodeURIComponent(options.scene);
      scene = scene.split('&');
      var gid = scene[1].split('=')[1];
      var gtype = scene[0].split('=')[1];
      var action = scene[2].split('=')[1];
    }
    // 设置form高度
    let sysInfo = wx.getSystemInfoSync();
    let modalHeight = sysInfo.windowHeight - 100 * (sysInfo.screenWidth / 750);
    let formHeight = sysInfo.windowHeight - 140 * (sysInfo.screenWidth / 750);
    this.setData({
      modalHeight: modalHeight,
      formHeight: formHeight,
      gid: options.gid || gid,
      gtype: options.gtype || gtype,
      action: options.action || action, // share 分享操作 poster 获取海报 into 分享进入
      siteroot: app.globalData.siteroot
    })
    this.getGroupInfo();
  },

  /**
   * 获取群组信息
   */
  getGroupInfo: function() {
    var that = this;
    util.post('/api/group/information', {
      groupType: that.data.gTypeArr[that.data.gtype],
      groupId: that.data.gid
    }, that, 300).then(res => {
      that.setData({
        groupInfo: res.data || {}
      })
      if (that.data.action == "poster") {
        return util.post('/api/group/poster', {
          groupType: that.data.gTypeArr[that.data.gtype],
          groupId: that.data.gid
        }, that, 300);
      } else if (that.data.action == "into") {
        // 判断当前用户是否为该群的成员
        return util.post('/api/group/user/member', {
          groupType: that.data.gTypeArr[that.data.gtype],
          groupId: that.data.gid
        }, that, 300);
      }
    }).catch(error => {
      that.setData({
        toastHidden: false,
        toastTitle: "群信息获取失败",
        toastAction: ""
      })
    }).then(res => {
      if (that.data.action == "poster" && res) {
        that.setData({
          poster: res.data.poster_url,
          qrcode: res.data.qrcode_url
        })
      } else if (that.data.action == "into" && res) {
        that.setData({
          isMem: res.data.is_member
        })
        if (!res.data.is_member) {
          return util.post('/api/group/user/confirm', {
            groupType: that.data.gTypeArr[that.data.gtype],
            groupId: that.data.gid
          }, that, 100, false);
        }
      }
    }).catch(error => {
      console.log(error);
      that.setData({
        toastHidden: false,
        toastTitle: "群海报获取失败",
        toastAction: ""
      })
    }).then(res => {
      if (!that.data.isMem && res) {
        that.setData({
          hasApplied: res.data.has_applied,
          applyId: res.data.apply_id
        })
      }
    }).catch(error => {
      that.setData({
        toastHidden: false,
        toastTitle: "申请记录获取失败",
        toastAction: ""
      })
    })
  },

  /**
   * 取消分享 返回上一页
   */
  cancel: function(evt) {
    tool.storeFormId(evt.detail.formId);
    wx.navigateBack({
      delta: 1
    })
  },

  /**
   * 将图片保存至手机
   */
  savePhoto: function(evt) {
    var that = this;
    if (evt) {
      tool.storeFormId(evt.detail.formId);
    }
    wx.showLoading({
      title: '保存中',
      mask: true
    })
    // 先下载
    wx.downloadFile({
      url: app.globalData.siteroot + that.data.poster,
      success: function(res) {
        wx.hideLoading();
        wx.saveImageToPhotosAlbum({
          filePath: res.tempFilePath,
          success: function(res) {
            that.setData({
              toastHidden: false,
              toastTitle: "保存成功",
              toastAction: ""
            })
          },
          fail: function(error) {
            console.log(error);
            // 授权失败或其它原因
            let funAuth = tool.checkAuth('writePhotosAlbum');
            if (!funAuth) {
              that.setData({
                toastHidden: false,
                toastTitle: "需要先授权才可保存到相册",
                toastAction: "photo"
              })
            } else {
              that.setData({
                toastHidden: false,
                toastTitle: "保存失败",
                toastAction: ""
              })
            }
          }
        })
      },
      fail: function(error) {
        wx.hideLoading();
        that.setData({
          toastHidden: false,
          toastTitle: "下载失败，请检查网络后重试",
          toastAction: ""
        })
      }
    })
  },

  /**
   * 用户分享
   */
  onShareAppMessage: function() {
    return {
      title: app.globalData.shareText || (app.globalData.nickname + '邀请您加入群「' + this.data.groupInfo.group_name) + '」',
      path: '/pages/share/share?action=into&gid=' + this.data.gid + '&gtype=' + this.data.gtype
    }
  },

  /**
   * 用户进入分享界面，点击进入首页
   */
  navToIndex: function(evt) {
    tool.storeFormId(evt.detail.formId);
    wx.switchTab({
      url: '/pages/index/index'
    })
  },

  /**
   * 用户进入分享界面，点击查看群组详情
   */
  navToDetail: function(evt) {
    tool.storeFormId(evt.detail.formId);
    if (this.data.isMem) {
      wx.navigateTo({
        url: '/pages/gdetail/gdetail?gid=' + this.data.gid + '&gtype=' + this.data.gtype
      })
    } else if (!this.data.isMem && !this.data.hasApplied) {
      wx.navigateTo({
        url: '/pages/gdetails/gdetails?gid=' + this.data.gid + '&gtype=' + this.data.gtype
      })
    } else if (!this.data.isMem && this.data.hasApplied) {
      wx.navigateTo({
        url: '/pages/memdetail/memdetail?action=apply&gid=' + this.data.gid + '&gtype=' + this.data.gtype + '&applyid=' + this.data.applyId
      })
    }
  },

  /**
   * Toast 点击
   */
  toastConfirm: function(evt) {
    if (evt.detail.cancel) return;
    var that = this;
    if (that.data.toastAction == "navback") {
      wx.navigateBack({
        delta: 1
      })
    } else if (that.data.toastAction == "photo") {
      wx.navigateTo({
        url: '/pages/setting/setting?authfunc=' + 'writePhotosAlbum'
      })
    }
  }

})