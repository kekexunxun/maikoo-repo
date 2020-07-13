package com.maikoo.superminercions.model.dto;

import com.fasterxml.jackson.annotation.JsonIgnoreProperties;
import com.fasterxml.jackson.annotation.JsonInclude;
import com.fasterxml.jackson.annotation.JsonUnwrapped;
import com.fasterxml.jackson.databind.annotation.JsonSerialize;
import com.maikoo.superminercions.model.SMCLockDO;
import com.maikoo.superminercions.serialize.NullStringSerializer;
import lombok.Data;

import java.math.BigDecimal;

@JsonInclude(JsonInclude.Include.NON_NULL)
@Data
public class SMCLockWithCustomerDTO {
    private String userName;
    private String userMobile;
    @JsonSerialize(nullsUsing = NullStringSerializer.class)
    private String remark;
    private BigDecimal lpAward;
    private int lpDate;
    @JsonIgnoreProperties({"lp_reward"})
    @JsonUnwrapped
    private SMCLockInformationDTO smcLockInformationDTO;

    public static SMCLockWithCustomerDTO valueOf(SMCLockDO smcLockDO) {
        SMCLockWithCustomerDTO smcLockWithCustomerDTO = new SMCLockWithCustomerDTO();
        smcLockWithCustomerDTO.setSmcLockInformationDTO(SMCLockInformationDTO.valueOf(smcLockDO));
        smcLockWithCustomerDTO.setUserName(smcLockDO.getCustomerDO().getName());
        smcLockWithCustomerDTO.setUserMobile(smcLockDO.getCustomerDO().getPhone());
        smcLockWithCustomerDTO.setLpAward(smcLockWithCustomerDTO.getSmcLockInformationDTO().getLpReward());
        smcLockWithCustomerDTO.setLpDate(smcLockDO.getSmcLockCycleDO().getCycle());
        smcLockWithCustomerDTO.setRemark(smcLockDO.getNote());
        if (smcLockWithCustomerDTO.getSmcLockInformationDTO().getFinishAt() == null) {
            smcLockWithCustomerDTO.getSmcLockInformationDTO().setFinishAt("");
        }
        return smcLockWithCustomerDTO;
    }
}
