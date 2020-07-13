package com.maikoo.superminercions.model.dto;

import com.maikoo.superminercions.util.StatusUtil;
import lombok.Data;

import java.math.BigDecimal;

@Data
public class ETHSellDTO {
    private long listSn;
    private BigDecimal ethNum;
    private String status;

    public static ETHSellDTO valueOf(ETHSellDO ethSellDO) {
        ETHSellDTO ethSellDTO = new ETHSellDTO();
        ethSellDTO.setListSn(ethSellDO.getOrderSN());
        ethSellDTO.setEthNum(ethSellDO.getQuantity());
        ethSellDTO.setStatus(StatusUtil.commonOrderStatus(ethSellDO.getStatus()));
        return ethSellDTO;
    }
}
