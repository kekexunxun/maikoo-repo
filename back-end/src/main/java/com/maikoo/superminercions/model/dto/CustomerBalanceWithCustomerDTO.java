package com.maikoo.superminercions.model.dto;

import com.maikoo.superminercions.model.CustomerDO;
import com.maikoo.superminercions.util.ConstantUtil;
import com.maikoo.superminercions.util.TimeUtil;
import lombok.Data;

import java.math.BigDecimal;

@Data
public class CustomerBalanceWithCustomerDTO {
    private long userId;
    private String userName;
    private String userMobile;
    private BigDecimal eth;
    private BigDecimal smc;
    private String createdAt;

    public static CustomerBalanceWithCustomerDTO valueOf(CustomerDO customerDO){
        CustomerBalanceWithCustomerDTO customerBalanceWithCustomerDTO = new CustomerBalanceWithCustomerDTO();
        customerBalanceWithCustomerDTO.setUserId(customerDO.getId());
        customerBalanceWithCustomerDTO.setUserName(customerDO.getName());
        customerBalanceWithCustomerDTO.setUserMobile(customerDO.getPhone());
        customerBalanceWithCustomerDTO.setEth(customerDO.getEthBalance());
        customerBalanceWithCustomerDTO.setSmc(customerDO.getSmcBalance());
        customerBalanceWithCustomerDTO.setCreatedAt(TimeUtil.timeStampToDateTime(customerDO.getCreatedAt()).format(ConstantUtil.ORDER_DATE_TIME_PATTERN));
        return customerBalanceWithCustomerDTO;
    }
}
