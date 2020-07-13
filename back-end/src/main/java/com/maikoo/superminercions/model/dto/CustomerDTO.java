package com.maikoo.superminercions.model.dto;

import com.maikoo.superminercions.model.CustomerDO;
import com.maikoo.superminercions.util.ConstantUtil;
import com.maikoo.superminercions.util.TimeUtil;
import lombok.Data;

@Data
public class CustomerDTO {
    private long userId;
    private String userMobile;
    private String userName;
    private String createdAt;
    private String loginAccount;
    private String status;

    public static CustomerDTO valueOf(CustomerDO customerDO) {
        CustomerDTO customerDTO = new CustomerDTO();
        customerDTO.setUserId(customerDO.getId());
        customerDTO.setLoginAccount(customerDO.getUsername());
        customerDTO.setUserName(customerDO.getName());
        customerDTO.setUserMobile(customerDO.getPhone());
        customerDTO.setStatus(customerDO.isDisable() ? "禁用" : "启用");
        customerDTO.setCreatedAt(TimeUtil.timeStampToDateTime(customerDO.getCreatedAt()).format(ConstantUtil.BASE_DATE_TIME_PATTERN));
        return customerDTO;
    }
}
