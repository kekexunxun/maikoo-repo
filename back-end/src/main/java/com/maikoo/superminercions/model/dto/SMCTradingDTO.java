package com.maikoo.superminercions.model.dto;

import com.maikoo.superminercions.model.SMCOrderDO;
import com.maikoo.superminercions.util.StatusUtil;
import lombok.Data;

import java.math.BigDecimal;

@Data
public class SMCTradingDTO {
    private long listSN;
    private BigDecimal smcNum;
    private BigDecimal smcPrice;
    private String status;

    public static SMCTradingDTO valueOf(SMCOrderDO smcOrderDO) {
        SMCTradingDTO smcTradingDTO = new SMCTradingDTO();
        smcTradingDTO.setListSN(smcOrderDO.getOrderSN());
        smcTradingDTO.setSmcNum(smcOrderDO.getQuantity());
        smcTradingDTO.setSmcPrice(smcOrderDO.getType() == 0 ? smcOrderDO.getBuyingPrice() : smcOrderDO.getPrice());
        smcTradingDTO.setStatus(StatusUtil.tradingStatus(smcOrderDO.getStatus()));
        return smcTradingDTO;
    }
}
