const app = getApp();
const util = require('../../utils/util.js');
const tool = require('../../utils/tool.js');

Page({

  data: {
    gTypeArr: ["COUNTRY", "SCHOOL", "CLASS", "COMMUNITY"], // 群类别
    dismissHidden: true,
    toastHidden: true
  },

  onLoad: function(options) {
    if (!options.gid && !options.gtype) {
      this.setData({
        toastHidden: false,
        toastTitle: "参数缺失",
        toastAction: "navback",
        toastCancel: 0
      })
      return;
    }
    this.setData({
      gtype: options.gtype,
      gid: options.gid
    })
  },

  /**
   * 更新群组权限
   */
  updateAuth: function(evt) {
    tool.storeFormId(evt.detail.formId);
    wx.navigateTo({
      url: '/pages/gauth/gauth?gid=' + this.data.gid + '&gtype=' + this.data.gtype
    })
  },

  /**
   * 修改群组信息
   */
  updateGroupInfo: function(evt) {
    tool.storeFormId(evt.detail.formId);
    wx.navigateTo({
      url: '/pages/gupdate/gupdate?gid=' + this.data.gid + '&gtype=' + this.data.gtype
    })
  },

  /**
   * 解散群组
   */
  dismissGroup: function(evt) {
    tool.storeFormId(evt.detail.formId);
    this.setData({
      toastHidden: false,
      toastTitle: "确定要解散群组吗？",
      toastAction: "dismiss",
      toastCancel: 1
    })
  },

  /**
   * 解散群组Modal确认按钮
   */
  toastConfirm: function(evt) {
    var that = this;
    if (evt.detail.cancel) return;
    if (that.data.toastAction == "dismiss") {
      util.post('/api/group/dismiss', {
        groupType: that.data.gTypeArr[that.data.gtype],
        groupId: that.data.gid
      }, that, 300).then(res => {
        that.setData({
          toastHidden: false,
          toastTitle: "群组解散成功",
          toastAction: "navgroup",
          toastCancel: 0
        })
      }).catch(error => {
        that.setData({
          toastHidden: false,
          toastTitle: "群组解散失败",
          toastAction: "",
          toastCancel: 0
        })
      })
    } else if (that.data.toastAction == "navback") {
      wx.navigateBack({
        delta: 1
      })
    } else if (that.data.toastAction == "navgroup") {
      wx.reLaunch({
        url: '/pages/group/group'
      })
    }
  }

})