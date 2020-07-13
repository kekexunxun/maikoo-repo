package com.maikoo.superminercions.model;

import lombok.Data;

import java.math.BigDecimal;

@Data
public class ETHSwapSMCDO extends OrderDO{
    private BigDecimal ethPrice;
    private BigDecimal smcPrice;
    private BigDecimal ethQuantity;
    private BigDecimal smcQuantity;
    private int type;
}
