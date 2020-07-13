package com.maikoo.businessdirectory.model;

import com.maikoo.businessdirectory.model.query.SchoolGroupInformationQuery;
import lombok.Data;

@Data
public class SchoolGroupDO {

    private long groupId;
    private String groupName;
    private String groupAvatarUrl;
    private String groupAddrCode;
    private String groupBrief;
    private String groupAddrDetail;
    private String schoolName;
    private String posterUrl;
    private String qrCodeUrl;
    private UserDO userDO;
    private boolean isEnable;
    private long createdAt;
    private long updatedAt;
    private long dismissedAt;
    private long groupMemCount;

    public static SchoolGroupDO valueOf(SchoolGroupInformationQuery schoolGroupInformationQuery){
        SchoolGroupDO schoolGroupDO = new SchoolGroupDO();
        schoolGroupDO.setGroupId(schoolGroupInformationQuery.getGroupId());
        schoolGroupDO.setGroupName(schoolGroupInformationQuery.getName());
        schoolGroupDO.setGroupAvatarUrl(schoolGroupInformationQuery.getAvatarUrl());
        schoolGroupDO.setGroupAddrDetail(schoolGroupInformationQuery.getAddrDetail());
        schoolGroupDO.setGroupAddrCode(schoolGroupInformationQuery.getAddrCode());
        schoolGroupDO.setGroupBrief(schoolGroupInformationQuery.getBrief());
        schoolGroupDO.setSchoolName(schoolGroupInformationQuery.getSchoolName());
        return schoolGroupDO;
    }

}
