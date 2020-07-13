<?php
namespace app\index\controller;

use app\index\model\Catagory;
use app\index\model\Goods as GoodsDb;
use app\index\model\Order as OrderDb;
use \think\File;
use \think\Loader;
use \think\Session;

class Excel
{
    /**
     * 订单表格下载
     * @param  string $startTime 开始时间
     * @param  string $endTime   时间结束
     * @return string            路径
     */
    public function orderExcel($startTime, $endTime)
    {
        $order = new OrderDb;
        //获取你要导出的数据，即获取的到数据库的数据
        $orderList = $order->alias('o')->join('mk_order_detail d', 'o.order_id = d.order_id', 'LEFT')->join('mk_merchant m', 'o.mch_id = m.mch_id', 'LEFT')->join('mk_goods g', 'd.goods_id = g.goods_id', 'LEFT')->field('o.order_id, o.order_sn, o.username, o.total_fee, o.phone, o.address, o.message, o.pay_at, o.created_at, o.finish_at, o.cancel_at, o.status, g.goods_name, d.quantity, d.fee, m.mch_name')->where('o.created_at', 'between', [$startTime, $endTime])->select();
        // 数据非空时
        if (!$orderList || count($orderList) == 0) {
            return null;
        }
        $orderList = collection($orderList)->toArray();
        foreach ($orderList as &$info) {
            $info['pay_at']     = !empty($info['pay_at']) ? date('Y-m-d H:i:s', $info['pay_at']) : '';
            $info['finish_at']  = !empty($info['finish_at']) ? date('Y-m-d H:i:s', $info['finish_at']) : '';
            $info['cancel_at']  = !empty($info['cancel_at']) ? date('Y-m-d H:i:s', $info['cancel_at']) : '';
            $info['created_at'] = !empty($info['created_at']) ? date('Y-m-d H:i:s', $info['created_at']) : '';
            $info['mch_name']   = htmlspecialchars_decode($info['mch_name']);
            $info['goods_name'] = htmlspecialchars_decode($info['goods_name']);
            switch ($info['status']) {
                case 1:
                    $info['status'] = '未付款';
                    break;
                case 2:
                    $info['status'] = '待发货';
                    break;
                case 3:
                    $info['status'] = '已发货';
                    break;
                case 4:
                    $info['status'] = '待评价';
                    break;
                case 5:
                    $info['status'] = '已完成';
                    break;
                case 6:
                    $info['status'] = '已取消';
                    break;
            }
        }
        // dump($orderList);die;
        $data = $orderList;
        // $data[] = array(
        //     'order_id'   => '2',
        //     'order_sn'   => '200',
        //     'username'   => 'test',
        //     'total_fee'  => '100',
        //     'phone'      => '110',
        //     'address'    => 'china',
        //     'message'    => 'test666',
        //     'pay_at'     => '1',
        //     'created_at' => '1',
        //     'finish_at'  => '1',
        //     'cancel_at'  => '1',
        //     'status'     => '1',
        //     'goods_name' => 'test777',
        //     'quantity'   => '99',
        //     'fee'        => '100',
        //     'mch_name'   => 'market',
        // );
        //设置要导出excel的表头
        $fileheader = array('ID', '订单编号', '用户名称', '总费用', '电话号码', '地址', '用户留言', '付款时间', '创建时间', '结束时间', '取消时间', '状态', '商品名称', '商品数量', '商品单价', '店铺名称');
        // 输出的excel名称
        $excelName = date("y-m-d H:i", time());
        $path      = $this->exportExcel($data, $excelName, $fileheader, 'Sheet1');
        return $path;
    }

    /**
     * 导出excel
     * @param array $data 导入数据
     * @param string $savefile 导出excel文件名
     * @param array $fileheader excel的表头
     * @param string $sheetname sheet的标题名
     * @param string $isselect  是否带下拉框
     */
    public function exportExcel($data, $savefile, $fileheader, $sheetname, $isselect = null)
    {
        //引入phpexcel核心文件
        // import("Org.Util.PHPExcel");
        // import("Org.Util.PHPExcel.Reader.Excel2007");
        // $path = dirname(__FILE__); //找到当前脚本所在路径
        Loader::import('PHPExcel.PHPExcel'); //必须手动导入，否则会报PHPExcel类找不到
        Loader::import('PHPExcel.PHPExcel.Reader.Excel2007');
        Loader::import('PHPExcel.PHPExcel.Worksheet.Drawing');
        Loader::import('PHPExcel.PHPExcel.Writer.Excel2007');
        Loader::import('PHPExcel.PHPExcel.IOFactory.PHPExcel_IOFactory'); //引入IOFactory.php 文件里面的PHPExcel_IOFactory这个类

        //new一个PHPExcel类，或者说创建一个excel，tp中“\”不能掉
        $excel = new \PHPExcel();
        if (is_null($savefile)) {
            $savefile = time();
        } else {
            //防止中文命名，下载时ie9及其他情况下的文件名称乱码
            iconv('UTF-8', 'GB2312', $savefile);
        }
        //设置excel属性
        $objActSheet = $excel->getActiveSheet();
        if ($data != '') {
            //根据有生成的excel多少列，$letter长度要大于等于这个值
            $letter = array('A', 'B', 'C', 'D', 'E', 'F', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P');
        } else {
            //根据有生成的excel多少列，$letter长度要大于等于这个值
            $letter = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I');
        }
        //设置当前的sheet
        $excel->setActiveSheetIndex(0);
        //设置sheet的name
        $objActSheet->setTitle($sheetname);
        //设置表头
        for ($i = 0; $i < count($fileheader); $i++) {
            //单元宽度自适应,1.8.1版本phpexcel中文支持勉强可以，自适应后单独设置宽度无效
            //$objActSheet->getColumnDimension("$letter[$i]")->setAutoSize(true);
            //设置表头值，这里的setCellValue第二个参数不能使用iconv，否则excel中显示false
            $objActSheet->setCellValue("$letter[$i]1", $fileheader[$i]);
            //设置表头字体样式
            $objActSheet->getStyle("$letter[$i]1")->getFont()->setName('微软雅黑');
            //设置表头字体大小
            $objActSheet->getStyle("$letter[$i]1")->getFont()->setSize(12);
            //设置表头字体是否加粗
            $objActSheet->getStyle("$letter[$i]1")->getFont()->setBold(true);
            //设置表头文字垂直居中
            $objActSheet->getStyle("$letter[$i]1")->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            //设置文字上下居中
            $objActSheet->getStyle($letter[$i])->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
            //设置表头外的文字垂直居中
            $excel->setActiveSheetIndex(0)->getStyle($letter[$i])->getAlignment()->setHorizontal(\PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            /*设置金额字段格式为2位小数点*/
            $excel->getActiveSheet()->getStyle('C')->getNumberFormat()->setFormatCode("0.00");
            $excel->getActiveSheet()->getStyle('D')->getNumberFormat()->setFormatCode("0.00");
            $excel->getActiveSheet()->getStyle('E')->getNumberFormat()->setFormatCode("0.00");
        }
        //单独设置D列宽度为15
        $objActSheet->getColumnDimension('D')->setWidth(15);
        if ($data != '') {
            //这里$i初始值设置为2，$j初始值设置为0
            for ($i = 2; $i <= count($data) + 1; $i++) {
                $j = 0;
                foreach ($data[$i - 2] as $key => $value) {
                    //不是图片时将数据加入到excel，这里数据库存的图片字段是img
                    if ($key != 'img') {
                        $objActSheet->setCellValue("$letter[$j]$i", $value);
                    }
                    //是图片是加入图片到excel
                    if ($key == 'img') {
                        if ($value != '') {
                            $value = iconv("UTF-8", "GB2312", $value); //防止中文命名的文件
                            // 图片生成
                            $objDrawing[$key] = new \PHPExcel_Worksheet_Drawing();
                            // 图片地址
                            $objDrawing[$key]->setPath('.\Uploads' . $value);
                            // 设置图片宽度高度
                            $objDrawing[$key]->setHeight('80px'); //照片高度
                            $objDrawing[$key]->setWidth('80px'); //照片宽度
                            // 设置图片要插入的单元格
                            $objDrawing[$key]->setCoordinates('D' . $i);
                            // 图片偏移距离
                            $objDrawing[$key]->setOffsetX(12);
                            $objDrawing[$key]->setOffsetY(12);
                            //下边两行图片单元格的格式有作用
                            //$objDrawing[$key]->getShadow()->setVisible(true);
                            //$objDrawing[$key]->getShadow()->setDirection(50);
                            $objDrawing[$key]->setWorksheet($objActSheet);
                        }
                    }
                    $j++;
                }
                //设置单元格高度，暂时没有找到统一设置高度方法
                $objActSheet->getRowDimension($i)->setRowHeight('80px');
            }
        }
        // 带下拉框
        if ($isselect) {
            // 查询商品二级分类数据
            $catagory = new Catagory;
            $catData  = $catagory->field('cname')->where('parent_id', '<>', 0)->where('status', 1)->select();
            $catData  = collection($catData)->toArray();
            if (!$catData) {
                return objReturn(400, '导出失败！请检查是否添加商品分类！');
                exit;
            } else {
                // 数组重组
                foreach ($catData as $key => $value) {
                    $list[] = $value['cname'];
                }
                // 定义下拉框
                foreach ($catData as $k => $v) {
                    /*设置下拉*/
                    $str            = implode(',', $list);
                    $objValidation1 = $excel->getActiveSheet()->getCell('B' . ($k + 2))->getDataValidation(); //从第二行开始有下拉样式
                    $objValidation1->setType(\PHPExcel_Cell_DataValidation::TYPE_LIST)
                        ->setErrorStyle(\PHPExcel_Cell_DataValidation::STYLE_INFORMATION)
                        ->setAllowBlank(false)
                        ->setShowInputMessage(true)
                        ->setShowErrorMessage(true)
                        ->setShowDropDown(true)
                        ->setErrorTitle('输入的值有误')
                        ->setError('您输入的值不在下拉框列表内.')
                    // ->setPromptTitle('')
                        ->setPrompt('')
                        ->setFormula1('"' . $str . '"');
                };
            }
        }
        // 文件重命名
        // $fileName = iconv("utf-8", "gb2312", $fileName);
        ob_end_clean(); //清除缓冲区,避免乱码
        header('Content-Type: application/vnd.ms-excel');
        // 下载的excel文件名称，Excel5后缀为xls
        header('Content-Disposition: attachment;filename="' . $savefile . '.xlsx"');
        header('Cache-Control: max-age=0');

        // IE9
        // header('Cache-Control: max-age=1');
        // 用户下载excel
        // $objWriter = \PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
        // dump($objWriter);die;
        // $objWriter ->save('php://output');

        // 保存excel在服务器上
        $objWriter = new \PHPExcel_Writer_Excel2007($excel);
        $objWriter->save(DEFAULT_STATIC_PATH . "excel" . DS . 'export' . DS . $savefile . '.xlsx');

        $baseUrl      = "https://xnps.up.maikoo.cn/static";
        $downloadPath = $baseUrl . DS . "excel" . DS . 'export' . DS . $savefile . '.xlsx';
        // if(file_exists($downloadPath)){
        //     readfile($downloadPath);
        // }
        return $downloadPath;
    }

    //取出excel中的数据
    public function getExcelData($filename, $exts)
    {
        //导入PHPExcel类库
        // import("Org.Util.PHPExcel");
        Loader::import('PHPExcel.PHPExcel'); //必须手动导入，否则会报PHPExcel类找不到
        //不同类型的文件导入不同的类
        if ($exts == 'xls') {
            Loader::import("PHPExcel.PHPExcel.Reader.Excel5");
            $PHPReader = new \PHPExcel_Reader_Excel5();
        } else if ($exts == 'xlsx') {
            Loader::import("PHPExcel.PHPExcel.Reader.Excel2007");
            $PHPReader = new \PHPExcel_Reader_Excel2007();
        }
        //载入文件
        $PHPExcel = $PHPReader->load($filename);
        //获取表中的第一个工作表，如果要获取第二个，把0改为1，依次类推
        $currentSheet = $PHPExcel->getSheet(0);
        //获取总列数
        $allColumn = $currentSheet->getHighestColumn();
        //获取总行数
        $allRow = $currentSheet->getHighestRow();
        //循环获取表中的数据，$currentRow表示当前行，从哪行开始读取数据，索引值从0开始
        for ($currentRow = 1; $currentRow <= $allRow; $currentRow++) {
            //从哪列开始，A表示第一列
            for ($currentColumn = 'A'; $currentColumn <= $allColumn; $currentColumn++) {
                //数据坐标
                $address = $currentColumn . $currentRow;
                //读取到的数据，保存到数组$arr中
                $data[$currentRow][$currentColumn] = $currentSheet->getCell($address)->getValue();
            }
        }
        // @unlink ( $filename ); //删除上传的文件
        $res = $this->importData($data);
        return $res;
    }

    /**
     * 导入excel数据
     * @param  ary  $data 读取到的excel数据
     * @return ary        导入结果
     */
    public function importData($data)
    {
        //检测模版是否标准
        $title = array(
            'A' => '商品名称',
            'B' => '商品分类',
            'C' => '商品店铺价格',
            'D' => '商品实际售价',
            'E' => '商品会员售价',
            'F' => '商品关键词',
            'G' => '商品排序',
            'H' => '商品单位',
            // 'I' => '购买商品赠送的积分',
        );
        if ($title != $data[1]) {
            return objReturn(400, '您的模版不正确，请下载标准模版！');
            exit;
        }
        // data不为空
        if (count($data) < 2) {
            return objReturn(0, '导入失败！excel数据不完整！');
            exit;
        }
        // 数据字段非空判断
        foreach ($data as $key => $value) {
            if (empty($value)) {
                return objReturn(400, '导入失败！excel数据不完整！');
                // break 1;
            }
        }
        // 查询商品二级分类数据
        $catagory = new Catagory;
        $catData  = $catagory->field('cat_id, cname')->where('parent_id', '<>', 0)->where('status', 1)->select();
        if (!$catData) {
            return objReturn(400, '导入失败！请检查是否添加商品分类！');
            exit;
        }
        $dataList = [];
        // 去掉表头
        unset($data[1]);
        // dump($data);die;
        // 构造数组存入数据库
        foreach ($data as $k => $v) {
            // if ($k > 1) {
            $temp['goods_name']   = $v['A'];
            $temp['shop_price']   = $v['C'];
            $temp['market_price'] = $v['D'];
            $temp['member_price'] = $v['E'];
            $temp['keywords']     = $v['F'];
            $temp['sort']         = $v['G'];
            $temp['unit']         = $v['H'];
            // $temp['points']       = $v['I'];
            $temp['goods_sn']     = time();
            $temp['status']       = 1;
            $temp['created_at']   = time();
            // 分类的id
            foreach ($catData as $key => $value) {
                if ($v['B'] == $value['cname']) {
                    $temp['cat_id'] = $value['cat_id'];
                }
            }
            $dataList[] = $temp;

            // $dataList[$k-2]['goods_id'] = $v['A'];
            // // $dataList[$k-2]['cat_id']     = $v['B'];
            // $dataList[$k-2]['shop_price'] = $v['C'];
            // $dataList[$k-2]['market_price'] = $v['D'];
            // $dataList[$k-2]['member_price'] = $v['E'];
            // $dataList[$k-2]['keywords'] = $v['F'];
            // $dataList[$k-2]['sort'] = $v['G'];
            // $dataList[$k-2]['unit'] = $v['H'];
            // $dataList[$k-2]['points'] = $v['I'];
            // // 分类的id
            // foreach ($catData as $key => $value) {
            //     if($v['B'] == $value['cname']){
            //         $dataList[$k-2]['cat_id'] = $value['cat_id'];
            //     }
            // }
            // }
        }
        // dump($dataList);die;
        //批量导入数组键名必须从0开始
        $goods  = new GoodsDb;
        $result = $goods->isUpdate(false)->saveAll($dataList);
        // dump($result);die;
        if ($result) {
            // 写入数据库成功后删除Session
            Session::delete('excelPath');
            return objReturn(0, '导入成功！');
        } else {
            return objReturn(0, '导入失败！');
        }
    }

    // 输入excel模板
    public function template()
    {
        //设置要导出excel的表头
        $fileheader = array(
            '商品名称',
            '商品分类',
            '商品店铺价格',
            '商品实际售价',
            '商品会员售价',
            '商品关键词',
            '商品排序',
            '商品单位',
            // '购买商品赠送的积分',
        );
        // 模板名称
        $templateName = date("y-m-d", time());
        // 数据
        $data = '';
        // 下拉框 第二列
        $select = 'B';
        $path   = $this->exportExcel($data, $templateName, $fileheader, 'Sheet1', $select);
        return $path;
    }

}
