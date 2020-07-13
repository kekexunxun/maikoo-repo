package com.maikoo.superminercions.model.dto;

import com.maikoo.superminercions.model.ProductDO;
import lombok.Data;

import java.math.BigDecimal;

@Data
public class ProductDTO {
    private String minerSn;
    private String minerName;
    private String minerModel;
    private BigDecimal minerPrice;

    public static ProductDTO valueOf(ProductDO productDO){
        ProductDTO productDTO = new ProductDTO();
        productDTO.setMinerSn(productDO.getProductNumber());
        productDTO.setMinerName(productDO.getName());
        productDTO.setMinerModel(productDO.getModel());
        productDTO.setMinerPrice(productDO.getPrice());
        return productDTO;
    }
}
