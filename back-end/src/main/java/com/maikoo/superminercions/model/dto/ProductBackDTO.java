package com.maikoo.superminercions.model.dto;

import com.maikoo.superminercions.model.ProductDO;
import com.maikoo.superminercions.util.ConstantUtil;
import com.maikoo.superminercions.util.TimeUtil;
import lombok.Data;

import java.math.BigDecimal;

@Data
public class ProductBackDTO {
    private long minerId;
    private String minerName;
    private String minerModel;
    private BigDecimal minerPrice;
    private BigDecimal minerCountingForce;
    private String createdAt;

    public static ProductBackDTO valueOf(ProductDO productDO){
        ProductBackDTO productBackDTO = new ProductBackDTO();
        productBackDTO.setMinerId(productDO.getId());
        productBackDTO.setMinerName(productDO.getName());
        productBackDTO.setMinerModel(productDO.getModel());
        productBackDTO.setMinerPrice(productDO.getPrice());
        productBackDTO.setMinerCountingForce(productDO.getPerformance());
        productBackDTO.setCreatedAt(TimeUtil.timeStampToDateTime(Long.valueOf(productDO.getCreatedAt())).format(ConstantUtil.BASE_DATE_TIME_PATTERN));
        return productBackDTO;
    }

}
