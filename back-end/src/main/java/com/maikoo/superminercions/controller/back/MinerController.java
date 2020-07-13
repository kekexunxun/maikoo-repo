package com.maikoo.superminercions.controller.back;

import com.maikoo.superminercions.model.dto.ProductBackDTO;
import com.maikoo.superminercions.model.dto.ResponseDTO;
import com.maikoo.superminercions.model.query.ProductQuery;
import com.maikoo.superminercions.service.ProductService;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Controller;
import org.springframework.validation.annotation.Validated;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.ResponseBody;

import java.util.List;

@ResponseBody
@Controller
@RequestMapping("/api/admin/miner")
public class MinerController {

    @Autowired
    private ProductService productService;

    @RequestMapping("/list")
    public ResponseDTO<List<ProductBackDTO>> getMinerList(){
        return new ResponseDTO(200,"获取成功",productService.backList());
    }

    @RequestMapping("/update")
    public ResponseDTO updateMiner(@Validated ProductQuery productQuery){
        productService.updateMiner(productQuery);
        return new ResponseDTO(200, "更新成功", null);
    }

    @RequestMapping("/add")
    public ResponseDTO addMiner(@Validated ProductQuery productQuery){
        productService.addMiner(productQuery);
        return new ResponseDTO(200, "添加成功", null);
    }
}
