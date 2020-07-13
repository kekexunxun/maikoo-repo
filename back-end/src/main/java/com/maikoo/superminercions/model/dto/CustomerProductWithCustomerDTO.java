package com.maikoo.superminercions.model.dto;

import com.maikoo.superminercions.model.CustomerProductDO;
import com.maikoo.superminercions.util.ConstantUtil;
import com.maikoo.superminercions.util.TimeUtil;
import lombok.Data;

import java.math.BigDecimal;

@Data
public class CustomerProductWithCustomerDTO {
    private long listSn;
    private String userName;
    private String userMobile;
    private String minerName;
    private String minerModel;
    private BigDecimal minerCurrentCountingForce;
    private BigDecimal minerCumulativeProduction;
    private String createdAt;
    private String status;

    public static CustomerProductWithCustomerDTO valueOf(CustomerProductDO customerProductDO) {
        CustomerProductWithCustomerDTO customerProductWithCustomerDTO = new CustomerProductWithCustomerDTO();
        customerProductWithCustomerDTO.setListSn(customerProductDO.getId());
        customerProductWithCustomerDTO.setUserName(customerProductDO.getCustomerDO().getName());
        customerProductWithCustomerDTO.setUserMobile(customerProductDO.getCustomerDO().getPhone());
        customerProductWithCustomerDTO.setMinerName(customerProductDO.getName());
        customerProductWithCustomerDTO.setMinerModel(customerProductDO.getModel());
        customerProductWithCustomerDTO.setMinerCumulativeProduction(customerProductDO.getOutput());
        customerProductWithCustomerDTO.setMinerCurrentCountingForce(customerProductDO.getPerformance());
        customerProductWithCustomerDTO.setCreatedAt(TimeUtil.timeStampToDateTime(customerProductDO.getCreatedAt()).format(ConstantUtil.ORDER_DATE_TIME_PATTERN));
        customerProductWithCustomerDTO.setStatus(customerProductDO.isDisable() ? "停止" : "启动");
        return customerProductWithCustomerDTO;
    }
}
