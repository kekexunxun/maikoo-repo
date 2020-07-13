package com.maikoo.businessdirectory.model;

import com.maikoo.businessdirectory.model.constant.GenderEnum;
import com.maikoo.businessdirectory.model.constant.SchoolTypeEnum;
import com.maikoo.businessdirectory.model.query.GroupUserQuery;
import lombok.Data;

@Data
public class SchoolUserApplyDO {
    private long idx;
    private UserDO userDO;
    private String name;
    private int gender;
    private String mobile;
    private String company;
    private String position;
    private String brief;
    private int type;
    private int graduatedAt;
    private SchoolGroupDO schoolGroupDO;
    private int status;
    private long processedUserId;
    private long appliedAt;
    private long processedAt;

    public static SchoolUserApplyDO valueOf(GroupUserQuery groupUserQuery) {
        SchoolUserApplyDO schoolUserApplyDO = new SchoolUserApplyDO();

        schoolUserApplyDO.setName(groupUserQuery.getName());
        schoolUserApplyDO.setMobile(groupUserQuery.getMobile());
        schoolUserApplyDO.setPosition(groupUserQuery.getPosition());
        schoolUserApplyDO.setCompany(groupUserQuery.getCompany());
        schoolUserApplyDO.setMobile(groupUserQuery.getMobile());
        schoolUserApplyDO.setBrief(groupUserQuery.getBrief());
        schoolUserApplyDO.setType(SchoolTypeEnum.valueOf(groupUserQuery.getUserType()).getIntStatus());
        schoolUserApplyDO.setGraduatedAt(groupUserQuery.getGraduateAt());
//        schoolUserApplyDO.setAppliedAt(groupUserQuery.getAppliedAt());

        GenderEnum genderEnum = GenderEnum.stringGenderToEnum(groupUserQuery.getGender());
        if (genderEnum != null) {
            schoolUserApplyDO.setGender(genderEnum.getIntStatus());
        }

        if (groupUserQuery.getGroupId() > 0) {
            SchoolGroupDO schoolGroupDO = new SchoolGroupDO();
            schoolGroupDO.setGroupId(groupUserQuery.getGroupId());
            schoolUserApplyDO.setSchoolGroupDO(schoolGroupDO);
        }

        if (groupUserQuery.getUserId() > 0) {
            UserDO userDO = new UserDO();
            userDO.setUserId(groupUserQuery.getUserId());
        }
        return schoolUserApplyDO;
    }

}
