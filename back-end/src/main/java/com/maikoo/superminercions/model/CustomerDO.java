package com.maikoo.superminercions.model;

import lombok.Data;

import java.math.BigDecimal;

@Data
public class CustomerDO {
    private Long id;
    private Long userSN;
    private String username;
    private String password;
    private String passwordPhone;
    private boolean isUpdatedPassword;
    private String tradingPassword;
    private String tradingPasswordPhone;
    private boolean isUpdatedTransactionPassword;
    private String profilePhoto;
    private String account;
    private String name;
    private String phone;
    private String idCard;
    private String frontIdCardUri;
    private String backIdCardUri;
    private boolean isUpdatedAuthentication;
    private String wallet;
    private BigDecimal balance;
    private BigDecimal availableBalance;
    private BigDecimal smcBalance;
    private BigDecimal availableSMCBalance;
    private BigDecimal ethBalance;
    private BigDecimal availableETHBalance;
    private String ali;
    private boolean isBindAli;
    private String wechat;
    private String bank;
    private String bankBranch;
    private String bankCardNumber;
    private boolean isBindBank;
    private boolean isDisable;
    private Long createdAt;
}
