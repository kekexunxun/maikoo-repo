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
public class ETHExchangeSMCInformationDTO {
    @JsonUnwrapped
    private ETHExchangeSMCDTO ethExchangeSMCDTO;
    private BigDecimal ethNum;
    private String applyAt;
    private String finishAt;
    private String remark;

    public static ETHExchangeSMCInformationDTO valueOf(ETHSwapSMCDO ethSwapSMCDO){
        ETHExchangeSMCInformationDTO ethExchangeSMCInformationDTO = new ETHExchangeSMCInformationDTO();
        ethExchangeSMCInformationDTO.setEthExchangeSMCDTO(ETHExchangeSMCDTO.valueOf(ethSwapSMCDO));
        ethExchangeSMCInformationDTO.setEthNum(ethSwapSMCDO.getEthQuantity());
        ethExchangeSMCInformationDTO.setApplyAt(TimeUtil.timeStampToDateTime(ethSwapSMCDO.getCreatedAt()).format(ConstantUtil.ORDER_DATE_TIME_PATTERN));
        if (ethSwapSMCDO.getStatus() == ConstantUtil.ORDER_STATUS_COMPLETED || ethSwapSMCDO.getStatus() == ConstantUtil.ORDER_STATUS_REJECTED) {
            ethExchangeSMCInformationDTO.setFinishAt(TimeUtil.timeStampToDateTime(ethSwapSMCDO.getUpdatedAt()).format(ConstantUtil.ORDER_DATE_TIME_PATTERN));
        }

        if (ethSwapSMCDO.getStatus() == ConstantUtil.ORDER_STATUS_REJECTED) {
            ethExchangeSMCInformationDTO.setRemark(ethSwapSMCDO.getNote());
        }

        return ethExchangeSMCInformationDTO;
    }
}
