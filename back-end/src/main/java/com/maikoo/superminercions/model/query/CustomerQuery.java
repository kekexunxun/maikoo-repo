package com.maikoo.superminercions.model.query;

import lombok.Data;

@Data
public class CustomerQuery {
    private long userId;
    private String userName;
    private String userMobile;
    private String loginAccount;
    private String loginPassword;
    private String transPassword;
}
