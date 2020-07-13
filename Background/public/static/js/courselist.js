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

        "aocourseDefs": [

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
/*course-详情*/
function course_detail(id){
    var index = layer.open({
        type: 2,
        title: '课程详情',
        content: 'coursedetail/course_id/'+id,
        end: function(){
            location.replace(location.href);
        }
    });
    layer.full(index);
} 