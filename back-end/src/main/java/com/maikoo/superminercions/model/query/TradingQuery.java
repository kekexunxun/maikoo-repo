package com.maikoo.superminercions.model.query;

import lombok.Data;

import javax.validation.constraints.NotNull;

@Data
public class TradingQuery {
    @NotNull
    private String transPass;
}
