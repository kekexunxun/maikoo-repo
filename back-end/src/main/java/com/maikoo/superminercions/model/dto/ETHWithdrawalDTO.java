package com.maikoo.superminercions.model.dto;

import com.maikoo.superminercions.model.ETHWithdrawalDO;
import com.maikoo.superminercions.util.StatusUtil;
import lombok.Data;

import java.math.BigDecimal;

@Data
public class ETHWithdrawalDTO {
    private long listSn;
    private BigDecimal ethNum;
    private String status;

    public static ETHWithdrawalDTO valueOf(ETHWithdrawalDO ethWithdrawalDO){
        ETHWithdrawalDTO ethWithdrawalDTO = new ETHWithdrawalDTO();
        ethWithdrawalDTO.setListSn(ethWithdrawalDO.getOrderSN());
        ethWithdrawalDTO.setEthNum(ethWithdrawalDO.getQuantity());
        ethWithdrawalDTO.setStatus(StatusUtil.commonOrderStatus(ethWithdrawalDO.getStatus()));
        return ethWithdrawalDTO;
    }
}
