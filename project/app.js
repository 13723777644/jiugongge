//app.js
App({
  d: {
   // Url: 'http://192.168.2.239/jiugongge/index.php'
    Url: 'https://dreamate.top/jiugongge2/index.php'
  },
  onLaunch: function (options) {
    let shareUid = options.uid || ''
    this.globalData.shareUid = shareUid
    //调用API从本地缓存中获取数据
    var logs = wx.getStorageSync('logs') || []
    logs.unshift(Date.now())
    wx.setStorageSync('logs', logs);
    var that = this
    if (that.globalData.photo != '' && that.globalData.photo != 'undifined') {

    } else {
      wx.login({
        success: function (res) {
          var code = res.code;
          that.getUserSessionKey(code);
        }
      })
    }
  },

  getUserSessionKey: function (code) {

    var that = this;
    wx.request({
      url: that.d.Url + '/Api/Login/getsessionkey',
      method: 'post',
      data: {
        code: code
      },
      header: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      success: function (res) {
        //--init data        
        var data = res.data;
        if (data.status == 0) {
          wx.showToast({
            title: data.err,
            duration: 2000
          });
          return false;
        }
        that.globalData.sessionId = data.session_key;
        that.globalData.openid = data.openid;
        that.onLoginUser();
        console.log(data.openid)
      },
      fail: function (e) {
        wx.showToast({
          title: '网络异常！73',
          icon: 'loading',
          duration: 2000
        });
      },
    });
  },
  onLoginUser: function () {
    var that = this;

    wx.request({
      url: that.d.Url + '/Api/Login/authlogin',
      method: 'post',
      data: {
        uuid: that.globalData.shareUid,
        openid: that.globalData.openid
      },
      header: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      success: function (res) {
        console.log(res)
        //--init data
        var data = res.data.arr;
        var status = res.data.status;
        if (res.data.status == 2) {//有用户信息
          that.globalData.photo = data.photo;
          that.globalData.nickname = data.nickname;
          that.globalData.uid = res.data.uid;
          that.globalData.status = res.data.status;
        } else if (res.data.status == 1) {//无用户信息
          that.globalData.uid = res.data.uid;
          that.globalData.status = res.data.status;
        } else if (res.data.status == 0) {
          wx.showToast({
            title: res.err,
            icon: 'loading',
            duration: 2000
          });
        }
      },
      fail: function (e) {
        wx.showToast({
          title: '网络异常！119',
          icon: 'loading',
          duration: 2000
        });
      },
    });
  },

  globalData: {
    tempFilePaths: [],
    uid: '',
    photo: '',
    status: '',//1为无用户信息，2为有用户信息
    nickname: '',
    sessionId: '',
    openid: '',
   // serverUrl: 'http://192.168.2.239/jiugongge/index.php',
    serverUrl: 'https://dreamate.top/jiugongge2/index.php'
                
  },
})