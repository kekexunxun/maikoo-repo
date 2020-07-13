<?php
namespace app\index\controller;

use \think\File;
use \think\Loader;

class Excel
{
    public function test()
    {
        //获取你要导出的数据，你要获取的到数据库的数据
        // $data = M("user")->field("id,username,password,img")->order("id DESC")->limit(520)->select();\
        $data[] = array(
            'order_id'   => '2',
            'order_sn'   => '200',
            'username'   => 'test',
            'total_fee'  => '100',
            'phone'      => '110',
            'address'    => 'china',
            'message'    => 'test666',
            'pay_at'     => '1',
            'created_at' => '1',
            'finish_at'  => '1',
            'cancel_at'  => '1',
            'status'     => '1',
            'goods_name' => 'test777',
            'quantity'   => '99',
            'fee'        => '100',
            'mch_name'   => 'market',
            // 'fee'=>'100',
        );
        //设置要导出excel的表头
        $fileheader = array('ID', '订单编号', '用户名称', '总费用', '电话号码', '地址', '用户留言', '付款时间', '创建时间', '结束时间', '取消时间', '状态', '商品名称', '商品数量', '商品单价', '店铺名称');

        $path = $this->exportExcel($data, 'test123', $fileheader, 'Sheet1');
        return $path;
    }

    /**
     * 导出excel
     * @param array $data 导入数据
     * @param string $savefile 导出excel文件名
     * @param array $fileheader excel的表头
     * @param string $sheetname sheet的标题名
     */
    public function exportExcel($data, $savefile, $fileheader, $sheetname)
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
        //根据有生成的excel多少列，$letter长度要大于等于这个值
        $letter = array('A', 'B', 'C', 'D', 'E', 'F', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P');
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
        }
        //单独设置D列宽度为15
        $objActSheet->getColumnDimension('D')->setWidth(15);
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
                        //下边两行不知道对图片单元格的格式有什么作用，有知道的要告诉我哟^_^
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
        $objWriter->save(DEFAULT_STATIC_PATH . "excel" . DS . $savefile . '.xlsx');

        $baseUrl      = "https://store.up.maikoo.cn/static";
        $downloadPath = $baseUrl . DS . "excel" . DS . $savefile . '.xlsx';
        // if(file_exists($downloadPath)){
        //     readfile($downloadPath);
        // }
        return $downloadPath;
    }

}
