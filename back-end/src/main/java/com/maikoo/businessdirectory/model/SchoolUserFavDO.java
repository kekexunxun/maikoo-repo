package com.maikoo.businessdirectory.model;

import lombok.Data;

@Data
public class SchoolUserFavDO {
    //用于用户的收藏
    private long idx;
    private long userId;
    private SchoolUserDO schoolUserDO;
    private long createdAt;
}
