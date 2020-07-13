package com.maikoo.businessdirectory.model;

import com.maikoo.businessdirectory.model.constant.CommunityTypeEnum;
import com.maikoo.businessdirectory.model.constant.GenderEnum;
import com.maikoo.businessdirectory.model.query.GroupUserQuery;
import lombok.Data;

@Data
public class CommunityUserApplyDO {
    private long idx;
    private UserDO userDO;
    private String name;
    private int gender;
    private String mobile;
    private String company;
    private String position;
    private String brief;
    private int type;
    private int building;
    private int room;
    private CommunityGroupDO communityGroupDO;
    private int status;
    private long processedUserId;
    private long appliedAt;
    private long processedAt;

    public static CommunityUserApplyDO valueOf(GroupUserQuery groupUserQuery) {
        CommunityUserApplyDO communityUserApplyDO = new CommunityUserApplyDO();

        communityUserApplyDO.setName(groupUserQuery.getName());
        communityUserApplyDO.setMobile(groupUserQuery.getMobile());
        communityUserApplyDO.setPosition(groupUserQuery.getPosition());
        communityUserApplyDO.setCompany(groupUserQuery.getCompany());
        communityUserApplyDO.setMobile(groupUserQuery.getMobile());
        communityUserApplyDO.setBrief(groupUserQuery.getBrief());
        communityUserApplyDO.setType(CommunityTypeEnum.valueOf(groupUserQuery.getUserType()).getIntStatus());
        communityUserApplyDO.setRoom(groupUserQuery.getRoom());
        communityUserApplyDO.setBuilding(groupUserQuery.getBuilding());

        GenderEnum genderEnum = GenderEnum.stringGenderToEnum(groupUserQuery.getGender());
        if (genderEnum != null) {
            communityUserApplyDO.setGender(genderEnum.getIntStatus());
        }

        if (groupUserQuery.getGroupId() > 0) {
            CommunityGroupDO communityGroupDO = new CommunityGroupDO();
            communityGroupDO.setGroupId(groupUserQuery.getGroupId());
            communityUserApplyDO.setCommunityGroupDO(communityGroupDO);
        }

        if (groupUserQuery.getUserId() > 0) {
            UserDO userDO = new UserDO();
            userDO.setUserId(groupUserQuery.getUserId());
        }
        return communityUserApplyDO;
    }

}
