package com.maikoo.superminercions.model.dto;

import lombok.Data;

import java.math.BigDecimal;

@Data
public class ExchangeRateDTO {
    private BigDecimal smc2rmb;
    private BigDecimal eth2rmb;
}
