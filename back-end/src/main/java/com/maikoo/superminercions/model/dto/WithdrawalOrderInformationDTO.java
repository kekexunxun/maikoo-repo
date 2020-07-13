package com.maikoo.superminercions.model.dto;

import com.fasterxml.jackson.annotation.JsonInclude;
import com.fasterxml.jackson.annotation.JsonUnwrapped;
import com.maikoo.superminercions.model.WithdrawalOrderDO;
import com.maikoo.superminercions.util.ConstantUtil;
import com.maikoo.superminercions.util.TimeUtil;
import lombok.Data;

import java.math.BigDecimal;

@JsonInclude(JsonInclude.Include.NON_NULL)
@Data
public class WithdrawalOrderInformationDTO {
    @JsonUnwrapped
    private WithdrawalOrderDTO withdrawalOrderDTO;
    private BigDecimal listPrice;
    private String applyAt;
    private String finishAt;
    private String remark;

    public static WithdrawalOrderInformationDTO valueOf(WithdrawalOrderDO withdrawalOrderDO) {
        WithdrawalOrderInformationDTO withdrawalOrderInformationDTO = new WithdrawalOrderInformationDTO();

        withdrawalOrderInformationDTO.setWithdrawalOrderDTO(WithdrawalOrderDTO.valueOf(withdrawalOrderDO));
        withdrawalOrderInformationDTO.setListPrice(withdrawalOrderDO.getPrice().multiply(withdrawalOrderDO.getQuantity()).subtract(withdrawalOrderDO.getFee()).setScale(6, BigDecimal.ROUND_HALF_EVEN));
        withdrawalOrderInformationDTO.setApplyAt(TimeUtil.timeStampToDateTime(withdrawalOrderDO.getCreatedAt()).format(ConstantUtil.ORDER_DATE_TIME_PATTERN));
        if (withdrawalOrderDO.getStatus() == ConstantUtil.ORDER_STATUS_COMPLETED || withdrawalOrderDO.getStatus() == ConstantUtil.ORDER_STATUS_REJECTED) {
            withdrawalOrderInformationDTO.setFinishAt(TimeUtil.timeStampToDateTime(withdrawalOrderDO.getUpdatedAt()).format(ConstantUtil.ORDER_DATE_TIME_PATTERN));
        }
        if (withdrawalOrderDO.getStatus() == ConstantUtil.ORDER_STATUS_REJECTED) {
            withdrawalOrderInformationDTO.setRemark(withdrawalOrderDO.getNote());
        }

        return withdrawalOrderInformationDTO;
    }
}
