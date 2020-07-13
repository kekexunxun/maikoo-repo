package com.maikoo.superminercions.model.query;

import lombok.Data;

import javax.validation.constraints.DecimalMin;
import javax.validation.constraints.Max;
import javax.validation.constraints.Min;
import javax.validation.constraints.NotNull;
import java.math.BigDecimal;

@Data
public class WithdrawalOrderQuery {
    @DecimalMin("0.000001")
    @NotNull
    private BigDecimal smcNum;
    @Max(2)
    @Min(1)
    private int extractTo;
}
