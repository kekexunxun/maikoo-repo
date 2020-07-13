package com.maikoo.businessdirectory.model;

import lombok.Data;

@Data
public class CommunityUserFavDO {
    private long idx;
    private long userId;
    private CommunityUserDO communityUserDO;
    private long createdAt;
}
