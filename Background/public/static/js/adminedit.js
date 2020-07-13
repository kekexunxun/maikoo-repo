 // 点击选择管理员类型
$("#admin_type").click(function(){
    // console.log($(this).val());
    $(".merchant").hide();
    $(".area_code").hide();
    if($(this).val()==2){
        $(".merchant").show();
    }else{
        $(".merchant").hide();
    }
    if($(this).val()==3){
        $(".area_code").show();
    }else{
        $(".area_code").hide();
    }  
})
// 提交表单
function addadmin_save_submit() {
    var admin_id = $('#admin_id').val();
    var admin_name = $('#admin_username').val();
    var admin_active = $('#admin_active option:selected').val();
    var admin_type = $('#admin_type option:selected').val();
    var password = md5($('#password').val());
    var password1 = md5($('#password1').val());
    var password2 = md5($('#password2').val());
    var role_id = $('#role option:selected').val();
    if(admin_name==''){
        layer.msg('管理员名称不能为空！',{icon:2,time:1000});
        return false;
    }
    if($('#password').val() ==''){
        layer.msg('初始密码不能为空！',{icon:2,time:1000});
        return false;
    }
    if($('#password1').val() == '' || $('#password2').val() == ''){
        password1 = 0;
        // layer.msg('密码不能为空！',{icon:2,time:1000});
        // return false;
    }
    if($('#password1').val() != $('#password2').val()){
        layer.msg('两次输入的密码不一致！',{icon:2,time:1000});
        return false;
    }
    var province = 0;
    var city = 0;
    var area = 0;
    if($('#admin_type option:selected').val() == 3){
        mch_id = 0;
        province = $('#province option:selected').val();
        city = $('#city option:selected').val();
        area = $('#area option:selected').val();
    }
    if($('#admin_type option:selected').val() == 2){
        var mch_id = $('#mch option:selected').val();
    }
    if($('#admin_type option:selected').val() == 1){
        var mch_id = 0;
    }
    // 发送ajax请求
    $.ajax({
        type: 'POST',
        url: '../../editAdmin',
        dataType: 'json',
        data: {admin_id:admin_id,password: password,password1: password1,admin_name: admin_name,
            admin_active: admin_active,mch_id:mch_id,role_id:role_id,
            admin_type:admin_type,province:province,city:city,area:area},
        success: function(res){
            if(res.code == '0'){
                index = parent.layer.getFrameIndex(window.name);
                layer.msg(res.msg,{icon:1,time:1000});
                setTimeout('parent.layer.close(index);',1000);
            }
            if(res.code == '400'){
                layer.msg(res.msg,{icon:2,time:1000});
            }
        },
        error:function(res) {
            layer.msg('网络错误，请稍后重试!',{icon:2,time:1000});
        },
    });
    return false;
}