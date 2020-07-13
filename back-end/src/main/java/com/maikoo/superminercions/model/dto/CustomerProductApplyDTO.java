package com.maikoo.superminercions.model.dto;

import com.fasterxml.jackson.databind.annotation.JsonSerialize;
import com.maikoo.superminercions.model.CustomerProductApplyDO;
import com.maikoo.superminercions.serialize.NullStringSerializer;
import com.maikoo.superminercions.util.ConstantUtil;
import com.maikoo.superminercions.util.TimeUtil;
import lombok.Data;

import java.math.BigDecimal;

@Data
public class CustomerProductApplyDTO {
    private long listSn;
    private String userName;
    private String userMobile;
    private String minerName;
    private String minerModel;
    private BigDecimal minerCountingForce;
    private BigDecimal minerPrice;
    private String applyAt;
    private String status;
    @JsonSerialize(nullsUsing = NullStringSerializer.class)
    private String remark;

    public static CustomerProductApplyDTO valueOf(CustomerProductApplyDO customerProductApplyDO) {
        CustomerProductApplyDTO customerProductApplyDTO = new CustomerProductApplyDTO();
        customerProductApplyDTO.setListSn(customerProductApplyDO.getOrderSN());
        customerProductApplyDTO.setUserName(customerProductApplyDO.getCustomerDO().getName());
        customerProductApplyDTO.setUserMobile(customerProductApplyDO.getCustomerDO().getPhone());
        customerProductApplyDTO.setMinerName(customerProductApplyDO.getProductDO().getName());
        customerProductApplyDTO.setMinerModel(customerProductApplyDO.getProductDO().getModel());
        customerProductApplyDTO.setMinerCountingForce(customerProductApplyDO.getProductDO().getPerformance());
        customerProductApplyDTO.setMinerPrice(customerProductApplyDO.getProductDO().getPrice());
        customerProductApplyDTO.setApplyAt(TimeUtil.timeStampToDateTime(customerProductApplyDO.getCreatedAt()).format(ConstantUtil.ORDER_DATE_TIME_PATTERN));
        customerProductApplyDTO.setStatus(customerProductApplyDO.getStatus() == ConstantUtil.ORDER_STATUS_PROCESSING ? "未处理" : "已处理");
        customerProductApplyDTO.setRemark(customerProductApplyDO.getNote());
        return customerProductApplyDTO;
    }
}
