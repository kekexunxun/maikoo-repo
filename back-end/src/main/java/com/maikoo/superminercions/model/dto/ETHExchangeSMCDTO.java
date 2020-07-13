package com.maikoo.superminercions.model.dto;

import com.maikoo.superminercions.model.ETHSwapSMCDO;
import com.maikoo.superminercions.util.StatusUtil;
import lombok.Data;

import java.math.BigDecimal;

@Data
public class ETHExchangeSMCDTO {
    private long listSn;
    private BigDecimal smcNum;
    private String status;

    public static ETHExchangeSMCDTO valueOf(ETHSwapSMCDO ethSwapSMCDO){
        ETHExchangeSMCDTO ethExchangeSMCDTO = new ETHExchangeSMCDTO();
        ethExchangeSMCDTO.setListSn(ethSwapSMCDO.getOrderSN());
        ethExchangeSMCDTO.setSmcNum(ethSwapSMCDO.getSmcQuantity());
        ethExchangeSMCDTO.setStatus(StatusUtil.commonOrderStatus(ethSwapSMCDO.getStatus()));
        return ethExchangeSMCDTO;
    }
}
