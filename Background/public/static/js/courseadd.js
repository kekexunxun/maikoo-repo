// ueditor初始化
$(function(){
    $('.skin-minimal input').iCheck({
        checkboxClass: 'icheckbox-blue',
        radioClass: 'iradio-blue',
        increaseArea: '20%'
    });
    UE.getEditor('container');
});

// 在键盘按下并释放及提交后验证提交表单
    $("#form-course-add").validate({
        rules: {
          course_name: {
            required: true,
            maxlength: 30
          },
          course_brief: {
            required: true,
            maxlength: 50
          },
          course_price: {
            required: true,
            maxlength: 10,
            number: true
          },
          countTimestart: {
            required: true,
          }, 
          countTimeend: {
            required: true,
          }, 
          countTimestart1: {
            required: true,
          }, 
          countTimeend1: {
            required: true,
          },
          countTimestart2: {
            required: true,
          }, 
          countTimeend2: {
            required: true,
          },
          checkbox: {
            isSelect:true   
          }   
        },
        // messages: {
        //   course_name: {
        //     required: "这是必填字段",
        //     maxlength: "30"
        //   },
        //   course_brief: {
        //     required: "这是必填字段",
        //     maxlength: "50"
        //   },
        //   course_price: {
        //     required: "这是必填字段",
        //     maxlength: "10"
        //   },
        // }
    })

// 表单正则
$.validator.addMethod("isSelect",function(value,element){
 return this.optional(element) ||(value!= -1);            
  }," ！请选择至少一项！"); 

// 提交表单信息
function courseadd_save_submit() {
    var flag = $("#form-course-add").valid();
    if(!flag){
        //没有通过验证
        layer.msg('请填写完整信息！', { icon: 2, time: 1000 });
        return false;
    }
    if(UE.getEditor('container').getContent()==''){
        layer.msg('内容不能为空！',{icon:2,time:1000});
        return false;
    }
    // 判断是勾选星期
    if($("input[type='checkbox']").is(':checked') == false ){
        layer.msg('请勾选课程日期！',{icon:2,time:1000});
        return false;
    }     
    $.ajax({
        type: 'POST',
        url: 'addCourse',
        dataType: 'json',
        data: $("#form-course-add").serialize(),
        success: function (data) {
            if (data.code == "0") {
                layer.msg(data.msg, { icon: 1, time: 1000 });
                index = parent.layer.getFrameIndex(window.name);
                setTimeout('parent.layer.close(index);', 1000);
            }
            if (data.code == '400') {
                layer.msg(data.msg, { icon: 2, time: 1000 });
            }
        },
        error: function () {
            layer.msg('网络错误，请稍后重试！', { icon: 2, time: 1000 });
        },
    });
}

// 图片上传
$(function () {
    var $list = $("#fileList");
    // 优化retina, 在retina下这个值是2
    var ratio = window.devicePixelRatio || 1;
    // 缩略图大小
    var thumbnailWidth = 200 * ratio;
    var thumbnailHeight = 100 * ratio;
    // 初始化Web Uploader
    var uploader = WebUploader.create({
        // 选完文件后，是否自动上传。
        auto: true,
        // swf文件路径
        swf: '__ROOT__/public/static/lib/webuploader/0.1.5/Uploader.swf',
        // 文件接收服务端。
        server: 'addPic',
        // 选择文件的按钮。可选。
        // 内部根据当前运行是创建，可能是input元素，也可能是flash.
        pick: '#filePicker',
        // 只允许选择图片文件。
        accept: {
            title: 'Images',
            extensions: 'gif,jpg,jpeg,bmp,png',
            mimeTypes: 'image/*'
        },
        thumb: {
            width: 110,
            height: 110,
            // 图片质量，只有type为`image/jpeg`的时候才有效。  
            quality: 100,
            // 是否允许放大，如果想要生成小图的时候不失真，此选项应该设置为false.  
            allowMagnify: true,
            // 是否允许裁剪。是否采用裁剪模式。如果采用这样可以避免空白内容。  
            crop: true,
            // 为空的话则保留原有图片格式。  
            // 否则强制转换成指定的类型。  
            type: ''
        },
        disableGlobalDnd: true,
        fileNumLimit: 1,
        fileSizeLimit: 2000 * 1024,    // 2000 K
        fileSingleSizeLimit: 2000 * 1024    // 2000 K
    });
    // uploader.options ={formData:"userName":"吉安娜","gender":"女"};
    // 当有文件添加进来的时候
    uploader.on('fileQueued', function (file) {
        var $li = $(
            '<div id="' + file.id + '" class="file-item thumbnail">' +
            '<img>' +
            '<div class="info">' + file.name + '</div>' +
            '</div>'
        ),
            $img = $li.find('img');
        // $list为容器jQuery实例
        $list.append($li);
        // 创建缩略图
        // 如果为非图片文件，可以不用调用此方法。
        // thumbnailWidth x thumbnailHeight 为 100 x 100
        uploader.makeThumb(file, function (error, src) {
            if (error) {
                $img.replaceWith('<span>不能预览</span>');
                return;
            }
            $img.attr('src', src);
        }, 400, 200);
    });
    // 文件上传过程中创建进度条实时显示
    uploader.on('uploadProgress', function (file, percentage) {
        var $li = $('#' + file.id),
            $percent = $li.find('.progress span');
        // 避免重复创建
        if (!$percent.length) {
            $percent = $('<p class="progress"><span></span></p>')
                .appendTo($li)
                .find('span');
        }
        $percent.css('width', percentage * 100 + '%');
    });
    // 文件上传成功，给item添加成功class, 用样式标记上传成功。
    uploader.onUploadSuccess = function (file, response) {
        $('#' + file.id).addClass('upload-state-done');
        layer.msg('图片添加成功!', { icon: 1, time: 1200 });
        // 添加成功之后隐藏
        $('#filePicker').hide();
    };
    uploader.on("error", function (type) {
        if (type == "Q_EXCEED_SIZE_LIMIT") {
            layer.msg("图片大小不能超过2mb");
        } else {
            layer.msg("上传出错！请检查后重新上传！错误代码" + type);
        }
    });
    // 文件上传失败，显示上传出错。
    uploader.on('uploadError', function (file) {
        var $li = $('#' + file.id),
            $error = $li.find('div.error');
        // 避免重复创建
        if (!$error.length) {
            $error = $('<div class="error"></div>').appendTo($li);
        }
        $error.text('上传失败');
    });
    // 完成上传完了，成功或者失败，先删除进度条。
    uploader.on('uploadComplete', function (file) {
        $('#' + file.id).find('.progress').remove();
    });
})    
