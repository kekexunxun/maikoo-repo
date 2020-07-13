package com.maikoo.businessdirectory.model.query;

import lombok.Data;

@Data
public class UserQuery {
    private String username;
    private String password;
    private String nickName;
    private String avatarUrl;
    private String oriPass;
    private String newPass;
}
