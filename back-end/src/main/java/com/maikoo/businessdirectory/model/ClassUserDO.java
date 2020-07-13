package com.maikoo.businessdirectory.model;

import com.maikoo.businessdirectory.model.constant.GenderEnum;
import com.maikoo.businessdirectory.model.constant.SchoolTypeEnum;
import com.maikoo.businessdirectory.model.query.GroupUserQuery;
import lombok.Data;

@Data
public class ClassUserDO {
    private long idx;
    private UserDO userDO;
    private String name;
    private int gender;
    private int type;
    private String mobile;
    private String company;
    private String position;
    private String brief;
    private int status;
    private ClassGroupDO classGroupDO;
    private ClassUserDO processedRemoveClassUserDO;
    private String schoolName;
    private String className;
    private long joinedAt;
    private long quitedAt;
    private String search;

    public static ClassUserDO valueOf(GroupUserQuery groupUserQuery) {
        ClassUserDO classUserDO = new ClassUserDO();

        classUserDO.setName(groupUserQuery.getName());
        classUserDO.setMobile(groupUserQuery.getMobile());
        classUserDO.setPosition(groupUserQuery.getPosition());
        classUserDO.setCompany(groupUserQuery.getCompany());
        classUserDO.setMobile(groupUserQuery.getMobile());
        classUserDO.setBrief(groupUserQuery.getBrief());
        if(groupUserQuery.getUserType()!=null){
            classUserDO.setType(SchoolTypeEnum.valueOf(groupUserQuery.getUserType()).getIntStatus());
        }
        classUserDO.setSearch(groupUserQuery.getSearch());

        GenderEnum genderEnum = GenderEnum.stringGenderToEnum(groupUserQuery.getGender());
        if (genderEnum != null) {
            classUserDO.setGender(genderEnum.getIntStatus());
        }

        if (groupUserQuery.getGroupId() > 0) {
            ClassGroupDO classGroupDO = new ClassGroupDO();
            classGroupDO.setGroupId(groupUserQuery.getGroupId());
            classUserDO.setClassGroupDO(classGroupDO);
        }

        if (groupUserQuery.getUserId() > 0) {
            UserDO userDO = new UserDO();
            userDO.setUserId(groupUserQuery.getUserId());
        }
        return classUserDO;
    }
}
