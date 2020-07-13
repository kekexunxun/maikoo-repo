package com.maikoo.businessdirectory.model.dto;

import com.fasterxml.jackson.annotation.JsonView;
import com.fasterxml.jackson.databind.annotation.JsonSerialize;
import com.maikoo.businessdirectory.model.ClassUserDO;
import com.maikoo.businessdirectory.model.CommunityUserDO;
import com.maikoo.businessdirectory.model.CountryUserDO;
import com.maikoo.businessdirectory.model.SchoolUserDO;
import com.maikoo.businessdirectory.model.constant.CommunityTypeEnum;
import com.maikoo.businessdirectory.model.constant.SchoolTypeEnum;
import com.maikoo.businessdirectory.model.constant.UserRoleEnum;
import com.maikoo.businessdirectory.model.constant.UserStatusEnum;
import com.maikoo.businessdirectory.model.serializer.BaseDateTimeSerializer;
import com.maikoo.businessdirectory.model.view.GroupView;
import lombok.Data;

@Data
public class AdminGroupUserDTO {
    @JsonView(GroupView.Public.class)
    private long userId;
    @JsonView(GroupView.Public.class)
    private String userAvatarUrl;
    @JsonView(GroupView.Public.class)
    private String userName;
    @JsonView(GroupView.Public.class)
    private String userPosition;
    @JsonView(GroupView.Public.class)
    private String userCompany;
    @JsonSerialize(using = BaseDateTimeSerializer.class)
    @JsonView(GroupView.Public.class)
    private String joinAt;
    @JsonView(GroupView.Public.class)
    private UserRoleEnum memType;
    @JsonView(GroupView.Public.class)
    private UserStatusEnum status;

    @JsonView(GroupView.School.class)
    private String graduateAt;

    @JsonView({GroupView.School.class,GroupView.Class.class,GroupView.Community.class})
    private String userType;

    @JsonView(GroupView.Country.class)
    private String userTag;


    public static AdminGroupUserDTO valueOf(ClassUserDO classUserDO) {
        AdminGroupUserDTO adminGroupUserDTO = new AdminGroupUserDTO();
        adminGroupUserDTO.setUserName(classUserDO.getName());
        adminGroupUserDTO.setUserPosition(classUserDO.getPosition());
        adminGroupUserDTO.setUserCompany(classUserDO.getCompany());
        adminGroupUserDTO.setJoinAt(classUserDO.getJoinedAt() > 0 ? String.valueOf(classUserDO.getJoinedAt()) : null);
        adminGroupUserDTO.setStatus(classUserDO.getStatus()==1?UserStatusEnum.DISABLE.ENABLE:UserStatusEnum.DISABLE.DISABLE);


        if (classUserDO.getUserDO() != null) {
            adminGroupUserDTO.setUserId(classUserDO.getUserDO().getUserId());
            adminGroupUserDTO.setUserAvatarUrl(classUserDO.getUserDO().getAvatarUrl());
            if (classUserDO.getClassGroupDO() != null) {
                if (classUserDO.getClassGroupDO().getUserDO() != null) {
                    adminGroupUserDTO.setMemType(classUserDO.getUserDO().getUserId() == classUserDO.getClassGroupDO().getUserDO().getUserId() ? UserRoleEnum.ADMIN : UserRoleEnum.MEMBER);
                }
            }
        }

        SchoolTypeEnum schoolTypeEnum = SchoolTypeEnum.intStatusToEnum(classUserDO.getType());
        if (schoolTypeEnum != null) {
            adminGroupUserDTO.setUserType(schoolTypeEnum.toString());
        }

        return adminGroupUserDTO;
    }

    public static AdminGroupUserDTO valueOf(CountryUserDO countryUserDO) {
        AdminGroupUserDTO adminGroupUserDTO = new AdminGroupUserDTO();
        adminGroupUserDTO.setUserName(countryUserDO.getName());
        adminGroupUserDTO.setUserPosition(countryUserDO.getPosition());
        adminGroupUserDTO.setUserCompany(countryUserDO.getCompany());
        adminGroupUserDTO.setStatus(countryUserDO.getStatus()==1?UserStatusEnum.DISABLE.ENABLE:UserStatusEnum.DISABLE.DISABLE);
        adminGroupUserDTO.setJoinAt(countryUserDO.getJoinedAt() > 0 ? String.valueOf(countryUserDO.getJoinedAt()) : null);

        adminGroupUserDTO.setUserTag(countryUserDO.getTag());

        if (countryUserDO.getUserDO() != null) {
            adminGroupUserDTO.setUserId(countryUserDO.getUserDO().getUserId());
            adminGroupUserDTO.setUserAvatarUrl(countryUserDO.getUserDO().getAvatarUrl());
            if (countryUserDO.getCountryGroupDO() != null) {
                if (countryUserDO.getCountryGroupDO().getUserDO() != null) {
                    adminGroupUserDTO.setMemType(countryUserDO.getUserDO().getUserId() == countryUserDO.getCountryGroupDO().getUserDO().getUserId() ? UserRoleEnum.ADMIN : UserRoleEnum.MEMBER);
                }
            }
        }


        return adminGroupUserDTO;
    }

    public static AdminGroupUserDTO valueOf(CommunityUserDO communityUserDO) {
        AdminGroupUserDTO adminGroupUserDTO = new AdminGroupUserDTO();
        adminGroupUserDTO.setUserName(communityUserDO.getName());
        adminGroupUserDTO.setUserPosition(communityUserDO.getPosition());
        adminGroupUserDTO.setUserCompany(communityUserDO.getCompany());
        adminGroupUserDTO.setStatus(communityUserDO.getStatus()==1?UserStatusEnum.DISABLE.ENABLE:UserStatusEnum.DISABLE.DISABLE);
        adminGroupUserDTO.setJoinAt(communityUserDO.getJoinedAt() > 0 ? String.valueOf(communityUserDO.getJoinedAt()) : null);

        if (communityUserDO.getUserDO() != null) {
            adminGroupUserDTO.setUserId(communityUserDO.getUserDO().getUserId());
            adminGroupUserDTO.setUserAvatarUrl(communityUserDO.getUserDO().getAvatarUrl());
            if (communityUserDO.getCommunityGroupDO() != null) {
                if (communityUserDO.getCommunityGroupDO().getUserDO() != null) {
                    adminGroupUserDTO.setMemType(communityUserDO.getUserDO().getUserId() == communityUserDO.getCommunityGroupDO().getUserDO().getUserId() ? UserRoleEnum.ADMIN : UserRoleEnum.MEMBER);
                }
            }
        }
        CommunityTypeEnum communityTypeEnum = CommunityTypeEnum.intStatusToEnum(communityUserDO.getType());
        if (communityTypeEnum != null) {
            adminGroupUserDTO.setUserType(communityTypeEnum.toString());
        }

        return adminGroupUserDTO;
    }

    public static AdminGroupUserDTO valueOf(SchoolUserDO schoolUserDO) {
        AdminGroupUserDTO adminGroupUserDTO = new AdminGroupUserDTO();
        adminGroupUserDTO.setUserName(schoolUserDO.getName());
        adminGroupUserDTO.setUserPosition(schoolUserDO.getPosition());
        adminGroupUserDTO.setUserCompany(schoolUserDO.getCompany());
        adminGroupUserDTO.setGraduateAt(schoolUserDO.getGraduatedAt()>0?String.valueOf(schoolUserDO.getGraduatedAt()):null);
        adminGroupUserDTO.setStatus(schoolUserDO.getStatus()==1?UserStatusEnum.DISABLE.ENABLE:UserStatusEnum.DISABLE.DISABLE);
        adminGroupUserDTO.setJoinAt(schoolUserDO.getJoinedAt() > 0 ? String.valueOf(schoolUserDO.getJoinedAt()) : null);

        if (schoolUserDO.getUserDO() != null) {
            adminGroupUserDTO.setUserId(schoolUserDO.getUserDO().getUserId());
            adminGroupUserDTO.setUserAvatarUrl(schoolUserDO.getUserDO().getAvatarUrl());
            if (schoolUserDO.getSchoolGroupDO() != null) {
                if (schoolUserDO.getSchoolGroupDO().getUserDO() != null) {
                    adminGroupUserDTO.setMemType(schoolUserDO.getUserDO().getUserId() == schoolUserDO.getSchoolGroupDO().getUserDO().getUserId() ? UserRoleEnum.ADMIN : UserRoleEnum.MEMBER);
                }
            }
        }

        SchoolTypeEnum schoolTypeEnum = SchoolTypeEnum.intStatusToEnum(schoolUserDO.getType());
        if (schoolTypeEnum != null) {
            adminGroupUserDTO.setUserType(schoolTypeEnum.toString());
        }

        return adminGroupUserDTO;
    }


}
