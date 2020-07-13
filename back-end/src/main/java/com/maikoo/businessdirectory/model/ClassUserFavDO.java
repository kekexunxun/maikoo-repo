package com.maikoo.businessdirectory.model;

import lombok.Data;

@Data
public class ClassUserFavDO {
    //用于用户的收藏
    private long idx;
    private long userId;
    private ClassUserDO classUserDO;
    private long createdAt;
}
