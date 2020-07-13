package com.maikoo.businessdirectory.model.view;

public interface GroupUserView {

    public static interface RoleType {
    }

    public static interface GroupUserType {
    }

    public static interface FavoriteFlag {
    }

    public static interface ReviewStatus {
    }

    public static interface SchoolGroupUser {
    }

    public static interface ClassGroupUser {
    }

    public static interface CommunityGroupUser {
    }

    public static interface CountryGroupUser {
    }

    public static interface InformationPublic {
    }

    public static interface  GroupUserDetail {
    }

    public static interface UserName {
    }

    public static interface GroupName{
    }

    public static interface GroupType{
    }

    public static interface  GoupCommon{
    }

    public static interface ApplyId{
    }
    public static interface  UserStatus{}

    public static interface QuiteAt{
    }
    public static interface CommTime{
    }

    public static interface JoinAt{
    }

    public static interface IsMember{
    }


    /**
     * 群成员模块
     * 1. 群成员列表
     * 2. 群成员详情：个人详情、群成员详情（收藏标记）
     */
    public static interface List extends RoleType,UserName {
    }

    public static interface BaseDetailList extends List {
    }

    public static interface CommunityDetailList extends List, CommunityGroupUser{
    }

    public static interface SchoolInformation extends InformationPublic, SchoolGroupUser, RoleType, GroupUserType,GroupUserDetail ,GroupName,GroupType{
    }

    public static interface ClassInformation extends InformationPublic, ClassGroupUser, RoleType, GroupUserType,GroupUserDetail,GroupName,GroupType {
    }

    public static interface CommunityInformation extends InformationPublic, CommunityGroupUser, RoleType, GroupUserType ,GroupUserDetail,GroupName,GroupType {
    }

    public static interface CountryInformation extends InformationPublic, CountryGroupUser, RoleType, GroupUserType ,GroupUserDetail,GroupName,GroupType{
    }

    public static interface SchoolInformationAndFavor extends SchoolInformation, FavoriteFlag {
    }

    public static interface ClassInformationAndFavor extends ClassInformation, FavoriteFlag {
    }

    public static interface CommunityInformationAndFavor extends CommunityInformation, FavoriteFlag {
    }

    public static interface CountryInformationAndFavor extends CountryInformation, FavoriteFlag {
    }

    /**
     * 后台群成员详情
     */
    public static interface SchoolUserInformation extends InformationPublic, SchoolGroupUser, RoleType, GroupUserType,GroupUserDetail ,UserStatus,CommTime{
    }

    public static interface ClassUserInformation extends InformationPublic, ClassGroupUser, RoleType, GroupUserType,GroupUserDetail ,UserStatus,CommTime{
    }

    public static interface CommunityUserInformation extends InformationPublic, CommunityGroupUser, RoleType, GroupUserType ,GroupUserDetail,UserStatus ,CommTime{
    }

    public static interface CountryUserInformation extends InformationPublic, CountryGroupUser, RoleType ,GroupUserDetail,UserStatus,CommTime{
    }

    /**
     * 审核模块
     */
    public static interface ReviewList extends ReviewStatus,ApplyId {
    }

    public static interface SchoolReviewInformation extends InformationPublic, SchoolGroupUser, GroupUserType, ReviewStatus,ReviewList,GroupName{
    }

    public static interface ClassReviewInformation extends InformationPublic, ClassGroupUser, GroupUserType, ReviewStatus,ReviewList ,GroupName{
    }

    public static interface CommunityReviewInformation extends InformationPublic, CommunityGroupUser, GroupUserType, ReviewStatus ,ReviewList,GroupName{
    }

    public static interface CountryReviewInformation extends InformationPublic, CountryGroupUser, ReviewStatus ,ReviewList,GroupName{
    }

    /**
     * 收藏模块
     */
    public static interface FavorList {
    }

    /**
     * 申请模块
     */
    public static interface ApplyList extends ReviewStatus {
    }

    public static interface SchoolApplyInformation extends InformationPublic, SchoolGroupUser, GroupUserType, ReviewStatus ,ReviewList,GroupName{
    }

    public static interface ClassApplyInformation extends InformationPublic, ClassGroupUser, GroupUserType, ReviewStatus ,ReviewList,GroupName{
    }

    public static interface CommunityApplyInformation extends InformationPublic, CommunityGroupUser, GroupUserType, ReviewStatus ,ReviewList,GroupName{
    }

    public static interface CountryApplyInformation extends InformationPublic, CountryGroupUser, GroupUserType, ReviewStatus ,ReviewList,GroupName{
    }

    /**
     * 用户是否申请
     */
    public static  interface UserApply extends ApplyId{
    }
}
