package com.maikoo.businessdirectory.model;

import lombok.Data;

@Data
public class UserDO {
    private long userId;
    private String openid;
    private String unionId;
    private String sessionKey;
    private String nickname;
    //用户微信头像
    private String avatarUrl;
    private String mobile;
    private Boolean isAuth;
    private long createdAt;

}
