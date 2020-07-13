package com.maikoo.superminercions.model.dto;

import com.fasterxml.jackson.annotation.JsonIgnoreProperties;
import com.fasterxml.jackson.annotation.JsonUnwrapped;
import com.maikoo.superminercions.model.ProductDO;
import lombok.Data;

import java.math.BigDecimal;

@Data
public class ProductInformationDTO {
    @JsonIgnoreProperties({"miner_img"})
    @JsonUnwrapped
    private ProductDTO productDTO;
    private BigDecimal minerCountingForce;

    public static ProductInformationDTO valueOf(ProductDO productDO) {
        ProductInformationDTO productInformationDTO = new ProductInformationDTO();
        productInformationDTO.setProductDTO(ProductDTO.valueOf(productDO));
        productInformationDTO.setMinerCountingForce(productDO.getPerformance());
        return productInformationDTO;
    }
}
