const formatTime = date => {
  const year = date.getFullYear()
  const month = date.getMonth() + 1
  const day = date.getDate()
  const hour = date.getHours()
  const minute = date.getMinutes()
  const second = date.getSeconds()

  return [year, month, day].map(formatNumber).join('/') + ' ' + [hour, minute, second].map(formatNumber).join(':')
}

const formatNumber = n => {
  n = n.toString()
  return n[1] ? n : '0' + n
}

/**
 * 统计小程序点击情况
 * 
 * @param mini_id 小程序的索引id
 * @param appid 小程序的appid
 * @param isEnter 是否跳转到小程序中（isEnter = 0时则为只进入小程序详情页）
 */
function miniClickCount(mini_id, appid, isEnter) {
  var miniLogs = wx.getStorageSync('miniLogs') || [];
  miniLogs.push({
    mini_id: mini_id,
    mini_appid: appid,
    is_enter: isEnter,
    click_time: parseInt(Date.now() / 1000)
  });
  wx.setStorageSync('miniLogs', miniLogs);
}

/**
 * 统计小程序分类目录的点击情况
 */
function miniCatagoryCount(catId) {
  var catLogs = wx.getStorageSync('catLogs') || [];
  catLogs.push({
    cat_id: catId,
    click_time: parseInt(Date.now() / 1000)
  });
  wx.setStorageSync('catLogs', catLogs);
}

/**
 * 统计小程序分类目录的点击情况
 */
function miniColumnCount(columnId) {
  var columnLogs = wx.getStorageSync('columnLogs') || [];
  columnLogs.push({
    column_id: columnId,
    click_time: parseInt(Date.now() / 1000)
  });
  wx.setStorageSync('columnLogs', columnLogs);
}

/**
 * 统计小程序文章的点击情况
 */
function miniArticleCount(articleId) {
  var articleLogs = wx.getStorageSync('articleLogs') || [];
  articleLogs.push({
    article_id: articleId,
    click_time: parseInt(Date.now() / 1000)
  });
  wx.setStorageSync('articleLogs', articleLogs);
}

module.exports = {
  formatTime: formatTime,
  miniClickCount: miniClickCount,
  miniCatagoryCount: miniCatagoryCount,
  miniColumnCount: miniColumnCount,
  miniArticleCount: miniArticleCount
}