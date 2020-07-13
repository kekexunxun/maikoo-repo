package com.maikoo.superminercions.model.query;

import lombok.Data;

import javax.validation.constraints.NotNull;

@Data
public class CustomerAliQuery {
    @NotNull
    private String alipayAccount;
    private String verifyCode;
}
