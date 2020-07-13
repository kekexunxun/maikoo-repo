const app = getApp();
const util = require('../../utils/util.js');
const tool = require('../../utils/tool.js');

Page({

  data: {
    gTypeArr: ["COUNTRY", "SCHOOL", "CLASS", "COMMUNITY"], // 群组类别
    shareHidden: true, // 分享按钮
    pageNum: 1, // 群成员页码
    toastHidden: true,
    groupMemTemp: []
  },

  onLoad: function(options) {
    if (!options.gtype && !options.gid) {
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
      gid: options.gid,
      siteroot: app.globalData.siteroot
    })
    this.getGroupDetail();
  },

  /**
   * 键盘点击完成
   * 搜索指定的群
   */
  searchGroupMem: function(evt) {
    var that = this;
    util.post('/api/group/user/search', {
      groupType: that.data.gTypeArr[that.data.gtype],
      groupId: that.data.gid,
      search: tool.stringTrim(evt.detail.value)
    }, that, 100).then(res => {
      if (res.data.length == 0) {
        that.setData({
          toastHidden: false,
          toastTitle: "未搜索到相关数据",
          toastAction: "",
          toastCancel: 0
        })
      } else {
        that.setData({
          groupMemTemp: that.data.groupMem,
          groupMem: res.data
        })
      }
    }).catch(error => {
      that.setData({
        toastHidden: false,
        toastTitle: "搜索失败",
        toastAction: "",
        toastCancel: 0
      })
    })
  },

  /**
   * 搜索框输入事件
   */
  onInputSearch: function(evt) {
    if (evt.detail.value.length == 0 && this.data.groupMemTemp.length > 0) {
      this.setData({
        groupMem: this.data.groupMemTemp,
        groupMemTemp: []
      })
    }
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
        hasDetail: true,
        pageNum: that.data.pageNum
      }, that, 300)
    }).catch(error => {
      that.setData({
        toastHidden: false,
        toastTitle: "群组信息获取失败",
        toastAction: "",
        toastCancel: 0
      })
    }).then(res => {
      that.setData({
        groupMem: res.data || [],
        pageNum: that.data.pageNum + 1
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
    }
  },

  /**
   * 跳转到群设置界面
   */
  navToGroupSetting: function() {
    app.globalData.groupInfo = this.data.groupInfo
    wx.navigateTo({
      url: '/pages/gsetting/gsetting?gid=' + this.data.gid + '&gtype=' + this.data.gtype
    })
  },

  /**
   * 跳转到会员信息详情
   */
  navToMemDetail: function(evt) {
    wx.navigateTo({
      url: '/pages/memdetail/memdetail?action=detail&uid=' + evt.currentTarget.dataset.uid + '&gtype=' + this.data.gtype + '&gid=' + this.data.groupInfo.group_id
    })
  },

  /**
   * 群组分享
   */
  shareGroup: function(evt) {
    tool.storeFormId(evt.detail.formId);
    this.setData({
      shareHidden: false
    })
  },

  /**
   * 分享类别选择
   */
  shareType: function(evt) {
    let action = evt.detail.idx == 1 ? 'share' : 'poster';
    wx.navigateTo({
      url: '/pages/share/share?gtype=' + this.data.gtype + '&gid=' + this.data.gid + '&action=' + action
    })
  },

  /**
   * 用户下拉刷新
   */
  onPullDownRefresh: function() {
    this.setData({
      groupMem: [],
      pageNum: 1
    })
    this.getGroupDetail();
  }

})