//获取应用实例
const app = getApp()

Page({
  data: {
    inputShowed: false,
    inputVal: "",
    isHaveSearchValue: false, // 是否有搜索结果
    ticketList: [{
      icon: '../../images/jingdian.png',
      text: '经典',
      tap: 'navToCat',
      catId: 1
    }, {
      icon: '../../images/changxian.png',
      text: '尝鲜',
      tap: 'navToCat',
      catId: 2
    }, {
      icon: '../../images/ciji.png',
      text: '刺激',
      tap: 'navToCat',
      catId: 3
    }, {
      icon: '../../images/xiuxian.png',
      text: '休闲',
      tap: 'navToCat',
      catId: 4
    }],
    // 生活用品产品测试
    dailyList: [{
      pic: '/images/waitmore.png',
      name: '敬请期待',
      shop_price: '0.00'
    }],
  },

  /**
   * 初始化界面
   */
  onLoad: function() {
    var that = this;
    var timeCount = 0;
    var intval = setInterval(res => {
      if (app.globalData.userID && app.globalData.mini_name) {
        // console.log(timeCount);
        clearInterval(intval);
        // 设置小程序名称和顶部导航颜色
        wx.setNavigationBarTitle({
          title: app.globalData.mini_name || 'A·Q大玩家'
        })
        // if (app.globalData.mini_color){
        //   wx.setNavigationBarColor({
        //     frontColor: '#ffffff',
        //     backgroundColor: app.globalData.mini_color,
        //     animation: {
        //       duration: 1000,
        //       timingFunc: 'easeInOut'
        //     }
        //   })
        // }
        that.getIndexData();
      } else {
        timeCount++;
        if (timeCount == 40) {
          clearInterval(intval);
          wx.showModal({
            title: '系统提示',
            content: '网络错误，请检查网络是否有效',
            showCancel: false
          })
        }
      }
    }, 100)
  },

  getIndexData: function() {
    var that = this;
    // 判断当前用户是否有认证
    if (!app.globalData.inviteCode) {
      wx.redirectTo({
        url: '/pages/invitecode/invitecode',
      })
      return;
    }
    wx.showLoading({
      title: '加载中...',
      mask: true
    })
    wx.request({
      url: app.globalData.siteroot + 'fangte/getShopInfo',
      method: 'GET',
      dataType: 'json',
      success: function(res) {
        // 数据处理
        var ticketList = that.data.ticketList;
        if (res.data.ticket != 'none') {
          ticketList[0].icon = res.data.ticket['jd'] ? res.data.ticket['jd'] : ticketList[0].icon;
          ticketList[1].icon = res.data.ticket['cx'] ? res.data.ticket['cx'] : ticketList[1].icon;
          ticketList[2].icon = res.data.ticket['cj'] ? res.data.ticket['cj'] : ticketList[2].icon;
          ticketList[3].icon = res.data.ticket['xx'] ? res.data.ticket['xx'] : ticketList[3].icon;
        }
        // 处理daily
        var dailyList = that.data.dailyList;
        var banner = ['https://ft.up.maikoo.cn/public/banner/defaultbanner.png'];
        if (res.data.banner != 'none') {
          banner = [];
          for (var i = 0; i < res.data.banner.length; i++) {
            banner.push(res.data.banner[i]);
          }
        }
        // 将系统设置放到globalData里面
        app.globalData.setting = res.setting;
        that.setData({
          banner: banner,
          ticketList: ticketList,
          dailyList: res.data.daily || dailyList
        })
      },
      fail: function() {},
      complete: function() {
        wx.hideLoading()
      }
    })
  },

  /**
   * 跳转到对应分类界面
   */
  navToCat: function(evt) {
    var that = this;
    let catId = evt.currentTarget.dataset.catid;
    let url = "";
    if (catId == 0) {
      url = "../goods/goods?catId=" + catId + "&title=票券Ticket";
    } else if (catId != 5) {
      url = "../goods/goods?catId=" + catId + "&title=票券Ticket" + ' - ' + that.data.ticketList[catId - 1].text;
    } else {
      url = "../goods/goods?catId=" + catId + "&title=生活用品Daily"
    }
    wx.navigateTo({
      url: url
    })
  },

  /**
   * 跳转到商品详情
   */
  navToGood: function(evt) {
    let goodsid = evt.currentTarget.dataset.goodsid;
    if (goodsid) {
      wx.navigateTo({
        url: '/pages/goodsdetail/goodsdetail?goodsid=' + goodsid
      })
    }
  },

  /**
   * 当搜索框失去焦点时，将搜索状态取消
   */
  inputLoseBlur: function() {
    this.setData({
      isHaveSearchValue: false,
      searchResult: [],
      inputShowed: false,
      inputVal: ''
    })
  },

  /**
   * 页面上啦触底
   */
  onReachBottom: function() {
    // console.log("reach bottom");
  },

  /**
   * 展示搜索框的取消搜索按钮
   */
  showInput: function() {
    this.setData({
      inputShowed: true
    });
  },

  /**
   * 清空搜索框，取消搜索
   */
  hideInput: function() {
    this.setData({
      inputVal: "",
      inputShowed: false,
      isHaveSearchValue: false
    });
  },
  clearInput: function() {
    this.setData({
      inputVal: "",
      isHaveSearchValue: false,
      searchResult: []
    });
  },

  /**
   * 搜索栏商品搜索
   */
  inputValue: function(e) {
    var that = this;
    this.setData({
      inputVal: e.detail.value
    });
  },

  /**
   * 搜索商品
   */
  searchResult: function() {
    var that = this;
    if (that.data.inputVal) {
      wx.request({
        url: app.globalData.siteroot + 'fangte/searchGoods',
        method: 'POST',
        dataType: 'json',
        data: {
          inputVal: that.data.inputVal
        },
        success: function(res) {
          if (res.data.code == "200") {
            that.setData({
              isHaveSearchValue: true,
              searchResult: res.data.goods || [{
                name: '没有找到相关商品'
              }]
            })
          } else {
            that.setData({
              isHaveSearchValue: true,
              searchResult: [{
                name: '没有找到相关商品'
              }]
            })
          }
        },
        fail: function() {},
        complete: function() {}
      })
    }
  },

  /**
   * 用户转发分享
   */
  onShareAppMessage: function() {
    return {
      title: app.globalData.share_text || 'A · Q大玩家！你值得拥有',
      path: '/pages/index/index'
    }
  }
})