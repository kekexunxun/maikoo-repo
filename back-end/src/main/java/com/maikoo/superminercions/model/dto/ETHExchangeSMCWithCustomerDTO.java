package com.maikoo.superminercions.model.dto;

import com.fasterxml.jackson.annotation.JsonUnwrapped;
import com.maikoo.superminercions.model.ETHSwapSMCDO;
import lombok.Data;

@Data
public class ETHExchangeSMCWithCustomerDTO {
    @JsonUnwrapped
    private ETHBuyWithCustomerDTO ethBuyWithCustomerDTO;

    public static ETHExchangeSMCWithCustomerDTO valueOf(ETHSwapSMCDO ethSwapSMCDO) {
        ETHExchangeSMCWithCustomerDTO ethExchangeSMCWithCustomerDTO = new ETHExchangeSMCWithCustomerDTO();
        ethExchangeSMCWithCustomerDTO.setEthBuyWithCustomerDTO(ETHBuyWithCustomerDTO.valueOf(ethSwapSMCDO));
        return ethExchangeSMCWithCustomerDTO;
    }
}
