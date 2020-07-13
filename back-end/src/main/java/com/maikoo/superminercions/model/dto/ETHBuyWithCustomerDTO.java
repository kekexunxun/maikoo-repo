package com.maikoo.superminercions.model.dto;

import com.fasterxml.jackson.annotation.JsonUnwrapped;
import com.maikoo.superminercions.model.ETHSwapSMCDO;
import lombok.Data;

import java.math.BigDecimal;

@Data
public class ETHBuyWithCustomerDTO {
    private String userName;
    private String userMobile;
    private BigDecimal smcPrice;
    private BigDecimal ethPrice;
    @JsonUnwrapped
    private ETHBuyInformationDTO ethBuyInformationDTO;

    public static ETHBuyWithCustomerDTO valueOf(ETHSwapSMCDO ethSwapSMCDO) {
        ETHBuyWithCustomerDTO ethBuyWithCustomerDTO = new ETHBuyWithCustomerDTO();
        ethBuyWithCustomerDTO.setEthBuyInformationDTO(ETHBuyInformationDTO.valueOf(ethSwapSMCDO));
        ethBuyWithCustomerDTO.setUserName(ethSwapSMCDO.getCustomerDO().getName());
        ethBuyWithCustomerDTO.setUserMobile(ethSwapSMCDO.getCustomerDO().getPhone());
        ethBuyWithCustomerDTO.setEthPrice(ethSwapSMCDO.getEthPrice());
        ethBuyWithCustomerDTO.setSmcPrice(ethSwapSMCDO.getSmcPrice());
        if (ethBuyWithCustomerDTO.getEthBuyInformationDTO().getRemark() == null) {
            ethBuyWithCustomerDTO.getEthBuyInformationDTO().setRemark(ethSwapSMCDO.getNote() == null ? "" : ethSwapSMCDO.getNote());
        }
        if (ethBuyWithCustomerDTO.getEthBuyInformationDTO().getFinishAt() == null) {
            ethBuyWithCustomerDTO.getEthBuyInformationDTO().setFinishAt("");
        }
        return ethBuyWithCustomerDTO;
    }
}
