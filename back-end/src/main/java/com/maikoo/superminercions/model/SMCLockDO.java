package com.maikoo.superminercions.model;

import lombok.Data;

import java.math.BigDecimal;

@Data
public class SMCLockDO extends OrderDO {
    private BigDecimal quantity;
    private SMCLockCycleDO smcLockCycleDO;
}
