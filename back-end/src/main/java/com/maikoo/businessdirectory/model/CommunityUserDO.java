package com.maikoo.businessdirectory.model;

import com.maikoo.businessdirectory.model.constant.CommunityTypeEnum;
import com.maikoo.businessdirectory.model.constant.GenderEnum;
import com.maikoo.businessdirectory.model.query.GroupUserQuery;
import lombok.Data;

@Data
public class CommunityUserDO {
    private long idx;
    private UserDO userDO;
    private String name;
    private int gender;
    //用户身份 1 业主 2 物业
    private int type;
    private String mobile;
    private String company;
    //用户职位
    private String position;
    //用户群中状态 1 正常 2 已被移除群聊
    private String brief;
    private int status;
    private CommunityGroupDO communityGroupDO;
    private CommunityUserDO processedRemoveCommunityUserDO;
    private String communityName;
    private int building;
    private int room;
    private long joinedAt;
    private long quitedAt;
    private String search;

    public static CommunityUserDO valueOf(GroupUserQuery groupUserQuery) {
        CommunityUserDO communityUserDO = new CommunityUserDO();

        communityUserDO.setName(groupUserQuery.getName());
        communityUserDO.setMobile(groupUserQuery.getMobile());
        communityUserDO.setPosition(groupUserQuery.getPosition());
        communityUserDO.setCompany(groupUserQuery.getCompany());
        communityUserDO.setMobile(groupUserQuery.getMobile());
        communityUserDO.setBrief(groupUserQuery.getBrief());
        if (groupUserQuery.getUserType() != null) {
            communityUserDO.setType(CommunityTypeEnum.valueOf(groupUserQuery.getUserType()).getIntStatus());
        }
        communityUserDO.setRoom(groupUserQuery.getRoom());
        communityUserDO.setBuilding(groupUserQuery.getBuilding());
        communityUserDO.setSearch(groupUserQuery.getSearch());

        GenderEnum genderEnum = GenderEnum.stringGenderToEnum(groupUserQuery.getGender());
        if (genderEnum != null) {
            communityUserDO.setGender(genderEnum.getIntStatus());
        }

        if (groupUserQuery.getGroupId() > 0) {
            CommunityGroupDO communityGroupDO = new CommunityGroupDO();
            communityGroupDO.setGroupId(groupUserQuery.getGroupId());
            communityUserDO.setCommunityGroupDO(communityGroupDO);
        }

        if (groupUserQuery.getUserId() > 0) {
            UserDO userDO = new UserDO();
            userDO.setUserId(groupUserQuery.getUserId());
        }
        return communityUserDO;
    }
}
