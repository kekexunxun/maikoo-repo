package com.maikoo.superminercions.model.query;

import lombok.Data;

import javax.validation.constraints.DecimalMin;
import javax.validation.constraints.NotNull;
import java.math.BigDecimal;

@Data
public class ETHWithdrawalQuery extends TradingQuery{
    @DecimalMin("0.001")
    @NotNull
    private BigDecimal ethNum;
    @NotNull
    private String walletAddress;
}
