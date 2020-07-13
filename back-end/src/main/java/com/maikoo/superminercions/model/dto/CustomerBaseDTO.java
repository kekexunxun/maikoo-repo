package com.maikoo.superminercions.model.dto;

import com.maikoo.superminercions.model.CustomerDO;
import lombok.Data;

@Data
public class CustomerBaseDTO {
    private long userId;
    private String userName;
    private String userMobile;
    private String identId;
    private String alipay;
    private String bankcard;
    private String bankName;
    private String walletAddress;

    public static CustomerBaseDTO valueOf(CustomerDO customerDO){
        CustomerBaseDTO customerBaseDTO = new CustomerBaseDTO();
        customerBaseDTO.setUserId(customerDO.getId());
        customerBaseDTO.setUserName(customerDO.getName());
        customerBaseDTO.setUserMobile(customerDO.getPhone());
        customerBaseDTO.setIdentId(customerDO.getIdCard());
        customerBaseDTO.setAlipay(customerDO.getAli());
        customerBaseDTO.setBankcard(customerDO.getBankCardNumber());
        customerBaseDTO.setBankName(customerDO.getBank());
        customerBaseDTO.setWalletAddress(customerDO.getWallet());
        return customerBaseDTO;
    }
}
