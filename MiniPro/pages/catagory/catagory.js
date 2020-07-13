var app = getApp();
var util = require('../../utils/util.js');
Page({

  data: {
    tabs: ["全部"],
    activeIndex: 0,
    sliderOffset: 0,
    sliderLeft: 0,
  },

  onLoad: function(options) {
    console.log(options)
    if (!options.catId) {
      wx.showToast({
        title: '参数错误',
        icon: 'loading'
      })
      setTimeout(function() {
        wx.navigateBack({
          delta: 1
        })
      }, 1000)
    } else {
      this.getCatagory(options.catId);
    }

  },

  /**
   * 获取用户的收藏列表
   * 
   */
  getCatagory: function(catId) {
    var that = this;
    // 如果有数据，那么向后台进行数据获取
    wx.showLoading({
      title: '加载中...',
      mask: true
    })
    wx.request({
      url: app.globalData.siteroot + 'catagory/getUserCatagory',
      method: 'POST',
      dataType: 'json',
      data: {
        catId: catId,
        openid: wx.getStorageSync('openid')
      },
      success: function(res) {
        if (res.statusCode == 200 && res.data.code == 0) {
          if (res.data.data.length >= 1) {
            // 构造相关数据
            var allMinis = []; // 全部小程序
            var tabInfo = [];
            var tabs = that.data.tabs; // 导航tab
            var catInfo = res.data.data;
            var currentCatName = null;
            for (var i = 0; i < catInfo.length; i++) {
              if (catInfo[i].minis) {
                catInfo[i].minis.forEach(function(current, index) {
                  allMinis.push(current)
                })
              }
              currentCatName = catInfo[i].father_name;
              tabs.push(catInfo[i].name);
              tabInfo.push(catInfo[i]);
            }

            // 动态设置navTitleName
            if (currentCatName) {
              wx.setNavigationBarTitle({
                title: currentCatName,
              })
            }
            if(catId == "all"){
              wx.setNavigationBarTitle({
                title: "全部分类",
              })
            }
            // 将tabs第一个全部的加入当前tab元素栈顶
            tabInfo.unshift({
              catagory_id: catId,
              name: currentCatName,
              minis: allMinis
            });
            var sliderWidth = 60;
            // 计算顶部TAB
            wx.getSystemInfo({
              success: function(res) {
                that.setData({
                  sliderLeft: tabs.length <= 5 ? (res.windowWidth / tabs.length - sliderWidth) : 30,
                  sliderOffset: res.windowWidth / tabs.length * that.data.activeIndex,
                  itemWidth: tabs.length <= 5 ? '120' + 'rpx' : '100rpx'
                });
              }
            });
            // 设置当前数据
            that.setData({
              // allMinis: allMinis,
              tabs: tabs,
              tabInfo: tabInfo
            })
          } else {
            wx.showToast({
              title: '暂无数据',
              icon: 'loading'
            })
            setTimeout(function() {
              wx.navigateBack({
                delta: 1
              })
            }, 1000)
          }
        } else {
          isHaveMore = false;
          wx.showToast({
            title: '网络错误',
            icon: 'none'
          })
        }
      },
      fail: function() {},
      complete: function() {
        // 数据更新
        wx.hideLoading();
        wx.stopPullDownRefresh();
      }
    })
  },

  /**
   * 用户改变收藏状态
   */
  updateUserFav: function(evt) {
    var that = this;
    console.log(evt);
    var favId = evt.currentTarget.dataset.favid;
    // 这个idx是收藏表中的主键idx
    var favIdx = evt.currentTarget.dataset.favidx;
    // 这个index是当前删除的程序在数组中的位置
    var index = evt.currentTarget.dataset.index;
    // 判断当前该小程序是否为收藏状态
    var isFav = evt.currentTarget.dataset.isfav;

    // 判断是要收藏还是取消收藏
    if (isFav) {
      var url = 'user/userCancelFav';
      wx.showModal({
        title: '系统提示',
        content: '确定要取消收藏该小程序吗',
        success: function(res) {
          if (res.confirm) {
            that.sendChangeFavRequest(favIdx, favId, index, 0, url);
          }
        }
      })
    } else {
      var url = 'user/userAddFav';
      wx.showModal({
        title: '系统提示',
        content: '确定要收藏该小程序吗',
        success: function(res) {
          if (res.confirm) {
            that.sendChangeFavRequest(favIdx, favId, index, 1, url);
          }
        }
      })
    }

  },

  /**
   * 向后台发送修改用户收藏的请求
   * 
   * favIdx 当前在数据库中对应的索引IDX
   * favId 收藏对应的小程序Id
   * index 在列表中所处的索引位置
   * action 是收藏还是取消 0 取消 1 收藏
   * url 需要请求的地址
   */
  sendChangeFavRequest: function(favIdx, favId, index, action, url) {
    var that = this;
    wx.request({
      url: app.globalData.siteroot + url,
      method: 'POST',
      dataType: 'json',
      data: {
        openid: wx.getStorageSync('openid'),
        idx: favIdx,
        favId: favId,
        favType: 1
      },
      success: function(res) {
        if (res.statusCode == 200 && res.data.code == 0) {
          wx.showToast({
            title: action == 0 ? '取消收藏成功' : '收藏成功',
            duration: 1000
          })
          // 更新当前数据
          var tabInfo = that.data.tabInfo;
          var activeIndex = that.data.activeIndex;
          tabInfo[activeIndex].minis[index].isFav = !tabInfo[activeIndex].minis[index].isFav;
          tabInfo[activeIndex].minis[index].favIdx = res.data.data ? res.data.data : tabInfo[activeIndex].minis[index].favIdx;
          that.setData({
            tabInfo: tabInfo
          })
        } else {
          wx.showToast({
            title: '网络错误',
            icon: 'loading'
          })
        }
      },
      fail: function() {},
      complete: function() {
        wx.hideLoading();
      }
    })
  },

  // TAB点击事件
  tabClick: function(evt) {
    this.setData({
      sliderOffset: evt.currentTarget.offsetLeft,
      activeIndex: evt.currentTarget.id
    });
  },

  /**
   * 跳转到小程序详情页面
   * isEnter传0
   */
  navToMiniDetail: function(evt) {
    var that = this;
    // 这个index是当前点击的小程序在列表中的位置索引
    var index = evt.currentTarget.dataset.idx;
    // 获取该小程序相关信息
    var tabInfo = that.data.tabInfo;
    var miniId = tabInfo[that.data.activeIndex].minis[index].mini_id;
    wx.navigateTo({
      url: '../minidetail/minidetail?miniId=' + miniId,
    })
  },

  /**
   * 小程序点击统计
   * 这里是直接跳转 所以isEnter传 1
   */
  miniClick: function(evt) {
    // console.log('miniClick')
    var that = this;
    // 这个index是当前点击的小程序在列表中的位置索引
    var index = evt.currentTarget.dataset.idx;
    // 获取该小程序相关信息
    var tabInfo = that.data.tabInfo;
    var miniId = tabInfo[that.data.activeIndex].minis[index].mini_id;
    var appid = tabInfo[that.data.activeIndex].minis[index].appid;
    util.miniClickCount(miniId, appid, 1);
  },

  /**
   * 页面相关事件处理函数--监听用户下拉动作
   */
  onPullDownRefresh: function() {
    this.getCatagory();
  },

  /**
   * 用户分享
   */
  onShareAppMessage: function() {
    return {
      title: '这里有很多好玩的~快来看看吧~',
      path: '/pages/index/index'
    }
  },

})