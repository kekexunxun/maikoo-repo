package com.maikoo.businessdirectory.model;

import com.maikoo.businessdirectory.model.constant.GenderEnum;
import com.maikoo.businessdirectory.model.query.GroupUserQuery;
import lombok.Data;

@Data
public class CountryUserDO {
    private long idx;
    private UserDO userDO;
    private String name;
    //性别
    private int gender;
    //年龄段
    private String tag;
    private String mobile;
    private String company;
    private String position;
    //个人简介
    private String brief;
    //用户群中状态 1 正常 2 已被移除群聊
    private int status;
    private CountryGroupDO countryGroupDO;
    private CountryUserDO processedRemoveCountryUserDO;
    private long joinedAt;
    private long quitedAt;
    private String search;

    public static CountryUserDO valueOf(GroupUserQuery groupUserQuery) {
        CountryUserDO countryUserDO = new CountryUserDO();

        countryUserDO.setName(groupUserQuery.getName());
        countryUserDO.setMobile(groupUserQuery.getMobile());
        countryUserDO.setPosition(groupUserQuery.getPosition());
        countryUserDO.setCompany(groupUserQuery.getCompany());
        countryUserDO.setMobile(groupUserQuery.getMobile());
        countryUserDO.setBrief(groupUserQuery.getBrief());
        countryUserDO.setTag(groupUserQuery.getTag());
        countryUserDO.setSearch(groupUserQuery.getSearch());

        GenderEnum genderEnum = GenderEnum.stringGenderToEnum(groupUserQuery.getGender());
        if (genderEnum != null) {
            countryUserDO.setGender(genderEnum.getIntStatus());
        }

        if (groupUserQuery.getGroupId() > 0) {
            CountryGroupDO countryGroupDO = new CountryGroupDO();
            countryGroupDO.setGroupId(groupUserQuery.getGroupId());
            countryUserDO.setCountryGroupDO(countryGroupDO);
        }

        if (groupUserQuery.getUserId() > 0) {
            UserDO userDO = new UserDO();
            userDO.setUserId(groupUserQuery.getUserId());
        }
        return countryUserDO;
    }
}
