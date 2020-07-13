package com.maikoo.superminercions.controller.front;

import com.maikoo.superminercions.model.dto.ProductDTO;
import com.maikoo.superminercions.model.dto.ProductInformationDTO;
import com.maikoo.superminercions.model.dto.ResponseDTO;
import com.maikoo.superminercions.service.ProductService;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Controller;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RequestParam;
import org.springframework.web.bind.annotation.ResponseBody;

import java.util.List;

@Controller
@ResponseBody
@RequestMapping("/api/product")
public class ProductController {
    @Autowired
    private ProductService productService;

    @RequestMapping
    public ResponseDTO<List<ProductDTO>> list() {
        ResponseDTO<List<ProductDTO>> productResponseDTO = new ResponseDTO<>(200, "获取成功", productService.list());
        return productResponseDTO;
    }

    @RequestMapping("/information")
    public ResponseDTO<ProductInformationDTO> information(@RequestParam("minerSn") String productNumber) {
        ResponseDTO<ProductInformationDTO> productInformationResponseDTO = new ResponseDTO<>(200, "获取成功", productService.information(productNumber));
        return productInformationResponseDTO;
    }

    @RequestMapping("/buy")
    public ResponseDTO buy(@RequestParam("minerSn") String productNumber){
        productService.buy(productNumber);
        return new ResponseDTO<>(200, "申请成功", null);
    }
}
