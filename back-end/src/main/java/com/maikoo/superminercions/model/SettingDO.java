package com.maikoo.superminercions.model;

import lombok.Data;

import java.math.BigDecimal;

@Data
public class SettingDO {
    private BigDecimal smcPrice;
    private BigDecimal ethPrice;
    private Float withdrawalFee;
    private Float smcFee;
}
