package com.maikoo.superminercions.model.query;

import com.fasterxml.jackson.annotation.JsonProperty;
import lombok.Data;

import javax.validation.constraints.DecimalMin;
import javax.validation.constraints.Min;
import javax.validation.constraints.NotNull;

@Data
public class SMCLockCycleQuery {
    @JsonProperty("lpId")
    @NotNull
    private long lpId;
    @JsonProperty("lpDate")
    @Min(1)
    @NotNull
    private int lpDate;
    @JsonProperty("lpRate")
    @DecimalMin("0.01")
    @NotNull
    private float lpRate;
}
