package com.maikoo.businessdirectory.model;

import com.maikoo.businessdirectory.model.constant.GenderEnum;
import com.maikoo.businessdirectory.model.query.GroupUserQuery;
import lombok.Data;

@Data
public class CountryUserApplyDO {

    private long idx;
    private UserDO userDO;
    private String name;
    private int gender;
    private String mobile;
    private String company;
    private String position;
    private String brief;
    //年龄段
    private String tag;
    private CountryGroupDO countryGroupDO;
    private int status;
    private long processedUserId;
    private long appliedAt;
    private long processedAt;

    public static CountryUserApplyDO valueOf(GroupUserQuery groupUserQuery) {
        CountryUserApplyDO countryUserApplyDO = new CountryUserApplyDO();

        countryUserApplyDO.setName(groupUserQuery.getName());
        countryUserApplyDO.setMobile(groupUserQuery.getMobile());
        countryUserApplyDO.setPosition(groupUserQuery.getPosition());
        countryUserApplyDO.setCompany(groupUserQuery.getCompany());
        countryUserApplyDO.setMobile(groupUserQuery.getMobile());
        countryUserApplyDO.setBrief(groupUserQuery.getBrief());
        countryUserApplyDO.setTag(groupUserQuery.getTag());

        GenderEnum genderEnum = GenderEnum.stringGenderToEnum(groupUserQuery.getGender());
        if (genderEnum != null) {
            countryUserApplyDO.setGender(genderEnum.getIntStatus());
        }

        if (groupUserQuery.getGroupId() > 0) {
            CountryGroupDO countryGroupDO = new CountryGroupDO();
            countryGroupDO.setGroupId(groupUserQuery.getGroupId());
            countryUserApplyDO.setCountryGroupDO(countryGroupDO);
        }

        if (groupUserQuery.getUserId() > 0) {
            UserDO userDO = new UserDO();
            userDO.setUserId(groupUserQuery.getUserId());
        }
        return countryUserApplyDO;
    }
}
