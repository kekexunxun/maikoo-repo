package com.maikoo.superminercions.model.dto;

import lombok.Data;

import java.math.BigDecimal;
import java.util.List;

@Data
public class SettingDTO {
    private BigDecimal smc2rmb;
    private BigDecimal eth2rmb;
    private float smcExtractRate;
    private float smc2rmbRate;
    private List<SMCLockCycleDTO> lpList;
}
