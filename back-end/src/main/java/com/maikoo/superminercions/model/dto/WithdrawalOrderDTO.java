package com.maikoo.superminercions.model.dto;

import com.maikoo.superminercions.model.WithdrawalOrderDO;
import com.maikoo.superminercions.util.StatusUtil;
import lombok.Data;

import java.math.BigDecimal;

@Data
public class WithdrawalOrderDTO {
    private long listSN;
    private BigDecimal smcNum;
    private BigDecimal smcPrice;
    private String status;

    public static WithdrawalOrderDTO valueOf(WithdrawalOrderDO withdrawalOrderDO){
        WithdrawalOrderDTO withdrawalOrderDTO = new WithdrawalOrderDTO();
        withdrawalOrderDTO.setListSN(withdrawalOrderDO.getOrderSN());
        withdrawalOrderDTO.setSmcNum(withdrawalOrderDO.getQuantity());
        withdrawalOrderDTO.setSmcPrice(withdrawalOrderDO.getPrice());
        withdrawalOrderDTO.setStatus(StatusUtil.commonOrderStatus(withdrawalOrderDO.getStatus()));
        return withdrawalOrderDTO;
    }
}
