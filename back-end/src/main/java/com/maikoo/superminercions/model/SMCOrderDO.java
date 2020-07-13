package com.maikoo.superminercions.model;

import lombok.Data;
import lombok.ToString;

import java.math.BigDecimal;

@Data
@ToString(callSuper = true)
public class SMCOrderDO extends OrderDO {
    private BigDecimal quantity;
    private BigDecimal price;
    private BigDecimal buyingPrice;
    private BigDecimal fee;
    private Integer type;
}
