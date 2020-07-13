// 引入城市数据
const cityData = require('../../utils/city.js');

Component({
  /**
   * 组件的属性列表
   */
  properties: {
    hidden: {
      type: Boolean,
      value: true,
      observer(newVal, oldVal, changedPath) {
        this.setData({
          hidden: newVal
        })
      }
    },
    showCancel: {
      type: Boolean,
      value: true
    },
    confirmText: {
      type: String,
      value: "确定"
    },
    cancelText: {
      type: String,
      value: "取消"
    },
    titleText: String,
    columnArr: {
      type: Array,
      value: []
    },
    value: {
      type: Number,
      value: 0
    }
  },

  /**
   * 组件的初始数据
   */
  data: {
    temp: 0, // 记录缓存和初始值
  },

  /**
   * 属性初始化
   */
  attached() {
    this._init();
  },

  /**
   * 组件展示
   */
  pageLifetimes: {
    show() {
      this.setData({
        showCancel: this.data.showCancel,
        confirmText: this.data.confirmText,
        cancelText: this.data.cancelText,
        titleText: this.data.titleText,
        columnArr: this.data.columnArr
      })
    }
  },

  /**
   * 组件的方法列表
   */
  methods: {

    /**
     * 数据初始化
     */
    _init: function() {
      this.setData({
        value: this.data.temp
      })
    },

    /**
     * picker 发生值改变
     */
    _pickerChange: function(evt) {
      this.setData({
        temp: evt.detail.value,
      })
    },

    /**
     * 确定选择
     */
    confirm: function() {
      // 关闭picker
      this._hidePicker();

      const myEventDetail = {
        index: this.data.temp,
        value: this.data.columnArr[this.data.temp]
      }
      this.triggerEvent('confirm', myEventDetail, {});
    },

    /**
     * 取消选择
     */
    cancel: function() {
      this._hidePicker();
      this.triggerEvent('cancel', {}, {});
    },

    /**
     * 隐藏picker
     */
    _hidePicker: function() {
      this.setData({
        hidden: true
      })
    },

    /**
     * 防止界面滑动
     */
    _preventTouchMove: function() {},

  }
})