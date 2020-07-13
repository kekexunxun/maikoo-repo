const app = getApp();
const util = require('../../utils/util.js');
const tool = require('../../utils/tool.js');

Page({

  data: {
    pageNum: 1,
    hasGroup: false,
    toastHidden: true,
    searchResult: [], // 搜索的结果
    groupList: [], // 群列表
    groupListTemp: [], // 群列表缓存
    gTypeArr: ["COUNTRY", "SCHOOL", "CLASS", "COMMUNITY"], // 群组类别
  },

  onLoad: function() {
    this.getMyGroup();
    this.setData({
      siteroot: app.globalData.siteroot
    })
  },

  /**
   * 获取我的所有名录群
   */
  getMyGroup: function() {
    var that = this;
    util.post('/api/group', {
      pageNum: that.data.pageNum
    }, that, 200).then(res => {
      if (res && res.data.length > 0) {
        that.setData({
          groupList: that.data.groupList.concat(res.data || []),
          pageNum: that.data.pageNum + 1,
          hasGroup: true
        })
      }
    }).catch(error => {
      that.setData({
        toastHidden: false,
        toastTitle: "群组获取失败",
        toastAction: "",
        toastCancel: 0
      })
    })
  },

  /**
   * 键盘点击完成
   * 搜索指定的群
   */
  searchGroup: function(evt) {
    var that = this;
    if (evt.detail.value == "") return;
    let value = tool.stringTrim(evt.detail.value);
    util.post('/api/group/search', {
      search: value
    }, that, 300).then(res => {
      that.setData({
        groupListTemp: that.data.groupList,
        groupList: res.data || []
      })
    }).catch(error => {
      that.setData({
        toastHidden: false,
        toastTitle: "搜索群成员失败",
        toastAction: "",
        toastCancel: 0
      })
    })
  },

  /**
   * 搜索框输入事件
   */
  onInputSearch: function(evt) {
    if (evt.detail.value.length == 0 && this.data.groupListTemp.length > 0) {
      this.setData({
        groupList: this.data.groupListTemp,
        groupListTemp: []
      })
    }
  },

  /**
   * 创建群组
   */
  createGroup: function(evt) {
    tool.storeFormId(evt.detail.formId);
    this.setData({
      toastHidden: false,
      toastTitle: "确认要创建群组吗？",
      toastAction: "create",
      toastCancel: 1
    })
  },

  /**
   * Toast 点击
   */
  toastConfirm: function(evt) {
    if (evt.detail.cancel) return;
    if (this.data.toastAction == "create") {
      wx.navigateTo({
        url: '/pages/gcreate/gcreate?gIdx=0'
      })
    }
  },

  /**
   * 跳转到群详情
   */
  navToGroupDetail: function(evt) {
    wx.navigateTo({
      url: '/pages/gdetail/gdetail?gid=' + evt.currentTarget.dataset.gid + '&gtype=' + this.data.gTypeArr.indexOf(evt.currentTarget.dataset.gtype)
    })
  },

  /**
   * 用户下拉刷新
   */
  onPullDownRefresh: function() {
    this.setData({
      pageNum: 1,
      groupList: []
    })
    this.getMyGroup();
  },

  /**
   * 用户上拉加载
   */
  onReachBottom: function() {
    this.getMyGroup();
  }

})