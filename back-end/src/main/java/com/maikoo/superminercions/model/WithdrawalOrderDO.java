package com.maikoo.superminercions.model;

import lombok.Data;

import java.math.BigDecimal;

@Data
public class WithdrawalOrderDO extends OrderDO{
    private BigDecimal smcBalance;
    private BigDecimal quantity;
    private BigDecimal price;
    private BigDecimal fee;
    private Integer method;
}
