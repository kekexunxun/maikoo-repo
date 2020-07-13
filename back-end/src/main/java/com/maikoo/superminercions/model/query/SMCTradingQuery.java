package com.maikoo.superminercions.model.query;

import com.maikoo.superminercions.validator.SMCBuy;
import com.maikoo.superminercions.validator.SMCSell;
import lombok.Data;

import javax.validation.constraints.DecimalMin;
import javax.validation.constraints.NotNull;
import java.math.BigDecimal;

@Data
public class SMCTradingQuery extends TradingQuery{
    @DecimalMin("0.000001")
    @NotNull(groups = {SMCBuy.class})
    private BigDecimal buyInPrice;
    @DecimalMin("0.000001")
    @NotNull(groups = {SMCBuy.class, SMCSell.class})
    private BigDecimal smcNum;
}
