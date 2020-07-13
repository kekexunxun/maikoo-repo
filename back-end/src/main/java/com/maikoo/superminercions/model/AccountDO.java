package com.maikoo.superminercions.model;

import lombok.Data;

@Data
public class AccountDO<T> {
    /**
     * 0表示客户端用户，
     * 1表示后台用户。
     */
    private int accountType;
    private T userDO;
}
