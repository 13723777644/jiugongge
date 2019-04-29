//index.js
const app = getApp()
// 获取显示区域长宽
const device = wx.getSystemInfoSync()
const W = device.windowWidth
const H = device.windowHeight - 50

let cropper = require('../../welCropper/welCropper.js');

console.log(device)

Page({
  data: {
    imgUrls: [
    ],
    num: 0


  },
  onLoad: function () {
    var that = this
    // 初始化组件数据和绑定事件
    cropper.init.apply(that, [W, H]);
    that.getData()
  },
  onShow: function () {
    var that = this;


  },
  onShareAppMessage: function () {

  },
  choosePhoto: function () {
    wx.chooseImage({
      count: 1, // 默认9
      sizeType: ['compressed'], // 可以指定是原图还是压缩图，默认二者都有
      sourceType: ['album', 'camera'], // 可以指定来源是相册还是相机，默认二者都有
      success: function (res) {
        let photo = res.tempFilePaths[0]
        app.globalData.tempFilePaths = res.tempFilePaths
        wx.navigateTo({
          url: '../todo/todos?photo=' + photo,
        })
      }
    })
  },
  getData: function () {
    var that = this;

    wx.request({
      url: app.d.Url + '/Api/Index/index',
      method: "post",
      data: {
        //   id: id
      },
      header: {
        'Content-Type': 'application/x-www-form-urlencoded'
      },
      success: function (res) {
        console.log(res)
        that.setData({
          imgUrls: res.data.ggtop,
          num: res.data.num
        })

      },
      fail: function (e) {
        wx.showToast({
          title: '网络异常！',
          duration: 30000
        });
      },

    })
  },
  tiao(e) {
    var id = e.currentTarget.dataset.id;
    var p = e.currentTarget.dataset.p;
    var name = e.currentTarget.dataset.name;
    if (p == 1) {
      //跳转富文本
      wx.navigateTo({
        url: '../knowledge/knowledge?id=' + id,
      })

    } else {
      //跳转小程序
      wx.navigateToMiniProgram({
        appId: name,
        path: 'pages/index/index',
        extraData: {
          foo: 'bar'
        },
        envVersion: 'develop',
        success(res) {

        }
      })
    }

  },
  toCoupon(e){
    wx.showModal({
      title: '看我下面',
      content: '暂未开发你还点，你想干啥？',
      success: function (res) {
        if (res.confirm) {
          console.log('用户点击确定')
        } else if (res.cancel) {
          console.log('用户点击取消')
        }
      }
    })
  },
  selectTap(e) {
    let that = this
    wx.showModal({
      title: '提示',
      content: '请截正方形的图，排版比较美观',
      success: function (res) {
        if (res.confirm) {
          let mode = e.currentTarget.dataset.mode
          console.log(e)

          wx.chooseImage({
            count: 1,
            sizeType: ['original', 'compressed'],  // 可以指定是原图还是压缩图，默认二者都有
            sourceType: ['album', 'camera'], // 可以指定来源是相册还是相机，默认二者都有
            success(res) {
              const tempFilePath = res.tempFilePaths[0]
              console.log(tempFilePath)

              // 将选取图片传入cropper，并显示cropper
              // mode=rectangle 返回图片path
              // mode=quadrangle 返回4个点的坐标，并不返回图片。这个模式需要配合后台使用，用于perspective correction
              // let modes = ["rectangle", "quadrangle"]
              // let mode = modes[1]   //rectangle, quadrangle
              that.showCropper({
                src: tempFilePath,
                mode: mode,
                sizeType: ['original', 'compressed'],   //'original'(default) | 'compressed'
                callback: (res) => {
                  if (mode == 'rectangle') {
                    console.log(99999)
                    ///         console.log("crop callback:" + res)
                    // wx.previewImage({
                    //     current: '',
                    //     urls: [res]
                    // })
                    wx.navigateTo({
                      url: '../knowledge/knowledges?url=' + res,
                    })
                  }
                  else {
                    wx.showModal({
                      title: '',
                      content: JSON.stringify(res),
                    })

                    console.log(res)
                  }

                  // that.hideCropper() //隐藏，我在项目里是点击完成就上传，所以如果回调是上传，那么隐藏掉就行了，不用previewImage
                }
              })
            }
          })


        } else if (res.cancel) {
          console.log('用户点击取消')
        }
      }
    })

  },
//拍照精准取字
  toCoupons(e) {
    let that = this
    let mode = e.currentTarget.dataset.mode
    console.log(e)

    wx.chooseImage({
      count: 1,
      sizeType: ['original', 'compressed'],  // 可以指定是原图还是压缩图，默认二者都有
      sourceType: ['album', 'camera'], // 可以指定来源是相册还是相机，默认二者都有
      success(res) {
        const tempFilePath = res.tempFilePaths[0]
        console.log(tempFilePath)

        // 将选取图片传入cropper，并显示cropper
        // mode=rectangle 返回图片path
        // mode=quadrangle 返回4个点的坐标，并不返回图片。这个模式需要配合后台使用，用于perspective correction
        // let modes = ["rectangle", "quadrangle"]
        // let mode = modes[1]   //rectangle, quadrangle
        that.showCropper({
          src: tempFilePath,
          mode: mode,
          sizeType: ['original', 'compressed'],   //'original'(default) | 'compressed'
          callback: (res) => {
            if (mode == 'rectangle') {
              console.log(99999)
              ///         console.log("crop callback:" + res)
              // wx.previewImage({
              //     current: '',
              //     urls: [res]
              // })
              that.hideCropper()
              wx.navigateTo({
                url: '../todo/todo?photo=' + res,
              })
            }
            else {
              wx.showModal({
                title: '',
                content: JSON.stringify(res),
              })

              console.log(res)
            }

            // that.hideCropper() //隐藏，我在项目里是点击完成就上传，所以如果回调是上传，那么隐藏掉就行了，不用previewImage
          }
        })
      }
    })


  },



  selectTaps(e) {
    let that = this
    wx.showModal({
      title: '提示',
      content: '请截正方形的图，排版比较美观',
      success: function (res) {
        if (res.confirm) {
          
          let mode = e.currentTarget.dataset.mode
          console.log(e)

          wx.chooseImage({
            count: 1,
            sizeType: ['original', 'compressed'],  // 可以指定是原图还是压缩图，默认二者都有
            sourceType: ['album', 'camera'], // 可以指定来源是相册还是相机，默认二者都有
            success(res) {
              const tempFilePath = res.tempFilePaths[0]
              console.log(tempFilePath)

              // 将选取图片传入cropper，并显示cropper
              // mode=rectangle 返回图片path
              // mode=quadrangle 返回4个点的坐标，并不返回图片。这个模式需要配合后台使用，用于perspective correction
              // let modes = ["rectangle", "quadrangle"]
              // let mode = modes[1]   //rectangle, quadrangle
              that.showCropper({
                src: tempFilePath,
                mode: mode,
                sizeType: ['original', 'compressed'],   //'original'(default) | 'compressed'
                callback: (res) => {
                  if (mode == 'rectangle') {
                    console.log(99999)
                    ///         console.log("crop callback:" + res)
                    // wx.previewImage({
                    //     current: '',
                    //     urls: [res]
                    // })
                    wx.navigateTo({
                      url: '../knowledge/knowledgess?url=' + res,
                    })
                  }
                  else {
                    wx.showModal({
                      title: '',
                      content: JSON.stringify(res),
                    })

                    console.log(res)
                  }

                  // that.hideCropper() //隐藏，我在项目里是点击完成就上传，所以如果回调是上传，那么隐藏掉就行了，不用previewImage
                }
              })
            }
          })

        } else if (res.cancel) {
          console.log('用户点击取消')
        }
      }
    })
 
  },

})
