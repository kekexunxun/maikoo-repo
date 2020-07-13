var app = getApp();
var WxParse = require('../../utils/wxParse/wxParse.js');
Page({

  data: {
    goodsInfo: [], //商品信息
    activeIndex: 1,
    isAllNoStock: false, // 是否所有的商品都有存货
  },

  onLoad: function(options) {
    var that = this;
    // 请求参数判断 
    if (!options.goodsid) {
      wx.showModal({
        title: '系统提示',
        content: '参数错误',
        showCancel: false,
        success: function(res) {
          wx.redirectTo({
            url: '/pages/index/index'
          })
        }
      })
      return;
    }
    // 数据处理和请求
    wx.showLoading({
      title: '加载中...',
      mask: true
    })
    // 当从二维码进入时
    // 判断是否还有scene参数 其格式为 goodsid*1#userid*1 对其做相关处理获取goodsid
    let goodsid = options.goodsid;
    let parentid = options.parentid || "";
    if (options.scene) {
      let scene = decodeURIComponent(options.scene);
      let sceneArr = scene.split('#');
      let goodsArr = sceneArr[0].split('*');
      goodsid = goodsArr[1];
      let parentArr = sceneArr[1].split('*');
      parentid = parentArr[1];
    }
    // 当前参数必能获取到goodsid
    // 根据goodsid去获取商品信息
    // 同时如果该商品携带用户参数，那么需要将对应信息写入数据库
    that.setData({
      goodsid: goodsid,
      parentid: parentid
    })
    // 获取当前用户信息
    let timeCount = 0;
    let intval = setInterval(res => {
      if (app.globalData.userID) {
        clearInterval(intval);
        that.getGoodsById();
      } else {
        timeCount++;
        if (timeCount == 30) {
          clearInterval(intval);
          wx.showModal({
            title: '系统提示',
            content: '网络错误，请检查网络是否有效',
            showCancel: false,
            success: function(res) {
              wx.redirectTo({
                url: '/pages/index/index'
              })
            }
          })
        }
      }
    }, 100)
  },

  /**
   * 查询当前商品信息
   */
  getGoodsById: function(goodsid, parentid) {
    var that = this;
    // 判断当前用户是否有认证
    if (!app.globalData.inviteCode) {
      wx.redirectTo({
        url: '/pages/invitecode/invitecode',
      })
      return;
    }
    wx.request({
      url: app.globalData.siteroot + 'fangte/getGoodsById',
      method: 'POST',
      dataType: 'json',
      data: {
        parentid: that.data.parentid || "no",
        goodsid: that.data.goodsid,
        openid: wx.getStorageSync('openid'),
        userid: app.globalData.userID
      },
      success: function(res) {
        if (res.data.code == "200") {
          let detail = res.data.goodsInfo.detail;
          let isAllNoStock = true;
          for (let i = 0; i < detail.length; i++) {
            if (detail[i].stock > 0) {
              isAllNoStock = false;
              break;
            }
          }
          that.setData({
            isAllNoStock: isAllNoStock,
            goodsInfo: res.data.goodsInfo,
            expressFee: app.globalData.logi_fee,
            expressFreeFee: app.globalData.logi_free_fee
          })

          // 初始化商品详情
          /**
           * WxParse.wxParse(bindName , type, data, target,imagePadding)
           * 1.bindName绑定的数据名(必填)
           * 2.type可以为html或者md(必填)
           * 3.data为传入的具体数据(必填)
           * 4.target为Page对象,一般为this(必填)
           * 5.imagePadding为当图片自适应是左右的单一padding(默认为0,可选)
           */
          WxParse.wxParse('article', 'html', res.data.goodsInfo.intro, that, 5);
        } else {
          wx.showToast({
            title: '商品已下架',
            icon: 'loading'
          })
          // 跳转回上一级界面
          setTimeout(function() {
            wx.navigateBack({
              delta: 1
            })
          }, 1000)
        }
      },
      fail: function() {
        wx.hideLoading();
        wx.showModal({
          title: '系统提示',
          content: '参数错误',
          showCancel: false,
          success: function(res) {
            if (res.confirm) {
              wx.redirectTo({
                url: '/pages/index/index',
              })
            }
          }
        })
      },
      complete: function() {
        wx.hideLoading()
      }
    })
  },

  /**
   * 将当前商品加入到购物车
   */
  addToCart: function() {
    var that = this;
    // 先判断当前用户是否实名认证
    if (!app.globalData.isAuth) {
      wx.showModal({
        title: '系统提示',
        content: '您需要实名认证后才可购买',
        showCancel: false,
        success: function(res) {
          if (res.confirm) {
            wx.navigateTo({
              url: '../userinfo/userinfo',
            })
          }
        }
      })
      return;
    }

    // 若当前用户为实名认证用户，则直接加入购物车
    // 发送加入购物车请求
    // 如果goods_type == 1 则让用户选择加入购物车或者是查看购物车

    let actionSheet = [];
    // 构造actionSheet
    let detail = that.data.goodsInfo.detail;
    detail.forEach(function(item, index) {
      actionSheet.push(item.detail_name)
    })
    actionSheet.push('进入购物车');
    wx.showActionSheet({
      itemList: actionSheet,
      success: function(res) {
        if (res.tapIndex == actionSheet.length - 1) {
          wx.navigateTo({
            url: '../cart/cart',
          })
        } else {
          that.addCartRequest(that.data.goodsid, detail[res.tapIndex].idx);
        }
      }
    })
  },

  /**
   * 封装进入购物车请求
   * 
   * goods_id 商品id
   * goods_type 商品分类 若为2 则必须传detail_id
   * detail_id 商品详情id
   */
  addCartRequest: function(goods_id, detail_id = null) {
    var that = this;
    wx.showLoading({
      title: '加载中',
    })
    // 构造增加至购物车的商品数组
    wx.request({
      url: app.globalData.siteroot + 'fangte/addToCart',
      method: "POST",
      dataType: 'json',
      data: {
        goodsid: goods_id,
        detailid: detail_id,
        openid: wx.getStorageSync('openid')
      },
      success: function(res) {
        wx.hideLoading();
        if (res.data.code == "200") {
          wx.showToast({
            title: '加入购物车成功',
            duration: 1000
          })
        } else {
          wx.showToast({
            title: '网络错误',
            icon: 'none'
          })
        }
      },
      fail: function() {},
      complete: function() {
        wx.hideLoading();
      }
    })
  },

  /**
   * 用户在商品界面直接发起购买
   */
  directBuy: function(e) {
    // 判断用户是否实名认证
    if (!app.globalData.isAuth) {
      wx.hideLoading();
      wx.showModal({
        title: '系统提示',
        content: '您需要实名认证后才可购买',
        showCancel: false,
        success: function(res) {
          wx.navigateTo({
            url: '../userinfo/userinfo',
          })
        }
      })
      return;
    }

    var that = this;
    let goodsItem = [];
    let curGoods = {};
    let totalFee = 0;
    let goodsInfo = that.data.goodsInfo;
    // 判断是点底部的直接购买还是商品的直接购买
    // 1 商品直接购买
    var detailIdx = e.currentTarget.dataset.idx;
    if (detailIdx != null) {
      curGoods = goodsInfo.detail[detailIdx];
      totalFee = goodsInfo.is_on_promotion ? curGoods.pro_price : curGoods.shop_price;
      curGoods.detail_id = curGoods.idx;
      curGoods.pic = goodsInfo.pic;
      curGoods.catagory_id = goodsInfo.catagory_id;
      curGoods.quantity = 1;
      curGoods.goods_name = goodsInfo.name + '-' + curGoods.detail_name;
      curGoods.is_distri = goodsInfo.is_distri;
      curGoods.dis_percent = goodsInfo.dis_percent;
      curGoods.parent_dis_percent = goodsInfo.parent_dis_percent;
      curGoods.grand_dis_percent = that.data.goodsInfo.grand_dis_percent;
      curGoods.is_on_promotion = goodsInfo.is_on_promotion;
      curGoods.promotion_name = goodsInfo.is_on_promotion ? goodsInfo.promotion_name : '';
      curGoods.promotion_id = goodsInfo.is_on_promotion ? goodsInfo.promotion_id : 0;
      curGoods.promotion_count = goodsInfo.is_on_promotion ? goodsInfo.promotion_count : '';
      goodsItem.push(curGoods);
      wx.setStorageSync('goodsItem', goodsItem);
      wx.navigateTo({
        url: '../pay/pay?isCart=0&totalFee=' + totalFee,
      })
    } else {
      // 2 底部立即购买链接
      let actionSheet = [];
      goodsInfo.detail.forEach(function(item, index) {
        actionSheet.push(item.detail_name)
      })
      wx.showActionSheet({
        itemList: actionSheet,
        success: function(res) {
          curGoods = goodsInfo.detail[res.tapIndex];
          if (curGoods.stock <= 0) {
            wx.showModal({
              title: '系统提示',
              content: '该商品库存不足，无法购买',
              showCancel: false
            })
          } else {
            curGoods.pic = goodsInfo.pic;
            curGoods.quantity = 1;
            curGoods.goods_name = goodsInfo.name + '-' + curGoods.detail_name;
            curGoods.is_distri = goodsInfo.is_distri;
            curGoods.detail_id = curGoods.idx;
            curGoods.catagory_id = goodsInfo.catagory_id;
            curGoods.dis_percent = goodsInfo.dis_percent;
            curGoods.parent_dis_percent = goodsInfo.parent_dis_percent;
            curGoods.grand_dis_percent = goodsInfo.grand_dis_percent;
            curGoods.promotion_name = goodsInfo.is_on_promotion ? goodsInfo.promotion_name : '';
            curGoods.promotion_count = goodsInfo.is_on_promotion ? goodsInfo.promotion_count : '';
            curGoods.promotion_id = goodsInfo.is_on_promotion ? goodsInfo.promotion_id : 0;
            curGoods.is_on_promotion = goodsInfo.is_on_promotion;
            goodsItem.push(curGoods);
            wx.setStorageSync('goodsItem', goodsItem);
            totalFee = goodsInfo.is_on_promotion ? curGoods.pro_price : curGoods.shop_price;
            wx.navigateTo({
              url: '../pay/pay?isCart=0&totalFee=' + totalFee,
            })
          }
        }
      })
    }
  },


  // 用户界面分享
  onShareAppMessage: function(res) {
    var that = this;
    if (!app.globalData.isAuth) {
      wx.showModal({
        title: '系统提示',
        content: '您还未实名认证，实名认证后有更多优惠哦',
        success: function(res) {
          if (res.confirm) {
            wx.navigateTo({
              url: '../userinfo/userinfo',
            })
          }
        }
      })
      return;
    }
    return {
      title: '这个宝贝~你值得拥有！',
      path: '/pages/goodsdetail/goodsdetail?goodsid=' + that.data.goodsid + '&parentid=' + app.globalData.userID
    }
  },

  /**
   * 上拉加载到底部
   */
  onReachBottom: function() {
    var that = this;
    wx.showLoading({
      title: '跳转中...',
      mask: true
    })
    setTimeout(function() {
      wx.navigateTo({
        url: '../goodsdetailinfo/goodsdetailinfo?goodsid=' + that.data.goodsid,
      })
      wx.hideLoading();
    }, 600)

  },

})