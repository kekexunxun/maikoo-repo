package com.maikoo.superminercions.model.query;

import com.maikoo.superminercions.validator.ETHSwapSMC;
import com.maikoo.superminercions.validator.SMCSwapETH;
import lombok.Data;

import javax.validation.constraints.DecimalMin;
import javax.validation.constraints.NotNull;
import java.math.BigDecimal;

@Data
public class ETHSwapSMCQuery extends TradingQuery {
    @DecimalMin("0.001")
    @NotNull(groups = {ETHSwapSMC.class})
    private BigDecimal ethNum;
    @DecimalMin("0.001")
    @NotNull(groups = {SMCSwapETH.class})
    private BigDecimal smcNum;
}
