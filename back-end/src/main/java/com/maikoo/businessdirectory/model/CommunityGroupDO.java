package com.maikoo.businessdirectory.model;

import com.maikoo.businessdirectory.model.query.CommunityGroupInformationQuery;
import lombok.Data;

@Data
public class CommunityGroupDO {
    private long groupId;
    private String groupName;
    private String groupAvatarUrl;
    private String groupAddrCode;
    private String groupBrief;
    private String groupAddrDetail;
    private String communityName;
    private String posterUrl;
    private String qrCodeUrl;
    private UserDO userDO;
    private boolean isEnable;
    private long createdAt;
    private long updatedAt;
    private long dismissedAt;
    private long groupMemCount;

    public static CommunityGroupDO valueOf(CommunityGroupInformationQuery communityGroupInformationQuery){
        CommunityGroupDO communityGroupDO = new CommunityGroupDO();
        communityGroupDO.setGroupId(communityGroupInformationQuery.getGroupId());
        communityGroupDO.setGroupName(communityGroupInformationQuery.getName());
        communityGroupDO.setGroupAvatarUrl(communityGroupInformationQuery.getAvatarUrl());
        communityGroupDO.setGroupAddrCode(communityGroupInformationQuery.getAddrCode());
        communityGroupDO.setGroupAddrDetail(communityGroupInformationQuery.getAddrDetail());
        communityGroupDO.setGroupBrief(communityGroupInformationQuery.getBrief());
        communityGroupDO.setCommunityName(communityGroupInformationQuery.getCommunityName());
        return communityGroupDO;
    }
}
