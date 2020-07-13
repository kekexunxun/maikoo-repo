package com.maikoo.superminercions.model;

import lombok.Data;

import java.math.BigDecimal;

@Data
public class SMCPriceDO {
    private Long id;
    private Long date;
    private BigDecimal price;
}
