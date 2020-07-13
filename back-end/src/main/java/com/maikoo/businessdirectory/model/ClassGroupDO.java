package com.maikoo.businessdirectory.model;

import com.maikoo.businessdirectory.model.query.ClassGroupInformationQuery;
import lombok.Data;

@Data
public class ClassGroupDO {
    private long groupId;
    private String groupName;
    //群头像
    private String groupAvatarUrl;
    private String groupAddrCode;
    private String groupBrief;
    private String groupAddrDetail;
    private String schoolName;
    private String className;
    private String posterUrl;
    private String qrCodeUrl;
    private boolean isEnable;
    private UserDO userDO;
    private long createdAt;
    private long updatedAt;
    private long dismissedAt;
    private long groupMemCount;

    public static ClassGroupDO valueOf(ClassGroupInformationQuery classGroupInformationQuery){
        ClassGroupDO classGroupDO = new ClassGroupDO();
        classGroupDO.setGroupId(classGroupInformationQuery.getGroupId());
        classGroupDO.setGroupName(classGroupInformationQuery.getName());
        classGroupDO.setGroupAvatarUrl(classGroupInformationQuery.getAvatarUrl());
        classGroupDO.setGroupAddrCode(classGroupInformationQuery.getAddrCode());
        classGroupDO.setGroupAddrDetail(classGroupInformationQuery.getAddrDetail());
        classGroupDO.setGroupBrief(classGroupInformationQuery.getBrief());
        classGroupDO.setSchoolName(classGroupInformationQuery.getSchoolName());
        classGroupDO.setClassName(classGroupInformationQuery.getClassName());
        return classGroupDO;
    }
}
