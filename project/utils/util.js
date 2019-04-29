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
// 封装微信小程序 wx.request 方法
function Request(_url, data, callback) {
  wx.request({
    url: getApp().globalData.serverUrl + _url,
    data: data,
    method: 'POST',
    header: {
      'Content-Type': 'application/x-www-form-urlencoded'
    },
    success: function (res) {
      callback && callback(res.data);
    },
    fail: function (res) {
      wx.showToast({
        title: '网络错误',
        icon: 'loading',
        duration: 1500,
        mask: true
      })
    }
  })
}

module.exports = {
  formatTime: formatTime,
  Request: Request
}
