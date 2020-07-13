/**
  * 获取时间字符串函数
  */
function getTimeStr(totalSecond) {
  // 秒数  
  var second = totalSecond;
  // 天数位  
  var day = Math.floor(second / 3600 / 24);
  var dayStr = day.toString();
  if (dayStr.length == 1) dayStr = '0' + dayStr;

  // 小时位  
  var hr = Math.floor((second - day * 3600 * 24) / 3600);
  var hrStr = hr.toString();
  if (hrStr.length == 1) hrStr = '0' + hrStr;

  // 分钟位  
  var min = Math.floor((second - day * 3600 * 24 - hr * 3600) / 60);
  var minStr = min.toString();
  if (minStr.length == 1) minStr = '0' + minStr;

  // 秒位  
  var sec = second - day * 3600 * 24 - hr * 3600 - min * 60;
  var secStr = sec.toString();
  if (secStr.length == 1) secStr = '0' + secStr;

  return dayStr + '天' + hrStr + '时' + minStr + '分' + secStr + '秒';
}

function stringTrim(str) {
  str = str.replace(/^\s\s*/, '');
  var ws = /\s/;
  var i = str.length;
  while (ws.test(str.charAt(--i)));
  return str.slice(0, i + 1);
}


module.exports = {
  getTimeStr: getTimeStr,
  stringTrim: stringTrim
}
