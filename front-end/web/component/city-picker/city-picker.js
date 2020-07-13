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
    addrCode: {
      type: Array,
      value: [0, 0, 0]
    }
  },

  /**
   * 组件的初始数据
   */
  data: {
    temp: [0, 0, 0], // 记录缓存和初始值
    provinceArr: [],
    cityArr: [],
    areaArr: []
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
        temp: this.data.addrCode ? this.data.addrCode : this.data.temp
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
      this._initProvince();
      this._updateCity();
      this._updateArea();
    },

    /**
     * 更新 省级 数据列表
     */
    _initProvince: function() {
      let provinceArr = [];
      for (let i = 0; i < cityData.city.length; i++) {
        provinceArr.push({
          name: cityData.city[i].name,
          code: cityData.city[i].code
        })
      }
      this.setData({
        provinceArr: provinceArr
      })
    },

    /**
     * 更新 市级 数据列表
     */
    _updateCity: function() {
      let cityList = cityData.city[this.data.temp[0]].sub,
        cityArr = [];
      for (let i = 0; i < cityList.length; i++) {
        cityArr.push({
          name: cityList[i].name,
          code: cityList[i].code
        })
      }
      this.setData({
        cityArr: cityArr
      })
    },

    /**
     * 更新 区级 数据列表
     */
    _updateArea: function() {
      let areaList = cityData.city[this.data.temp[0]].sub[this.data.temp[1]].sub,
        areaArr = [];
      for (let i = 0; i < areaList.length; i++) {
        areaArr.push({
          name: areaList[i].name,
          code: areaList[i].code
        })
      }
      this.setData({
        areaArr: areaArr
      })
    },

    /**
     * picker 发生值改变
     */
    _pickerChange: function(evt) {
      let temp = this.data.temp,
        value = evt.detail.value;
      if (temp[0] != value[0]) {
        value[1] = 0;
        value[2] = 0;
      } else if (temp[1] != value[1]) {
        value[2] = 0;
      }
      this.setData({
        temp: value
      })
      // 延时
      wx.nextTick(() => {
        this._updateCity();
        wx.nextTick(() => {
          this._updateArea();
        })
      })
    },

    /**
     * 确定选择
     */
    confirm: function() {
      // 关闭picker
      this._hidePicker();

      let temp = this.data.temp;
      const myEventDetail = {
        id: temp,
        code: [cityData.city[temp[0]].code, cityData.city[temp[0]].sub[temp[1]].code, cityData.city[temp[0]].sub[temp[1]].sub[temp[2]].code],
        name: [cityData.city[temp[0]].name, cityData.city[temp[0]].sub[temp[1]].name, cityData.city[temp[0]].sub[temp[1]].sub[temp[2]].name]
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