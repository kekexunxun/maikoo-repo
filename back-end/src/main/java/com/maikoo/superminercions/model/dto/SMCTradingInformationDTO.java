package com.maikoo.superminercions.model.dto;

import com.fasterxml.jackson.annotation.JsonInclude;
import com.fasterxml.jackson.annotation.JsonUnwrapped;
import com.maikoo.superminercions.model.SMCOrderDO;
import com.maikoo.superminercions.util.ConstantUtil;
import com.maikoo.superminercions.util.TimeUtil;
import lombok.Data;

import java.math.BigDecimal;

@JsonInclude(JsonInclude.Include.NON_NULL)
@Data
public class SMCTradingInformationDTO {
    @JsonUnwrapped
    private SMCTradingDTO smcTradingDTO;
    private BigDecimal listPrice;
    private String applyAt;
    private String finishAt;
    private String remark;

    public static SMCTradingInformationDTO valueOf(SMCOrderDO smcOrderDO) {
        SMCTradingInformationDTO smcTradingInformationDTO = new SMCTradingInformationDTO();
        smcTradingInformationDTO.setSmcTradingDTO(SMCTradingDTO.valueOf(smcOrderDO));

        BigDecimal totalPrice = smcTradingInformationDTO.getSmcTradingDTO().getSmcNum().multiply(smcTradingInformationDTO.getSmcTradingDTO().getSmcPrice());
        if(smcOrderDO.getType() == ConstantUtil.SMC_TRADING_SELL){
            totalPrice = totalPrice.subtract(smcOrderDO.getFee());
        }

        smcTradingInformationDTO.setListPrice(totalPrice.setScale(6, BigDecimal.ROUND_HALF_EVEN));
        smcTradingInformationDTO.setApplyAt(TimeUtil.timeStampToDateTime(smcOrderDO.getCreatedAt()).format(ConstantUtil.ORDER_DATE_TIME_PATTERN));

        if (smcOrderDO.getStatus() == ConstantUtil.ORDER_STATUS_SMC_TRADING_COMPLETED || smcOrderDO.getStatus() == ConstantUtil.ORDER_STATUS_SMC_TRADING_REJECTED) {
            long finishTimeStamp = smcOrderDO.getUpdatedAt() == null ? smcOrderDO.getCreatedAt() : smcOrderDO.getUpdatedAt();
            smcTradingInformationDTO.setFinishAt(TimeUtil.timeStampToDateTime(finishTimeStamp).format(ConstantUtil.ORDER_DATE_TIME_PATTERN));
        }

        if (smcOrderDO.getStatus() == ConstantUtil.ORDER_STATUS_SMC_TRADING_REJECTED) {
            smcTradingInformationDTO.setRemark(smcOrderDO.getNote());
        }

        return smcTradingInformationDTO;
    }
}
