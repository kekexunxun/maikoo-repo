package com.maikoo.superminercions.model.dto;

import com.fasterxml.jackson.annotation.JsonIgnoreProperties;
import com.fasterxml.jackson.annotation.JsonInclude;
import com.fasterxml.jackson.annotation.JsonUnwrapped;
import com.maikoo.superminercions.model.SMCLockDO;
import com.maikoo.superminercions.util.ConstantUtil;
import com.maikoo.superminercions.util.TimeUtil;
import lombok.Data;

import java.math.BigDecimal;

@Data
@JsonInclude(JsonInclude.Include.NON_NULL)
public class SMCLockInformationDTO {
    @JsonUnwrapped
    @JsonIgnoreProperties({"lp_date"})
    private SMCLockDTO smcLockDTO;
    private BigDecimal lpReward;
    private String applyAt;
    private String finishAt;

    public static SMCLockInformationDTO valueOf(SMCLockDO smcLockDO){
        BigDecimal reward = new BigDecimal(smcLockDO.getSmcLockCycleDO().getReward());
        BigDecimal hundred = new BigDecimal(100);
        SMCLockInformationDTO smcLockInformationDTO = new SMCLockInformationDTO();
        smcLockInformationDTO.setSmcLockDTO(SMCLockDTO.valueOf(smcLockDO));
        smcLockInformationDTO.setLpReward(smcLockDO.getQuantity().multiply(reward).divide(hundred).setScale(6, BigDecimal.ROUND_HALF_EVEN));
        smcLockInformationDTO.setApplyAt(TimeUtil.timeStampToDateTime(smcLockDO.getCreatedAt()).format(ConstantUtil.ORDER_DATE_TIME_PATTERN));

        if (smcLockDO.getStatus() == ConstantUtil.ORDER_STATUS_SMC_TRADING_COMPLETED) {
            long finishTimeStamp = smcLockDO.getUpdatedAt() == null ? smcLockDO.getCreatedAt() : smcLockDO.getUpdatedAt();
            smcLockInformationDTO.setFinishAt(TimeUtil.timeStampToDateTime(finishTimeStamp).format(ConstantUtil.ORDER_DATE_TIME_PATTERN));
        }

        return smcLockInformationDTO;
    }
}
