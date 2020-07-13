const app = getApp();
var util = require('../../utils/util.js');

Page({
  data: {
    windowHeight: 'auto',
    commentList: [], // 需要评价的数据
    ordersn: "", // 订单编号
    shopRate: [0, 0, 0], // 店铺评分 分别对应了 描述 物流 服务
    rate: ['非常差', '差', '一般', '好', '非常好'], // 用于做循环的辅助数组
    isCanSubmit: true, // 是否可以提交评价
  },

  /**
   * 初始化数据
   */
  onLoad: function(options) {
    if (!options.ordersn || !wx.getStorageSync('orderGoods')) {
      util.modalPromisified({
        title: '系统提示',
        content: '参数错误或系统内存不足',
        showCancel: false
      }).then(res => {
        wx.redirectTo({
          url: '/pages/index/index'
        })
      })
    } else {
      this.setData({
        ordersn: options.ordersn
      })
    }
  },

  onShow: function() {
    // 页面显示
    var that = this
    var commentList = [];
    let orderGoods = wx.getStorageSync('orderGoods');
    // 初始化评论选项为好评
    for (let i = 0; i < orderGoods.length; i++) {
      commentList.push({
        goods_img: orderGoods[i].goods_img,
        comment: "",
        goods_id: orderGoods[i].goods_id,
        satisfy: 1 // 用户满意度 1 好评 2 中评 3 差评 默认全好评
      })
    }
    that.setData({
      commentList: commentList
    });

    // 移除缓存
    // wx.removeStorageSync('orderGoods');
  },

  /**
   * 用户修改评价选项
   */
  changeSatisfy: function(evt) {
    var that = this;
    let commentList = that.data.commentList;
    let idx = evt.currentTarget.dataset.idx,
      sat = evt.currentTarget.dataset.sat;
    commentList[idx].satisfy = sat;
    that.setData({
      commentList: commentList
    })
  },

  /**
   * 用户进行评价发布
   */
  inputComment: function(evt) {
    var that = this;
    let commentList = that.data.commentList;
    let idx = evt.currentTarget.dataset.idx;
    commentList[idx].comment = evt.detail.value;
    that.setData({
      commentList: commentList
    })
  },

  /**
   * 用户修改店铺评价
   */
  changeShopRate: function(evt) {
    var that = this;
    let idx = evt.currentTarget.dataset.idx,
      stype = evt.currentTarget.dataset.stype,
      shopRate = that.data.shopRate;
    shopRate[stype] = idx + 1;
    that.setData({
      shopRate: shopRate
    })
  },

  /**
   * 用户提交评价
   */
  submitRate: function(evt) {
    var that = this;
    // 避免重复评价
    if (!that.data.isCanSubmit) {
      return;
    } else {
      that.setData({
        isCanSubmit: false
      })
    }
    // 判断是否能够提交评价
    if (!that.isCanSubmit()) return;
    util.modalPromisified({
      title: '系统提示',
      content: '您确认要提交评价吗？'
    }).then(res => {
      if (res.cancel) {
        that.setData({
          isCanSubmit: true
        })
        return;
      }
      wx.showLoading({
        title: '发布中',
        mask: true
      })
      // 构造请求参数
      return util.post('order/submitRate', {
        uid: app.globalData.uid,
        ordersn: that.data.ordersn,
        shoprate: that.data.shopRate,
        goodscomment: that.data.commentList
      }, 600)
    }).then(res => {
      wx.showToast({
        title: '评价成功',
        duration: 1000,
        mask: true
      })
      setTimeout(res => {
        wx.navigateBack({
          delta: 1
        })
      }, 800)
    }).catch(res => {
      util.modalPromisified({
        title: '系统提示',
        content: '网络错误，请稍后重试',
        showCancel: false
      })
    }).finally(res => {
      that.setData({
        isCanSubmit: true
      })
    })
  },

  /**
   * 提交前的检验
   */
  isCanSubmit: function() {
    var that = this;
    let shopRate = that.data.shopRate;
    for (let i = 0; i < shopRate.length; i++) {
      if (shopRate[i] == 0) {
        util.modalPromisified({
          title: '系统提示',
          content: '评价信息未完善，请完善后重试',
          showCancel: false
        }).then(res => {
          that.setData({
            isCanSubmit: true
          })
        })
        return false;
      }
    }
    return true;
  }

})