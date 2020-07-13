package com.maikoo.superminercions.model.dto;

import com.fasterxml.jackson.annotation.JsonInclude;
import com.fasterxml.jackson.annotation.JsonUnwrapped;
import com.maikoo.superminercions.model.ETHSwapSMCDO;
import com.maikoo.superminercions.util.ConstantUtil;
import com.maikoo.superminercions.util.TimeUtil;
import lombok.Data;

import java.math.BigDecimal;

@Data
@JsonInclude(JsonInclude.Include.NON_NULL)
public class ETHBuyInformationDTO {
    @JsonUnwrapped
    private ETHBuyDTO ethBuyDTO;
    private BigDecimal smcNum;
    private String applyAt;
    private String finishAt;
    private String remark;

    public static ETHBuyInformationDTO valueOf(ETHSwapSMCDO ethSwapSMCDO){
        ETHBuyInformationDTO ethBuyInformationDTO = new ETHBuyInformationDTO();
        ethBuyInformationDTO.setEthBuyDTO(ETHBuyDTO.valueOf(ethSwapSMCDO));
        ethBuyInformationDTO.setSmcNum(ethSwapSMCDO.getSmcQuantity());
        ethBuyInformationDTO.setApplyAt(TimeUtil.timeStampToDateTime(ethSwapSMCDO.getCreatedAt()).format(ConstantUtil.ORDER_DATE_TIME_PATTERN));
        if (ethSwapSMCDO.getStatus() == ConstantUtil.ORDER_STATUS_COMPLETED || ethSwapSMCDO.getStatus() == ConstantUtil.ORDER_STATUS_REJECTED) {
            ethBuyInformationDTO.setFinishAt(TimeUtil.timeStampToDateTime(ethSwapSMCDO.getUpdatedAt()).format(ConstantUtil.ORDER_DATE_TIME_PATTERN));
        }

        if (ethSwapSMCDO.getStatus() == ConstantUtil.ORDER_STATUS_REJECTED) {
            ethBuyInformationDTO.setRemark(ethSwapSMCDO.getNote());
        }
        return ethBuyInformationDTO;
    }
}
