package com.maikoo.businessdirectory.model;

import com.maikoo.businessdirectory.model.constant.GenderEnum;
import com.maikoo.businessdirectory.model.constant.SchoolTypeEnum;
import com.maikoo.businessdirectory.model.query.GroupUserQuery;
import lombok.Data;

@Data
public class ClassUserApplyDO {
    private long idx;
    private UserDO userDO;
    private String name;
    private int gender;
    private int type;
    private String mobile;
    private String company;
    private String position;
    private String brief;
    private ClassGroupDO classGroupDO;
    //审核的管理员的id
    private long processedUserId;
    //审核的状态
    private int status;
    //申请时间
    private long appliedAt;
    //审核时间
    private long processedAt;

    public static ClassUserApplyDO valueOf(GroupUserQuery groupUserQuery) {
        ClassUserApplyDO classUserApplyDO = new ClassUserApplyDO();

        classUserApplyDO.setName(groupUserQuery.getName());
        classUserApplyDO.setMobile(groupUserQuery.getMobile());
        classUserApplyDO.setPosition(groupUserQuery.getPosition());
        classUserApplyDO.setCompany(groupUserQuery.getCompany());
        classUserApplyDO.setMobile(groupUserQuery.getMobile());
        classUserApplyDO.setBrief(groupUserQuery.getBrief());
        classUserApplyDO.setType(SchoolTypeEnum.valueOf(groupUserQuery.getUserType()).getIntStatus());

        GenderEnum genderEnum = GenderEnum.stringGenderToEnum(groupUserQuery.getGender());
        if (genderEnum != null) {
            classUserApplyDO.setGender(genderEnum.getIntStatus());
        }

        if (groupUserQuery.getGroupId() > 0) {
            ClassGroupDO classGroupDO = new ClassGroupDO();
            classGroupDO.setGroupId(groupUserQuery.getGroupId());
            classUserApplyDO.setClassGroupDO(classGroupDO);
        }

        if (groupUserQuery.getUserId() > 0) {
            UserDO userDO = new UserDO();
            userDO.setUserId(groupUserQuery.getUserId());
        }
        return classUserApplyDO;
    }




}
