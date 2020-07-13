package com.maikoo.businessdirectory.model.dto;

import com.fasterxml.jackson.annotation.JsonView;
import com.fasterxml.jackson.databind.annotation.JsonSerialize;
import com.maikoo.businessdirectory.model.*;
import com.maikoo.businessdirectory.model.constant.GroupTypeEnum;
import com.maikoo.businessdirectory.model.constant.UserRoleEnum;
import com.maikoo.businessdirectory.model.view.GroupAnalysisView;
import com.maikoo.businessdirectory.model.serializer.BaseDateTimeSerializer;
import com.maikoo.businessdirectory.model.view.GroupView;
import lombok.Data;

import java.util.List;

@Data
public class GroupDTO {
    @JsonView(GroupView.Public.class)
    private UserRoleEnum memType;

    @JsonView(GroupView.SearchList.class)
    private GroupTypeEnum groupType;
    @JsonView(value = {GroupView.Public.class, GroupView.SearchList.class, GroupView.AdminPublic.class})
    private long groupMemCount;
    @JsonView(value = {GroupView.AdminPublic.class, GroupView.AdminInformation.class})
    private String status;
    @JsonSerialize(using = BaseDateTimeSerializer.class)
    @JsonView(value = {GroupView.AdminPublic.class,GroupView.AdminInformation.class})
    private String createdAt;
    @JsonSerialize(using = BaseDateTimeSerializer.class)
    @JsonView(GroupView.AdminInformation.class)
    private String dismissAt;

    @JsonView(value = {GroupView.Public.class, GroupView.SearchList.class, GroupView.Insert.class, GroupView.AdminPublic.class, GroupView.AdminInformation.class})
    private long groupId;
    @JsonView(value = {GroupView.Public.class, GroupView.SearchList.class, GroupView.AdminPublic.class, GroupView.AdminInformation.class})
    private String groupName;
    @JsonView(value = {GroupView.Public.class, GroupView.SearchList.class, GroupView.AdminPublic.class, GroupView.AdminInformation.class})
    private String groupAvatarUrl;
    @JsonView(value = {GroupView.Public.class, GroupView.AdminInformation.class})
    private String groupBrief;
    @JsonView(value = {GroupView.Public.class, GroupView.SearchList.class, GroupView.AdminPublic.class, GroupView.AdminInformation.class})
    private String groupAddress;
    @JsonView(value = {GroupView.Public.class, GroupView.AdminInformation.class})
    private String groupAddrCode;

    @JsonView(value = {GroupView.School.class, GroupView.Class.class, GroupView.AdminSchool.class, GroupView.AdminSchoolInformation.class, GroupView.AdminClassInformation.class})
    private String groupSchoolName;
    @JsonView(value = {GroupView.Class.class, GroupView.AdminClassInformation.class})
    private String groupClassName;
    @JsonView(value = {GroupView.Community.class, GroupView.AdminCommunity.class,GroupView.AdminCommunityInformation.class})
    private String groupCommunityName;

    private String communityName;

    @JsonView(GroupAnalysisView.Public.class)
    private String name;
    @JsonView(GroupAnalysisView.Public.class)
    private List<Integer> data;

    public static GroupDTO valueOf(ClassGroupDO classGroupDO){
        GroupDTO groupDTO = new GroupDTO();
        groupDTO.setGroupId(classGroupDO.getGroupId());
        groupDTO.setGroupName(classGroupDO.getGroupName());
        groupDTO.setGroupAvatarUrl(classGroupDO.getGroupAvatarUrl());
        groupDTO.setGroupBrief(classGroupDO.getGroupBrief());
        groupDTO.setGroupAddrCode(classGroupDO.getGroupAddrCode());
        groupDTO.setGroupAddress(classGroupDO.getGroupAddrDetail());
        groupDTO.setGroupSchoolName(classGroupDO.getSchoolName());
        groupDTO.setGroupClassName(classGroupDO.getClassName());
        groupDTO.setGroupMemCount(classGroupDO.getGroupMemCount());
        groupDTO.setGroupType(GroupTypeEnum.CLASS);
        groupDTO.setStatus(classGroupDO.isEnable()?"ENABLE":"DISMISS");
        groupDTO.setCreatedAt(classGroupDO.getCreatedAt() > 0 ? String.valueOf(classGroupDO.getCreatedAt()) : null);
        groupDTO.setDismissAt(classGroupDO.isEnable() ? "" :  String.valueOf(classGroupDO.getDismissedAt()));
        return groupDTO;
    }

    public static GroupDTO valueOf(CountryGroupDO countryGroupDO){
        GroupDTO groupDTO = new GroupDTO();
        groupDTO.setGroupId(countryGroupDO.getGroupId());
        groupDTO.setGroupName(countryGroupDO.getGroupName());
        groupDTO.setGroupAvatarUrl(countryGroupDO.getGroupAvatarUrl());
        groupDTO.setGroupBrief(countryGroupDO.getGroupBrief());
        groupDTO.setGroupAddrCode(countryGroupDO.getGroupAddrCode());
        groupDTO.setGroupAddress(countryGroupDO.getGroupAddrDetail());
        groupDTO.setGroupMemCount(countryGroupDO.getGroupMemCount());
        groupDTO.setGroupType(GroupTypeEnum.COUNTRY);
        groupDTO.setStatus(countryGroupDO.isEnable()?"ENABLE":"DISMISS");
        groupDTO.setCreatedAt(countryGroupDO.getCreatedAt() > 0 ? String.valueOf(countryGroupDO.getCreatedAt()) : null);
        groupDTO.setDismissAt(countryGroupDO.isEnable() ? "" :  String.valueOf(countryGroupDO.getDismissedAt()));
        return groupDTO;
    }

    public static GroupDTO valueOf(CommunityGroupDO communityGroupDO){
        GroupDTO groupDTO = new GroupDTO();
        groupDTO.setGroupId(communityGroupDO.getGroupId());
        groupDTO.setGroupName(communityGroupDO.getGroupName());
        groupDTO.setGroupAvatarUrl(communityGroupDO.getGroupAvatarUrl());
        groupDTO.setGroupBrief(communityGroupDO.getGroupBrief());
        groupDTO.setGroupAddrCode(communityGroupDO.getGroupAddrCode());
        groupDTO.setGroupAddress(communityGroupDO.getGroupAddrDetail());
        groupDTO.setGroupCommunityName(communityGroupDO.getCommunityName());
        groupDTO.setCommunityName(communityGroupDO.getCommunityName());
        groupDTO.setGroupMemCount(communityGroupDO.getGroupMemCount());
        groupDTO.setGroupType(GroupTypeEnum.COMMUNITY);
        groupDTO.setStatus(communityGroupDO.isEnable()?"ENABLE":"DISMISS");
        groupDTO.setCreatedAt(communityGroupDO.getCreatedAt() > 0 ? String.valueOf(communityGroupDO.getCreatedAt()) : null);
        groupDTO.setDismissAt(communityGroupDO.isEnable() ? "" :  String.valueOf(communityGroupDO.getDismissedAt()));
        return groupDTO;
    }


    public static GroupDTO valueOf(SchoolGroupDO schoolGroupDO){
        GroupDTO groupDTO = new GroupDTO();
        groupDTO.setGroupId(schoolGroupDO.getGroupId());
        groupDTO.setGroupName(schoolGroupDO.getGroupName());
        groupDTO.setGroupAvatarUrl(schoolGroupDO.getGroupAvatarUrl());
        groupDTO.setGroupBrief(schoolGroupDO.getGroupBrief());
        groupDTO.setGroupAddrCode(schoolGroupDO.getGroupAddrCode());
        groupDTO.setGroupAddress(schoolGroupDO.getGroupAddrDetail());
        groupDTO.setGroupSchoolName(schoolGroupDO.getSchoolName());
        groupDTO.setGroupMemCount(schoolGroupDO.getGroupMemCount());
        groupDTO.setGroupType(GroupTypeEnum.SCHOOL);
        groupDTO.setStatus(schoolGroupDO.isEnable()?"ENABLE":"DISMISS");
        groupDTO.setCreatedAt(schoolGroupDO.getCreatedAt() > 0 ? String.valueOf(schoolGroupDO.getCreatedAt()) : null);
        groupDTO.setDismissAt(schoolGroupDO.isEnable() ? "" :  String.valueOf(schoolGroupDO.getDismissedAt()));
        return groupDTO;
    }

    public static GroupDTO valueOf(GroupDO groupDO){
        GroupDTO groupDTO = new GroupDTO();
        groupDTO.setGroupId(groupDO.getGroupId());
        groupDTO.setGroupName(groupDO.getGroupName());
        groupDTO.setGroupAvatarUrl(groupDO.getGroupAvatarUrl());
        groupDTO.setGroupAddrCode(groupDO.getGroupAddrCode());
        groupDTO.setGroupAddress(groupDO.getGroupAddrDetail());
        groupDTO.setGroupType(groupDO.getGroupType());
        groupDTO.setGroupMemCount(groupDO.getGroupMemCount());
        return groupDTO;
    }
}
