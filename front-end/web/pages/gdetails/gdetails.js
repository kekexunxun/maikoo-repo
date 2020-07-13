const app = getApp();
const util = require('../../utils/util.js');
const tool = require('../../utils/tool.js');

Page({

  data: {
    gTypeArr: ["COUNTRY", "SCHOOL", "CLASS", "COMMUNITY"], // 群组类别
    toastHidden: true
  },

  onLoad: function(options) {
    if (!options.gtype && !options.gid) {
      that.setData({
        toastHidden: false,
        toastTitle: "参数缺失",
        action: "navback",
        toastCancel: 0
      })
      return;
    }
    this.setData({
      gtype: options.gtype,
      gid: options.gid,
      siteroot: app.globalData.siteroot
    })
    this.getGroupDetail();
  },

  /**
   * 获取群详情
   */
  getGroupDetail: function() {
    var that = this;
    util.post('/api/group/information', {
      groupType: that.data.gTypeArr[that.data.gtype],
      groupId: that.data.gid
    }, that, 300).then(res => {
      that.setData({
        groupInfo: res.data || {}
      })
      return util.post('/api/group/user', {
        groupType: that.data.gTypeArr[that.data.gtype],
        groupId: that.data.gid,
        hasDetail: false,
        pageNum: 1
      }, that, 300);
    }).catch(error => {
      that.setData({
        toastHidden: false,
        toastTitle: "群组信息获取失败",
        toastAction: "",
        toastCancel: 0
      })
    }).then(res => {
      var hasMore = false;
      if (res.data.length >= 9) {
        res.data.splice(9, 1);
        hasMore = true;
      }
      that.setData({
        groupMem: res.data || [],
        hasMore: hasMore
      })
    }).catch(error => {
      that.setData({
        toastHidden: false,
        toastTitle: "群组成员获取失败",
        toastAction: "",
        toastCancel: 0
      })
    })
  },

  /**
   * modal 点击
   */
  toastConfirm: function(evt) {
    if (evt.detail.cancel) return;
    var that = this;
    if (that.data.toastAction == "navback") {
      wx.navigateBack({
        delta: 1
      })
    } else if (that.data.toastAction == "join") {
      var extData = "";
      if (that.data.gtype == 1) {
        extData = "&schname=" + that.data.groupInfo.group_school_name;
      } else if (that.data.gtype == 2) {
        extData = "&schname=" + that.data.groupInfo.group_school_name + '&clsname=' + that.data.groupInfo.group_class_name;
      } else if (that.data.gtype == 3) {
        extData = "&comname=" + that.data.groupInfo.group_community_name;
      }
      wx.navigateTo({
        url: '/pages/ginfofirst/ginfofirst?gid=' + this.data.gid + '&gtype=' + this.data.gtype + '&action=apply&gname=' + this.data.groupInfo.group_name + extData
      })
    } else if (that.data.toastAction == "auth") {
      wx.navigateTo({
        url: '/pages/auth/auth'
      })
      // 认证成功之后 跳转到首页 首页会自动跳转到这个详情界面
      app.globalData.share = that.data.gtype + '=' + that.data.gid;
    }
  },

  /**
   * 群组分享
   */
  joinGroup: function(evt) {
    tool.storeFormId(evt.detail.formId);
    if (!app.globalData.isAuth) {
      this.setData({
        toastHidden: false,
        toastTitle: "请登录后再进行后续操作",
        toastAction: "auth",
        toastCancel: 1
      })
    } else {
      this.setData({
        toastHidden: false,
        toastTitle: "确认要加入群组吗？",
        toastAction: "join",
        toastCancel: 1
      })
    }
  }

})