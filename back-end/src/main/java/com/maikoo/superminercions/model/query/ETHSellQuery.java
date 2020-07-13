package com.maikoo.superminercions.model.query;

import lombok.Data;

import javax.validation.constraints.DecimalMin;
import javax.validation.constraints.NotNull;
import java.math.BigDecimal;

@Data
public class ETHSellQuery extends TradingQuery {
    @DecimalMin("0.001")
    @NotNull
    private BigDecimal ethNum;
    @DecimalMin("0.000001")
    @NotNull
    private BigDecimal ethPrice;
}
