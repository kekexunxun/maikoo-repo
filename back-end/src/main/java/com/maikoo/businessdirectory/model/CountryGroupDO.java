package com.maikoo.businessdirectory.model;

import com.maikoo.businessdirectory.model.query.CountryGroupInformationQuery;
import lombok.Data;

@Data
public class CountryGroupDO {
    private long groupId;
    private String groupName;
    private String groupAvatarUrl;
    private String groupAddrDetail;
    private String groupAddrCode;
    private String groupBrief;
    private String posterUrl;
    private String qrCodeUrl;
    private UserDO userDO;
    private boolean isEnable;
    private long createdAt;
    private long updatedAt;
    private long dismissedAt;
    private long groupMemCount;

    public static CountryGroupDO valueOf(CountryGroupInformationQuery countryGroupInformationQuery){
        CountryGroupDO countryGroupDO = new CountryGroupDO();
        countryGroupDO.setGroupId(countryGroupInformationQuery.getGroupId());
        countryGroupDO.setGroupName(countryGroupInformationQuery.getName());
        countryGroupDO.setGroupAvatarUrl(countryGroupInformationQuery.getAvatarUrl());
        countryGroupDO.setGroupAddrDetail(countryGroupInformationQuery.getAddrDetail());
        countryGroupDO.setGroupAddrCode(countryGroupInformationQuery.getAddrCode());
        countryGroupDO.setGroupBrief(countryGroupInformationQuery.getBrief());
        return countryGroupDO;
    }
}
