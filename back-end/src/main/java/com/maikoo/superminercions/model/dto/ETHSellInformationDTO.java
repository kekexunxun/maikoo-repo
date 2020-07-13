package com.maikoo.superminercions.model.dto;

import com.fasterxml.jackson.annotation.JsonInclude;
import com.fasterxml.jackson.annotation.JsonUnwrapped;
import com.maikoo.superminercions.util.ConstantUtil;
import com.maikoo.superminercions.util.TimeUtil;
import lombok.Data;

import java.math.BigDecimal;

@Data
@JsonInclude(JsonInclude.Include.NON_NULL)
public class ETHSellInformationDTO {
    @JsonUnwrapped
    private ETHSellDTO ethSellDTO;
    private BigDecimal ethPrice;
    private String applyAt;
    private String finishAt;
    private String remark;

    public static ETHSellInformationDTO valueOf(ETHSellDO ethSellDO) {
        ETHSellInformationDTO ethSellInformationDTO = new ETHSellInformationDTO();
        ethSellInformationDTO.setEthSellDTO(ETHSellDTO.valueOf(ethSellDO));
        ethSellInformationDTO.setEthPrice(ethSellDO.getPrice());
        ethSellInformationDTO.setApplyAt(TimeUtil.timeStampToDateTime(ethSellDO.getCreatedAt()).format(ConstantUtil.ORDER_DATE_TIME_PATTERN));
        if (ethSellDO.getStatus() == ConstantUtil.ORDER_STATUS_COMPLETED || ethSellDO.getStatus() == ConstantUtil.ORDER_STATUS_REJECTED) {
            ethSellInformationDTO.setFinishAt(TimeUtil.timeStampToDateTime(ethSellDO.getUpdatedAt()).format(ConstantUtil.ORDER_DATE_TIME_PATTERN));
        }

        if (ethSellDO.getStatus() == ConstantUtil.ORDER_STATUS_REJECTED) {
            ethSellInformationDTO.setRemark(ethSellDO.getNote());
        }
        return ethSellInformationDTO;
    }
}
