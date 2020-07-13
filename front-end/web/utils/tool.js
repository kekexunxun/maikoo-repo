function stringTrim(str) {
  str = str.replace(/^\s\s*/, '');
  var ws = /\s/;
  var i = str.length;
  while (ws.test(str.charAt(--i)));
  return str.slice(0, i + 1);
}

function storeFormId(formId) {
  let formIdArr = wx.getStorageSync('formIdArr') || [];
  formIdArr.push({
    formId: formId,
    expireAt: parseInt(Date.now() / 1000) + 7 * 86400
  })
  wx.setStorageSync('formIdArr', formIdArr);
}

/**
 * 权限检查
 */
function checkAuth(authName) {
  wx.getSetting({
    success: res => {
      if (res.authSetting['scope.' + authName]) {
        return true;
      } else {
        return false;
      }
    }
  })
}

/**
 * 将地址的Code转换为对应的索引
 */
function code2Index(codeArr) {
  codeArr = codeArr.split('_');
  let cityArr = require('./city.js');
  let prov = city = area = 0,
    provn = cityn = arean = "";
  cityArr = cityArr.city;
  for (let i = 0; i < cityArr.length; i++) {
    if (cityArr[i].code == codeArr[0]) {
      prov = cityArr[i].code;
      provn = cityArr[i].name;
      for (let j = 0; j < cityArr[i].sub.length; j++) {
        if (cityArr[i].sub[j].code == codeArr[1]) {
          city = cityArr[i].sub[j].code;
          cityn = cityArr[i].sub[j].name;
          for (let k = 0; k < cityArr[i].sub[j].sub.length; k++) {
            if (cityArr[i].sub[j].sub[k].code == codeArr[2]) {
              area = cityArr[i].sub[j].sub[k].code;
              arean = cityArr[i].sub[j].sub[k].name;
              return {
                code: [prov, city, area],
                index: [i, j, k],
                name: [provn, cityn, arean]
              };
            }
          }
        }
      }
    }
  }
}

module.exports = {
  stringTrim: stringTrim,
  storeFormId: storeFormId,
  checkAuth: checkAuth,
  code2Index: code2Index
}