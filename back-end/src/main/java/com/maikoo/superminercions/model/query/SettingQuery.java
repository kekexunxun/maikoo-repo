package com.maikoo.superminercions.model.query;

import com.fasterxml.jackson.annotation.JsonProperty;
import lombok.Data;

import javax.validation.constraints.DecimalMin;
import javax.validation.constraints.NotNull;
import java.math.BigDecimal;
import java.util.List;

@Data
public class SettingQuery {
    @JsonProperty("accessToken")
    @NotNull
    private String accessToken;
    @JsonProperty("smc2rmb")
    @DecimalMin("0.001")
    @NotNull
    private BigDecimal smc2rmb;
    @JsonProperty("eth2rmb")
    @DecimalMin("0.001")
    @NotNull
    private BigDecimal eth2rmb;
    @JsonProperty("smcExtractRate")
    @DecimalMin("0.01")
    @NotNull
    private float smcExtractRate;
    @JsonProperty("smc2rmbRate")
    @DecimalMin("0.01")
    @NotNull
    private float smc2rmbRate;
    @JsonProperty("lpList")
    private List<SMCLockCycleQuery> lpList;
}
