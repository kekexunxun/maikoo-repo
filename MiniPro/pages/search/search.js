var app = getApp();
var util = require('../../utils/util.js');
Page({

  data: {
    searchList: [], // 搜索默认展示小程序列表
    searchResult: [], // 搜索的小程序结果列表
    inputShowed: false,
    inputVal: "",
    showSearchResult: false, // 是否展示搜索结果
    isHaveResult: false, // 搜索是否有结果
  },

  onLoad: function(options) {
    this.getSearchList();
  },

  /**
   * 获取搜索列表/直接展示
   */
  getSearchList: function() {
    var that = this
    wx.request({
      url: app.globalData.siteroot + 'search/getList',
      method: 'GET',
      dataType: 'json',
      success: function(res) {
        if (res.statusCode == 200 && res.data.code == 0) {
          if (res.data.data) {
            that.setData({
              searchList: res.data.data
            })
          }
        } else {
          wx.showToast({
            title: '网络错误',
            icon: 'loading'
          })
        }
      }
    })
  },

  showInput: function() {
    this.setData({
      inputShowed: true
    });
  },

  clearInput: function() {
    this.setData({
      inputVal: "",
      inputShowed: false,
      showSearchResult: false
    });
  },

  inputTyping: function(e) {
    this.setData({
      inputVal: e.detail.value
    });
  },

  search: function() {
    var that = this;
    var value = that.data.inputVal;
    // 向后台发送查询请求
    if (value != null || value != "") {
      that.sendSearchRequest(value);
      // 记录用户搜索的关键词
      var searchLog = wx.getStorageSync('searchLog') || [];
      searchLog.push({
        value: value,
        time: Date.now() / 1000
      })
      wx.setStorageSync('searchLog', searchLog);
    } else {
      wx.showToast({
        title: '搜索字段为空',
        icon: 'none'
      })
    }
  },

  /**
   * 向后台发送查询小程序的关键词
   */
  sendSearchRequest: function(value) {
    var that = this;
    wx.request({
      url: app.globalData.siteroot + 'search/getSearchReasult',
      method: 'POST',
      dataType: 'json',
      data: {
        value: value
      },
      success: function(res) {
        if (res.statusCode == 200 && res.data.code == 0) {
          that.setData({
            isHaveResult: res.data.data ? true : false,
            showSearchResult: true,
            searchResult: res.data.data || []
          })
        } else {
          that.setData({
            showSearchResult: false
          })
          wx.showToast({
            title: '网络错误',
            icon: 'loading'
          })
        }
      }
    })
  },

  /**
   * 跳转到小程序详情页
   * 为搜索时，展示的搜索列表点击也是跳转到小程序详情页 is_enter 0
   */
  navToMiniDetail: function(e) {
    var that = this;
    var idx = e.currentTarget.dataset.idx,
      w = e.currentTarget.dataset.w;
    var dataList = w == 0 ? that.data.searchList : that.data.searchResult;
    // 点击统计
    var miniId = dataList[idx].mini_id,
      appid = dataList[idx].appid;
    // util.miniClickCount(miniId, appid, 0);
    wx.navigateTo({
      url: '../minidetail/minidetail?miniId=' + miniId,
    })
  },

  /**
   * 统计小程序点击情况
   * 这里是搜索结果直接跳转 is_enter 1
   */
  miniClick: function(e) {
    var that = this;
    var idx = e.currentTarget.dataset.idx;
    var miniId = that.data.searchResult[idx].mini_id,
      appid = that.data.searchResult[idx].appid;
    util.miniClickCount(miniId, appid, 1);
  },

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function() {

  }
})