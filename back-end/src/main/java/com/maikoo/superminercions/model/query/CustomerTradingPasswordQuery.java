package com.maikoo.superminercions.model.query;

import lombok.Data;

import javax.validation.constraints.NotNull;
import javax.validation.constraints.Pattern;

@Data
public class CustomerTradingPasswordQuery {
    @Pattern(regexp = "^(?=\\d{11}$)^1(?:3\\d|4[57]|5[^4\\D]|66|7[^249\\D]|8\\d|9[89])\\d{8}$")
    @NotNull
    private String mobile;
    @NotNull
    private String password;
    private String verifyCode;
}
