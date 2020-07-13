package com.maikoo.superminercions.model.dto;

import com.maikoo.superminercions.model.SMCLockCycleDO;
import lombok.Data;

@Data
public class SMCLockCycleDTO {
    private long lpId;
    private int lpDate;
    private float lpRate;

    public static SMCLockCycleDTO valueOf(SMCLockCycleDO smcLockCycleDO) {
        SMCLockCycleDTO smcLockCycleDTO = new SMCLockCycleDTO();
        smcLockCycleDTO.setLpId(smcLockCycleDO.getId());
        smcLockCycleDTO.setLpDate(smcLockCycleDO.getCycle());
        smcLockCycleDTO.setLpRate(smcLockCycleDO.getReward());
        return smcLockCycleDTO;
    }
}
