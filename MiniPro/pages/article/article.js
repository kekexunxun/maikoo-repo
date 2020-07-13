var app = getApp();
var util = require('../../utils/util.js');
Page({

  data: {
    tabs: ["评测", "资讯"],
    subTabs: [],
    activeIndex: 0,
    sliderOffset: 0,
    sliderLeft: 0,
    subActiveIndex: 0, // 次级激活列表
    subSliderOffset: 0,
    subSliderLeft: 0,
    evalList: [], // 评测文章列表
    evalPageNum: 0, // 评测文章的页码
  },

  onLoad: function(options) {
    var that = this;
    var tabs = that.data.tabs;
    var sliderWidth = 96;
    wx.getSystemInfo({
      success: function(res) {
        that.setData({
          sliderLeft: (res.windowWidth / tabs.length - sliderWidth) / 2,
          sliderOffset: res.windowWidth / tabs.length * that.data.activeIndex,
        });
      }
    });
    // 默认获取评测列表数据
    that.getArticle(0, that.data.evalPageNum);
  },

  /**
   * 资讯子集分类的点击
   */
  subTabClick: function(evt) {
    var that = this;
    that.setData({
      subSliderOffset: evt.currentTarget.offsetLeft,
      subActiveIndex: evt.currentTarget.id
    })
    var subTabs = that.data.subTabs;
    var title = '资讯 - ' + subTabs[evt.currentTarget.id].name;
    wx.setNavigationBarTitle({
      title: title,
    })
    // 判断当前的list是否为空 如果为空就去后台请求
    if (!subTabs[evt.currentTarget.id].list) {
      that.getArticle(1, 0, subTabs[evt.currentTarget.id].cat_id);
    }
  },

  // TAB点击事件
  tabClick: function(evt) {
    var that = this;
    that.setData({
      sliderOffset: evt.currentTarget.offsetLeft,
      activeIndex: evt.currentTarget.id
    });
    // 动态设置title
    var title = evt.currentTarget.id == 0 ? '评测' : '资讯';
    wx.setNavigationBarTitle({
      title: title,
    })
    var subTabs = that.data.subTabs;
    // 如果activeIndex == 1 就要去请求后台
    if (evt.currentTarget.id == 1 && subTabs.length == 0) {
      wx.request({
        url: app.globalData.siteroot + 'Article/getArticleInfo',
        method: 'GET',
        dataType: 'json',
        success: function(res) {
          if (res.statusCode == 200 && res.data.code == 0) {
            subTabs = res.data.data;
            var subSliderWidth = 40;
            var subActiveIndex = 0;
            wx.getSystemInfo({
              success: function(res) {
                that.setData({
                  subSliderLeft: subTabs.length <= 5 ? (res.windowWidth / subTabs.length - subSliderWidth) / 2 : 30,
                  subSliderOffset: res.windowWidth / subTabs.length * subActiveIndex,
                  itemWidth: subTabs.length <= 5 ? '80rpx' : '160rpx'
                });
              }
            });
            that.setData({
              subTabs: subTabs
            })
          } else {
            wx.showToast({
              title: '没有更多啦',
              icon: 'loading',
              mask: true,
              duration: 1000
            })
          }
        }
      })

    }
  },

  /**
   * 获取用户资讯分类列表
   * 
   * articleType 0 评测 1 资讯
   * pageNum 页码 每页默认十个
   * subId 当articleType为 2 时，需传递subId
   */
  getArticle: function(articleType, pageNum, subId = null) {
    var that = this;
    wx.request({
      url: app.globalData.siteroot + 'article/getArticle',
      method: 'POST',
      dataType: 'json',
      data: {
        openid: wx.getStorageSync('openid'),
        articleType: articleType,
        pageNum: pageNum,
        subId: subId
      },
      success: function(res) {
        if (res.statusCode == 200 && res.data.code == 0) {
          if (articleType == 0) {
            var isHaveMoreEval = that.data.isHaveMoreEval;
            if (res.data.data) {
              that.setData({
                evalList: that.data.evalList.concat(res.data.data),
                evalPageNum: that.data.evalPageNum + 1
              })
            } else {
              wx.showToast({
                title: '没有更多啦',
                icon: 'loading'
              })
            }
          } else if (articleType == 1) {
            // 将数据绑定到指定的subTabs上面
            if (res.data.data) {
              var subTabs = that.data.subTabs;
              if (!subTabs[that.data.subActiveIndex].list){
                subTabs[that.data.subActiveIndex].list = [];
              }
              subTabs[that.data.subActiveIndex].list = subTabs[that.data.subActiveIndex].list.concat(res.data.data);
              subTabs[that.data.subActiveIndex].pageNum = subTabs[that.data.subActiveIndex].pageNum + 1;
              that.setData({
                subTabs: subTabs
              })
            } else {
              wx.showToast({
                title: '没有更多啦',
                icon: 'loading'
              })
            }
          }
        } else {
          wx.showLoading({
            title: '网络错误',
            icon: 'loading'
          })
        }
        wx.hideLoading();
        wx.hideNavigationBarLoading();
        wx.stopPullDownRefresh();
      },
      fail: function() {
        wx.hideLoading();
        wx.hideNavigationBarLoading();
        wx.stopPullDownRefresh();
      },
      complete: function() {}
    })
  },

  /**
   * 页面相关事件处理函数--监听用户下拉动作
   */
  onPullDownRefresh: function() {

    var that = this;
    // 延迟0.6s请求
    // 显示请求
    wx.showNavigationBarLoading();
    wx.showLoading({
      title: '请求中...',
      mask: true
    })
    setTimeout(function() {
      // 判断当前下拉是哪个界面
      if (that.data.activeIndex == 0) {
        that.setData({
          evalList: []
        })
        that.getArticle(0, 0);
      } else if (that.data.activeIndex == 1) {
        // 首先将指定index的list清空
        var subTabs = that.data.subTabs;
        subTabs[that.data.subActiveIndex].list = [];
        that.setData({
          subTabs: subTabs
        })
        // 数据请求
        that.getArticle(1, 0, that.data.subTabs[that.data.subActiveIndex].cat_id);
      }
    }, 700)
  },

  /**
   * 上拉加载
   */
  onReachBottom: function() {
    var that = this;
    // 判断是哪个界面的刷新
    // 延迟700s执行刷新

    setTimeout(function() {
      if (that.data.activeIndex == 0) {
        that.getArticle(0, that.data.evalPageNum);
      } else if (that.data.activeIndex == 1) {
        that.getArticle(1, that.data.subTabs[that.data.subActiveIndex].pageNum, that.data.subTabs[that.data.subActiveIndex].cat_id);
      }
    }, 700)
  },

  /**
   * 跳转到指定的文章详情界面
   */
  navToAriticle: function(evt) {
    var that = this;
    var articleId = evt.currentTarget.dataset.artid;
    // 文章点击量统计
    util.miniArticleCount(articleId)
    wx.navigateTo({
      url: '../articledetail/articledetail?articleId=' + articleId,
    })
  },

  /**
   * 用户分享
   */
  onShareAppMessage: function() {
    return {
      title: '新鲜资讯，尽在这里',
      path: '/pages/index/index'
    }
  },

})