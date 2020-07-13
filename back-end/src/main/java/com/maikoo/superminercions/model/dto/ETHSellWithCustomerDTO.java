package com.maikoo.superminercions.model.dto;

import com.fasterxml.jackson.annotation.JsonInclude;
import com.fasterxml.jackson.annotation.JsonUnwrapped;
import com.fasterxml.jackson.databind.annotation.JsonSerialize;
import com.maikoo.superminercions.serialize.NullStringSerializer;
import com.maikoo.superminercions.util.ConstantUtil;
import com.maikoo.superminercions.util.TimeUtil;
import lombok.Data;

import java.math.BigDecimal;

@Data
@JsonInclude(JsonInclude.Include.NON_NULL)
public class ETHSellWithCustomerDTO {
    private String userName;
    private String userMobile;
    @JsonUnwrapped
    private ETHSellDTO ethSellDTO;
    private BigDecimal listFee;
    private BigDecimal ethPrice;
    private String applyAt;
    @JsonSerialize(nullsUsing = NullStringSerializer.class)
    private String finishAt;
    @JsonSerialize(nullsUsing = NullStringSerializer.class)
    private String remark;

    public static ETHSellWithCustomerDTO valueOf(ETHSellDO ethSellDO) {
        ETHSellWithCustomerDTO ethSellWithCustomerDTO = new ETHSellWithCustomerDTO();
        ethSellWithCustomerDTO.setUserName(ethSellDO.getCustomerDO().getName());
        ethSellWithCustomerDTO.setUserMobile(ethSellDO.getCustomerDO().getPhone());
        ethSellWithCustomerDTO.setEthSellDTO(ETHSellDTO.valueOf(ethSellDO));
        ethSellWithCustomerDTO.setListFee(ethSellDO.getQuantity().multiply(ethSellDO.getPrice()).setScale(6, BigDecimal.ROUND_HALF_EVEN));
        ethSellWithCustomerDTO.setEthPrice(ethSellDO.getCurrentPrice());
        ethSellWithCustomerDTO.setApplyAt(TimeUtil.timeStampToDateTime(ethSellDO.getCreatedAt()).format(ConstantUtil.ORDER_DATE_TIME_PATTERN));
        ethSellWithCustomerDTO.setRemark(ethSellDO.getNote());
        if (ethSellDO.getStatus() == ConstantUtil.ORDER_STATUS_COMPLETED || ethSellDO.getStatus() == ConstantUtil.ORDER_STATUS_REJECTED) {
            ethSellWithCustomerDTO.setFinishAt(TimeUtil.timeStampToDateTime(ethSellDO.getUpdatedAt()).format(ConstantUtil.ORDER_DATE_TIME_PATTERN));
        }
        return ethSellWithCustomerDTO;
    }
}
