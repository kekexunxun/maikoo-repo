package com.maikoo.superminercions.model.query;

import lombok.Data;

import javax.validation.constraints.NotNull;

@Data
public class CustomerBankQuery {
    @NotNull
    private String bankName;
    @NotNull
    private String bankBranch;
    @NotNull
    private String bankCard;
    private String verifyCode;
}
