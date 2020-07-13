package com.maikoo.businessdirectory.model;

import com.maikoo.businessdirectory.model.constant.GenderEnum;
import com.maikoo.businessdirectory.model.constant.SchoolTypeEnum;
import com.maikoo.businessdirectory.model.query.GroupUserQuery;
import lombok.Data;

@Data
public class SchoolUserDO {
    private long idx;
    private UserDO userDO;
    private String name;
    private int gender;
    private int type;
    private String mobile;
    private String company;
    private String position;
    //用户个人简介
    private String brief;
    private int status;
    private SchoolGroupDO schoolGroupDO;
    private SchoolUserDO processedRemoveSchoolUserDO;
    private String schoolName;
    private long graduatedAt;
    private long joinedAt;
    private long quitedAt;
    private String search;

    public static SchoolUserDO valueOf(GroupUserQuery groupUserQuery) {
        SchoolUserDO schoolUserDO = new SchoolUserDO();

        schoolUserDO.setName(groupUserQuery.getName());
        schoolUserDO.setMobile(groupUserQuery.getMobile());
        schoolUserDO.setPosition(groupUserQuery.getPosition());
        schoolUserDO.setCompany(groupUserQuery.getCompany());
        schoolUserDO.setMobile(groupUserQuery.getMobile());
        schoolUserDO.setBrief(groupUserQuery.getBrief());
        if (groupUserQuery.getUserType() != null) {
            schoolUserDO.setType(SchoolTypeEnum.valueOf(groupUserQuery.getUserType()).getIntStatus());
        }
        schoolUserDO.setGraduatedAt(groupUserQuery.getGraduateAt());
        schoolUserDO.setSearch(groupUserQuery.getSearch());

        GenderEnum genderEnum = GenderEnum.stringGenderToEnum(groupUserQuery.getGender());
        if (genderEnum != null) {
            schoolUserDO.setGender(genderEnum.getIntStatus());
        }

        if (groupUserQuery.getGroupId() > 0) {
            SchoolGroupDO schoolGroupDO = new SchoolGroupDO();
            schoolGroupDO.setGroupId(groupUserQuery.getGroupId());
            schoolUserDO.setSchoolGroupDO(schoolGroupDO);
        }

        if (groupUserQuery.getUserId() > 0) {
            UserDO userDO = new UserDO();
            userDO.setUserId(groupUserQuery.getUserId());
        }
        return schoolUserDO;
    }




}
