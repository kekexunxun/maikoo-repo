package com.maikoo.superminercions.model.dto;

import com.maikoo.superminercions.model.OrderDO;
import lombok.Data;

import java.math.BigDecimal;

@Data
public class ETHSellDO extends OrderDO {
    private BigDecimal currentPrice;
    private BigDecimal price;
    private BigDecimal quantity;
}
