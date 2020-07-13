package com.maikoo.superminercions.model.dto;

import com.maikoo.superminercions.model.ETHSwapSMCDO;
import com.maikoo.superminercions.util.StatusUtil;
import lombok.Data;

import java.math.BigDecimal;

@Data
public class ETHBuyDTO {
    private long listSn;
    private BigDecimal ethNum;
    private String status;

    public static ETHBuyDTO valueOf(ETHSwapSMCDO ethSwapSMCDO){
        ETHBuyDTO ethBuyDTO = new ETHBuyDTO();
        ethBuyDTO.setListSn(ethSwapSMCDO.getOrderSN());
        ethBuyDTO.setEthNum(ethSwapSMCDO.getEthQuantity());
        ethBuyDTO.setStatus(StatusUtil.commonOrderStatus(ethSwapSMCDO.getStatus()));
        return ethBuyDTO;
    }
}
