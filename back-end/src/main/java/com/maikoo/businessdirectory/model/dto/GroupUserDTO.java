package com.maikoo.businessdirectory.model.dto;

import com.fasterxml.jackson.annotation.JsonView;
import com.fasterxml.jackson.databind.annotation.JsonSerialize;
import com.maikoo.businessdirectory.model.*;
import com.maikoo.businessdirectory.model.constant.*;
import com.maikoo.businessdirectory.model.serializer.BaseDateTimeSerializer;
import com.maikoo.businessdirectory.model.view.GroupUserView;
import lombok.Data;

@Data
public class GroupUserDTO {
    @JsonView(value = {GroupUserView.InformationPublic.class, GroupUserView.List.class, GroupUserView.ReviewList.class, GroupUserView.FavorList.class, GroupUserView.ApplyList.class})
    private long userId;

    @JsonView(value = GroupUserView.FavorList.class)
    private long groupId;

    @JsonView(value = {GroupUserView.ReviewList.class, GroupUserView.FavorList.class, GroupUserView.GroupUserDetail.class, GroupUserView.UserName.class})
    private String userName;

    @JsonView(value = {GroupUserView.InformationPublic.class, GroupUserView.List.class, GroupUserView.ReviewList.class, GroupUserView.FavorList.class})
    private String userAvatarUrl;

    @JsonView(value = {GroupUserView.InformationPublic.class, GroupUserView.BaseDetailList.class})
    private String userPosition;

    @JsonView(value = {GroupUserView.InformationPublic.class, GroupUserView.BaseDetailList.class, GroupUserView.ReviewList.class, GroupUserView.FavorList.class})
    private String userCompany;

    @JsonView(value = {GroupUserView.InformationPublic.class})
    private String userMobile;

    @JsonView(value = {GroupUserView.InformationPublic.class})
    private String userBrief;

    @JsonView(value = {GroupUserView.RoleType.class})
    private UserRoleEnum memType;

    @JsonView(value = {GroupUserView.GroupUserType.class,GroupUserView.CommunityDetailList.class})
    private String userType;

    @JsonView(value = {GroupUserView.ApplyList.class})
    private String groupAvatarUrl;

    @JsonView(value = {GroupUserView.GroupName.class, GroupUserView.ReviewList.class, GroupUserView.FavorList.class, GroupUserView.ApplyList.class})
    private String groupName;

    @JsonView(value = {GroupUserView.ReviewList.class, GroupUserView.GroupType.class})
    private String groupType;

    @JsonView(value = {GroupUserView.ReviewList.class, GroupUserView.GroupUserDetail.class})
    private String userGender;

    @JsonView(value = {GroupUserView.CountryGroupUser.class})
    private String userTag;

    @JsonView(value = {GroupUserView.CommunityGroupUser.class})
    private int userBuilding;

    @JsonView(value = {GroupUserView.CommunityGroupUser.class})
    private int userRoom;

    @JsonView(value = {GroupUserView.SchoolGroupUser.class})
    private long userGraduateAt;//userGradeAt

    @JsonView(value = {GroupUserView.SchoolGroupUser.class, GroupUserView.ClassGroupUser.class})
    private String schoolName;

    @JsonView(value = {GroupUserView.ClassGroupUser.class})
    private String className;

    @JsonView(value = {GroupUserView.FavoriteFlag.class})
    private boolean isFav;

    @JsonSerialize(using = BaseDateTimeSerializer.class)
    @JsonView(value = {GroupUserView.ReviewList.class, GroupUserView.ApplyList.class})
    private String applyAt;

    @JsonSerialize(using = BaseDateTimeSerializer.class)
    @JsonView(value = {GroupUserView.FavorList.class})
    private String favAt;

    @JsonView(value = {GroupUserView.ReviewStatus.class})
    private String reviewStatus;

    @JsonView(value = {GroupUserView.ApplyList.class, GroupUserView.ApplyId.class})
    private long applyId;

    @JsonView({GroupUserView.JoinAt.class, GroupUserView.CommTime.class})
    @JsonSerialize(using = BaseDateTimeSerializer.class)
    private String joinAt;

    @JsonView({GroupUserView.QuiteAt.class, GroupUserView.CommTime.class})
    @JsonSerialize(using = BaseDateTimeSerializer.class)
    private String quitAt;

    @JsonView(GroupUserView.UserStatus.class)
    private String Status;

    @JsonView(GroupUserView.CommunityUserInformation.class)
    private String communityName;

    @JsonView(GroupUserView.IsMember.class)
    private boolean isMember;

    @JsonView(GroupUserView.UserApply.class)
    private boolean hasApplied;

    public static GroupUserDTO valueOf(ClassUserDO classUserDO) {
        GroupUserDTO groupUserDTO = new GroupUserDTO();

        groupUserDTO.setUserCompany(classUserDO.getCompany());
        groupUserDTO.setUserPosition(classUserDO.getPosition());
        groupUserDTO.setUserMobile(classUserDO.getMobile());
        groupUserDTO.setUserBrief(classUserDO.getBrief());
        groupUserDTO.setUserName(classUserDO.getName());
        groupUserDTO.setSchoolName(classUserDO.getSchoolName());
        groupUserDTO.setClassName(classUserDO.getClassName());
        groupUserDTO.setUserGender(classUserDO.getGender() == 1 ? GenderEnum.MAN.getStringStatus() : GenderEnum.WOMAN.getStringStatus());
        groupUserDTO.setGroupType("CLASS");
        groupUserDTO.setStatus(classUserDO.getStatus() == 1 ? UserStatusEnum.ENABLE.getStringStatus() : UserStatusEnum.DISABLE.getStringStatus());
        groupUserDTO.setJoinAt(classUserDO.getJoinedAt() > 0 ? String.valueOf(classUserDO.getJoinedAt()) : "");
        groupUserDTO.setQuitAt(classUserDO.getQuitedAt() > 0 ? String.valueOf(classUserDO.getQuitedAt()) : "");

        if (classUserDO.getClassGroupDO() != null) {
            groupUserDTO.setGroupAvatarUrl(classUserDO.getClassGroupDO().getGroupAvatarUrl());
            groupUserDTO.setGroupName(classUserDO.getClassGroupDO().getGroupName());
        }

        if (classUserDO.getUserDO() != null) {
            groupUserDTO.setUserId(classUserDO.getUserDO().getUserId());
            groupUserDTO.setUserAvatarUrl(classUserDO.getUserDO().getAvatarUrl());

            if (classUserDO.getClassGroupDO() != null && classUserDO.getClassGroupDO().getUserDO() != null) {
                groupUserDTO.setMemType(classUserDO.getUserDO().getUserId() == classUserDO.getClassGroupDO().getUserDO().getUserId() ? UserRoleEnum.ADMIN : UserRoleEnum.MEMBER);
            }
        }

        SchoolTypeEnum schoolTypeEnum = SchoolTypeEnum.intStatusToEnum(classUserDO.getType());
        if (schoolTypeEnum != null) {
            groupUserDTO.setUserType(schoolTypeEnum.toString());
        }

        return groupUserDTO;
    }

    public static GroupUserDTO valueOf(SchoolUserDO schoolUserDO) {
        GroupUserDTO groupUserDTO = new GroupUserDTO();

        groupUserDTO.setUserCompany(schoolUserDO.getCompany());
        groupUserDTO.setUserPosition(schoolUserDO.getPosition());
        groupUserDTO.setUserMobile(schoolUserDO.getMobile());
        groupUserDTO.setUserBrief(schoolUserDO.getBrief());


        groupUserDTO.setSchoolName(schoolUserDO.getSchoolName());
        groupUserDTO.setUserGraduateAt(schoolUserDO.getGraduatedAt());
        groupUserDTO.setUserName(schoolUserDO.getName());
        groupUserDTO.setUserGender(schoolUserDO.getGender() == 1 ? GenderEnum.MAN.getStringStatus() : GenderEnum.WOMAN.getStringStatus());
        groupUserDTO.setGroupType("SCHOOL");
        groupUserDTO.setStatus(schoolUserDO.getStatus() == 1 ? UserStatusEnum.ENABLE.getStringStatus() : UserStatusEnum.DISABLE.getStringStatus());
        groupUserDTO.setJoinAt(schoolUserDO.getJoinedAt() > 0 ? String.valueOf(schoolUserDO.getJoinedAt()) : "");
        groupUserDTO.setQuitAt(schoolUserDO.getQuitedAt() > 0 ? String.valueOf(schoolUserDO.getQuitedAt()) : "");

        if (schoolUserDO.getSchoolGroupDO() != null) {
            groupUserDTO.setGroupAvatarUrl(schoolUserDO.getSchoolGroupDO().getGroupAvatarUrl());
            groupUserDTO.setGroupName(schoolUserDO.getSchoolGroupDO().getGroupName());
        }

        if (schoolUserDO.getUserDO() != null) {
            groupUserDTO.setUserId(schoolUserDO.getUserDO().getUserId());
            groupUserDTO.setUserAvatarUrl(schoolUserDO.getUserDO().getAvatarUrl());

            if (schoolUserDO.getSchoolGroupDO() != null && schoolUserDO.getSchoolGroupDO().getUserDO() != null) {
                groupUserDTO.setMemType(schoolUserDO.getUserDO().getUserId() == schoolUserDO.getSchoolGroupDO().getUserDO().getUserId() ? UserRoleEnum.ADMIN : UserRoleEnum.MEMBER);
            }
        }

        ReviewStatusEnum reviewStatusEnum = ReviewStatusEnum.intStatusToEnum(schoolUserDO.getStatus());
        if (reviewStatusEnum != null) {
            groupUserDTO.setReviewStatus(reviewStatusEnum.getStringStatus());
        }

        SchoolTypeEnum schoolTypeEnum = SchoolTypeEnum.intStatusToEnum(schoolUserDO.getType());
        if (schoolTypeEnum != null) {
            groupUserDTO.setUserType(schoolTypeEnum.toString());
        }

        return groupUserDTO;
    }


    public static GroupUserDTO valueOf(CommunityUserDO communityUserDO) {
        GroupUserDTO groupUserDTO = new GroupUserDTO();

        groupUserDTO.setUserCompany(communityUserDO.getCompany());
        groupUserDTO.setUserPosition(communityUserDO.getPosition());
        groupUserDTO.setUserMobile(communityUserDO.getMobile());
        groupUserDTO.setUserBrief(communityUserDO.getBrief());
        groupUserDTO.setUserName(communityUserDO.getName());
        groupUserDTO.setStatus(communityUserDO.getStatus() == 1 ? UserStatusEnum.ENABLE.getStringStatus() : UserStatusEnum.DISABLE.getStringStatus());
        groupUserDTO.setJoinAt(communityUserDO.getJoinedAt()>0?String.valueOf(communityUserDO.getJoinedAt()):"");
        groupUserDTO.setQuitAt(communityUserDO.getQuitedAt()>0?String.valueOf(communityUserDO.getQuitedAt()):"");
        groupUserDTO.setCommunityName(communityUserDO.getCommunityName());

        groupUserDTO.setUserBuilding(communityUserDO.getBuilding());
        groupUserDTO.setUserRoom(communityUserDO.getRoom());

        groupUserDTO.setUserGender(communityUserDO.getGender() == 1 ? GenderEnum.MAN.getStringStatus() : GenderEnum.WOMAN.getStringStatus());
        groupUserDTO.setGroupType("COMMUNITY");

        if (communityUserDO.getCommunityGroupDO() != null) {
            groupUserDTO.setGroupAvatarUrl(communityUserDO.getCommunityGroupDO().getGroupAvatarUrl());
            groupUserDTO.setGroupName(communityUserDO.getCommunityGroupDO().getGroupName());
        }

        if (communityUserDO.getUserDO() != null) {
            groupUserDTO.setUserId(communityUserDO.getUserDO().getUserId());
            groupUserDTO.setUserAvatarUrl(communityUserDO.getUserDO().getAvatarUrl());

            if (communityUserDO.getCommunityGroupDO() != null && communityUserDO.getCommunityGroupDO().getUserDO() != null) {
                groupUserDTO.setMemType(communityUserDO.getUserDO().getUserId() == communityUserDO.getCommunityGroupDO().getUserDO().getUserId() ? UserRoleEnum.ADMIN : UserRoleEnum.MEMBER);
            }
        }

        ReviewStatusEnum reviewStatusEnum = ReviewStatusEnum.intStatusToEnum(communityUserDO.getStatus());
        if (reviewStatusEnum != null) {
            groupUserDTO.setReviewStatus(reviewStatusEnum.getStringStatus());
        }

        CommunityTypeEnum communityTypeEnum = CommunityTypeEnum.intStatusToEnum(communityUserDO.getType());
        if (communityTypeEnum != null) {
            groupUserDTO.setUserType(communityTypeEnum.toString());
        }

        return groupUserDTO;
    }


    public static GroupUserDTO valueOf(CountryUserDO countryUserDO) {
        GroupUserDTO groupUserDTO = new GroupUserDTO();

        groupUserDTO.setUserCompany(countryUserDO.getCompany());
        groupUserDTO.setUserPosition(countryUserDO.getPosition());
        groupUserDTO.setUserMobile(countryUserDO.getMobile());
        groupUserDTO.setUserBrief(countryUserDO.getBrief());
        groupUserDTO.setUserTag(countryUserDO.getTag());
        groupUserDTO.setUserName(countryUserDO.getName());
        groupUserDTO.setQuitAt(countryUserDO.getQuitedAt() > 0 ? String.valueOf(countryUserDO.getQuitedAt()) : "");
        groupUserDTO.setJoinAt(countryUserDO.getJoinedAt() > 0 ? String.valueOf(countryUserDO.getJoinedAt()) : "");
        groupUserDTO.setStatus(countryUserDO.getStatus() == 1 ? UserStatusEnum.ENABLE.getStringStatus() : UserStatusEnum.DISABLE.getStringStatus());

        groupUserDTO.setUserGender(countryUserDO.getGender() == 1 ? GenderEnum.MAN.getStringStatus() : GenderEnum.WOMAN.getStringStatus());
        groupUserDTO.setGroupType("COUNTRY");

        if (countryUserDO.getCountryGroupDO() != null) {
            groupUserDTO.setGroupAvatarUrl(countryUserDO.getCountryGroupDO().getGroupAvatarUrl());
            groupUserDTO.setGroupName(countryUserDO.getCountryGroupDO().getGroupName());
        }

        if (countryUserDO.getUserDO() != null) {
            groupUserDTO.setUserId(countryUserDO.getUserDO().getUserId());
//            groupUserDTO.setUserName(countryUserDO.getUserDO().getNickname());
            groupUserDTO.setUserAvatarUrl(countryUserDO.getUserDO().getAvatarUrl());

            if (countryUserDO.getCountryGroupDO() != null && countryUserDO.getCountryGroupDO().getUserDO() != null) {
                groupUserDTO.setMemType(countryUserDO.getUserDO().getUserId() == countryUserDO.getCountryGroupDO().getUserDO().getUserId() ? UserRoleEnum.ADMIN : UserRoleEnum.MEMBER);
            }
        }

        ReviewStatusEnum reviewStatusEnum = ReviewStatusEnum.intStatusToEnum(countryUserDO.getStatus());
        if (reviewStatusEnum != null) {
            groupUserDTO.setReviewStatus(reviewStatusEnum.getStringStatus());
        }

        return groupUserDTO;
    }

    public static GroupUserDTO valueOf(ClassUserApplyDO classUserApplyDO) {
        GroupUserDTO groupUserDTO = new GroupUserDTO();
        groupUserDTO.setUserName(classUserApplyDO.getName());
        groupUserDTO.setUserCompany(classUserApplyDO.getCompany());
        groupUserDTO.setUserPosition(classUserApplyDO.getPosition());
        groupUserDTO.setUserMobile(classUserApplyDO.getMobile());
        groupUserDTO.setUserBrief(classUserApplyDO.getBrief());
        groupUserDTO.setApplyId(classUserApplyDO.getIdx());
        groupUserDTO.setApplyAt(classUserApplyDO.getAppliedAt() > 0 ? String.valueOf(classUserApplyDO.getAppliedAt()) : "");
        groupUserDTO.setReviewStatus(classUserApplyDO.getStatus() != 0 ? ReviewStatusEnum.intStatusToEnum(classUserApplyDO.getStatus()).getStringStatus() : "");
        groupUserDTO.setUserGender(classUserApplyDO.getGender() == 1 ? GenderEnum.MAN.getStringStatus() : GenderEnum.WOMAN.getStringStatus());
        groupUserDTO.setGroupType("CLASS");
        groupUserDTO.setApplyId(classUserApplyDO.getIdx());

        if (classUserApplyDO.getClassGroupDO() != null) {
            groupUserDTO.setGroupAvatarUrl(classUserApplyDO.getClassGroupDO().getGroupAvatarUrl());
            groupUserDTO.setGroupName(classUserApplyDO.getClassGroupDO().getGroupName());
            groupUserDTO.setSchoolName(classUserApplyDO.getClassGroupDO().getSchoolName());
            groupUserDTO.setClassName(classUserApplyDO.getClassGroupDO().getClassName());
        }

        if (classUserApplyDO.getUserDO() != null) {
            groupUserDTO.setUserId(classUserApplyDO.getUserDO().getUserId());
            groupUserDTO.setUserAvatarUrl(classUserApplyDO.getUserDO().getAvatarUrl());
        }

        SchoolTypeEnum schoolTypeEnum = SchoolTypeEnum.intStatusToEnum(classUserApplyDO.getType());
        if (schoolTypeEnum != null) {
            groupUserDTO.setUserType(schoolTypeEnum.toString());
        }
        return groupUserDTO;
    }

    public static GroupUserDTO valueOf(CommunityUserApplyDO communityUserApplyDO) {
        GroupUserDTO groupUserDTO = new GroupUserDTO();
        groupUserDTO.setUserName(communityUserApplyDO.getName());
        groupUserDTO.setUserCompany(communityUserApplyDO.getCompany());
        groupUserDTO.setUserPosition(communityUserApplyDO.getPosition());
        groupUserDTO.setUserMobile(communityUserApplyDO.getMobile());
        groupUserDTO.setUserBrief(communityUserApplyDO.getBrief());
        groupUserDTO.setUserBuilding(communityUserApplyDO.getBuilding());
        groupUserDTO.setUserRoom(communityUserApplyDO.getRoom());
        groupUserDTO.setApplyId(communityUserApplyDO.getIdx());
        groupUserDTO.setApplyAt(communityUserApplyDO.getAppliedAt() > 0 ? String.valueOf(communityUserApplyDO.getAppliedAt()) : "");
        groupUserDTO.setReviewStatus(communityUserApplyDO.getStatus() != 0 ? ReviewStatusEnum.intStatusToEnum(communityUserApplyDO.getStatus()).getStringStatus() : "");
        groupUserDTO.setUserGender(communityUserApplyDO.getGender() == 1 ? GenderEnum.MAN.getStringStatus() : GenderEnum.WOMAN.getStringStatus());
        groupUserDTO.setGroupType("COMMUNITY");

        if (communityUserApplyDO.getCommunityGroupDO() != null) {
            groupUserDTO.setGroupAvatarUrl(communityUserApplyDO.getCommunityGroupDO().getGroupAvatarUrl());
            groupUserDTO.setGroupName(communityUserApplyDO.getCommunityGroupDO().getGroupName());
        }

        if (communityUserApplyDO.getUserDO() != null) {
            groupUserDTO.setUserId(communityUserApplyDO.getUserDO().getUserId());
            groupUserDTO.setUserAvatarUrl(communityUserApplyDO.getUserDO().getAvatarUrl());

            if (communityUserApplyDO.getCommunityGroupDO() != null && communityUserApplyDO.getCommunityGroupDO().getUserDO() != null) {
                groupUserDTO.setMemType(communityUserApplyDO.getUserDO().getUserId() == communityUserApplyDO.getCommunityGroupDO().getUserDO().getUserId() ? UserRoleEnum.ADMIN : UserRoleEnum.MEMBER);
            }
        }

        CommunityTypeEnum communityTypeEnum = CommunityTypeEnum.intStatusToEnum(communityUserApplyDO.getType());
        if (communityTypeEnum != null) {
            groupUserDTO.setUserType(communityTypeEnum.toString());
        }
        return groupUserDTO;
    }


    public static GroupUserDTO valueOf(CountryUserApplyDO countryUserApplyDO) {
        GroupUserDTO groupUserDTO = new GroupUserDTO();
        groupUserDTO.setUserName(countryUserApplyDO.getName());
        groupUserDTO.setUserCompany(countryUserApplyDO.getCompany());
        groupUserDTO.setUserPosition(countryUserApplyDO.getPosition());
        groupUserDTO.setUserMobile(countryUserApplyDO.getMobile());
        groupUserDTO.setUserBrief(countryUserApplyDO.getBrief());
        groupUserDTO.setUserTag(countryUserApplyDO.getTag());
        groupUserDTO.setApplyId(countryUserApplyDO.getIdx());
        groupUserDTO.setApplyAt(countryUserApplyDO.getAppliedAt() > 0 ? String.valueOf(countryUserApplyDO.getAppliedAt()) : "");
        groupUserDTO.setReviewStatus(countryUserApplyDO.getStatus() != 0 ? ReviewStatusEnum.intStatusToEnum(countryUserApplyDO.getStatus()).getStringStatus() : "");
        groupUserDTO.setUserGender(countryUserApplyDO.getGender() == 1 ? GenderEnum.MAN.getStringStatus() : GenderEnum.WOMAN.getStringStatus());
        groupUserDTO.setGroupType("COUNTRY");

        if (countryUserApplyDO.getCountryGroupDO() != null) {
            groupUserDTO.setGroupAvatarUrl(countryUserApplyDO.getCountryGroupDO().getGroupAvatarUrl());
            groupUserDTO.setGroupName(countryUserApplyDO.getCountryGroupDO().getGroupName());
        }

        if (countryUserApplyDO.getUserDO() != null) {
            groupUserDTO.setUserId(countryUserApplyDO.getUserDO().getUserId());
            groupUserDTO.setUserAvatarUrl(countryUserApplyDO.getUserDO().getAvatarUrl());
            if (countryUserApplyDO.getCountryGroupDO() != null && countryUserApplyDO.getCountryGroupDO().getUserDO() != null) {
                groupUserDTO.setMemType(countryUserApplyDO.getUserDO().getUserId() == countryUserApplyDO.getCountryGroupDO().getUserDO().getUserId() ? UserRoleEnum.ADMIN : UserRoleEnum.MEMBER);
            }
        }
        return groupUserDTO;
    }

    public static GroupUserDTO valueOf(SchoolUserApplyDO schoolUserApplyDO) {
        GroupUserDTO groupUserDTO = new GroupUserDTO();
        groupUserDTO.setUserName(schoolUserApplyDO.getName());
        groupUserDTO.setUserCompany(schoolUserApplyDO.getCompany());
        groupUserDTO.setUserPosition(schoolUserApplyDO.getPosition());
        groupUserDTO.setUserMobile(schoolUserApplyDO.getMobile());
        groupUserDTO.setUserBrief(schoolUserApplyDO.getBrief());
        groupUserDTO.setUserGraduateAt(schoolUserApplyDO.getGraduatedAt());
        groupUserDTO.setApplyId(schoolUserApplyDO.getIdx());
        groupUserDTO.setApplyAt(schoolUserApplyDO.getAppliedAt() > 0 ? String.valueOf(schoolUserApplyDO.getAppliedAt()) : "");
        groupUserDTO.setReviewStatus(schoolUserApplyDO.getStatus() != 0 ? ReviewStatusEnum.intStatusToEnum(schoolUserApplyDO.getStatus()).getStringStatus() : "");
        groupUserDTO.setUserGender(schoolUserApplyDO.getGender() == 1 ? GenderEnum.MAN.getStringStatus() : GenderEnum.WOMAN.getStringStatus());
        groupUserDTO.setGroupType("SCHOOL");

        if (schoolUserApplyDO.getSchoolGroupDO() != null) {
            groupUserDTO.setGroupAvatarUrl(schoolUserApplyDO.getSchoolGroupDO().getGroupAvatarUrl());
            groupUserDTO.setGroupName(schoolUserApplyDO.getSchoolGroupDO().getGroupName());
            groupUserDTO.setSchoolName(schoolUserApplyDO.getSchoolGroupDO().getSchoolName());
        }

        if (schoolUserApplyDO.getUserDO() != null) {
            groupUserDTO.setUserId(schoolUserApplyDO.getUserDO().getUserId());
            groupUserDTO.setUserAvatarUrl(schoolUserApplyDO.getUserDO().getAvatarUrl());
            if (schoolUserApplyDO.getSchoolGroupDO() != null && schoolUserApplyDO.getSchoolGroupDO().getUserDO() != null) {
                groupUserDTO.setMemType(schoolUserApplyDO.getUserDO().getUserId() == schoolUserApplyDO.getSchoolGroupDO().getUserDO().getUserId() ? UserRoleEnum.ADMIN : UserRoleEnum.MEMBER);
            }
        }

        SchoolTypeEnum schoolTypeEnum = SchoolTypeEnum.intStatusToEnum(schoolUserApplyDO.getType());
        if (schoolTypeEnum != null) {
            groupUserDTO.setUserType(schoolTypeEnum.toString());
        }
        return groupUserDTO;
    }

    public static GroupUserDTO valueOf(SchoolUserFavDO schoolUserFavDO) {
        GroupUserDTO groupUserDTO = GroupUserDTO.valueOf(schoolUserFavDO.getSchoolUserDO());
        if (schoolUserFavDO.getSchoolUserDO() != null) {
            if (schoolUserFavDO.getSchoolUserDO().getSchoolGroupDO() != null) {
                groupUserDTO.setGroupId(schoolUserFavDO.getSchoolUserDO().getSchoolGroupDO().getGroupId());
            }
        }
        groupUserDTO.setFavAt(String.valueOf(schoolUserFavDO.getCreatedAt()));
        return groupUserDTO;
    }

    public static GroupUserDTO valueOf(ClassUserFavDO classUserFavDO) {
        GroupUserDTO groupUserDTO = GroupUserDTO.valueOf(classUserFavDO.getClassUserDO());
        if (classUserFavDO.getClassUserDO() != null) {
            if (classUserFavDO.getClassUserDO().getClassGroupDO() != null) {
                groupUserDTO.setGroupId(classUserFavDO.getClassUserDO().getClassGroupDO().getGroupId());
            }
        }
        groupUserDTO.setFavAt(String.valueOf(classUserFavDO.getCreatedAt()));
        return groupUserDTO;
    }

    public static GroupUserDTO valueOf(CountryUserFavDO countryUserFavDO) {
        GroupUserDTO groupUserDTO = GroupUserDTO.valueOf(countryUserFavDO.getCountryUserDO());
        if (countryUserFavDO.getCountryUserDO() != null) {
            if (countryUserFavDO.getCountryUserDO().getCountryGroupDO() != null) {
                groupUserDTO.setGroupId(countryUserFavDO.getCountryUserDO().getCountryGroupDO().getGroupId());
            }
        }
        groupUserDTO.setFavAt(String.valueOf(countryUserFavDO.getCreatedAt()));
        return groupUserDTO;
    }

    public static GroupUserDTO valueOf(CommunityUserFavDO communityUserFavDO) {
        GroupUserDTO groupUserDTO = GroupUserDTO.valueOf(communityUserFavDO.getCommunityUserDO());
        if (communityUserFavDO.getCommunityUserDO() != null) {
            if (communityUserFavDO.getCommunityUserDO().getCommunityGroupDO() != null) {
                groupUserDTO.setGroupId(communityUserFavDO.getCommunityUserDO().getCommunityGroupDO().getGroupId());
            }
        }
        groupUserDTO.setFavAt(String.valueOf(communityUserFavDO.getCreatedAt()));
        return groupUserDTO;
    }
}
