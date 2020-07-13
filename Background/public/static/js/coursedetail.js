 // 初始化表格
$('.table-sort').dataTable({
        language: {
        "sProcessing": "处理中...",
        "sLengthMenu": "显示 _MENU_ 项结果",
        "sZeroRecords": "没有匹配结果",
        "sInfo": "显示第 _START_ 至 _END_ 项结果，共 _TOTAL_ 项",
        "sInfoEmpty": "显示第 0 至 0 项结果，共 0 项",
        "sInfoFiltered": "(由 _MAX_ 项结果过滤)",
        "sInfoPostFix": "",
        "sSearch": "从当前数据中检索： ",
        "sUrl": "",
        "sEmptyTable": "表中数据为空",
        "sLoadingRecords": "载入中...",
        "sInfoThousands": ",",
        "oPaginate": {
            "sFirst": "首页",
            "sPrevious": "上页",
            "sNext": "下页",
            "sLast": "末页"
        },
        "oAria": {
            "sSortAscending": ": 以升序排列此列",
            "sSortDescending": ": 以降序排列此列"
        }
    },
        "aaSorting": [[ 1, "desc" ]],//默认第几个排序

        "bStateSave": true,//状态保存

        "aoColumnDefs": [

            {"orderable":false,"aTargets":[]}// 制定列不参与排序

        ],
    "aaSorting": [[ 0, "desc" ]],//默认第几个排序
    "bStateSave": true,//状态保存
    "pading":false,
    "aoColumnDefs": [
        //{"bVisible": false, "aTargets": [ 3 ]} //控制列的隐藏显示
        {"orderable":false,"aTargets":[0,1,2]}// 不参与排序的列
    ],
});
// 日期规则
function selecttime(flag,unweekData,startTime,endTime){
    var ary = unweekData.split(",");// 在每个逗号,进行分解 
    var weekData = [];
    for (var i = 0; i < ary.length; i++) {
        weekData.push(ary[i]);
    }
    // console.log(weekData);
    if(flag==1){
        if(endTime != ""){
            WdatePicker({dateFmt:'yyyy-MM-dd', maxDate: endTime, minDate: startTime,disabledDates:weekData})
        }else{
            WdatePicker({dateFmt:'yyyy-MM-dd', maxDate: endTime, minDate: startTime, disabledDates:weekData})
        }
    }else{
        if(startTime != "" || !startTime){
            WdatePicker({dateFmt:'yyyy-MM-dd', minDate: startTime, maxDate: endTime})   
        }else{
            WdatePicker({dateFmt:'yyyy-MM-dd', minDate: startTime, maxDate: endTime})
        }
    }
}
// 日期选择
function selecttime1(flag,startTime,endTime){   
    if(flag==1){
        // var endTime = $("#countTimeend1").val();
        if(endTime != ""){
            WdatePicker({dateFmt:'HH:mm', minDate:startTime,maxDate:endTime})
        }else{
            WdatePicker({dateFmt:'HH:mm', minDate:startTime,maxDate:endTime})
        }
    }else{
        // var startTime = $("#countTimestart1").val();
        if(startTime != "" || !startTime){
            WdatePicker({dateFmt:'HH:mm', minDate:startTime,maxDate:endTime})
        }else{
            WdatePicker({dateFmt:'HH:mm', minDate:startTime,maxDate:endTime})
        }
    }
}
/*course-type1*/
function course_clock1(obj,uid,courseId){
    var nickname = $(obj).parents("tr").find(".td-nickname").html();
    $('#typenum').text(0);
    $("#coursetype").html('选择打卡时间');
    postajax(nickname,uid,courseId);
    // $("#modal-demo").modal("show");

}
// 打卡与补打卡公共ajax
function postajax(nickname,uid,courseId){
    // 发送ajax
    $.ajax({
        type: 'POST',
        url: '../../limitDay',
        dataType: 'json',
        data: {course_id:courseId,uid:uid},
        success: function (data) {
            if (data.code == "0") {
                $("#coursename").html(data.data['course_name']);
                $("#username").html(nickname);
                $("#countTimestart").focus(function(){
                    selecttime(1,data.data['not_course_day'],data.data['course_start_at_conv'],data.data['course_end_at_conv']);
                });
                $("#countTimestart1").focus(function(){
                    selecttime1(1,data.data['start_at'],data.data['end_at']);
                });
                $("#uid").text(uid);
                $("#course_id").val(courseId); 
                $("#modal-demo").modal("show");        
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
// 点击确定
$("#confirm").click(function(){
    if($('#typenum').text()==0){
        clock(0);
    }
    if($('#typenum').text()==1){
        clock(1);
    }    
});
// 打卡与补打卡公共方法
function clock(type){
    if(type==0){
        var clockType = 0;
    }
    if(type==1){
        var clockType = 1;
    }
    var course_id = $('#course_id').val();
    var clockTime = $("#countTimestart").val() +' '+$("#countTimestart1").val();
    var uid = $('#uid').text();
    // console.log(clockTime);
    if($("#countTimestart").val() ==''||$("#countTimestart1").val() ==''){
        layer.msg('请选择打卡时间！', { icon: 2, time: 1000 });
        return false;
    }
// 发送ajax
    $.ajax({
        type: 'POST',
        url: '../../clockIn',
        dataType: 'json',
        data: {course_id:course_id,clockTime:clockTime,clockType:clockType,uid:uid},
        success: function (data) {
            if (data.code == "0") {
                layer.msg(data.msg, { icon: 1, time: 1000 });
                $("#modal-demo").modal("hide");
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

/*course-type2*/
function course_clock2(obj,uid,courseId){
    var nickname = $(obj).parents("tr").find(".td-nickname").html();
    // 发送ajax
    $.ajax({
        type: 'POST',
        url: '../../limitDay',
        dataType: 'json',
        data: {course_id:courseId,uid:uid},
        success: function (data) {
            if (data.code == "0") {
                // console.log(data);
                $("#course_name").html(data.data['course_name']);
                $("#user_name").html(nickname);
                $("#countTimestart2").focus(function(){
                    selecttime(1,data.data['not_course_day'],data.data['course_start_at_conv'],data.data['course_end_at_conv']);
                });
                $("#countTimestart3").focus(function(){
                    selecttime(1,data.data['course_day'],data.data['course_start_at_conv'],data.data['course_end_at_conv']);
                });
                $("#uid2").text(uid);
                $("#course_id2").val(courseId); 
                $("#modal-demo2").modal("show");
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
$("#confirm2").click(function(){
    var course_id2 = $("#course_id2").val();
    var oriTime = $("#countTimestart2").val();
    var changeTime = $("#countTimestart3").val();
    var reason = $("#reason").val();
    // console.log(changeTime);
    var uid2 = $('#uid2').text();
    if(oriTime =='' || changeTime ==''){
        layer.msg('请选择调课时间！', { icon: 2, time: 1000 });
        return false;
    }    
    // 发送ajax        
    $.ajax({
        type: 'POST',
        url: '../../clockChange',
        dataType: 'json',
        data: {uid2:uid2,course_id2:course_id2,changeTime:changeTime,oriTime:oriTime,reason:reason},
        success: function (data) {
            if (data.code == "0") {
                layer.msg(data.msg, { icon: 1, time: 1000 });
                $("#modal-demo2").modal("hide");
            }
            if (data.code == '400') {
                layer.msg(data.msg, { icon: 2, time: 1000 });
            }
        },
        error: function () {
            layer.msg('网络错误，请稍后重试！', { icon: 2, time: 1000 });
        },
    });  
});

/*course-type3*/
function course_clock3(uid,courseId){
    layer.confirm('确认旷课吗？',function(index){
        // 发送ajax        
        $.ajax({
            type: 'POST',
            url: '../../clockOut',
            dataType: 'json',
            data: {uid:uid,course_id:courseId},
            success: function (data) {
                if (data.code == "0") {
                    layer.msg(data.msg, { icon: 1, time: 1000 });
                }
                if (data.code == '400') {
                    layer.msg(data.msg, { icon: 2, time: 1000 });
                }
            },
            error: function () {
                layer.msg('网络错误，请稍后重试！', { icon: 2, time: 1000 });
            },
        });        
    });
}
/*course-type4*/
function course_clock4(obj,uid,courseId){
    var nickname = $(obj).parents("tr").find(".td-nickname").html();
    $('#typenum').text(1);
    $("#coursetype").html('选择补打卡时间');
    postajax(nickname,uid,courseId);
} 
/*course-type5*/
function course_clock5(obj,uid,courseId){
    var nickname = $(obj).parents("tr").find(".td-nickname").html();
    var coursename = $(obj).parents("tr").find(".td-coursename").html();
    $("#course_user2").html(nickname);
    $("#course_name2").html(coursename);
    $.ajax({
        type: 'POST',
        url: '../../getCourseChange',
        dataType: 'json',
        data: {uid:uid,course_id:courseId},
        success: function (data) {
            if (data.code == "0") {
                $('#course_content').html('');
                for (var i = 0; i < data.data.length; i++) {
                    var div = $('<div class="change_msg"><span>'+(i+1)+'</span><span style="margin-left:20px;">原上课时间：<span class="ori_time">'+data.data[i].ori_course_at+'</span></span><span style="margin-left:20px;">调课的时间：<span class="new_time">'+data.data[i].new_course_at+'</span></span><span style="margin-left:20px;">调课原因：'+data.data[i].reason+'</span><a style="text-decoration:none;margin-left:20px;" class="ml-5" onClick="del_change(this,'+uid+','+courseId+')" href="javascript:;" title="取消调课"><i class="Hui-iconfont">&#xe6e2;</i></a></div>');   
                    $('#course_content').append(div);
                }
                $("#modal-demo3").modal("show");
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
    // 取消调课
function del_change(obj,uid,courseId){
    var oriTime = $(obj).parent().find(".ori_time").html();
    // console.log(oriTime);
    var newTime = $(obj).parent().find(".new_time").html();
    // console.log(newTime);
    layer.confirm('确认取消吗？',function(index){
        // 发送ajax        
        $.ajax({
            type: 'POST',
            url: '../../delChange',
            dataType: 'json',
            data: {uid:uid,course_id:courseId,oriTime:oriTime,newTime:newTime},
            success: function (data) {
                if (data.code == "0") {
                    layer.msg(data.msg, { icon: 1, time: 1000 });
                    $("#modal-demo3").modal("hide");
                }
                if (data.code == '400') {
                    layer.msg(data.msg, { icon: 2, time: 1000 });
                }
            },
            error: function () {
                layer.msg('网络错误，请稍后重试！', { icon: 2, time: 1000 });
            },
        });        
    });
}  